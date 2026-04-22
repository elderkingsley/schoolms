<?php
// Deploy to: app/Jobs/ProcessJuicyWayDepositJob.php

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
 * This is the real-time path. PollJuicyWayDepositsJob is the fallback
 * safety net that runs every minute — if this job succeeds, the poll job
 * will find the reference already in fee_payments and skip it (idempotency).
 *
 * The deposit payload shape (payload['data']) mirrors the object returned
 * by GET /deposits — same fields, same structure.
 *
 * Flow:
 *   1. Extract account number and amount from payload
 *   2. Find matching parent by juicyway_account_number
 *   3. Apply payment to unpaid invoices FIFO via FeeService
 *   4. Notify PayGrid via POST /api/inflows
 *   5. Email parent a payment confirmation
 *   6. Mark webhook event as processed
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

        // ── Extract fields from deposit object ────────────────────────────
        // JuicyWay deposit.received payload mirrors GET /deposits response:
        //   data.payment_method.account_number — the credited NUBAN
        //   data.amount                        — amount in KOBO
        //   data.reference                     — unique deposit reference
        //   data.sender_name                   — originating account name
        //   data.status                        — must be 'settled'
        //   data.type                          — must be 'credit'
        $accountNumber = $deposit['payment_method']['account_number'] ?? null;
        $amountKobo    = (int) ($deposit['amount'] ?? 0);
        $amountNgn     = $amountKobo / 100;
        $reference     = $deposit['reference'] ?? null;
        $senderName    = $deposit['sender_name'] ?? 'Unknown Sender';
        $depositId     = $deposit['id'] ?? null;
        $depositedAt   = $deposit['created_at'] ?? now()->toISOString();
        $status        = $deposit['status'] ?? '';
        $type          = $deposit['type'] ?? '';

        // ── Validate required fields ──────────────────────────────────────
        if (! $accountNumber || $amountNgn <= 0 || ! $reference) {
            Log::warning('ProcessJuicyWayDepositJob: missing required fields', [
                'account'   => $accountNumber,
                'amount'    => $amountNgn,
                'reference' => $reference,
                'log_id'    => $this->logId,
            ]);
            return;
        }

        // Only process settled credit deposits
        // deposit.received may fire before settlement — if status isn't
        // 'settled' yet, we return and let the poll job catch it once settled.
        if ($status !== 'settled' || $type !== 'credit') {
            Log::info("ProcessJuicyWayDepositJob: deposit not yet settled (status={$status}, type={$type}) — poll job will catch it", [
                'reference' => $reference,
            ]);
            return;
        }

        // ── Idempotency ───────────────────────────────────────────────────
        // The poll job runs every minute and will also see this deposit.
        // Whichever runs first wins — the other finds the reference and skips.
        if (FeePayment::where('reference', $reference)->exists()) {
            Log::info("ProcessJuicyWayDepositJob: duplicate reference '{$reference}' — already recorded by poll job or previous attempt.");
            return;
        }

        // ── Find parent by NUBAN ──────────────────────────────────────────
        $parent = ParentGuardian::where('juicyway_account_number', $accountNumber)
            ->with(['user', 'students'])
            ->first();

        if (! $parent || ! $parent->user) {
            Log::warning("ProcessJuicyWayDepositJob: no parent found for account {$accountNumber}", [
                'reference' => $reference,
            ]);
            // Still notify PayGrid — the account may belong to a PayGrid org
            // that was forwarded from BudPay's fan-out. PayGrid will handle it.
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt, null, []);
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProcessJuicyWayDepositJob: parent {$parent->id} has no linked student");
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt, null, []);
            return;
        }

        // ── Find unpaid invoices FIFO ─────────────────────────────────────
        $invoices = FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session'])
            ->orderBy('id', 'asc')
            ->get();

        $settledInvoiceIds = [];

        if ($invoices->isEmpty()) {
            Log::info("ProcessJuicyWayDepositJob: ₦{$amountNgn} received for student {$student->id} but no unpaid invoices", [
                'account'   => $accountNumber,
                'reference' => $reference,
            ]);
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt, null, []);
            $this->markProcessed();
            return;
        }

        // ── Resolve system actor ──────────────────────────────────────────
        $systemActorId = User::whereIn('user_type', ['super_admin', 'admin'])
            ->orderBy('id')
            ->value('id');

        // ── Apply payment FIFO ────────────────────────────────────────────
        $remaining       = $amountNgn;
        $settledInvoices = [];

        DB::transaction(function () use (
            $invoices, &$remaining, &$settledInvoices, &$settledInvoiceIds,
            $reference, $feeService, $systemActorId, $student
        ) {
            foreach ($invoices as $invoice) {
                if ($remaining <= 0) break;

                $balance = (float) (string) $invoice->balance;
                if ($balance <= 0) continue;

                $isLast  = $invoices->last()->id === $invoice->id;
                $toApply = (float) (string) (($isLast && $remaining > $balance) ? $remaining : min($remaining, $balance));

                $feeService->recordPayment(
                    invoice:    $invoice,
                    amount:     $toApply,
                    method:     'JuicyWay Transfer',
                    reference:  $reference,
                    recordedBy: $systemActorId,
                    source:     'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[]   = $invoice->fresh();
                $settledInvoiceIds[] = (string) $invoice->id;

                Log::info("ProcessJuicyWayDepositJob: ₦{$toApply} applied to invoice {$invoice->id}", [
                    'student'   => $student->id,
                    'reference' => $reference,
                ]);
            }
        });

        // ── Notify PayGrid ────────────────────────────────────────────────
        $primaryInvoiceId = $settledInvoiceIds[0] ?? null;
        $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt, $primaryInvoiceId, $settledInvoiceIds);

        // ── Email the parent ──────────────────────────────────────────────
        if ($parent->user && ! empty($settledInvoices)) {
            try {
                $parent->user->notify(new PaymentReceivedNotification(
                    student:         $student,
                    amountPaid:      $amountNgn,
                    senderName:      $senderName,
                    reference:       $reference,
                    settledInvoices: $settledInvoices,
                ));
            } catch (\Throwable $e) {
                Log::warning("ProcessJuicyWayDepositJob: email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        // ── Mark webhook event as processed ──────────────────────────────
        $this->markProcessed();

        Log::info("ProcessJuicyWayDepositJob: ₦{$amountNgn} fully processed for student {$student->id}", [
            'account'          => $accountNumber,
            'reference'        => $reference,
            'invoices_settled' => count($settledInvoices),
            'invoice_ids'      => $settledInvoiceIds,
            'sender'           => $senderName,
        ]);
    }

    // ── Notify PayGrid ────────────────────────────────────────────────────────

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

        if ($invoiceId) {
            $payload['invoice_id'] = $invoiceId;
        }
        if (! empty($invoiceIds)) {
            $payload['invoice_ids'] = $invoiceIds;
        }

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

    // ── Helpers ───────────────────────────────────────────────────────────────

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
