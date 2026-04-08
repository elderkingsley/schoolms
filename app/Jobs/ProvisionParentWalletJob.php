<?php

namespace App\Jobs;

use App\Models\ParentGuardian;
use App\Services\BudPayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProvisionParentWalletJob — BudPay edition
 *
 * Provisions a BudPay dedicated virtual bank account (NUBAN) for a parent.
 * Dispatched when an enrolment is approved so the NUBAN is ready before
 * any invoice is ever sent.
 *
 * BudPay is simpler than JuicyWay — only 2 steps, fully synchronous:
 *   Step 1 — POST /customer    → budpay_customer_code
 *   Step 2 — POST /dedicated_virtual_account → NUBAN immediately
 *
 * No polling needed. If BudPay returns the account number, we're done.
 *
 * Idempotency:
 *   - If budpay_account_number is already set, skip entirely.
 *   - If budpay_customer_code is set but account is missing, skip step 1.
 *
 * The customer is created in the CHILD's name so the bank statement reads
 * "Nurtureville / Uchechi Smart" — unambiguously linked to one student.
 */
class ProvisionParentWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 5;
    public int $timeout = 60;

    public function backoff(): array
    {
        return [30, 60, 120, 300, 600];
    }

    public function __construct(public readonly ParentGuardian $parent) {}

    public function handle(BudPayService $budPay): void
    {
        $parent = $this->parent->fresh()->load(['user', 'students']);

        if (! $parent->user) {
            Log::warning("ProvisionParentWalletJob: parent {$parent->id} has no user account — skipping.");
            return;
        }

        // Already fully provisioned — nothing to do
        if (! empty($parent->budpay_account_number)) {
            Log::info("ProvisionParentWalletJob: parent {$parent->id} already has BudPay NUBAN — skipping.");
            $parent->update(['budpay_wallet_status' => 'active']);
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProvisionParentWalletJob: parent {$parent->id} has no linked student — skipping.");
            return;
        }

        try {
            $parent->update(['budpay_wallet_status' => 'pending']);

            // ── Step 1: Create BudPay customer in child's name ────────────
            if (empty($parent->budpay_customer_code)) {
                $customerCode = $budPay->createCustomer(
                    firstName: $student->first_name,
                    lastName:  $student->last_name,
                    email:     $parent->user->email,
                    phone:     $parent->phone ?? '08000000000',
                );

                $parent->update(['budpay_customer_code' => $customerCode]);
                $parent->refresh();

                Log::info("ProvisionParentWalletJob: BudPay customer created for parent {$parent->id}", [
                    'customer_code' => $customerCode,
                    'student'       => $student->full_name,
                ]);
            }

            // ── Step 2: Assign dedicated NUBAN ────────────────────────────
            // BudPay returns the account number synchronously — no polling.
            $account = $budPay->createDedicatedAccount($parent->budpay_customer_code);

            $parent->update([
                'budpay_account_number' => $account['account_number'],
                'budpay_bank_name'      => $account['bank_name'],
                'budpay_bank_code'      => $account['bank_code'],
                'budpay_wallet_status'  => 'active',
            ]);

            Log::info("ProvisionParentWalletJob: BudPay NUBAN provisioned for parent {$parent->id}", [
                'account_number' => $account['account_number'],
                'bank_name'      => $account['bank_name'],
                'student'        => $student->full_name,
            ]);

        } catch (\Throwable $e) {
            $parent->update(['budpay_wallet_status' => 'failed']);
            Log::error("ProvisionParentWalletJob: failed for parent {$parent->id}: {$e->getMessage()}");
            throw $e; // re-throw so queue retries with backoff
        }
    }

    public function failed(?\Throwable $e): void
    {
        $this->parent->fresh()?->update(['budpay_wallet_status' => 'failed']);

        Log::critical("ProvisionParentWalletJob: permanently failed for parent {$this->parent->id}", [
            'error' => $e?->getMessage(),
        ]);

        // Email all admins
        try {
            $admins = \App\Models\User::whereIn('user_type', ['super_admin', 'admin'])
                ->where('is_active', true)->get();

            foreach ($admins as $admin) {
                \Illuminate\Support\Facades\Mail::raw(
                    "ALERT: BudPay virtual account provisioning permanently failed.\n\n" .
                    "Parent ID: {$this->parent->id}\n" .
                    "Error: " . $e?->getMessage() . "\n\n" .
                    "This parent cannot receive automated payment detection until resolved.\n\n" .
                    "Time: " . now()->format('d M Y, g:ia') . " (Africa/Lagos)",
                    fn($m) => $m
                        ->to($admin->email)
                        ->subject('⚠️ BudPay Provisioning Failed — Nurtureville SchoolMS')
                );
            }
        } catch (\Throwable $mailError) {
            Log::error('ProvisionParentWalletJob: failed to send failure alert — ' . $mailError->getMessage());
        }
    }
}
