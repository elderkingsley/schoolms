<?php

/**
 * BudPay Pilot Script
 *
 * 1. Finds the last 10 enrolled students
 * 2. Deletes all their invoices (and invoice items + payments)
 * 3. Clears JuicyWay account data for their parents
 * 4. Provisions fresh BudPay virtual accounts
 *
 * Run on the SchoolMS server:
 *   php artisan tinker --execute="require '/tmp/budpay_pilot.php';"
 *
 * SAFE TO RUN: Only touches the 10 most recently enrolled students.
 * All other students and their payment history are untouched.
 */

use App\Jobs\ProvisionParentWalletJob;
use App\Models\Enrolment;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeePayment;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

$sep = str_repeat('─', 80);

echo PHP_EOL . $sep . PHP_EOL;
echo '  BudPay Pilot — ' . now()->format('d M Y, g:ia') . PHP_EOL;
echo $sep . PHP_EOL . PHP_EOL;

// ── Step 1: Find the last 10 enrolled students ─────────────────────────────
$recentEnrolments = Enrolment::with('student')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

if ($recentEnrolments->isEmpty()) {
    echo 'ERROR: No enrolments found.' . PHP_EOL;
    return;
}

$students = $recentEnrolments->map(fn($e) => $e->student)->filter()->unique('id');

echo "Found {$students->count()} students (last 10 enrolments):" . PHP_EOL;
foreach ($students as $s) {
    echo "  • {$s->full_name} ({$s->admission_number})" . PHP_EOL;
}
echo PHP_EOL;

// ── Step 2: Delete all invoices for these students ─────────────────────────
echo "Deleting invoices..." . PHP_EOL;

$studentIds    = $students->pluck('id')->toArray();
$invoiceIds    = FeeInvoice::whereIn('student_id', $studentIds)->pluck('id')->toArray();
$invoiceCount  = count($invoiceIds);

if ($invoiceCount > 0) {
    DB::transaction(function () use ($invoiceIds) {
        // Delete in order: payments → items → invoices
        FeePayment::whereIn('fee_invoice_id', $invoiceIds)->delete();
        FeeInvoiceItem::whereIn('fee_invoice_id', $invoiceIds)->delete();
        FeeInvoice::whereIn('id', $invoiceIds)->delete();
    });
    echo "  ✓ Deleted {$invoiceCount} invoice(s) and all related payments/items." . PHP_EOL;
} else {
    echo "  — No invoices found for these students." . PHP_EOL;
}

echo PHP_EOL;

// ── Step 3: Clear JuicyWay data, reset for BudPay provisioning ────────────
echo "Resetting parent virtual account data..." . PHP_EOL;

$parentIds = ParentGuardian::whereHas('students', fn($q) =>
    $q->whereIn('students.id', $studentIds)
)->pluck('id')->toArray();

$parentsUpdated = ParentGuardian::whereIn('id', $parentIds)->update([
    // Clear JuicyWay fields
    'juicyway_customer_id'  => null,
    'juicyway_wallet_id'    => null,
    'juicyway_account_id'   => null,
    'juicyway_account_number' => null,
    'juicyway_bank_name'    => null,
    'juicyway_bank_code'    => null,
    'juicyway_wallet_status' => null,
    // Clear any existing BudPay data (fresh start)
    'budpay_customer_code'  => null,
    'budpay_account_number' => null,
    'budpay_bank_name'      => null,
    'budpay_bank_code'      => null,
    'budpay_wallet_status'  => null,
]);

echo "  ✓ Reset {$parentsUpdated} parent record(s)." . PHP_EOL . PHP_EOL;

// ── Step 4: Provision BudPay accounts ─────────────────────────────────────
echo "Dispatching BudPay provisioning jobs..." . PHP_EOL;

$parents = ParentGuardian::whereIn('id', $parentIds)
    ->with(['user', 'students'])
    ->get();

$dispatched = 0;
foreach ($parents as $parent) {
    if (! $parent->user) {
        echo "  ⚠ Parent #{$parent->id} has no user account — skipping." . PHP_EOL;
        continue;
    }

    $student = $parent->students->first();

    ProvisionParentWalletJob::dispatch($parent)->onQueue('provisioning');
    $dispatched++;

    echo "  ✓ Queued: {$parent->user->name} → student: {$student?->full_name}" . PHP_EOL;
}

echo PHP_EOL . $sep . PHP_EOL;
echo "Done. {$dispatched} provisioning job(s) dispatched to 'provisioning' queue." . PHP_EOL;
echo PHP_EOL;
echo "Monitor progress:" . PHP_EOL;
echo "  tail -f /var/www/schoolms/storage/logs/worker-provisioning.log" . PHP_EOL;
echo PHP_EOL;
echo "Verify results after ~1 minute:" . PHP_EOL;
echo "  php artisan tinker --execute=\"" . PHP_EOL;
echo "  App\Models\ParentGuardian::whereIn('id', [" . implode(',', $parentIds) . "])" . PHP_EOL;
echo "    ->get(['id','budpay_account_number','budpay_bank_name','budpay_wallet_status'])" . PHP_EOL;
echo "    ->each(fn(\\\$p) => print(\\\$p->id . ' | ' . \\\$p->budpay_wallet_status . ' | ' . \\\$p->budpay_account_number . PHP_EOL));" . PHP_EOL;
echo "  \"" . PHP_EOL;
echo $sep . PHP_EOL . PHP_EOL;
