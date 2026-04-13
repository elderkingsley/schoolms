<?php
// Deploy to: /var/www/schoolms/scripts/delete_student_hard.php
// Usage: cd /var/www/schoolms && php scripts/delete_student_hard.php <student_id>
// Example: php scripts/delete_student_hard.php 42

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$studentId = $argv[1] ?? null;

if (! $studentId || ! is_numeric($studentId)) {
    echo "Usage: php scripts/delete_student_hard.php <student_id>\n";
    exit(1);
}

$student = \App\Models\Student::with([
    'feeInvoices.payments',
    'feeInvoices.items',
    'enrolments',
    'parents',
    'results',
])->find($studentId);

if (! $student) {
    echo "Student ID {$studentId} not found.\n";
    exit(1);
}

// ── Show summary before doing anything ───────────────────────────────────────
$totalPaid    = $student->feeInvoices->flatMap->payments->sum('amount');
$paymentCount = $student->feeInvoices->flatMap->payments->count();

echo "=== HARD DELETE PREVIEW ===\n\n";
echo "Student      : {$student->full_name}\n";
echo "Admission No : {$student->admission_number}\n";
echo "Status       : {$student->status}\n";
echo "Invoices     : {$student->feeInvoices->count()}\n";
echo "Payments     : {$paymentCount} (₦" . number_format($totalPaid, 2) . " total)\n";
echo "Results      : {$student->results->count()}\n";
echo "Enrolments   : {$student->enrolments->count()}\n";
echo "Parent links : {$student->parents->count()}\n";
echo "Photo        : " . ($student->photo ? $student->photo : 'none') . "\n\n";

if ($paymentCount > 0) {
    echo "⚠️  WARNING: This student has {$paymentCount} payment record(s) totalling\n";
    echo "   ₦" . number_format($totalPaid, 2) . ". These will be permanently deleted.\n";
    echo "   Only proceed if these payments never actually happened\n";
    echo "   (test data, duplicate entry, etc.)\n\n";
}

echo "Type YES to permanently delete all records, or anything else to cancel: ";
$confirm = trim(fgets(STDIN));

if ($confirm !== 'YES') {
    echo "Cancelled.\n";
    exit(0);
}

// ── Hard delete inside a transaction ─────────────────────────────────────────
DB::transaction(function () use ($student) {

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

    // 6. Detach parent pivot links (does NOT delete the parent record)
    $student->parents()->detach();

    // 7. Delete passport photo from storage if it exists
    if ($student->photo && Storage::disk('public')->exists($student->photo)) {
        Storage::disk('public')->delete($student->photo);
        echo "Photo deleted from storage.\n";
    }

    // 8. Delete the student
    $student->delete();
});

echo "Done — {$student->full_name} (ID: {$student->id}) permanently deleted.\n";
