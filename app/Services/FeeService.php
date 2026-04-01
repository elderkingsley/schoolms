<?php

namespace App\Services;

use App\Models\Enrolment;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeeService
{
    /**
     * Generate invoices as DRAFTS for ALL active students in a term.
     * No emails sent. Admin reviews and sends manually.
     * Returns count of newly created invoices.
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
            if ($this->generateInvoiceForStudent($enrolment->student, $term)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate a single draft invoice for one student in a given term.
     *
     * Returns the FeeInvoice if newly created, null if one already existed
     * or if no fee structures are configured for the student's class.
     *
     * This is the single source of truth for invoice generation logic.
     * Both generateInvoicesForTerm() and the manual "create invoice" UI
     * call this method — no duplication.
     */
    public function generateInvoiceForStudent(Student $student, Term $term): ?FeeInvoice
    {
        // Load enrolment for this term's session to find the student's class
        $enrolment = Enrolment::where('student_id', $student->id)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->first();

        // Find applicable fee structures: class-specific first, then school-wide
        $structures = FeeStructure::with('feeItem')
            ->where('term_id', $term->id)
            ->where(function ($q) use ($enrolment) {
                $q->where('school_class_id', $enrolment?->school_class_id)
                  ->orWhereNull('school_class_id');
            })
            ->get();

        $total = $structures->sum('amount');

        // No fee structure configured — cannot create invoice
        if ($total <= 0) {
            return null;
        }

        // firstOrCreate — safe to call multiple times; never creates duplicate
        $invoice = FeeInvoice::firstOrCreate(
            ['student_id' => $student->id, 'term_id' => $term->id],
            [
                'total_amount' => $total,
                'amount_paid'  => 0,
                'balance'      => $total,
                'status'       => 'unpaid',
                'sent_at'      => null,
            ]
        );

        // Already existed — return null to signal nothing was created
        if (! $invoice->wasRecentlyCreated) {
            return null;
        }

        // Snapshot line items from the fee structure
        foreach ($structures as $structure) {
            FeeInvoiceItem::create([
                'fee_invoice_id' => $invoice->id,
                'fee_item_id'    => $structure->fee_item_id,
                'item_name'      => $structure->feeItem->name,
                'amount'         => $structure->amount,
                'added_by'       => 'system',
            ]);
        }

        return $invoice;
    }

    /**
     * Preview what an invoice would contain for a student + term.
     * Does NOT create anything — used to show the admin before they confirm.
     *
     * Returns:
     *   'already_exists'  — invoice already created for this term
     *   'no_fee_structure'— no fees configured for this class/term
     *   array             — ['items' => [...], 'total' => 00.00]
     */
    public function previewInvoice(Student $student, Term $term): string|array
    {
        // Already has an invoice for this term
        if (FeeInvoice::where('student_id', $student->id)
            ->where('term_id', $term->id)->exists()) {
            return 'already_exists';
        }

        $enrolment = Enrolment::where('student_id', $student->id)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->first();

        $structures = FeeStructure::with('feeItem')
            ->where('term_id', $term->id)
            ->where(function ($q) use ($enrolment) {
                $q->where('school_class_id', $enrolment?->school_class_id)
                  ->orWhereNull('school_class_id');
            })
            ->get();

        if ($structures->isEmpty() || $structures->sum('amount') <= 0) {
            return 'no_fee_structure';
        }

        return [
            'items' => $structures->map(fn($s) => [
                'name'   => $s->feeItem->name,
                'amount' => (float) $s->amount,
            ])->toArray(),
            'total' => (float) $structures->sum('amount'),
        ];
    }

    /**
     * Record a payment against an invoice and update its balance.
     *
     * @param FeeInvoice $invoice    The invoice to record payment against
     * @param float      $amount     Amount paid in naira
     * @param string     $method     Payment method
     * @param string     $reference  Bank/transfer reference for idempotency
     * @param int|null   $recordedBy User ID who recorded the payment.
     *                               Pass null for automated payments (polling job, webhook).
     *                               Falls back to auth()->id() for manual UI payments.
     * @param string     $source     'manual' for UI, 'automation' for polling/webhook.
     *                               Stored on receipt_number prefix for audit trail.
     */
    public function recordPayment(
        FeeInvoice $invoice,
        float      $amount,
        string     $method,
        string     $reference = '',
        ?int       $recordedBy = null,
        string     $source = 'manual'
    ): void {
        // Resolve who recorded the payment.
        // Automated sources pass $recordedBy explicitly so we never need
        // auth()->id() in a queue worker context (where auth is null).
        $actor = $recordedBy ?? auth()->id();

        if (! $actor) {
            // Last resort — find first super_admin as system actor
            $actor = \App\Models\User::where('user_type', 'super_admin')
                ->orWhere('user_type', 'admin')
                ->orderBy('id')
                ->value('id');
        }

        // Prefix receipt number with source so audit trail is unambiguous:
        // RCP- = manual entry by staff
        // AUTO- = recorded automatically by polling/webhook
        $prefix = $source === 'automation' ? 'AUTO-' : 'RCP-';

        try {
            FeePayment::create([
                'fee_invoice_id' => $invoice->id,
                'amount'         => $amount,
                'method'         => $method,
                'receipt_number' => $prefix . strtoupper(Str::random(10)),
                'reference'      => $reference,
                'recorded_by'    => $actor,
                'paid_at'        => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Duplicate reference — another process already recorded this payment.
            // This is the database-level idempotency guard. Log and return safely.
            \Illuminate\Support\Facades\Log::info("FeeService: duplicate payment reference '{$reference}' rejected by DB constraint — skipping.");
            return;
        }

        $invoice->recalculateTotal();
    }
}
