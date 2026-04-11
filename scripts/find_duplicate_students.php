<?php
// Deploy to: /var/www/schoolms/scripts/find_duplicate_students.php
// Usage: cd /var/www/schoolms && php scripts/find_duplicate_students.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "=== Duplicate Student Finder ===\n\n";

// Find students who share the same first name + last name + date of birth
// These are the strongest signal of a duplicate enrolment
$duplicates = DB::table('students')
    ->select('first_name', 'last_name', 'date_of_birth', DB::raw('COUNT(*) as total'), DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as ids'))
    ->groupBy('first_name', 'last_name', 'date_of_birth')
    ->having('total', '>', 1)
    ->orderBy('last_name')
    ->get();

if ($duplicates->isEmpty()) {
    echo "No duplicate students found.\n";
    exit(0);
}

echo "Found {$duplicates->count()} duplicate group(s):\n\n";

foreach ($duplicates as $group) {
    $ids = explode(',', $group->ids);

    echo "─────────────────────────────────────────\n";
    echo "Name : {$group->first_name} {$group->last_name}\n";
    echo "DOB  : {$group->date_of_birth}\n";
    echo "Count: {$group->total} records\n\n";

    foreach ($ids as $id) {
        $student = Student::with(['parents.user', 'enrolments.schoolClass', 'feeInvoices.payments'])
            ->find($id);

        if (! $student) continue;

        $paymentTotal = $student->feeInvoices->flatMap->payments->sum('amount');
        $invoiceCount = $student->feeInvoices->count();
        $parent       = $student->parents->first();
        $parentEmail  = $parent?->user?->email ?? $parent?->_temp_email ?? '—';
        $enrolment    = $student->enrolments->first();
        $class        = $enrolment?->schoolClass?->display_name ?? '—';
        $status       = $student->status;

        echo "  ID     : {$student->id}\n";
        echo "  Adm No : {$student->admission_number}\n";
        echo "  Status : {$status}\n";
        echo "  Class  : {$class}\n";
        echo "  Parent : {$parentEmail}\n";
        echo "  Invoices : {$invoiceCount}";

        if ($paymentTotal > 0) {
            echo " — ⚠️  HAS PAYMENTS: ₦" . number_format($paymentTotal, 2);
        } else {
            echo " — no payments";
        }

        echo "\n  Created: {$student->created_at}\n\n";
    }
}

echo "─────────────────────────────────────────\n";
echo "To delete a duplicate with no payments, run:\n";
echo "  php scripts/delete_student.php <student_id>\n\n";
