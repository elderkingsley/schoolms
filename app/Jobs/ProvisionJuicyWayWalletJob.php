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
 * ProvisionJuicyWayWalletJob
 *
 * Provisions a JuicyWay dedicated virtual bank account (NUBAN) for a parent.
 * Dispatched when an enrolment is approved AND JuicyWay is the active provider.
 *
 * JuicyWay provisioning flow (3 steps with polling):
 *   Step 1 — createCustomer() in CHILD's name for reconciliation clarity
 *   Step 2 — createWallet() → NGN wallet per customer
 *   Step 3 — addBankAccount() → polls until NUBAN appears (up to 6 attempts)
 *
 * The customer is created in the CHILD's name so the bank statement reads
 * "Nurtureville / Uchechi Smart" — unambiguously linked to one student.
 *
 * Idempotency:
 *   - If juicyway_account_number is already set, skip entirely.
 *   - If juicyway_customer_id exists but no account, resume from step 2.
 *
 * Recovery from duplicate_currency_wallet:
 *   JuicyWay has no list-wallets endpoint. If a job crashed between steps 1-2,
 *   the wallet_id is lost. We delete the orphaned customer and start fresh.
 */
class ProvisionJuicyWayWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 180; // 3 minutes for polling

    public function backoff(): array
    {
        return [30, 60, 120, 300, 600];
    }

    public function __construct(public readonly ParentGuardian $parent) {}

    public function handle(JuicyWayService $juicyWay): void
    {
        $parent = $this->parent->fresh()->load(['user', 'students']);

        if (! $parent->user) {
            Log::warning("ProvisionJuicyWayWalletJob: parent {$parent->id} has no user account — skipping.");
            return;
        }

        // Already fully provisioned — nothing to do
        if (! empty($parent->juicyway_account_number)) {
            Log::info("ProvisionJuicyWayWalletJob: parent {$parent->id} already has JuicyWay NUBAN — skipping.");
            $parent->update(['juicyway_wallet_status' => 'active']);
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProvisionJuicyWayWalletJob: parent {$parent->id} has no linked student — skipping.");
            return;
        }

        try {
            $parent->update(['juicyway_wallet_status' => 'pending']);

            // ── Step 1: Create JuicyWay customer in child's name ────────────
            if (empty($parent->juicyway_customer_id)) {
                $customerId = $juicyWay->createCustomer(
                    firstName: $student->first_name,
                    lastName:  $student->last_name,
                    email:     $parent->user->email,
                    phone:     $parent->phone ?? '08000000000',
                );

                $parent->update(['juicyway_customer_id' => $customerId]);
                $parent->refresh();

                Log::info("ProvisionJuicyWayWalletJob: JuicyWay customer created for parent {$parent->id}", [
                    'customer_id' => $customerId,
                    'student'     => $student->full_name,
                ]);
            }

            // ── Step 2: Create NGN wallet ──────────────────────────────────
            if (empty($parent->juicyway_wallet_id)) {
                try {
                    $wallet = $juicyWay->createWallet($parent->juicyway_customer_id);

                    $parent->update([
                        'juicyway_wallet_id'  => $wallet['wallet_id'],
                        'juicyway_account_id' => $wallet['account_id'],
                    ]);
                    $parent->refresh();

                    Log::info("ProvisionJuicyWayWalletJob: wallet created for parent {$parent->id}", [
                        'wallet_id'  => $wallet['wallet_id'],
                        'account_id' => $wallet['account_id'],
                    ]);
                } catch (\RuntimeException $e) {
                    // duplicate_currency_wallet → wallet already exists but ID is lost.
                    // Recovery: delete orphaned customer, then retry from scratch.
                    if (str_contains($e->getMessage(), 'duplicate_currency_wallet')) {
                        Log::warning("ProvisionJuicyWayWalletJob: duplicate_currency_wallet — deleting orphaned customer and retrying.", [
                            'parent_id'   => $parent->id,
                            'customer_id' => $parent->juicyway_customer_id,
                        ]);

                        $juicyWay->deleteCustomer($parent->juicyway_customer_id);

                        $parent->update([
                            'juicyway_customer_id' => null,
                            'juicyway_wallet_id'   => null,
                            'juicyway_account_id'  => null,
                        ]);

                        throw $e; // Retry with backoff
                    }

                    throw $e;
                }
            }

            // ── Step 3: Provision NUBAN ────────────────────────────────────
            $account = $juicyWay->addBankAccount($parent->juicyway_wallet_id);

            $parent->update([
                'juicyway_account_number' => $account['account_number'],
                'juicyway_bank_name'      => $account['bank_name'],
                'juicyway_bank_code'      => $account['bank_code'],
                'juicyway_wallet_status'  => 'active',
            ]);

            Log::info("ProvisionJuicyWayWalletJob: JuicyWay NUBAN provisioned for parent {$parent->id}", [
                'account_number' => $account['account_number'],
                'bank_name'      => $account['bank_name'],
                'student'        => $student->full_name,
            ]);

        } catch (\Throwable $e) {
            $parent->update(['juicyway_wallet_status' => 'failed']);
            Log::error("ProvisionJuicyWayWalletJob: failed for parent {$parent->id}: {$e->getMessage()}");
            throw $e; // re-throw so queue retries with backoff
        }
    }

    public function failed(?\Throwable $e): void
    {
        $this->parent->fresh()?->update(['juicyway_wallet_status' => 'failed']);

        Log::critical("ProvisionJuicyWayWalletJob: permanently failed for parent {$this->parent->id}", [
            'error' => $e?->getMessage(),
        ]);

        // Email all admins
        try {
            $admins = \App\Models\User::whereIn('user_type', ['super_admin', 'admin'])
                ->where('is_active', true)
                ->get();

            foreach ($admins as $admin) {
                \Illuminate\Support\Facades\Mail::raw(
                    "ALERT: JuicyWay virtual account provisioning permanently failed.\n\n" .
                    "Parent ID: {$this->parent->id}\n" .
                    "Parent Name: {$this->parent->user?->name}\n" .
                    "Error: " . ($e?->getMessage() ?? 'Unknown error') . "\n\n" .
                    "This parent cannot receive automated payment detection until resolved.\n\n" .
                    "Time: " . now()->format('d M Y, g:ia') . " (Africa/Lagos)",
                    fn($m) => $m
                        ->to($admin->email)
                        ->subject('⚠️ JuicyWay Provisioning Failed — Nurtureville SchoolMS')
                );
            }
        } catch (\Throwable $mailError) {
            Log::error('ProvisionJuicyWayWalletJob: failed to send failure alert — ' . $mailError->getMessage());
        }
    }
}
