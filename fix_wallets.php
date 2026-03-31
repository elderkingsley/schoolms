<?php
/**
 * One-time recovery script.
 * Fixes every parent stuck in failed/half-provisioned state.
 *
 * The duplicate_currency_wallet case is handled by deleting the orphaned
 * customer and recreating it fresh — same logic as SendInvoiceJob.
 *
 * RUN:  cd /var/www/schoolms && php fix_wallets.php
 * DELETE after: rm fix_wallets.php
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
    $student   = $parent->students()->first();
    $childName = $student ? "{$student->first_name} {$student->last_name}" : '(no student)';

    echo "--- Parent ID: {$parent->id} | {$parent->user->name} | Child: {$childName}\n";

    if (! $student) {
        echo "  SKIP: no student linked.\n\n";
        continue;
    }

    try {
        // ── Step 1 ───────────────────────────────────────────────────────
        if (empty($parent->juicyway_customer_id)) {
            $cid = $svc->createCustomer(
                $student->first_name, $student->last_name,
                $parent->user->email, $parent->phone ?? '08000000000'
            );
            $parent->update(['juicyway_customer_id' => $cid]);
            $parent->refresh();
            echo "  Step 1 OK: created customer as '{$childName}' → {$cid}\n";
        } else {
            echo "  Step 1 SKIP: customer_id={$parent->juicyway_customer_id}\n";
        }

        // ── Step 2 ───────────────────────────────────────────────────────
        if (empty($parent->juicyway_wallet_id)) {
            try {
                $wallet = $svc->createWallet($parent->juicyway_customer_id);
                echo "  Step 2 OK: wallet_id={$wallet['wallet_id']}\n";

            } catch (\RuntimeException $e) {
                if (! str_contains($e->getMessage(), 'duplicate_currency_wallet')) {
                    throw $e;
                }

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
                echo "  Step 2 OK: wallet_id={$wallet['wallet_id']}\n";
            }

            $parent->update([
                'juicyway_wallet_id'  => $wallet['wallet_id'],
                'juicyway_account_id' => $wallet['account_id'],
            ]);
            $parent->refresh();
        } else {
            echo "  Step 2 SKIP: wallet_id={$parent->juicyway_wallet_id}\n";
        }

        // ── Step 3 ───────────────────────────────────────────────────────
        if (empty($parent->juicyway_account_number)) {
            echo "  Step 3: requesting NUBAN (polling up to 30s)...\n";
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
            echo "  Step 3 SKIP: account_number={$parent->juicyway_account_number}\n";
        }

        echo "  ✓ Done.\n\n";

    } catch (\Throwable $e) {
        $parent->update(['juicyway_wallet_status' => 'failed']);
        echo "  ✗ FAILED: {$e->getMessage()}\n\n";
    }
}

echo "Complete. Run: rm fix_wallets.php\n";
