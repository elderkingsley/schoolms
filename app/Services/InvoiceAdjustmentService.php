<?php

namespace App\Services;

use App\Jobs\PushInvoiceToPayGridJob;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceAdjustment;
use App\Models\ParentCredit;
use App\Notifications\InvoiceUpdatedNotification;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceAdjustmentService
{
    public function adjust(FeeInvoice $invoice, string $action, Closure $mutation, array $metadata = []): FeeInvoiceAdjustment
    {
        $actorId = auth()->id();
        $adjustment = null;

        DB::transaction(function () use ($invoice, $action, $mutation, $metadata, $actorId, &$adjustment): void {
            $invoice = FeeInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->with(['items.feeItem', 'payments'])
                ->firstOrFail();

            $before = $this->snapshot($invoice);

            $mutation($invoice);

            $invoice->recalculateTotal();
            $invoice->refresh()->load(['items.feeItem', 'payments']);

            $creditResult = $this->reconcileAdjustmentCredit($invoice, $actorId);
            $after = $this->snapshot($invoice);

            $adjustment = FeeInvoiceAdjustment::create([
                'fee_invoice_id' => $invoice->id,
                'adjusted_by' => $actorId,
                'action' => $action,
                'old_total_amount' => $before['total_amount'],
                'new_total_amount' => $after['total_amount'],
                'old_amount_paid' => $before['amount_paid'],
                'new_amount_paid' => $after['amount_paid'],
                'old_balance' => $before['balance'],
                'new_balance' => $after['balance'],
                'credit_adjustment_amount' => $creditResult['delta'],
                'paygrid_sync_status' => $invoice->sent_at ? 'pending' : 'not_required',
                'before_snapshot' => $before,
                'after_snapshot' => $after,
                'metadata' => array_merge($metadata, [
                    'credit' => $creditResult,
                ]),
            ]);
        });

        $adjustment->load('invoice.student.parents.user');
        $this->communicateAdjustment($adjustment);

        return $adjustment;
    }

    private function snapshot(FeeInvoice $invoice): array
    {
        return [
            'total_amount' => round((float) $invoice->total_amount, 2),
            'amount_paid' => round((float) $invoice->amount_paid, 2),
            'balance' => round((float) $invoice->balance, 2),
            'status' => $invoice->status,
            'items' => $invoice->items
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'fee_item_id' => $item->fee_item_id,
                    'name' => $item->item_name,
                    'amount' => round((float) $item->amount, 2),
                    'added_by' => $item->added_by,
                ])
                ->values()
                ->all(),
        ];
    }

    private function reconcileAdjustmentCredit(FeeInvoice $invoice, ?int $actorId): array
    {
        $desiredExcess = max(0, round((float) $invoice->amount_paid - (float) $invoice->total_amount, 2));
        $credits = ParentCredit::query()
            ->where('origin_fee_invoice_id', $invoice->id)
            ->where('source_reference', 'like', "invoice-adjustment-{$invoice->id}-%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->get();

        $currentTotal = round((float) $credits->sum('total_amount'), 2);
        $alreadyApplied = round((float) $credits->sum(fn (ParentCredit $credit) => max(0, (float) $credit->total_amount - (float) $credit->balance_amount)), 2);
        $targetTotal = max($desiredExcess, $alreadyApplied);
        $delta = round($targetTotal - $currentTotal, 2);

        if ($delta > 0) {
            $parent = $invoice->student->billingParent();

            if (! $parent) {
                return [
                    'desired_excess' => $desiredExcess,
                    'delta' => 0.0,
                    'status' => 'skipped_no_parent',
                ];
            }

            ParentCredit::create([
                'parent_id' => $parent->id,
                'student_id' => $invoice->student_id,
                'origin_fee_invoice_id' => $invoice->id,
                'source_reference' => 'invoice-adjustment-'.$invoice->id.'-'.Str::uuid(),
                'total_amount' => $delta,
                'balance_amount' => $delta,
                'status' => 'open',
                'notes' => 'Credit created because an invoice was adjusted below the amount already paid.',
                'created_by' => $actorId,
            ]);
        } elseif ($delta < 0) {
            $remainingReduction = abs($delta);

            foreach ($credits as $credit) {
                if ($remainingReduction <= 0) {
                    break;
                }

                $balance = (float) $credit->balance_amount;
                if ($balance <= 0) {
                    continue;
                }

                $reduction = min($balance, $remainingReduction);
                $newBalance = round($balance - $reduction, 2);
                $newTotal = round((float) $credit->total_amount - $reduction, 2);

                $credit->update([
                    'total_amount' => max(0, $newTotal),
                    'balance_amount' => max(0, $newBalance),
                    'status' => $newTotal <= 0 ? 'void' : ($newBalance > 0 ? 'open' : 'applied'),
                ]);

                $remainingReduction = round($remainingReduction - $reduction, 2);
            }
        }

        return [
            'desired_excess' => $desiredExcess,
            'delta' => $delta,
            'status' => $delta > 0 ? 'increased' : ($delta < 0 ? 'reduced' : 'unchanged'),
            'unreversible_applied_amount' => $alreadyApplied,
        ];
    }

    private function communicateAdjustment(FeeInvoiceAdjustment $adjustment): void
    {
        $invoice = $adjustment->invoice->fresh([
            'student.parents.user',
            'items.feeItem',
            'payments',
            'term.session',
        ]);

        if (! $invoice || ! $invoice->sent_at) {
            return;
        }

        PushInvoiceToPayGridJob::dispatch($invoice)->afterCommit();

        $parents = $invoice->student->parents->filter(fn ($parent) => $parent->user !== null);

        foreach ($parents as $parent) {
            try {
                $parent->user->notify(new InvoiceUpdatedNotification($adjustment->fresh(), $invoice));
            } catch (\Throwable $e) {
                Log::warning('InvoiceAdjustmentService: invoice update notification failed', [
                    'invoice_id' => $invoice->id,
                    'parent_id' => $parent->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $adjustment->update([
            'notified_at' => now(),
            'paygrid_sync_status' => 'queued',
        ]);
    }
}
