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
 *   4. Notifies PayGrid via POST /api/inflows
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
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName);
            $this->forwardToPayGrid();
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProcessBudPayWebhookJob: parent {$parent->id} has no linked student");
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName);
            $this->forwardToPayGrid();
            return;
        }

        // ── Find unpaid invoices FIFO ─────────────────────────────────────
        $invoices = \App\Models\FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session'])
            ->orderBy('id', 'asc')
            ->get();

        if ($invoices->isEmpty()) {
            Log::info("ProcessBudPayWebhookJob: ₦{$amountNgn} received for student {$student->id} but no unpaid invoices", [
                'account'   => $accountNumber,
                'reference' => $reference,
            ]);
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName);
            $this->forwardToPayGrid();
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
                    method:     'BudPay Transfer',
                    reference:  $reference,
                    recordedBy: $systemActorId,
                    source:     'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[] = $invoice->fresh();

                Log::info("ProcessBudPayWebhookJob: ₦{$toApply} applied to invoice {$invoice->id}", [
                    'student'   => $student->id,
                    'reference' => $reference,
                ]);
            }
        });

        // ── Notify PayGrid (inflow reconciliation) ───────────────────────
        $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName);

        // ── Forward raw BudPay payload to PayGrid webhook ─────────────────
        // PayGrid needs the raw webhook for its own organisations that also
        // use BudPay virtual accounts — completely independent of SchoolMS.
        $this->forwardToPayGrid();

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
                Log::warning("ProcessBudPayWebhookJob: email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        // ── Update webhook log ────────────────────────────────────────────
        DB::table('budpay_webhook_events')->where('id', $this->logId)->update([
            'processed_at' => now(),
            'updated_at'   => now(),
        ]);

        Log::info("ProcessBudPayWebhookJob: ₦{$amountNgn} fully processed for student {$student->id}", [
            'account'          => $accountNumber,
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
            Log::warning('ProcessBudPayWebhookJob: PAYGRID credentials not set — skipping PayGrid notification');
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
                Log::info("ProcessBudPayWebhookJob: PayGrid notified for ref {$reference}");
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
                    'Content-Type'       => 'application/json',
                    'X-SchoolMS-Sig'     => $signature,
                    'payloadsignature'   => $signature,
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
