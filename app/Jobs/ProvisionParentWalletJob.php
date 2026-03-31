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
 * Provisions a JuicyWay virtual bank account (NUBAN) for a parent row.
 * Dispatched immediately when an enrolment is approved — not when an invoice
 * is sent — so the NUBAN is ready before the first invoice ever goes out.
 *
 * The JuicyWay customer is created in the CHILD's name so each NUBAN is
 * unambiguously linked to one student:
 *   "Jaanai Kingsley — Access Bank — 0812345678"
 * Email and phone remain the parent's (JuicyWay identity requirement).
 *
 * Three-step sequence — each step is idempotent (skipped if already done):
 *   1. POST /customers → juicyway_customer_id
 *   2. POST /wallets   → juicyway_wallet_id + juicyway_account_id
 *   3. POST /wallets/{id}/payment-method → polls for NUBAN
 *
 * duplicate_currency_wallet recovery:
 *   JuicyWay has no list-wallets endpoint. If step 1 succeeded but the job
 *   crashed before step 2, the wallet_id is unrecoverable. We delete the
 *   orphaned customer and recreate it — safe because it has no wallet or
 *   transactions. DELETE /customers/{id} returns 204 on success.
 */
class ProvisionParentWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 10;
    public int $timeout = 180; // 3 min — covers NUBAN polling (up to 120 s) + API calls

    public function backoff(): array
    {
        // Exponential backoff — spreads retries across JuicyWay outages
        return [30, 60, 120, 240, 480, 960, 1920, 3600, 3600, 3600];
    }

    public function __construct(public readonly ParentGuardian $parent) {}

    public function handle(JuicyWayService $juicyWay): void
    {
        $parent = $this->parent->fresh()->load(['user', 'students']);

        if (! $parent->user) {
            Log::warning("ProvisionParentWalletJob: parent {$parent->id} has no user account — skipping.");
            return;
        }

        // Already fully provisioned — nothing to do
        if ($parent->hasVirtualAccount()) {
            Log::info("ProvisionParentWalletJob: parent {$parent->id} already has NUBAN — skipping.");
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProvisionParentWalletJob: parent {$parent->id} has no linked student — skipping.");
            return;
        }

        try {
            $parent->update(['juicyway_wallet_status' => 'pending']);

            // ── Step 1: Customer in the CHILD's name ─────────────────────
            if (empty($parent->juicyway_customer_id)) {
                $customerId = $juicyWay->createCustomer(
                    firstName: $student->first_name,
                    lastName:  $student->last_name,
                    email:     $parent->user->email,
                    phone:     $parent->phone ?? '08000000000',
                );
                $parent->update(['juicyway_customer_id' => $customerId]);
                $parent->refresh();
                Log::info("ProvisionParentWalletJob: customer created for parent {$parent->id}", [
                    'customer_id'   => $customerId,
                    'student_name'  => "{$student->first_name} {$student->last_name}",
                ]);
            }

            // ── Step 2: Wallet — with duplicate_currency_wallet recovery ──
            if (empty($parent->juicyway_wallet_id)) {
                try {
                    $wallet = $juicyWay->createWallet($parent->juicyway_customer_id);

                } catch (\RuntimeException $e) {
                    if (! str_contains($e->getMessage(), 'duplicate_currency_wallet')) {
                        throw $e;
                    }

                    // Orphaned customer — delete it and recreate fresh
                    Log::warning("ProvisionParentWalletJob: duplicate_currency_wallet for parent {$parent->id} — deleting orphaned customer.", [
                        'old_customer_id' => $parent->juicyway_customer_id,
                    ]);

                    $juicyWay->deleteCustomer($parent->juicyway_customer_id);

                    $newCustomerId = $juicyWay->createCustomer(
                        firstName: $student->first_name,
                        lastName:  $student->last_name,
                        email:     $parent->user->email,
                        phone:     $parent->phone ?? '08000000000',
                    );
                    $parent->update(['juicyway_customer_id' => $newCustomerId]);
                    $parent->refresh();

                    Log::info("ProvisionParentWalletJob: recreated customer for parent {$parent->id}", [
                        'new_customer_id' => $newCustomerId,
                    ]);

                    $wallet = $juicyWay->createWallet($newCustomerId);
                }

                $parent->update([
                    'juicyway_wallet_id'  => $wallet['wallet_id'],
                    'juicyway_account_id' => $wallet['account_id'],
                ]);
                $parent->refresh();
                Log::info("ProvisionParentWalletJob: wallet created for parent {$parent->id}");
            }

            // ── Step 3: NUBAN ─────────────────────────────────────────────
            if (empty($parent->juicyway_account_number)) {
                $bank = $juicyWay->addBankAccount($parent->juicyway_wallet_id);
                $parent->update([
                    'juicyway_account_number' => $bank['account_number'],
                    'juicyway_bank_name'      => $bank['bank_name'],
                    'juicyway_bank_code'      => $bank['bank_code'],
                    'juicyway_wallet_status'  => 'active',
                ]);
                Log::info("ProvisionParentWalletJob: NUBAN provisioned for parent {$parent->id}", [
                    'account_number' => $bank['account_number'],
                    'bank_name'      => $bank['bank_name'],
                    'student'        => "{$student->first_name} {$student->last_name}",
                ]);
            } else {
                $parent->update(['juicyway_wallet_status' => 'active']);
                Log::info("ProvisionParentWalletJob: parent {$parent->id} already has NUBAN — marked active.");
            }

        } catch (\Throwable $e) {
            $parent->update(['juicyway_wallet_status' => 'failed']);
            Log::error("ProvisionParentWalletJob: failed for parent {$parent->id}: {$e->getMessage()}");
            throw $e; // re-throw so queue retries with backoff
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
