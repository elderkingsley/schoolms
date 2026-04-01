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
use Illuminate\Support\Facades\Log;

/**
 * ProcessPayGridInflowJob
 *
 * Processes a confirmed deposit notification from PayGrid.
 *
 * Flow:
 *   1. Find the ParentGuardian row by juicyway_account_number
 *   2. Idempotency check — skip if reference already recorded
 *   3. Find the student linked to that parent row
 *   4. Find all unpaid/partial invoices ordered oldest first
 *   5. Apply the deposit amount across invoices (FIFO):
 *      - Partial payments are recorded as-is (invoice → 'partial')
 *      - Overpayments on the final invoice are recorded in full
 *        (balance will show 0 or negative, acting as a credit)
 *   6. Send the parent a payment confirmation email
 *
 * Idempotency: the bank transaction `reference` from PayGrid is stored
 * as fee_payments.reference. Before recording, we check whether any
 * payment with that reference already exists — if so, we skip silently.
 * This handles PayGrid retrying the webhook after a 5xx response.
 */
class ProcessPayGridInflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(protected array $payload) {}

    public function handle(FeeService $feeService): void
    {
        $accountNumber = $this->payload['account_number'] ?? null;
        $amountNgn     = (float) ($this->payload['amount_ngn'] ?? 0);
        $reference     = $this->payload['reference']      ?? null;
        $senderName    = $this->payload['sender_name']    ?? 'Unknown';
        $depositId     = $this->payload['deposit_id']     ?? $reference;

        if (! $accountNumber || $amountNgn <= 0 || ! $reference) {
            Log::warning('ProcessPayGridInflow: missing fields — skipping', $this->payload);
            return;
        }

        // ── Idempotency check ─────────────────────────────────────────────
        // Use the bank reference as the unique key. PayGrid may fire the
        // webhook more than once if SchoolMS returned 5xx on first attempt.
        $alreadyRecorded = FeePayment::where('reference', $reference)->exists();

        if ($alreadyRecorded) {
            Log::info("ProcessPayGridInflow: reference '{$reference}' already recorded — skipping.");
            return;
        }

        // ── Find parent by account number ─────────────────────────────────
        $parent = ParentGuardian::where('juicyway_account_number', $accountNumber)
            ->with(['user', 'students'])
            ->first();

        if (! $parent) {
            Log::warning("ProcessPayGridInflow: no parent found for account {$accountNumber}");
            return;
        }

        $student = $parent->students->first();

        if (! $student) {
            Log::warning("ProcessPayGridInflow: parent {$parent->id} has no linked student");
            return;
        }

        // ── Resolve system actor for audit trail ─────────────────────────
        $systemActorId = \App\Models\User::where('user_type', 'super_admin')
            ->orWhere('user_type', 'admin')
            ->orderBy('id')
            ->value('id');

        // ── Find unpaid/partial invoices — oldest first ───────────────────
        $invoices = FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session'])
            ->orderBy('id', 'asc') // oldest invoice first (FIFO)
            ->get();

        if ($invoices->isEmpty()) {
            Log::info("ProcessPayGridInflow: ₦{$amountNgn} received for student {$student->id} but no unpaid invoices found.", [
                'account_number' => $accountNumber,
                'reference'      => $reference,
                'sender'         => $senderName,
            ]);
            // Still log it — money arrived but no invoice to apply to
            // The school bursary will handle this manually
            return;
        }

        // ── Apply deposit across invoices (FIFO) ──────────────────────────
        $remaining       = $amountNgn;
        $settledInvoices = [];

        DB::transaction(function () use (
            $invoices, &$remaining, &$settledInvoices,
            $reference, $senderName, $depositId, $feeService, $systemActorId
        ) {
            foreach ($invoices as $invoice) {
                if ($remaining <= 0) break;

                $balance = (float) (string) $invoice->balance;

                if ($balance <= 0) continue; // already paid somehow

                // Amount to apply to this invoice
                $toApply = min($remaining, $balance);

                // If this is the LAST invoice and we have more money than
                // the balance (overpayment), record the full remaining amount
                $isLastInvoice = $invoices->last()->id === $invoice->id;
                if ($isLastInvoice && $remaining > $balance) {
                    $toApply = $remaining; // record full amount — creates credit
                }

                $feeService->recordPayment(
                    invoice:    $invoice,
                    amount:     $toApply,
                    method:     'JuicyWay Transfer',
                    reference:  $reference,
                    recordedBy: $systemActorId,
                    source:     'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[] = $invoice->fresh();

                Log::info("ProcessPayGridInflow: ₦{$toApply} applied to invoice {$invoice->id}", [
                    'student'    => $invoice->student_id,
                    'status'     => $invoice->fresh()->status,
                    'reference'  => $reference,
                ]);
            }
        });

        // ── Send payment confirmation email to parent ─────────────────────
        if ($parent->user && ! empty($settledInvoices)) {
            try {
                $parent->user->notify(
                    new PaymentReceivedNotification(
                        student:         $student,
                        amountPaid:      $amountNgn,
                        senderName:      $senderName,
                        reference:       $reference,
                        settledInvoices: $settledInvoices,
                    )
                );
            } catch (\Throwable $e) {
                // Email failure must never affect payment recording
                Log::warning("ProcessPayGridInflow: payment confirmation email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        Log::info("ProcessPayGridInflow: ₦{$amountNgn} fully processed for student {$student->id}", [
            'account_number'  => $accountNumber,
            'reference'       => $reference,
            'invoices_settled'=> count($settledInvoices),
            'sender'          => $senderName,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $reference = $this->payload['reference'] ?? 'unknown';
        Log::critical("ProcessPayGridInflow: permanently failed for reference '{$reference}': " . $e->getMessage());
    }
}
