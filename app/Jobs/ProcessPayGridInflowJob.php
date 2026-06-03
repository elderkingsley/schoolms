<?php

// Deploy to: /var/www/schoolms/app/Jobs/ProcessPayGridInflowJob.php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\ParentGuardian;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Services\FeeService;
use App\Services\ParentCreditService;
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
 *   1. Find the ParentGuardian row by budpay_account_number (or juicyway as legacy fallback)
 *   2. Idempotency check — skip if reference already recorded
 *   3. Find the student linked to that parent row
 *   4. Find all unpaid/partial invoices ordered oldest first
 *   5. Apply the deposit amount across invoices (FIFO):
 *      - Partial payments are recorded as-is (invoice → 'partial')
 *      - Any excess after all invoices are settled becomes parent credit
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

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(protected array $payload) {}

    public function handle(FeeService $feeService, ParentCreditService $parentCreditService): void
    {
        $accountNumber = $this->payload['account_number'] ?? null;
        $amountNgn = (float) ($this->payload['amount_ngn'] ?? 0);
        $reference = $this->payload['reference'] ?? null;
        $senderName = $this->payload['sender_name'] ?? 'Unknown';
        $depositId = $this->payload['deposit_id'] ?? $reference;

        if (! $accountNumber || $amountNgn <= 0 || ! $reference) {
            Log::warning('ProcessPayGridInflow: missing fields — skipping', $this->payload);

            return;
        }

        // ── Idempotency check ─────────────────────────────────────────────
        // Use the bank reference as the unique key. PayGrid may fire the
        // webhook more than once if SchoolMS returned 5xx on first attempt.
        $alreadyRecorded = FeePayment::where('reference', 'like', $reference.'%')->exists();

        if ($alreadyRecorded) {
            Log::info("ProcessPayGridInflow: reference '{$reference}' already recorded — skipping.");

            return;
        }

        // ── Find parent by account number ─────────────────────────────────
        // Check BudPay first (current provider), then JuicyWay as fallback
        // for any legacy parents who were never migrated.
        $parent = ParentGuardian::where('budpay_account_number', $accountNumber)
            ->orWhere('korapay_account_number', $accountNumber)
            ->orWhere('juicyway_account_number', $accountNumber)
            ->with(['user', 'students'])
            ->first();

        if (! $parent) {
            Log::warning("ProcessPayGridInflow: no parent found for account {$accountNumber}");

            return;
        }

        $studentIds = $parent->familyStudentIdsForBilling();

        if ($studentIds->isEmpty()) {
            Log::warning("ProcessPayGridInflow: parent {$parent->id} has no linked students");

            return;
        }

        // ── Resolve system actor for audit trail ─────────────────────────
        $systemActorId = User::where('user_type', 'super_admin')
            ->orWhere('user_type', 'admin')
            ->orderBy('id')
            ->value('id');

        // ── Find unpaid/partial invoices — oldest first ───────────────────
        $invoices = FeeInvoice::whereIn('student_id', $studentIds)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session', 'student'])
            ->orderBy('id', 'asc') // oldest invoice first (FIFO)
            ->get();

        if ($invoices->isEmpty()) {
            Log::info("ProcessPayGridInflow: ₦{$amountNgn} received for parent {$parent->id} but no unpaid invoices found across any child.", [
                'account_number' => $accountNumber,
                'reference' => $reference,
                'sender' => $senderName,
                'student_ids' => $studentIds->toArray(),
            ]);

            // Still log it — money arrived but no invoice to apply to
            // The school bursary will handle this manually
            return;
        }

        // ── Apply deposit across invoices (FIFO) ──────────────────────────
        $remaining = $amountNgn;
        $settledInvoices = [];

        DB::transaction(function () use (
            $invoices, &$remaining, &$settledInvoices,
            $reference, $feeService, $systemActorId, $parent, $accountNumber
        ) {
            foreach ($invoices as $invoice) {
                if ($remaining <= 0) {
                    break;
                }

                $balance = (float) (string) $invoice->balance;

                if ($balance <= 0) {
                    continue;
                } // already paid somehow

                // Amount to apply to this invoice
                $toApply = min($remaining, $balance);

                // Derive the payment method from whichever provider's NUBAN matched
                $paymentMethod = match (true) {
                    ! empty($parent->korapay_account_number) && $parent->korapay_account_number === $accountNumber => 'Korapay Transfer',
                    ! empty($parent->budpay_account_number) && $parent->budpay_account_number === $accountNumber => 'BudPay Transfer',
                    default => 'JuicyWay Transfer',
                };

                $feeService->recordPayment(
                    invoice: $invoice,
                    amount: $toApply,
                    method: $paymentMethod,
                    reference: $reference.'-inv-'.$invoice->id,
                    recordedBy: $systemActorId,
                    source: 'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[] = $invoice->fresh();

                Log::info("ProcessPayGridInflow: ₦{$toApply} applied to invoice {$invoice->id}", [
                    'student' => $invoice->student_id,
                    'status' => $invoice->fresh()->status,
                    'reference' => $reference.'-inv-'.$invoice->id,
                ]);
            }
        });

        if ($remaining > 0) {
            $parentCreditService->captureOverpayment(
                parent: $parent,
                amount: $remaining,
                reference: $reference.'-credit',
                recordedBy: $systemActorId,
                originInvoice: $settledInvoices[0] ?? null,
            );

            Log::info("ProcessPayGridInflow: ₦{$remaining} stored as parent credit for parent {$parent->id}", [
                'reference' => $reference,
                'account_number' => $accountNumber,
            ]);
        }

        // ── Send payment confirmation email to parent ─────────────────────
        if ($parent->user && ! empty($settledInvoices)) {
            $firstStudent = $settledInvoices[0]->student ?? $parent->students->first();
            try {
                $parent->user->notify(
                    new PaymentReceivedNotification(
                        student: $firstStudent,
                        amountPaid: $amountNgn,
                        senderName: $senderName,
                        reference: $reference,
                        settledInvoices: $settledInvoices,
                    )
                );
            } catch (\Throwable $e) {
                // Email failure must never affect payment recording
                Log::warning("ProcessPayGridInflow: payment confirmation email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        Log::info("ProcessPayGridInflow: ₦{$amountNgn} fully processed for parent {$parent->id}", [
            'account_number' => $accountNumber,
            'reference' => $reference,
            'invoices_settled' => count($settledInvoices),
            'student_ids' => $studentIds->toArray(),
            'sender' => $senderName,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $reference = $this->payload['reference'] ?? 'unknown';
        Log::critical("ProcessPayGridInflow: permanently failed for reference '{$reference}': ".$e->getMessage());
    }
}
