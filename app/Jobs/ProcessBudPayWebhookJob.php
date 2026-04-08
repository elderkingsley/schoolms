<?php

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
        $data      = $this->payload['data'] ?? [];
        $reference = $data['reference']     ?? null;
        $amountNgn = isset($data['amount'])  ? (float) $data['amount'] : 0;
        // BudPay sends amount in Naira directly (not kobo) for dedicated NUBAN
        // Verify: if amount looks like kobo (> 100000 for typical school fees), divide by 100
        // Based on webhook sample in docs: "amount": "5.22" — this is Naira
        $senderName    = $data['customer']['first_name'] ?? 'Unknown Sender';
        $depositId     = $data['id'] ?? null;

        // Extract account number from metadata or dedicated_account field
        $accountNumber = $data['dedicated_account']['account_number']
            ?? $data['metadata']['dedicated_account_number']
            ?? null;

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
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId);
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProcessBudPayWebhookJob: parent {$parent->id} has no linked student");
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId);
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
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId);
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

        // ── Notify PayGrid ────────────────────────────────────────────────
        $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId);

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
        ?string $depositId,
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
                    'deposit_id'     => $depositId,
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
}
