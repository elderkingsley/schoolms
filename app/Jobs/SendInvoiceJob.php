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
    public int   $timeout = 180; // 3 min: NUBAN polling up to 30s per parent

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
            try {
                $this->ensureVirtualAccount($parent, $juicyWay);
                $parent->refresh();
            } catch (\Throwable $e) {
                Log::error('SendInvoiceJob: provisioning failed for parent', [
                    'invoice_id' => $invoice->id,
                    'parent_id'  => $parent->id,
                    'error'      => $e->getMessage(),
                ]);
                $parent->update(['juicyway_wallet_status' => 'failed']);
                // Fall through — still send the email with bursary fallback
            }

            // ── 2. Send email (always, even if provisioning failed) ───────
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
     * Provision a JuicyWay virtual account for this parent row.
     *
     * The JuicyWay customer is created in the CHILD's name so each NUBAN
     * shows e.g. "Jaanai Kingsley — Access Bank — 0812345678", making
     * reconciliation unambiguous when a parent has multiple children.
     *
     * Handles the duplicate_currency_wallet failure case:
     * If a previous run created the customer but crashed before saving the
     * wallet_id, JuicyWay returns 400 duplicate_currency_wallet on the next
     * wallet creation attempt. Since JuicyWay has no list-wallets endpoint,
     * the only recovery is to DELETE the orphaned customer and recreate it.
     * This is safe — the orphaned customer has no wallet and no transactions.
     */
    private function ensureVirtualAccount(ParentGuardian $parent, JuicyWayService $juicyWay): void
    {
        if ($parent->hasVirtualAccount()) {
            return; // Already fully provisioned
        }

        $parent->update(['juicyway_wallet_status' => 'pending']);

        $student = $parent->students()->first();
        if (! $student) {
            throw new \RuntimeException("Parent {$parent->id} has no linked student.");
        }

        // ── Step 1: Customer in CHILD's name ─────────────────────────────
        if (empty($parent->juicyway_customer_id)) {
            $customerId = $juicyWay->createCustomer(
                firstName: $student->first_name,
                lastName:  $student->last_name,
                email:     $parent->user->email,
                phone:     $parent->phone ?? '08000000000',
            );
            $parent->update(['juicyway_customer_id' => $customerId]);
            $parent->refresh();
            Log::info("SendInvoiceJob: customer created for parent {$parent->id}", [
                'name' => "{$student->first_name} {$student->last_name}",
            ]);
        }

        // ── Step 2: Wallet — with duplicate recovery ──────────────────────
        if (empty($parent->juicyway_wallet_id)) {
            try {
                $wallet = $juicyWay->createWallet($parent->juicyway_customer_id);

            } catch (\RuntimeException $e) {
                if (! str_contains($e->getMessage(), 'duplicate_currency_wallet')) {
                    throw $e;
                }

                // Orphaned customer — delete it, recreate it, try wallet again
                Log::warning("SendInvoiceJob: duplicate_currency_wallet for parent {$parent->id} — deleting orphaned customer and recreating.", [
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

                Log::info("SendInvoiceJob: recreated customer for parent {$parent->id}", [
                    'new_customer_id' => $newCustomerId,
                ]);

                $wallet = $juicyWay->createWallet($newCustomerId);
            }

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
                'account_number' => $bank['account_number'],
                'bank_name'      => $bank['bank_name'],
                'student'        => "{$student->first_name} {$student->last_name}",
            ]);
        } else {
            $parent->update(['juicyway_wallet_status' => 'active']);
        }
    }
}
