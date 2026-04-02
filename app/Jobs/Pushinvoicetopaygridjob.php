<?php

namespace App\Jobs;

use App\Models\FeeInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PushInvoiceToPayGridJob
 *
 * Pushes a sent SchoolMS fee invoice to PayGrid so that Nurtureville's
 * ledger has a matching invoice record. When the parent later pays,
 * PayGrid's InflowController will match the inflow to this invoice and
 * auto-settle it directly to account 4400 (School Fees Income) instead
 * of parking it in the 2199 Unreconciled Receipts clearing account.
 *
 * Dispatch point: end of SendInvoiceJob::handle(), after sent_at is stamped.
 *
 * Idempotency: PayGrid returns {"status":"already_exists"} if the
 * schoolms_invoice_id already exists — safe to retry on failure.
 *
 * Skip condition: if no parent has a provisioned NUBAN yet, there is
 * nothing for PayGrid to match on. The job logs a warning and exits
 * cleanly — no retry needed. By design, ProvisionParentWalletJob runs
 * at enrolment approval so the NUBAN should be present by invoice-send
 * time. If it is genuinely missing, the inflow will fall through to
 * the existing 2199 unreconciled flow on the PayGrid side, which is
 * safe and correct.
 */
class PushInvoiceToPayGridJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 5;
    public int   $timeout = 30;
    public array $backoff  = [30, 60, 120, 300, 600];

    public function __construct(public readonly FeeInvoice $invoice) {}

    public function handle(): void
    {
        $url    = config('services.paygrid.api_base_url', '');
        $apiKey = config('services.paygrid.api_key', '');

        if (empty($url) || empty($apiKey)) {
            Log::warning('PushInvoiceToPayGridJob: PAYGRID_API_BASE_URL or PAYGRID_API_KEY not set — skipping.', [
                'invoice_id' => $this->invoice->id,
            ]);
            return;
        }

        // Reload with all relations needed to build the payload
        $invoice = $this->invoice->load([
            'student.parents.user',
            'items.feeItem',
            'term.session',
        ]);

        $student = $invoice->student;

        // Find the first parent with a portal account and a provisioned NUBAN
        $parent = $student->parents
            ->filter(fn ($p) => $p->user !== null && ! empty($p->juicyway_account_number))
            ->first();

        if (! $parent) {
            // No NUBAN provisioned yet. Log and exit cleanly — do not retry.
            // This is expected only if ProvisionParentWalletJob failed or is
            // still running. The inflow will fall through to 2199 on PayGrid,
            // which is safe. The school can reconcile manually if needed.
            Log::warning('PushInvoiceToPayGridJob: no parent with a provisioned NUBAN — skipping PayGrid push.', [
                'invoice_id' => $invoice->id,
                'student_id' => $student->id,
            ]);
            return;
        }

        $termLabel = optional($invoice->term)->name . ' — ' . optional($invoice->term?->session)->name;

        $payload = [
            'schoolms_invoice_id' => (string) $invoice->id,
            'student_name'        => $student->full_name,
            'student_email'       => $parent->user?->email,
            'amount'              => (float) $invoice->total_amount,
            'term_label'          => $termLabel,
            'account_number'      => $parent->juicyway_account_number,
            'due_date'            => now()->addDays(30)->toDateString(),
            'items'               => $invoice->items->map(fn ($item) => [
                'name'   => $item->item_name,
                'amount' => (float) $item->amount,
            ])->toArray(),
        ];

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ])
                ->post(rtrim($url, '/') . '/api/school-invoices', $payload);

            $status = $response->status();
            $body   = $response->json();

            if ($response->successful()) {
                $outcome = $body['status'] ?? 'unknown';
                Log::info("PushInvoiceToPayGridJob: invoice {$invoice->id} → PayGrid [{$outcome}]", [
                    'paygrid_invoice_id' => $body['invoice_id'] ?? null,
                    'student'            => $student->full_name,
                    'amount'             => $payload['amount'],
                    'account_number'     => $payload['account_number'],
                ]);
                return;
            }

            // 422 = validation failure — our payload is wrong. Do not retry.
            if ($status === 422) {
                Log::error('PushInvoiceToPayGridJob: PayGrid rejected payload (422) — will not retry.', [
                    'invoice_id' => $invoice->id,
                    'errors'     => $body,
                ]);
                $this->fail(new \RuntimeException('PayGrid validation error: ' . json_encode($body)));
                return;
            }

            // 5xx / other — throw so the queue retries with backoff
            throw new \RuntimeException(
                "PayGrid returned HTTP {$status}: " . substr($response->body(), 0, 200)
            );

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network error — throw so queue retries with backoff
            throw new \RuntimeException('PushInvoiceToPayGridJob: network error — ' . $e->getMessage());
        }
    }

    /**
     * Called when the job permanently fails after all retries.
     * Logs a critical alert — the invoice will not be auto-matched in PayGrid
     * and will fall through to the 2199 unreconciled flow.
     */
    public function failed(\Throwable $e): void
    {
        Log::critical('PushInvoiceToPayGridJob: permanently failed — invoice will not be auto-matched in PayGrid.', [
            'invoice_id' => $this->invoice->id,
            'student_id' => $this->invoice->student_id,
            'error'      => $e->getMessage(),
        ]);
    }
}
