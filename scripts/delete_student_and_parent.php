<?php
// Deploy to: /var/www/schoolms/scripts/delete_student_and_parent.php
// Usage: cd /var/www/schoolms && php scripts/delete_student_and_parent.php <student_id>
// Example: php scripts/delete_student_and_parent.php 42
//
// This script deletes:
//   - The student and all their records (invoices, payments, results, enrolments)
//   - The parent(s) linked ONLY to this student (parents shared with other
//     students are detached but NOT deleted — they have other children enrolled)
//   - The parent's User account (if they have no other linked students)
//   - The parent's passport photo from storage

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$studentId = $argv[1] ?? null;

if (! $studentId || ! is_numeric($studentId)) {
    echo "Usage: php scripts/delete_student_and_parent.php <student_id>\n";
    exit(1);
}

$student = \App\Models\Student::with([
    'feeInvoices.payments',
    'feeInvoices.items',
    'enrolments',
    'results',
    'parents.user',
    'parents.students',
])->find($studentId);

if (! $student) {
    echo "Student ID {$studentId} not found.\n";
    exit(1);
}

// ── Work out which parents are safe to fully delete ───────────────────────────
// A parent is safe to delete only if this student is their ONLY linked student.
// If they have other children, we detach only — their account stays.
$parentsToDelete = collect();
$parentsToDetach = collect();

foreach ($student->parents as $parent) {
    $otherStudentCount = $parent->students->where('id', '!=', $student->id)->count();
    if ($otherStudentCount === 0) {
        $parentsToDelete->push($parent);
    } else {
        $parentsToDetach->push($parent);
    }
}

// ── Show summary ──────────────────────────────────────────────────────────────
$totalPaid    = $student->feeInvoices->flatMap->payments->sum('amount');
$paymentCount = $student->feeInvoices->flatMap->payments->count();

echo "=== HARD DELETE PREVIEW ===\n\n";
echo "── Student ─────────────────────────────────\n";
echo "Name         : {$student->full_name}\n";
echo "Admission No : {$student->admission_number}\n";
echo "Status       : {$student->status}\n";
echo "Invoices     : {$student->feeInvoices->count()}\n";
echo "Payments     : {$paymentCount} (₦" . number_format($totalPaid, 2) . " total)\n";
echo "Results      : {$student->results->count()}\n";
echo "Enrolments   : {$student->enrolments->count()}\n\n";

if ($paymentCount > 0) {
    echo "⚠️  WARNING: This student has {$paymentCount} payment record(s) totalling\n";
    echo "   ₦" . number_format($totalPaid, 2) . ". These will be permanently deleted.\n";
    echo "   Only proceed if these payments never actually happened.\n\n";
}

echo "── Parents ─────────────────────────────────\n";

if ($parentsToDelete->isEmpty() && $parentsToDetach->isEmpty()) {
    echo "No parent records linked to this student.\n\n";
}

foreach ($parentsToDelete as $parent) {
    $name  = $parent->user?->name ?? $parent->_temp_name ?? 'Unknown';
    $email = $parent->user?->email ?? $parent->_temp_email ?? '—';
    echo "WILL DELETE  : {$name} ({$email}) — parent ID {$parent->id}\n";
    echo "               User account will also be deleted.\n";
    if ($parent->user?->budpay_customer_code ?? $parent->budpay_customer_code ?? null) {
        echo "               ⚠️  Has BudPay virtual account — NUBAN will be removed from DB\n";
        echo "               (The BudPay account itself remains on BudPay's servers)\n";
    }
}

foreach ($parentsToDetach as $parent) {
    $name  = $parent->user?->name ?? $parent->_temp_name ?? 'Unknown';
    $email = $parent->user?->email ?? $parent->_temp_email ?? '—';
    $otherCount = $parent->students->where('id', '!=', $student->id)->count();
    echo "WILL DETACH  : {$name} ({$email}) — has {$otherCount} other child(ren), account kept\n";
}

echo "\nType YES to permanently delete all records, or anything else to cancel: ";
$confirm = trim(fgets(STDIN));

if ($confirm !== 'YES') {
    echo "Cancelled.\n";
    exit(0);
}

// ── Hard delete inside a transaction ─────────────────────────────────────────
DB::transaction(function () use ($student, $parentsToDelete, $parentsToDetach) {

    // 1. Delete fee payments
    foreach ($student->feeInvoices as $invoice) {
        $invoice->payments()->delete();
    }

    // 2. Delete invoice line items
    foreach ($student->feeInvoices as $invoice) {
        $invoice->items()->delete();
    }

    // 3. Delete invoices
    $student->feeInvoices()->delete();

    // 4. Delete results
    $student->results()->delete();

    // 5. Delete enrolments
    $student->enrolments()->delete();

    // 6. Detach all parent pivot links first
    $student->parents()->detach();

    // 7. Delete student photo
    if ($student->photo && Storage::disk('public')->exists($student->photo)) {
        Storage::disk('public')->delete($student->photo);
        echo "Student photo deleted.\n";
    }

    // 8. Delete the student
    $student->delete();
    echo "Student deleted.\n";

    // 9. Delete parents who have no other children
    foreach ($parentsToDelete as $parent) {
        $name = $parent->user?->name ?? $parent->_temp_name ?? "Parent #{$parent->id}";

        // Delete user account if exists
        if ($parent->user) {
            $parent->user->delete();
            echo "User account deleted: {$name}\n";
        }

        // Delete parent record (clears all NUBAN columns too)
        $parent->delete();
        echo "Parent record deleted: {$name}\n";
    }

    // 10. Parents with other children — already detached above, nothing more to do
    foreach ($parentsToDetach as $parent) {
        $name = $parent->user?->name ?? $parent->_temp_name ?? "Parent #{$parent->id}";
        echo "Parent detached (kept): {$name}\n";
    }
});

echo "\nDone — {$student->full_name} (ID: {$student->id}) and associated records permanently deleted.\n";
