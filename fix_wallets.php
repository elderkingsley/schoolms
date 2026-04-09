<?php
// Deploy to: /var/www/schoolms/scripts/fix_wallet_status.php

/**
 * fix_wallet_status.php
 *
 * One-time repair script for parents whose budpay_wallet_status is stuck
 * at 'failed' (or 'pending') even though their NUBAN was successfully
 * provisioned and is sitting in budpay_account_number in the DB.
 *
 * This happens when ProvisionParentWalletJob retried, succeeded in
 * creating the BudPay account, but the final update() call was interrupted
 * — or when the job permanently failed AFTER BudPay had already assigned
 * the account (BudPay provisioned it, our DB save crashed).
 *
 * Run on the server:
 *   cd /var/www/schoolms
 *   php scripts/fix_wallet_status.php
 *
 * Safe to run multiple times — uses dry-run mode by default.
 * Set DRY_RUN=false to apply changes.
 */

define('DRY_RUN', true); // ← set to false to apply changes

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== fix_wallet_status.php ===\n";
echo "Mode: " . (DRY_RUN ? "DRY RUN (no changes will be saved)" : "LIVE — changes will be applied") . "\n\n";

// ── Case 1: NUBAN present but status is not 'active' ─────────────────────────
// These are the stuck-failed parents. The NUBAN is in the DB, BudPay is
// working, but our status column never got updated to 'active'.

$stuck = DB::table('parents')
    ->whereNotNull('budpay_account_number')
    ->where(fn($q) => $q
        ->where('budpay_wallet_status', '!=', 'active')
        ->orWhereNull('budpay_wallet_status')
    )
    ->select('id', 'budpay_account_number', 'budpay_bank_name', 'budpay_wallet_status')
    ->get();

echo "Found {$stuck->count()} parent(s) with NUBAN present but status NOT active:\n\n";

foreach ($stuck as $row) {
    echo "  Parent #{$row->id} | NUBAN: {$row->budpay_account_number} | Bank: {$row->budpay_bank_name} | Status: {$row->budpay_wallet_status}\n";
    if (! DRY_RUN) {
        DB::table('parents')->where('id', $row->id)->update([
            'budpay_wallet_status' => 'active',
            'updated_at'           => now(),
        ]);
        echo "    → Fixed to 'active'\n";
    } else {
        echo "    → Would fix to 'active'\n";
    }
}

// ── Case 2: Status is 'failed' AND no NUBAN — these need a real retry ────────
$needsRetry = DB::table('parents')
    ->whereNull('budpay_account_number')
    ->where('budpay_wallet_status', 'failed')
    ->whereNotNull('user_id')
    ->select('id', 'budpay_customer_code', 'budpay_wallet_status')
    ->get();

echo "\nFound {$needsRetry->count()} parent(s) with failed status AND no NUBAN (need real retry):\n\n";

foreach ($needsRetry as $row) {
    echo "  Parent #{$row->id} | customer_code: " . ($row->budpay_customer_code ?? 'none') . "\n";
    echo "    → Use the 'Retry NUBAN' button on the admin parent page, or dispatch ProvisionParentWalletJob manually.\n";
}

echo "\nDone.\n";
if (DRY_RUN) {
    echo "\nTo apply Case 1 fixes, set DRY_RUN = false and run again.\n";
}
