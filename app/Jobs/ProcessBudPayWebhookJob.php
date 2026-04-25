<?php
// Deploy to: app/Jobs/ProcessBudPayWebhookJob.php

namespace App\Jobs;

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
 * ProcessBudPayWebhookJob
 *
 * Processes a confirmed dedicated_nuban payment from BudPay.
 *
 * Mirrors ProcessJuicyWayPaymentJob but reads from BudPay's webhook structure:
 *   data.amount          — amount in the smallest unit (kobo)
 *   data.reference       — unique transaction reference
 *   data.metadata        — contains the dedicated account number
 *   data.customer        — customer code (CUS_xxx)
 *
 * The job:
 *   1. Extracts the account number from the webhook payload
 *   2. Finds the matching parent by budpay_account_number
 *   3. Records payment against the student's invoice(s) via FeeService (FIFO)
 *   4. Notifies PayGrid via POST /api/inflows (with invoice_id(s) for strong matching)
 *   5. Emails the parent a payment confirmation
 */
class ProcessBudPayWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 55;
    public array $backoff = [30, 60, 120];

    public function __construct(
        private readonly array  $payload,
        private readonly string $logId,
    ) {}

    public function handle(FeeService $feeService): void
    {
        $data      = $this->payload['data']            ?? [];
        $reference = $data['reference']                ?? null;
        // Use gross amount from transferDetails — what the parent actually paid.
        // data.amount is net after BudPay deducts its processing fee.
        $amountNgn = (float) ($this->payload['transferDetails']['amount'] ?? $data['amount'] ?? 0);

        // Real BudPay webhook puts account number in transferDetails.craccount
        $transferDetails = $this->payload['transferDetails'] ?? [];
        $accountNumber   = (string) ($transferDetails['craccount']      ?? '');
        $senderName      = $transferDetails['originatorname']           ?? 'Unknown Sender';

        // Fallback: older payload shape used data.dedicated_account
        if (empty($accountNumber)) {
            $accountNumber = (string) ($data['dedicated_account']['account_number']
                ?? $data['metadata']['dedicated_account_number']
                ?? '');
        }

        if (! $reference || $amountNgn <= 0 || ! $accountNumber) {
            Log::error('ProcessBudPayWebhookJob: missing required fields', [
                'reference'     => $reference,
                'amount'        => $amountNgn,
                'account'       => $accountNumber,
                'log_id'        => $this->logId,
            ]);
            return;
        }

        // ── Idempotency ───────────────────────────────────────────────────
        if (FeePayment::where('reference', $reference)->exists()) {
            Log::info("ProcessBudPayWebhookJob: duplicate reference '{$reference}' — skipping.");
            return;
        }

        // ── Find parent by BudPay account number ──────────────────────────
        $parent = ParentGuardian::where('budpay_account_number', $accountNumber)
            ->with(['user', 'students'])
            ->first();

        if (! $parent || ! $parent->user) {
            Log::warning("ProcessBudPayWebhookJob: no parent found for account {$accountNumber}", [
                'reference' => $reference,
            ]);
            // Still notify PayGrid so the inflow appears in the ledger
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, null, []);
            $this->forwardToPayGrid();
            return;
        }

        $studentIds = $parent->students->pluck('id');
        if ($studentIds->isEmpty()) {
            Log::warning("ProcessBudPayWebhookJob: parent {$parent->id} has no linked students");
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, null, []);
            $this->forwardToPayGrid();
            return;
        }

        // ── Find unpaid invoices across ALL children FIFO ─────────────────
        $invoices = \App\Models\FeeInvoice::whereIn('student_id', $studentIds)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session', 'student'])
            ->orderBy('id', 'asc')
            ->get();

        $settledInvoiceIds = [];

        if ($invoices->isEmpty()) {
            Log::info("ProcessBudPayWebhookJob: ₦{$amountNgn} received for parent {$parent->id} but no unpaid invoices across any child", [
                'account'     => $accountNumber,
                'reference'   => $reference,
                'student_ids' => $studentIds->toArray(),
            ]);
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, null, []);
            $this->forwardToPayGrid();
            return;
        }

        // ── Resolve system actor ──────────────────────────────────────────
        $systemActorId = \App\Models\User::where('user_type', 'super_admin')
            ->orWhere('user_type', 'admin')
            ->orderBy('id')
            ->value('id');

        // ── Apply payment across all children's invoices FIFO ─────────────
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

                $feeService->recordPayment(
                    invoice:    $invoice,
                    amount:     $toApply,
                    method:     'BudPay Transfer',
                    reference:  $reference,
                    recordedBy: $systemActorId,
                    source:     'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[]   = $invoice->fresh();
                $settledInvoiceIds[] = (string) $invoice->id;

                Log::info("ProcessBudPayWebhookJob: ₦{$toApply} applied to invoice {$invoice->id}", [
                    'student'   => $invoice->student_id,
                    'reference' => $reference,
                ]);
            }
        });

        // ── Notify PayGrid — one call per settled invoice ─────────────────
        foreach ($settledInvoices as $settledInvoice) {
            $payment = $settledInvoice->payments()
                ->where('reference', $reference)
                ->first();
            if ($payment) {
                $this->notifyPayGrid(
                    (float) $payment->amount,
                    $reference . '-inv-' . $settledInvoice->id,
                    $accountNumber,
                    $senderName,
                    (string) $settledInvoice->id,
                    [(string) $settledInvoice->id]
                );
            }
        }

        // ── Forward raw BudPay payload to PayGrid webhook ─────────────────
        $this->forwardToPayGrid();

        // ── Email the parent ──────────────────────────────────────────────
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
                Log::warning("ProcessBudPayWebhookJob: email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        // ── Update webhook log ────────────────────────────────────────────
        DB::table('budpay_webhook_events')->where('id', $this->logId)->update([
            'processed_at' => now(),
            'updated_at'   => now(),
        ]);

        Log::info("ProcessBudPayWebhookJob: ₦{$amountNgn} fully processed for parent {$parent->id}", [
            'account'          => $accountNumber,
            'reference'        => $reference,
            'invoices_settled' => count($settledInvoices),
            'invoice_ids'      => $settledInvoiceIds,
        ]);
    }

    /**
     * Notify PayGrid that a student fee payment has been received.
     * PayGrid will post a journal entry to Nurtureville's ledger (DR 1110 / CR 2199).
     *
     * Fire-and-forget: failure is logged but never affects SchoolMS payment recording.
     * PayGrid uses the same `reference` as its idempotency key, so safe to retry.
     *
     * @param float       $amountNgn
     * @param string      $reference
     * @param string      $accountNumber
     * @param string      $senderName
     * @param string|null $invoiceId      Primary SchoolMS invoice ID that was settled
     * @param array       $invoiceIds     All SchoolMS invoice IDs that were settled
     */
    private function notifyPayGrid(
        float   $amountNgn,
        string  $reference,
        string  $accountNumber,
        string  $senderName,
        ?string $invoiceId = null,
        array   $invoiceIds = []
    ): void {
        $url    = config('services.paygrid.api_base_url', '');
        $apiKey = config('services.paygrid.api_key', '');

        if (empty($url) || empty($apiKey)) {
            Log::warning('ProcessBudPayWebhookJob: PAYGRID credentials not set — skipping PayGrid notification');
            return;
        }

        $payload = [
            'reference'      => $reference,
            'amount_ngn'     => $amountNgn,
            'account_number' => $accountNumber,
            'sender_name'    => $senderName,
            'deposited_at'   => now()->toISOString(),
            'source'         => 'schoolms',
        ];

        // Add invoice ID(s) for stronger matching in PayGrid
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
                Log::info("ProcessBudPayWebhookJob: PayGrid notified for ref {$reference}", [
                    'invoice_id' => $invoiceId,
                ]);
            } else {
                Log::warning("ProcessBudPayWebhookJob: PayGrid notification failed", [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("ProcessBudPayWebhookJob: PayGrid notification exception: {$e->getMessage()}");
        }
    }

    /**
     * Forward the raw BudPay webhook payload to PayGrid's own BudPay webhook endpoint.
     *
     * PayGrid has other organisations using BudPay virtual accounts that are
     * completely independent of SchoolMS. By forwarding the raw payload, PayGrid
     * can process those payments through its own webhook handler — exactly as if
     * BudPay had posted directly to it.
     *
     * This is signed with an HMAC-SHA256 signature so PayGrid can verify the
     * request came from SchoolMS and not an impostor.
     */
    private function forwardToPayGrid(): void
    {
        $url    = config('services.paygrid.webhook_url', '');
        $secret = config('services.paygrid.webhook_secret', '');

        if (empty($url)) {
            Log::warning('ProcessBudPayWebhookJob: PAYGRID_WEBHOOK_URL not set — skipping raw forward');
            return;
        }

        try {
            $rawPayload = json_encode($this->payload);
            $signature  = hash_hmac('sha256', $rawPayload, $secret);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type'        => 'application/json',
                    // Match the header names PayGrid's BudPayWebhookController checks
                    'X-Webhook-Signature' => $signature,
                    'payloadsignature'    => $signature,
                ])
                ->post($url, $this->payload);

            if ($response->successful()) {
                Log::info('ProcessBudPayWebhookJob: raw payload forwarded to PayGrid webhook successfully');
            } else {
                Log::warning('ProcessBudPayWebhookJob: PayGrid webhook forward failed', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('ProcessBudPayWebhookJob: PayGrid webhook forward exception: ' . $e->getMessage());
        }
    }
}
