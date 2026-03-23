<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Enrolment;
use App\Models\FeeInvoice;
use App\Models\FeeStructure;
use App\Models\Term;
use App\Notifications\FeeInvoiceNotification;

class FeeService
{
    /**
     * Generate fee invoices for all active students in the current term.
     * Safe to call multiple times — uses firstOrCreate to avoid duplicates.
     */
    public function generateInvoicesForTerm(Term $term): int
    {
        $session     = $term->session;
        $enrolments  = Enrolment::with('student', 'schoolClass')
            ->where('academic_session_id', $session->id)
            ->where('status', 'active')
            ->get();

        $count = 0;

        foreach ($enrolments as $enrolment) {
            // Get fee items for this class + term
            $feeItems = FeeStructure::where('term_id', $term->id)
                ->where(function ($q) use ($enrolment) {
                    $q->where('school_class_id', $enrolment->school_class_id)
                      ->orWhereNull('school_class_id'); // school-wide fees
                })->get();

            $total = $feeItems->sum('amount');

            if ($total <= 0) continue;

            [$invoice, $created] = [
                FeeInvoice::firstOrCreate(
                    ['student_id' => $enrolment->student_id, 'term_id' => $term->id],
                    [
                        'total_amount' => $total,
                        'amount_paid'  => 0,
                        'balance'      => $total,
                        'status'       => 'unpaid',
                    ]
                ),
                false,
            ];

            if ($invoice->wasRecentlyCreated) {
                $count++;
                // Email the parent
                foreach ($enrolment->student->parents as $parent) {
                    if ($parent->user) {
                        $parent->user->notify(new FeeInvoiceNotification($invoice, $feeItems));
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Record a payment and update the invoice balance.
     */
    public function recordPayment(FeeInvoice $invoice, float $amount, string $method, string $reference = ''): void
    {
        $receiptNumber = 'RCP-' . strtoupper(\Str::random(10));

        \App\Models\FeePayment::create([
            'fee_invoice_id' => $invoice->id,
            'amount'         => $amount,
            'method'         => $method,
            'receipt_number' => $receiptNumber,
            'reference'      => $reference,
            'recorded_by'    => auth()->id() ?? 1,
            'paid_at'        => now(),
        ]);

        $newPaid    = $invoice->amount_paid + $amount;
        $newBalance = max(0, $invoice->total_amount - $newPaid);
        $newStatus  = $newBalance <= 0 ? 'paid' : 'partial';

        $invoice->update([
            'amount_paid' => $newPaid,
            'balance'     => $newBalance,
            'status'      => $newStatus,
        ]);
    }
}
