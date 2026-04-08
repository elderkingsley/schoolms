<?php

namespace App\Jobs;

use App\Models\ParentGuardian;
use App\Services\KorapayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProvisionParentWalletJob — Korapay edition
 *
 * Provisions a permanent Korapay virtual bank account (NUBAN) for a parent.
 * Dispatched when an enrolment is approved so the NUBAN is ready before
 * any invoice is ever sent.
 *
 * Korapay is a single-step flow:
 *   POST /virtual-bank-account → returns permanent NUBAN immediately
 *
 * The account_reference is deterministic (NV-P{parentId}-S{studentId})
 * so if the job runs twice, the second call either returns the existing
 * account or we fetch it — no duplicate accounts are created.
 *
 * The account is created in the student's name so the bank statement
 * reads "Nurtureville / Uchechi Smart" — unambiguous per student.
 */
class ProvisionParentWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 5;
    public int   $timeout = 60;

    public function backoff(): array
    {
        return [30, 60, 120, 300, 600];
    }

    public function __construct(public readonly ParentGuardian $parent) {}

    public function handle(KorapayService $korapay): void
    {
        $parent = $this->parent->fresh()->load(['user', 'students']);

        if (! $parent->user) {
            Log::warning("ProvisionParentWalletJob: parent {$parent->id} has no user account — skipping.");
            return;
        }

        // Already fully provisioned with Korapay — nothing to do
        if (! empty($parent->korapay_account_number)) {
            Log::info("ProvisionParentWalletJob: parent {$parent->id} already has Korapay NUBAN — skipping.");
            $parent->update(['korapay_wallet_status' => 'active']);
            return;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("ProvisionParentWalletJob: parent {$parent->id} has no linked student — skipping.");
            return;
        }

        // Generate deterministic reference — stable across retries
        $accountReference = KorapayService::makeAccountReference($parent->id, $student->id);

        try {
            $parent->update(['korapay_wallet_status' => 'pending']);

            $account = $korapay->createVirtualAccount(
                studentName:      $student->full_name,
                parentEmail:      $parent->user->email,
                accountReference: $accountReference,
            );

            $parent->update([
                'korapay_account_reference' => $account['account_reference'],
                'korapay_account_number'    => $account['account_number'],
                'korapay_bank_name'         => $account['bank_name'],
                'korapay_bank_code'         => $account['bank_code'],
                'korapay_wallet_status'     => 'active',
            ]);

            Log::info("ProvisionParentWalletJob: Korapay NUBAN provisioned for parent {$parent->id}", [
                'account_number'    => $account['account_number'],
                'bank_name'         => $account['bank_name'],
                'account_reference' => $account['account_reference'],
                'student'           => $student->full_name,
            ]);

        } catch (\Throwable $e) {
            $parent->update(['korapay_wallet_status' => 'failed']);
            Log::error("ProvisionParentWalletJob: failed for parent {$parent->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(?\Throwable $e): void
    {
        $this->parent->fresh()?->update(['korapay_wallet_status' => 'failed']);

        Log::critical("ProvisionParentWalletJob: permanently failed for parent {$this->parent->id}", [
            'error' => $e?->getMessage(),
        ]);

        try {
            $admins = \App\Models\User::whereIn('user_type', ['super_admin', 'admin'])
                ->where('is_active', true)->get();

            foreach ($admins as $admin) {
                \Illuminate\Support\Facades\Mail::raw(
                    "ALERT: Korapay virtual account provisioning permanently failed.\n\n" .
                    "Parent ID: {$this->parent->id}\n" .
                    "Error: " . $e?->getMessage() . "\n\n" .
                    "This parent cannot receive automated payment detection until resolved.\n\n" .
                    "Time: " . now()->format('d M Y, g:ia') . " (Africa/Lagos)",
                    fn($m) => $m
                        ->to($admin->email)
                        ->subject('⚠️ Korapay Provisioning Failed — Nurtureville SchoolMS')
                );
            }
        } catch (\Throwable $mailError) {
            Log::error('ProvisionParentWalletJob: failed to send alert — ' . $mailError->getMessage());
        }
    }
}
