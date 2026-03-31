<?php
/**
 * One-time recovery script for stuck/failed parent wallet provisioning.
 *
 * Fixes every parent who:
 *  - has juicyway_wallet_status = 'failed', OR
 *  - has a customer_id but no wallet_id (crashed between steps 1 and 2), OR
 *  - has a wallet_id but no account_number (crashed between steps 2 and 3)
 *
 * Uses the child's name for the JuicyWay customer (same as new code).
 * For parents already provisioned in the parent's name, their existing
 * accounts are left untouched — only new/recovery provisioning uses child names.
 *
 * RUN:
 *   cd /var/www/schoolms
 *   php fix_wallets.php
 *
 * DELETE after running:
 *   rm fix_wallets.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ParentGuardian;
use App\Services\JuicyWayService;

$svc = app(JuicyWayService::class);

$parents = ParentGuardian::whereNotNull('user_id')
    ->where(function ($q) {
        $q->where('juicyway_wallet_status', 'failed')
          ->orWhere(function ($q2) {
              $q2->whereNotNull('juicyway_customer_id')
                 ->whereNull('juicyway_wallet_id');
          })
          ->orWhere(function ($q3) {
              $q3->whereNotNull('juicyway_wallet_id')
                 ->whereNull('juicyway_account_number');
          });
    })
    ->get();

echo "Found {$parents->count()} parent(s) to fix.\n\n";

foreach ($parents as $parent) {
    $student = $parent->students()->first();
    $childName = $student ? "{$student->first_name} {$student->last_name}" : '(no student linked)';

    echo "--- Parent ID: {$parent->id} | User: {$parent->user->name} | Child: {$childName}\n";

    try {
        // Step 1
        if (empty($parent->juicyway_customer_id)) {
            if (! $student) {
                echo "  ✗ SKIP: no student linked to this parent row.\n\n";
                continue;
            }
            $customerId = $svc->createCustomer(
                $student->first_name,
                $student->last_name,
                $parent->user->email,
                $parent->phone ?? '08000000000'
            );
            $parent->update(['juicyway_customer_id' => $customerId]);
            $parent->refresh();
            echo "  Step 1 OK: customer created as '{$childName}' → {$customerId}\n";
        } else {
            echo "  Step 1 SKIP: customer_id already exists ({$parent->juicyway_customer_id})\n";
        }

        // Step 2 (createWallet handles duplicate internally)
        if (empty($parent->juicyway_wallet_id)) {
            $wallet = $svc->createWallet($parent->juicyway_customer_id);
            $parent->update([
                'juicyway_wallet_id'  => $wallet['wallet_id'],
                'juicyway_account_id' => $wallet['account_id'],
            ]);
            $parent->refresh();
            echo "  Step 2 OK: wallet_id={$wallet['wallet_id']}\n";
        } else {
            echo "  Step 2 SKIP: wallet_id already exists ({$parent->juicyway_wallet_id})\n";
        }

        // Step 3
        if (empty($parent->juicyway_account_number)) {
            echo "  Step 3: requesting NUBAN (polling up to 30 s)...\n";
            $bank = $svc->addBankAccount($parent->juicyway_wallet_id);
            $parent->update([
                'juicyway_account_number' => $bank['account_number'],
                'juicyway_bank_name'      => $bank['bank_name'],
                'juicyway_bank_code'      => $bank['bank_code'],
                'juicyway_wallet_status'  => 'active',
            ]);
            echo "  Step 3 OK: {$bank['bank_name']} — {$bank['account_number']}\n";
        } else {
            $parent->update(['juicyway_wallet_status' => 'active']);
            echo "  Step 3 SKIP: account_number already exists ({$parent->juicyway_account_number})\n";
        }

        echo "  ✓ Parent {$parent->id} fully provisioned.\n\n";

    } catch (\Throwable $e) {
        $parent->update(['juicyway_wallet_status' => 'failed']);
        echo "  ✗ FAILED: {$e->getMessage()}\n\n";
    }
}

echo "Done. Delete this file: rm fix_wallets.php\n";
