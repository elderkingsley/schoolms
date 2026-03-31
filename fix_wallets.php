<?php
/**
 * One-time recovery script.
 *
 * 1. Deletes the orphaned test customer (cbf60d8c) from JuicyWay which is
 *    blocking wallet creation for real parents.
 * 2. Clears parent 84's stale customer_id so provisioning can start fresh.
 * 3. Runs the full three-step provisioning sequence for all stuck parents.
 *
 * ALSO handles any other parents stuck in failed/half-provisioned state.
 *
 * RUN:  cd /var/www/schoolms && php fix_wallets.php
 * DELETE after: rm fix_wallets.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ParentGuardian;
use App\Services\JuicyWayService;
use Illuminate\Support\Facades\Http;

$svc  = app(JuicyWayService::class);
$key  = config('services.juicyway.api_key');
$base = config('services.juicyway.base_url');

// ── Step 0: Delete the orphaned test customer that is blocking provisioning ──
$orphanedCustomerIds = [
    'cbf60d8c-e04b-43ad-8831-c9e0aa40b3cf', // "Test Parent" — created during debugging
];

echo "=== Cleaning up orphaned test customers ===\n";
foreach ($orphanedCustomerIds as $cid) {
    $r = Http::timeout(30)->withHeaders(['Authorization' => $key, 'Accept' => 'application/json'])
        ->delete($base . "/customers/{$cid}");
    echo "DELETE /customers/{$cid}: HTTP {$r->status()}" . ($r->status() === 204 ? " ✓ deleted" : " (already gone or error)") . "\n";
}

// ── Also clear the stale customer_id from parent 84 (if wallet_id is still NULL) ──
$parent84 = ParentGuardian::find(84);
if ($parent84 && empty($parent84->juicyway_wallet_id) && ! empty($parent84->juicyway_customer_id)) {
    echo "\nClearing stale customer_id from parent 84 (wallet_id is NULL — customer was orphaned)...\n";
    // Delete the old customer from JuicyWay too (in case it still exists)
    $r = Http::timeout(30)->withHeaders(['Authorization' => $key, 'Accept' => 'application/json'])
        ->delete($base . "/customers/{$parent84->juicyway_customer_id}");
    echo "DELETE /customers/{$parent84->juicyway_customer_id}: HTTP {$r->status()}\n";
    $parent84->update([
        'juicyway_customer_id'   => null,
        'juicyway_wallet_id'     => null,
        'juicyway_account_id'    => null,
        'juicyway_account_number'=> null,
        'juicyway_bank_name'     => null,
        'juicyway_bank_code'     => null,
        'juicyway_wallet_status' => null,
    ]);
    echo "Parent 84 reset to clean state.\n";
}

echo "\n=== Provisioning all unprovisioned / stuck parents ===\n";

// Find all parents who need provisioning
$parents = ParentGuardian::whereNotNull('user_id')
    ->where(function ($q) {
        $q->whereNull('juicyway_account_number')     // never provisioned OR stuck
          ->orWhere('juicyway_wallet_status', 'failed'); // previously failed
    })
    ->get();

echo "Found {$parents->count()} parent(s) to provision.\n\n";

foreach ($parents as $parent) {
    $parent->refresh();
    $student   = $parent->students()->first();
    $childName = $student ? "{$student->first_name} {$student->last_name}" : '(no student)';

    echo "--- Parent ID: {$parent->id} | {$parent->user->name} | Child: {$childName}\n";

    if (! $student) {
        echo "  SKIP: no student linked.\n\n";
        continue;
    }

    try {
        // Step 1
        if (empty($parent->juicyway_customer_id)) {
            $cid = $svc->createCustomer(
                $student->first_name, $student->last_name,
                $parent->user->email, $parent->phone ?? '08000000000'
            );
            $parent->update(['juicyway_customer_id' => $cid]);
            $parent->refresh();
            echo "  Step 1 OK: customer as '{$childName}' → {$cid}\n";
        } else {
            echo "  Step 1 SKIP: customer_id={$parent->juicyway_customer_id}\n";
        }

        // Step 2 — with duplicate recovery
        if (empty($parent->juicyway_wallet_id)) {
            try {
                $wallet = $svc->createWallet($parent->juicyway_customer_id);
            } catch (\RuntimeException $e) {
                if (! str_contains($e->getMessage(), 'duplicate_currency_wallet')) throw $e;

                echo "  Step 2: duplicate_currency_wallet — deleting orphaned customer and recreating...\n";
                $svc->deleteCustomer($parent->juicyway_customer_id);

                $newCid = $svc->createCustomer(
                    $student->first_name, $student->last_name,
                    $parent->user->email, $parent->phone ?? '08000000000'
                );
                $parent->update(['juicyway_customer_id' => $newCid]);
                $parent->refresh();
                echo "  Step 2: new customer_id={$newCid}\n";

                $wallet = $svc->createWallet($newCid);
            }

            $parent->update([
                'juicyway_wallet_id'  => $wallet['wallet_id'],
                'juicyway_account_id' => $wallet['account_id'],
            ]);
            $parent->refresh();
            echo "  Step 2 OK: wallet_id={$wallet['wallet_id']}\n";
        } else {
            echo "  Step 2 SKIP: wallet_id={$parent->juicyway_wallet_id}\n";
        }

        // Step 3
        if (empty($parent->juicyway_account_number)) {
            echo "  Step 3: requesting NUBAN (polling up to 30s)...\n";
            $bank = $svc->addBankAccount($parent->juicyway_wallet_id);
            $parent->update([
                'juicyway_account_number' => $bank['account_number'],
                'juicyway_bank_name'      => $bank['bank_name'],
                'juicyway_bank_code'      => $bank['bank_code'],
                'juicyway_wallet_status'  => 'active',
            ]);
            echo "  Step 3 OK: {$bank['bank_name']} — {$bank['account_number']} (name: {$childName})\n";
        } else {
            $parent->update(['juicyway_wallet_status' => 'active']);
            echo "  Step 3 SKIP: account_number={$parent->juicyway_account_number}\n";
        }

        echo "  ✓ Done.\n\n";

    } catch (\Throwable $e) {
        $parent->update(['juicyway_wallet_status' => 'failed']);
        echo "  ✗ FAILED: {$e->getMessage()}\n\n";
    }
}

echo "Complete. Run: rm fix_wallets.php\n";
