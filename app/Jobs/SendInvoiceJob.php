<?php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Notifications\FeeInvoiceNotification;
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
    public array $backoff = [30, 60, 120];

    public function __construct(public FeeInvoice $invoice) {}

    public function handle(): void
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
            try {
                // Provision a virtual account for this parent if they don't have one yet.
                // ProvisionParentWalletJob is idempotent — safe to dispatch even if
                // provisioning is already in progress or complete.
                if (empty($parent->juicyway_account_number)) {
                    ProvisionParentWalletJob::dispatch($parent);
                }

                $parent->user->notify(
                    new FeeInvoiceNotification($invoice, $invoice->items)
                );
            } catch (\Throwable $e) {
                Log::error('SendInvoiceJob: failed for parent', [
                    'invoice_id' => $invoice->id,
                    'parent_id'  => $parent->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        // Mark invoice as sent
        $invoice->update(['sent_at' => now()]);

        Log::info("SendInvoiceJob: invoice {$invoice->id} sent to parents.");
    }
}
