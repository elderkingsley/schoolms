<?php

namespace App\Jobs;

use App\Models\ParentGuardian;
use App\Services\JuicyWayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProvisionParentWalletJob
 *
 * Provisions a JuicyWay virtual bank account (NUBAN) for a parent.
 * Mirrors PayGrid's ProvisionJuicyWayWalletJob exactly.
 *
 * Three-step sequence (each step is idempotent — skipped if already done):
 *   1. POST /customers         → juicyway_customer_id
 *   2. POST /wallets           → juicyway_wallet_id + juicyway_account_id
 *   3. POST /wallets/{id}/payment-method → polls for NUBAN
 *
 * The resulting NUBAN (juicyway_account_number) is included in every
 * invoice email sent to this parent. When money arrives in the NUBAN,
 * JuicyWay webhooks SchoolMS which auto-records the payment.
 *
 * Dispatched from SendInvoiceJob the first time an invoice is sent to a
 * parent who does not yet have a virtual account.
 */
class ProvisionParentWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 10;
    public int   $timeout = 120;  // 2 min — covers NUBAN polling loop

    /**
     * Exponential backoff — mirrors PayGrid.
     * Spreads retries so transient JuicyWay outages don't exhaust all attempts quickly.
     */
    public function backoff(): array
    {
        return [30, 60, 120, 240, 480, 960, 1920, 3600, 3600, 3600];
    }

    public function __construct(public readonly ParentGuardian $parent) {}

    public function handle(JuicyWayService $juicyWay): void
    {
        $parent = $this->parent->fresh()->load('user');

        if (! $parent->user) {
            Log::warning("ProvisionParentWalletJob: parent {$parent->id} has no user account — skipping.");
            return;
        }

        try {
            $parent->update(['juicyway_wallet_status' => 'pending']);

            // ── Step 1: Create JuicyWay customer ─────────────────────────
            if (empty($parent->juicyway_customer_id)) {

                // Split user name into first/last — same strategy as PayGrid
                $nameParts = explode(' ', trim($parent->user->name), 2);
                $firstName = $nameParts[0];
                $lastName  = $nameParts[1] ?? $nameParts[0];

                $customerId = $juicyWay->createCustomer(
                    firstName: $firstName,
                    lastName:  $lastName,
                    email:     $parent->user->email,
                    phone:     $parent->phone ?? '08000000000',
                );

                $parent->update(['juicyway_customer_id' => $customerId]);
                $parent->refresh();

                Log::info("ProvisionParentWalletJob: customer created for parent {$parent->id}", [
                    'customer_id' => $customerId,
                    'name'        => "{$firstName} {$lastName}",
                ]);
            }

            // ── Step 2: Create NGN wallet ─────────────────────────────────
            if (empty($parent->juicyway_wallet_id)) {
                $wallet = $juicyWay->createWallet($parent->juicyway_customer_id);

                $parent->update([
                    'juicyway_wallet_id'  => $wallet['wallet_id'],
                    'juicyway_account_id' => $wallet['account_id'],
                ]);
                $parent->refresh();

                Log::info("ProvisionParentWalletJob: wallet created for parent {$parent->id}", $wallet);
            }

            // ── Step 3: Provision NUBAN ───────────────────────────────────
            if (empty($parent->juicyway_account_number)) {
                $bankAccount = $juicyWay->addBankAccount($parent->juicyway_wallet_id);

                $parent->update([
                    'juicyway_account_number' => $bankAccount['account_number'],
                    'juicyway_bank_name'      => $bankAccount['bank_name'],
                    'juicyway_bank_code'      => $bankAccount['bank_code'],
                    'juicyway_wallet_status'  => 'active',
                ]);

                Log::info("ProvisionParentWalletJob: NUBAN provisioned for parent {$parent->id}", [
                    'account_number' => $bankAccount['account_number'],
                    'bank_name'      => $bankAccount['bank_name'],
                ]);
            } else {
                // Already has a NUBAN — just mark active
                $parent->update(['juicyway_wallet_status' => 'active']);
            }

        } catch (\Throwable $e) {
            $parent->update(['juicyway_wallet_status' => 'failed']);
            Log::error("ProvisionParentWalletJob: failed for parent {$parent->id}: {$e->getMessage()}");
            throw $e; // re-throw so queue retries
        }
    }

    public function failed(?\Throwable $e): void
    {
        $this->parent->fresh()?->update(['juicyway_wallet_status' => 'failed']);

        Log::critical("ProvisionParentWalletJob: permanently failed for parent {$this->parent->id}", [
            'error' => $e?->getMessage(),
        ]);
    }
}
