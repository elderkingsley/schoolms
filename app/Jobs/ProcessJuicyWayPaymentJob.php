<?php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Services\FeeService;
use App\Services\JuicyWayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessJuicyWayPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        protected array  $payload,
        protected string $logId,
    ) {}

    public function handle(FeeService $feeService, JuicyWayService $juicyWay): void
    {
        $data      = $this->payload['data'] ?? [];
        $reference = $data['reference']     ?? null;

        if (! $reference) {
            Log::warning('ProcessJuicyWayPayment: missing reference in payload');
            return;
        }

        // ── Idempotency check ─────────────────────────────────────────────
        // The same webhook may arrive up to 3 times from JuicyWay retries.
        // We use the juicyway_payment_processed flag on the invoice as the
        // idempotency gate. This is cheaper than a separate table lookup.
        $invoice = FeeInvoice::where('payment_link_reference', $reference)
            ->with('student.parents.user', 'term.session')
            ->first();

        if (! $invoice) {
            Log::warning("ProcessJuicyWayPayment: no invoice found for reference '{$reference}'");
            return;
        }

        if ($invoice->juicyway_payment_processed) {
            Log::info("ProcessJuicyWayPayment: duplicate event for reference '{$reference}' — skipping.");
            return;
        }

        // ── Extract payment amount ────────────────────────────────────────
        // JuicyWay sends amounts in KOBO. Convert to naira.
        // Note: amount is confirmed to be in kobo per the spec.
        $amountKobo  = $data['amount'] ?? 0;
        $amountNaira = $amountKobo / 100;

        if ($amountNaira <= 0) {
            Log::warning("ProcessJuicyWayPayment: invalid amount {$amountKobo} kobo for reference '{$reference}'");
            return;
        }

        // ── Record the payment ────────────────────────────────────────────
        // We use DB::transaction() so the payment record and the
        // juicyway_payment_processed flag are updated atomically.
        // If recordPayment() throws, neither change is committed.
        DB::transaction(function () use ($invoice, $amountNaira, $data, $reference, $feeService, $juicyWay) {

            $senderName   = $data['customer']['first_name'] ?? '';
            $senderLast   = $data['customer']['last_name']  ?? '';
            $senderFull   = trim("{$senderName} {$senderLast}") ?: 'JuicyWay Payment';
            $paymentMethod = 'JuicyWay';

            if (isset($data['payment_method']['type'])) {
                $paymentMethod = match($data['payment_method']['type']) {
                    'card'         => 'JuicyWay Card',
                    'bank_account' => 'JuicyWay Transfer',
                    'ussd'         => 'JuicyWay USSD',
                    default        => 'JuicyWay',
                };
            }

            // Use the existing FeeService::recordPayment() so all the same
            // logic runs (receipt number generation, recalculateTotal, etc.)
            // Pass the JuicyWay data.id as the reference for cross-referencing.
            $feeService->recordPayment(
                invoice:   $invoice,
                amount:    $amountNaira,
                method:    $paymentMethod,
                reference: $data['id'] ?? $reference,
            );

            // Mark as processed — idempotency flag
            $invoice->update(['juicyway_payment_processed' => true]);

            // Update the webhook event log
            DB::table('juicyway_webhook_events')
                ->where('id', $this->logId)
                ->update(['processed_at' => now(), 'updated_at' => now()]);

            Log::info("ProcessJuicyWayPayment: ₦{$amountNaira} recorded for invoice {$invoice->id}", [
                'reference'  => $reference,
                'sender'     => $senderFull,
                'new_status' => $invoice->fresh()->status,
            ]);

            // ── Deactivate payment link if fully paid ─────────────────────
            // Prevents overpayment. Fire-and-forget — failure is non-fatal.
            $invoice->refresh();
            if ($invoice->status === 'paid' && $invoice->payment_link_id) {
                $juicyWay->deactivatePaymentLink($invoice->payment_link_id);
            }
        });
    }

    public function failed(\Throwable $e): void
    {
        $reference = $this->payload['data']['reference'] ?? 'unknown';

        Log::critical("ProcessJuicyWayPayment: permanently failed for reference '{$reference}': " . $e->getMessage());

        DB::table('juicyway_webhook_events')
            ->where('id', $this->logId)
            ->update(['processing_error' => $e->getMessage(), 'updated_at' => now()]);
    }
}
