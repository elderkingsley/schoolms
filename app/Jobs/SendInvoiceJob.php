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
            'term.session',
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
    }
}
