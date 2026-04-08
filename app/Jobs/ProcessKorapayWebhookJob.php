<?php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\ParentGuardian;
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
 * ProcessKorapayWebhookJob
 *
 * Processes a confirmed charge.success webhook from Korapay for a
 * dedicated virtual bank account payment.
 *
 * Korapay webhook payload (relevant fields):
 *   data.amount                                                  — NGN amount (not kobo)
 *   data.reference                                               — Korapay transaction reference
 *   data.virtual_bank_account_details.virtual_bank_account
 *     .account_reference                                         — our reference (NV-P{id}-S{id})
 *     .account_number                                            — the NUBAN paid into
 *   data.virtual_bank_account_details.payer_bank_account
 *     .account_name                                              — sender name
 *
 * The job:
 *   1. Finds the parent by korapay_account_reference
 *   2. Records payment against unpaid invoices (FIFO) via FeeService
 *   3. Notifies PayGrid via POST /api/inflows
 *   4. Emails the parent a payment confirmation
 */
class ProcessKorapayWebhookJob implements ShouldQueue
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
        $data      = $this->payload['data'] ?? [];
        $reference = $data['reference']     ?? null;
        $amountNgn = (float) ($data['amount'] ?? 0);

        $vbaDetails    = $data['virtual_bank_account_details'] ?? [];
        $vba           = $vbaDetails['virtual_bank_account']   ?? [];
        $payerAccount  = $vbaDetails['payer_bank_account']     ?? [];

        $accountReference = $vba['account_reference'] ?? null;
        $accountNumber    = $vba['account_number']    ?? null;
        $senderName       = $payerAccount['account_name'] ?? 'Unknown Sender';

        if (! $reference || $amountNgn <= 0 || ! $accountReference) {
            Log::error('ProcessKorapayWebhookJob: missing required fields', [
                'reference'         => $reference,
                'amount'            => $amountNgn,
                'account_reference' => $accountReference,
                'log_id'            => $this->logId,
            ]);
            return;
        }

        // ── Idempotency ───────────────────────────────────────────────────
        if (FeePayment::where('reference', $reference)->exists()) {
            Log::info("ProcessKorapayWebhookJob: duplicate reference '{$reference}' — skipping.");
            return;
        }

        // ── Find parent by Korapay account reference ──────────────────────
        $parent = ParentGuardian::where('korapay_account_reference', $accountReference)
            ->with(['user', 'students'])
            ->first();

        if (! $parent || ! $parent->user) {
            Log::warning("ProcessKorapayWebhookJob: no parent found for reference {$accountReference}", [
                'reference' => $reference,
            ]);
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber ?? $accountReference, $senderName);
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProcessKorapayWebhookJob: parent {$parent->id} has no linked student");
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber ?? $accountReference, $senderName);
            return;
        }

        // ── Find unpaid invoices FIFO ─────────────────────────────────────
        $invoices = FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session'])
            ->orderBy('id', 'asc')
            ->get();

        if ($invoices->isEmpty()) {
            Log::info("ProcessKorapayWebhookJob: ₦{$amountNgn} received for {$student->full_name} but no unpaid invoices", [
                'reference' => $reference,
            ]);
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber ?? $accountReference, $senderName);
            return;
        }

        // ── Resolve system actor ──────────────────────────────────────────
        $systemActorId = \App\Models\User::where('user_type', 'super_admin')
            ->orWhere('user_type', 'admin')
            ->orderBy('id')
            ->value('id');

        // ── Apply payment FIFO ────────────────────────────────────────────
        $remaining       = $amountNgn;
        $settledInvoices = [];

        DB::transaction(function () use (
            $invoices, &$remaining, &$settledInvoices,
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
                    method:     'Korapay Transfer',
                    reference:  $reference,
                    recordedBy: $systemActorId,
                    source:     'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[] = $invoice->fresh();

                Log::info("ProcessKorapayWebhookJob: ₦{$toApply} applied to invoice {$invoice->id}", [
                    'student'   => $student->id,
                    'reference' => $reference,
                ]);
            }
        });

        // ── Notify PayGrid ────────────────────────────────────────────────
        $this->notifyPayGrid($amountNgn, $reference, $accountNumber ?? $accountReference, $senderName);

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
                Log::warning("ProcessKorapayWebhookJob: email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        DB::table('korapay_webhook_events')->where('id', $this->logId)->update([
            'processed_at' => now(),
            'updated_at'   => now(),
        ]);

        Log::info("ProcessKorapayWebhookJob: ₦{$amountNgn} fully processed for student {$student->full_name}", [
            'reference'        => $reference,
            'invoices_settled' => count($settledInvoices),
        ]);
    }

    private function notifyPayGrid(
        float   $amountNgn,
        string  $reference,
        string  $accountNumber,
        string  $senderName,
    ): void {
        $url    = config('services.paygrid.api_base_url', '');
        $apiKey = config('services.paygrid.api_key', '');

        if (empty($url) || empty($apiKey)) {
            Log::warning('ProcessKorapayWebhookJob: PayGrid credentials not set — skipping');
            return;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ])
                ->post(rtrim($url, '/') . '/api/inflows', [
                    'reference'      => $reference,
                    'amount_ngn'     => $amountNgn,
                    'account_number' => $accountNumber,
                    'sender_name'    => $senderName,
                    'deposited_at'   => now()->toISOString(),
                    'source'         => 'schoolms',
                ]);

            if ($response->successful()) {
                Log::info("ProcessKorapayWebhookJob: PayGrid notified for ref {$reference}");
            } else {
                Log::warning("ProcessKorapayWebhookJob: PayGrid notification failed", [
                    'status' => $response->status(),
                    'ref'    => $reference,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("ProcessKorapayWebhookJob: PayGrid exception: {$e->getMessage()}");
        }
    }
}
