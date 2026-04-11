$studentId = 99; // ← change this

$student = \App\Models\Student::with(['feeInvoices.payments', 'enrolments', 'parents'])->findOrFail($studentId);

// Safety check — abort if any payments exist
$totalPayments = $student->feeInvoices->flatMap->payments->count();
if ($totalPayments > 0) {
    echo "ABORT: student has {$totalPayments} payment(s) — will not delete.\n";
    return;
}

// Show what will be deleted before committing
echo "Student: {$student->full_name} ({$student->admission_number})\n";
echo "Invoices to delete: {$student->feeInvoices->count()}\n";
echo "Enrolments to delete: {$student->enrolments->count()}\n";
echo "Parent links to detach: {$student->parents->count()}\n";

\Illuminate\Support\Facades\DB::transaction(function () use ($student) {
    // Delete invoice line items then invoices
    foreach ($student->feeInvoices as $invoice) {
        $invoice->items()->delete();
        $invoice->delete();
    }

    // Delete enrolments
    $student->enrolments()->delete();

    // Detach parent pivot links (does NOT delete the parent record)
    $student->parents()->detach();

    // Delete the student
    $student->delete();

    echo "Done — student deleted cleanly.\n";
});
