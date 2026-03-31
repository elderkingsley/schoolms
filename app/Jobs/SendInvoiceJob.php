<?php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Models\ParentGuardian;
use App\Notifications\FeeInvoiceNotification;
use App\Services\JuicyWayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public int   $timeout = 180;  // 3 min: NUBAN polling is up to 30 s per parent

    public array $backoff = [30, 60, 120];

    public function __construct(public FeeInvoice $invoice) {}

    public function handle(JuicyWayService $juicyWay): void
    {
        $invoice = $this->invoice->load([
            'student.parents.user',
            'items.feeItem',
            'term.session',
        ]);

        $parents = $invoice->student->parents->filter(fn($p) => $p->user !== null);

        if ($parents->isEmpty()) {
            Log::info("SendInvoiceJob: no parent accounts for student {$invoice->student_id}");
        }

        foreach ($parents as $parent) {
            // ── 1. Provision virtual account BEFORE sending email ─────────
            // Runs synchronously so the NUBAN is ready in the inbox.
            // If provisioning fails, we still send the email with bursary fallback.
            try {
                $this->ensureVirtualAccount($parent, $juicyWay);
                $parent->refresh(); // pick up fresh account details
            } catch (\Throwable $e) {
                Log::error('SendInvoiceJob: provisioning failed for parent', [
                    'invoice_id' => $invoice->id,
                    'parent_id'  => $parent->id,
                    'error'      => $e->getMessage(),
                ]);
                // Mark failed so admin can see it and retry
                $parent->update(['juicyway_wallet_status' => 'failed']);
            }

            // ── 2. Send the email (always, even if provisioning failed) ───
            try {
                $parent->user->notify(
                    new FeeInvoiceNotification($invoice, $invoice->items)
                );
            } catch (\Throwable $e) {
                Log::error('SendInvoiceJob: email failed for parent', [
                    'invoice_id' => $invoice->id,
                    'parent_id'  => $parent->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $invoice->update(['sent_at' => now()]);
        Log::info("SendInvoiceJob: invoice {$invoice->id} sent.");
    }

    /**
     * Provision a JuicyWay virtual bank account for this parent row.
     *
     * IMPORTANT: The JuicyWay CUSTOMER is created in the CHILD's name
     * (student first_name + last_name), not the parent's name. This means
     * the NUBAN is issued as e.g. "Adaeze Okonkwo — Access Bank — 0812345678",
     * making reconciliation unambiguous when multiple children share a parent.
     *
     * The email and phone remain the parent's — JuicyWay requires a real
     * person's contact details for identity purposes.
     *
     * Each parent row is linked to exactly one child via parent_student pivot,
     * so $parent->students()->first() reliably returns the right student.
     */
    private function ensureVirtualAccount(ParentGuardian $parent, JuicyWayService $juicyWay): void
    {
        // Already fully provisioned — nothing to do
        if ($parent->hasVirtualAccount()) {
            return;
        }

        $parent->update(['juicyway_wallet_status' => 'pending']);

        // Get the child this parent row is linked to
        $student = $parent->students()->first();

        if (! $student) {
            throw new \RuntimeException(
                "ProvisionWallet: parent {$parent->id} has no linked student."
            );
        }

        // ── Step 1: Customer — in the CHILD's name ───────────────────────
        if (empty($parent->juicyway_customer_id)) {
            $customerId = $juicyWay->createCustomer(
                firstName: $student->first_name,         // child's first name
                lastName:  $student->last_name,          // child's last name
                email:     $parent->user->email,         // parent's email
                phone:     $parent->phone ?? '08000000000', // parent's phone
            );
            $parent->update(['juicyway_customer_id' => $customerId]);
            $parent->refresh();
            Log::info("SendInvoiceJob: customer created for parent {$parent->id}", [
                'customer_name' => "{$student->first_name} {$student->last_name}",
                'customer_id'   => $customerId,
            ]);
        }

        // ── Step 2: Wallet (duplicate handled inside createWallet) ────────
        if (empty($parent->juicyway_wallet_id)) {
            $wallet = $juicyWay->createWallet($parent->juicyway_customer_id);
            $parent->update([
                'juicyway_wallet_id'  => $wallet['wallet_id'],
                'juicyway_account_id' => $wallet['account_id'],
            ]);
            $parent->refresh();
            Log::info("SendInvoiceJob: wallet created for parent {$parent->id}");
        }

        // ── Step 3: NUBAN ─────────────────────────────────────────────────
        if (empty($parent->juicyway_account_number)) {
            $bank = $juicyWay->addBankAccount($parent->juicyway_wallet_id);
            $parent->update([
                'juicyway_account_number' => $bank['account_number'],
                'juicyway_bank_name'      => $bank['bank_name'],
                'juicyway_bank_code'      => $bank['bank_code'],
                'juicyway_wallet_status'  => 'active',
            ]);
            Log::info("SendInvoiceJob: NUBAN provisioned for parent {$parent->id}", [
                'account_number'  => $bank['account_number'],
                'bank_name'       => $bank['bank_name'],
                'student_name'    => "{$student->first_name} {$student->last_name}",
            ]);
        } else {
            $parent->update(['juicyway_wallet_status' => 'active']);
        }
    }
}
