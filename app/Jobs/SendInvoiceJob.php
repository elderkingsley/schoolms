<?php
// Deploy to: app/Jobs/SendInvoiceJob.php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Notifications\FeeInvoiceNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendInvoiceJob
 *
 * Sends a fee invoice email to all parents of the student.
 * Provisioning of JuicyWay virtual accounts is NO LONGER done here —
 * it is triggered at enrolment approval (EnrolmentQueue::confirmApproval)
 * so the NUBAN is always ready before any invoice is ever sent.
 *
 * If a parent somehow still has no NUBAN when this job runs (e.g. the
 * provisioning job failed and was never retried), the FeeInvoiceNotification
 * handles it gracefully by showing bursary fallback instructions instead.
 *
 * After marking the invoice sent, this job dispatches PushInvoiceToPayGridJob
 * so PayGrid creates a matching invoice in Nurtureville's account. When the
 * parent pays, PayGrid will auto-settle directly to account 4400 (School Fees
 * Income) instead of parking the inflow in 2199 (Unreconciled Receipts).
 */
class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public int   $timeout = 60;

    public array $backoff = [30, 60, 120];

    public function __construct(public FeeInvoice $invoice) {}

    public function handle(): void
    {
        $invoice = $this->invoice->load([
            'student.parents.user',
            'items.feeItem',
            'term.session',  // null for miscellaneous invoices — handled gracefully
        ]);

        $parents = $invoice->student->parents->filter(fn($p) => $p->user !== null);

        if ($parents->isEmpty()) {
            Log::info("SendInvoiceJob: no parent portal accounts for student {$invoice->student_id}");
        }

        foreach ($parents as $parent) {
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

        // Push the invoice to PayGrid so it can be auto-matched when the
        // parent pays. Dispatched on the 'payments' queue so it shares
        // priority with the polling job. Dispatched with a 5-second delay
        // to ensure sent_at has propagated before PayGrid reads any state.
        // Dispatched to the default queue — no separate 'payments' queue worker needed.
        // 5-second delay ensures sent_at has propagated before PayGrid reads any state.
        PushInvoiceToPayGridJob::dispatch($invoice->fresh())
            ->delay(now()->addSeconds(5));

        Log::info("SendInvoiceJob: PushInvoiceToPayGridJob queued for invoice {$invoice->id}.");
    }
}
