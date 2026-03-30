<?php

namespace App\Services;

use App\Models\Enrolment;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Term;
use Illuminate\Support\Str;

class FeeService
{
    /**
     * Generate invoices as DRAFTS (sent_at = null).
     * No emails sent. Admin reviews and sends manually.
     */
    public function generateInvoicesForTerm(Term $term): int
    {
        $session    = $term->session;
        $enrolments = Enrolment::with('student.parents.user', 'schoolClass')
            ->where('academic_session_id', $session->id)
            ->where('status', 'active')
            ->get();

        $count = 0;

        foreach ($enrolments as $enrolment) {
            $structures = FeeStructure::with('feeItem')
                ->where('term_id', $term->id)
                ->where(function ($q) use ($enrolment) {
                    $q->where('school_class_id', $enrolment->school_class_id)
                      ->orWhereNull('school_class_id');
                })
                ->get();

            $total = $structures->sum('amount');
            if ($total <= 0) continue;

            $invoice = FeeInvoice::firstOrCreate(
                ['student_id' => $enrolment->student_id, 'term_id' => $term->id],
                ['total_amount' => $total, 'amount_paid' => 0, 'balance' => $total,
                 'status' => 'unpaid', 'sent_at' => null]
            );

            if (! $invoice->wasRecentlyCreated) continue;

            foreach ($structures as $structure) {
                FeeInvoiceItem::create([
                    'fee_invoice_id' => $invoice->id,
                    'fee_item_id'    => $structure->fee_item_id,
                    'item_name'      => $structure->feeItem->name,
                    'amount'         => $structure->amount,
                    'added_by'       => 'system',
                ]);
            }

            $count++;
        }

        return $count;
    }

    public function recordPayment(FeeInvoice $invoice, float $amount, string $method, string $reference = ''): void
    {
        FeePayment::create([
            'fee_invoice_id' => $invoice->id,
            'amount'         => $amount,
            'method'         => $method,
            'receipt_number' => 'RCP-' . strtoupper(Str::random(10)),
            'reference'      => $reference,
            'recorded_by'    => auth()->id(),
            'paid_at'        => now(),
        ]);

        $invoice->recalculateTotal();
    }
}
