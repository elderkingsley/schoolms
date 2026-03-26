<?php

namespace App\Services;

use App\Models\Enrolment;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Term;
use App\Notifications\FeeInvoiceNotification;
use Illuminate\Support\Str;

class FeeService
{
    /**
     * Generate fee invoices for all active enrolments in the given term.
     *
     * Safe to call multiple times — uses firstOrCreate so existing invoices
     * are never duplicated. Returns the count of newly created invoices.
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
            // Fetch fee structures that apply to this class OR school-wide (null class)
            $structures = FeeStructure::with('feeItem')
                ->where('term_id', $term->id)
                ->where(function ($q) use ($enrolment) {
                    $q->where('school_class_id', $enrolment->school_class_id)
                      ->orWhereNull('school_class_id');
                })
                ->get();

            $total = $structures->sum('amount');

            if ($total <= 0) {
                continue; // No fees configured for this class/term — skip
            }

            // firstOrCreate returns the model; wasRecentlyCreated tells us if
            // it was just INSERTed (true) or already existed and was SELECTed (false)
            $invoice = FeeInvoice::firstOrCreate(
                [
                    'student_id' => $enrolment->student_id,
                    'term_id'    => $term->id,
                ],
                [
                    'total_amount' => $total,
                    'amount_paid'  => 0,
                    'balance'      => $total,
                    'status'       => 'unpaid',
                ]
            );

            if (! $invoice->wasRecentlyCreated) {
                continue; // Invoice already existed — do not re-create items or re-send email
            }

            // Snapshot each fee line item onto the invoice so the record is
            // immutable even if the fee structure changes later
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

            // Email every parent who has a portal account
            foreach ($enrolment->student->parents as $parent) {
                if ($parent->user) {
                    $parent->user->notify(
                        new FeeInvoiceNotification($invoice, $structures)
                    );
                }
            }
        }

        return $count;
    }

    /**
     * Record a manual payment against an invoice and update its balance.
     */
    public function recordPayment(
        FeeInvoice $invoice,
        float      $amount,
        string     $method,
        string     $reference = ''
    ): void {
        $receiptNumber = 'RCP-' . strtoupper(Str::random(10));

        FeePayment::create([
            'fee_invoice_id' => $invoice->id,
            'amount'         => $amount,
            'method'         => $method,
            'receipt_number' => $receiptNumber,
            'reference'      => $reference,
            'recorded_by'    => auth()->id(),
            'paid_at'        => now(),
        ]);

        // Recalculate from source of truth rather than arithmetic on stored values,
        // so repeated calls or edge cases never let the balance go wrong
        $invoice->recalculateTotal();
    }
}
