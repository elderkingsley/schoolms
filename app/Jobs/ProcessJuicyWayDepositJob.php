<?php
// Deploy to: /var/www/schoolms/app/Jobs/ProcessJuicyWayDepositJob.php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\ParentGuardian;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Services\FeeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ProcessJuicyWayDepositJob
 *
 * Handles the `deposit.received` webhook event from JuicyWay.
 * Fired when a parent makes a bank transfer to their assigned NUBAN.
 *
 * Reference strategy (IMPORTANT):
 *   A single deposit may be split across multiple children's invoices.
 *   fee_payments.reference has a UNIQUE constraint, so each allocation
 *   gets a suffixed reference: "{depositId}-inv-{invoiceId}".
 *   The idempotency check uses LIKE "{depositId}%" to catch all splits.
 */
class ProcessJuicyWayDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public int   $timeout = 55;
    public array $backoff  = [30, 60, 120];

    public function __construct(
        private readonly array  $payload,
        private readonly string $logId,
    ) {}

    public function handle(FeeService $feeService): void
    {
        $deposit = $this->payload['data'] ?? [];

        $accountNumber = $deposit['beneficiary']['account_number']      ?? null;
        $amountKobo    = (int) ($deposit['amount']                       ?? 0);
        $amountNgn     = $amountKobo / 100;
        $reference     = $deposit['id']                                  ?? null;
        $senderName    = $deposit['sender']['details']['account_name']   ?? 'Unknown Sender';
        $depositId     = $deposit['id']                                  ?? null;
        $depositedAt   = $deposit['occurred_at']                         ?? now()->toISOString();

        if (! $accountNumber || $amountNgn <= 0 || ! $reference) {
            Log::warning('ProcessJuicyWayDepositJob: missing required fields', [
                'account'   => $accountNumber,
                'amount'    => $amountNgn,
                'reference' => $reference,
                'log_id'    => $this->logId,
            ]);
            return;
        }

        // Idempotency: check for base reference OR any suffixed split record
        if (FeePayment::where('reference', 'like', $reference . '%')->exists()) {
            Log::info("ProcessJuicyWayDepositJob: duplicate reference '{$reference}' — already recorded.");
            return;
        }

        $parent = ParentGuardian::where('juicyway_account_number', $accountNumber)
            ->with(['user', 'students'])
            ->first();

        if (! $parent || ! $parent->user) {
            Log::warning("ProcessJuicyWayDepositJob: no parent found for account {$accountNumber}", [
                'reference' => $reference,
            ]);
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt, null, []);
            return;
        }

        $studentIds = $parent->students->pluck('id');
        if ($studentIds->isEmpty()) {
            Log::warning("ProcessJuicyWayDepositJob: parent {$parent->id} has no linked students");
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt, null, []);
            return;
        }

        $invoices = FeeInvoice::whereIn('student_id', $studentIds)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session', 'student'])
            ->orderBy('id', 'asc')
            ->get();

        $settledInvoiceIds = [];

        if ($invoices->isEmpty()) {
            Log::info("ProcessJuicyWayDepositJob: NGN{$amountNgn} received for parent {$parent->id} but no unpaid invoices", [
                'account'     => $accountNumber,
                'reference'   => $reference,
                'student_ids' => $studentIds->toArray(),
            ]);
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt, null, []);
            $this->markProcessed();
            return;
        }

        $systemActorId = User::whereIn('user_type', ['super_admin', 'admin'])
            ->orderBy('id')
            ->value('id');

        $remaining       = $amountNgn;
        $settledInvoices = [];

        DB::transaction(function () use (
            $invoices, &$remaining, &$settledInvoices, &$settledInvoiceIds,
            $reference, $feeService, $systemActorId
        ) {
            foreach ($invoices as $invoice) {
                if ($remaining <= 0) break;

                $balance = (float) (string) $invoice->balance;
                if ($balance <= 0) continue;

                $isLast  = $invoices->last()->id === $invoice->id;
                $toApply = (float) (string) (($isLast && $remaining > $balance) ? $remaining : min($remaining, $balance));

                // Unique reference per invoice — prevents UNIQUE constraint
                // violation when a deposit is split across multiple siblings
                $invoiceRef = $reference . '-inv-' . $invoice->id;

                $feeService->recordPayment(
                    invoice:    $invoice,
                    amount:     $toApply,
                    method:     'JuicyWay Transfer',
                    reference:  $invoiceRef,
                    recordedBy: $systemActorId,
                    source:     'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[]   = $invoice->fresh();
                $settledInvoiceIds[] = (string) $invoice->id;

                Log::info("ProcessJuicyWayDepositJob: NGN{$toApply} applied to invoice {$invoice->id}", [
                    'student'   => $invoice->student_id,
                    'reference' => $invoiceRef,
                ]);
            }
        });

        // One PayGrid notification per settled invoice
        foreach ($settledInvoices as $settledInvoice) {
            $invoiceRef = $reference . '-inv-' . $settledInvoice->id;
            $payment    = $settledInvoice->payments()->where('reference', $invoiceRef)->first();
            if ($payment) {
                $this->notifyPayGrid(
                    (float) $payment->amount,
                    $invoiceRef,
                    $accountNumber,
                    $senderName,
                    $depositId,
                    $depositedAt,
                    (string) $settledInvoice->id,
                    [(string) $settledInvoice->id]
                );
            }
        }

        if ($parent->user && ! empty($settledInvoices)) {
            $firstStudent = $settledInvoices[0]->student ?? $parent->students->first();
            try {
                $parent->user->notify(new PaymentReceivedNotification(
                    student:         $firstStudent,
                    amountPaid:      $amountNgn,
                    senderName:      $senderName,
                    reference:       $reference,
                    settledInvoices: $settledInvoices,
                ));
            } catch (\Throwable $e) {
                Log::warning("ProcessJuicyWayDepositJob: email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        $this->markProcessed();

        Log::info("ProcessJuicyWayDepositJob: NGN{$amountNgn} fully processed for parent {$parent->id}", [
            'account'          => $accountNumber,
            'reference'        => $reference,
            'invoices_settled' => count($settledInvoices),
            'invoice_ids'      => $settledInvoiceIds,
            'sender'           => $senderName,
        ]);
    }

    private function notifyPayGrid(
        float   $amountNgn,
        string  $reference,
        string  $accountNumber,
        string  $senderName,
        ?string $depositId,
        string  $depositedAt,
        ?string $invoiceId = null,
        array   $invoiceIds = []
    ): void {
        $url    = config('services.paygrid.api_base_url', '');
        $apiKey = config('services.paygrid.api_key', '');

        if (empty($url) || empty($apiKey)) {
            Log::warning('ProcessJuicyWayDepositJob: PAYGRID credentials not set — skipping PayGrid notification');
            return;
        }

        $payload = [
            'reference'      => $reference,
            'amount_ngn'     => $amountNgn,
            'account_number' => $accountNumber,
            'sender_name'    => $senderName,
            'deposit_id'     => $depositId,
            'deposited_at'   => $depositedAt,
            'source'         => 'schoolms',
        ];

        if ($invoiceId)          $payload['invoice_id']  = $invoiceId;
        if (! empty($invoiceIds)) $payload['invoice_ids'] = $invoiceIds;

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ])
                ->post(rtrim($url, '/') . '/api/inflows', $payload);

            if ($response->successful()) {
                Log::info("ProcessJuicyWayDepositJob: PayGrid notified for ref {$reference}", [
                    'invoice_id' => $invoiceId,
                ]);
            } else {
                Log::warning('ProcessJuicyWayDepositJob: PayGrid notification failed', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                    'ref'    => $reference,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('ProcessJuicyWayDepositJob: PayGrid notification exception: ' . $e->getMessage());
        }
    }

    private function markProcessed(): void
    {
        DB::table('juicyway_webhook_events')
            ->where('id', $this->logId)
            ->update(['processed_at' => now(), 'updated_at' => now()]);
    }

    public function failed(\Throwable $e): void
    {
        Log::critical('ProcessJuicyWayDepositJob: permanently failed — ' . $e->getMessage(), [
            'log_id' => $this->logId,
        ]);

        DB::table('juicyway_webhook_events')
            ->where('id', $this->logId)
            ->update(['processing_error' => $e->getMessage(), 'updated_at' => now()]);
    }
}
