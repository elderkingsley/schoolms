<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\ParentCredit;
use App\Models\ParentCreditApplication;
use App\Models\ParentGuardian;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ParentCreditService
{
    public function __construct(
        private FeeService $feeService
    ) {}

    public function resolveBillingParent(FeeInvoice $invoice): ?ParentGuardian
    {
        $invoice->loadMissing('student.parents.user');

        return $invoice->student->billingParent(requireAccount: true);
    }

    public function captureOverpayment(
        ParentGuardian $parent,
        float $amount,
        string $reference,
        ?int $recordedBy = null,
        ?FeeInvoice $originInvoice = null
    ): ?ParentCredit {
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($parent, $amount, $reference, $recordedBy, $originInvoice) {
            $existing = ParentCredit::query()
                ->where('parent_id', $parent->id)
                ->where('source_reference', $reference)
                ->first();

            if ($existing) {
                return $existing;
            }

            return ParentCredit::create([
                'parent_id' => $parent->id,
                'student_id' => $originInvoice?->student_id,
                'origin_fee_invoice_id' => $originInvoice?->id,
                'source_reference' => $reference,
                'total_amount' => $amount,
                'balance_amount' => $amount,
                'status' => 'open',
                'notes' => 'Credit created from parent overpayment.',
                'created_by' => $this->resolveActorId($recordedBy),
            ]);
        });
    }

    public function applyAvailableCreditsToInvoice(
        FeeInvoice $invoice,
        ?ParentGuardian $parent = null,
        ?int $recordedBy = null
    ): float {
        $invoice->refresh();

        if ((float) $invoice->balance <= 0) {
            return 0.0;
        }

        $parent ??= $this->resolveBillingParent($invoice);

        if (! $parent) {
            return 0.0;
        }

        $actorId = $this->resolveActorId($recordedBy);
        $appliedTotal = 0.0;

        $credits = ParentCredit::query()
            ->where('parent_id', $parent->id)
            ->where(function ($query) use ($invoice) {
                $query->where('student_id', $invoice->student_id)
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('student_id')
                            ->whereNull('origin_fee_invoice_id');
                    });
            })
            ->where('status', 'open')
            ->where('balance_amount', '>', 0)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($credits as $credit) {
            $invoice->refresh();
            $balance = (float) $invoice->balance;

            if ($balance <= 0) {
                break;
            }

            $amount = min((float) $credit->balance_amount, $balance);
            if ($amount <= 0) {
                continue;
            }

            $reference = "credit-{$credit->id}-inv-{$invoice->id}";

            /** @var FeePayment|null $payment */
            $payment = $this->feeService->recordPayment(
                invoice: $invoice,
                amount: $amount,
                method: 'Parent Credit',
                reference: $reference,
                recordedBy: $actorId,
                source: 'automation',
            );

            if (! $payment) {
                continue;
            }

            ParentCreditApplication::create([
                'parent_credit_id' => $credit->id,
                'fee_invoice_id' => $invoice->id,
                'fee_payment_id' => $payment->id,
                'amount' => $amount,
                'reference' => $reference,
                'applied_by' => $actorId,
            ]);

            $remaining = round((float) $credit->balance_amount - $amount, 2);
            $credit->update([
                'balance_amount' => $remaining,
                'status' => $remaining > 0 ? 'open' : 'applied',
            ]);

            $appliedTotal = round($appliedTotal + $amount, 2);
        }

        return $appliedTotal;
    }

    private function resolveActorId(?int $recordedBy): ?int
    {
        if ($recordedBy) {
            return $recordedBy;
        }

        return User::query()
            ->whereIn('user_type', ['super_admin', 'admin'])
            ->orderBy('id')
            ->value('id');
    }
}
