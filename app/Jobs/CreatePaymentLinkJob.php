<?php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Services\JuicyWayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreatePaymentLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Exponential backoff: retry after 30s, 60s, 120s.
     * This handles JuicyWay being temporarily unavailable without hammering them.
     */
    public int   $tries   = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(public FeeInvoice $invoice) {}

    public function handle(JuicyWayService $juicyWay): void
    {
        // Idempotency: already has a link — nothing to do
        if ($this->invoice->payment_link_url) {
            Log::info("CreatePaymentLinkJob: invoice {$this->invoice->id} already has a payment link, skipping.");
            return;
        }

        // Invoice must be unpaid to be worth creating a link for
        if ($this->invoice->status === 'paid') {
            Log::info("CreatePaymentLinkJob: invoice {$this->invoice->id} is already paid, skipping.");
            return;
        }

        try {
            $result = $juicyWay->createPaymentLink($this->invoice);

            $this->invoice->update([
                'payment_link_id'            => $result['id'],
                'payment_link_url'           => $result['url'],
                'payment_link_reference'     => "INV-{$this->invoice->id}-T{$this->invoice->term_id}",
                'payment_link_generated_at'  => now(),
                'payment_link_error'         => null,
            ]);

            Log::info("CreatePaymentLinkJob: link created for invoice {$this->invoice->id}", [
                'url'       => $result['url'],
                'reference' => $result['reference'],
            ]);

        } catch (\Throwable $e) {
            // Store the error on the invoice so the admin can see it
            $this->invoice->update([
                'payment_link_error' => $e->getMessage(),
            ]);

            Log::error("CreatePaymentLinkJob: failed for invoice {$this->invoice->id}: " . $e->getMessage());

            // Re-throw so the queue retries
            throw $e;
        }
    }

    /**
     * Called after all retries are exhausted.
     * Invoice already has the error stored — admin can manually resend from the detail page.
     */
    public function failed(\Throwable $e): void
    {
        Log::critical("CreatePaymentLinkJob: permanently failed for invoice {$this->invoice->id}: " . $e->getMessage());
    }
}
