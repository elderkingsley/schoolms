<?php

namespace App\Jobs;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\ParentGuardian;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Services\FeeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PollJuicyWayDepositsJob — SchoolMS edition
 *
 * Polls JuicyWay GET /deposits every minute to detect payments made
 * into student virtual accounts (NUBANs).
 *
 * This job only processes deposits whose account_number matches a
 * ParentGuardian row in SchoolMS — all other deposits are skipped.
 * PayGrid's own PollJuicyWayDepositsJob handles Nurtureville's
 * organisation-level wallet and all other PayGrid customers.
 *
 * On each matching deposit this job does TWO things:
 *   1. Records the payment against the student's fee invoice in SchoolMS
 *   2. Notifies PayGrid via POST /api/inflows so Nurtureville's PayGrid
 *      ledger also shows the inflow (DR 1110 / CR 2199 journal entry)
 *
 * Idempotency: fee_payments.reference is the unique key. Both steps
 * check this before writing anything, so safe to run on duplicate deposits.
 *
 * Non-overlapping: withoutOverlapping(2) in the scheduler ensures only
 * one instance runs at a time even if a poll cycle takes longer than 1 min.
 */
class PollJuicyWayDepositsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 55; // must finish within 1-minute window

    public function handle(FeeService $feeService): void
    {
        $apiKey  = config('services.juicyway.api_key', '');
        $baseUrl = config('services.juicyway.base_url', 'https://api.spendjuice.com');

        if (empty($apiKey)) {
            Log::warning('PollJuicyWayDeposits[SchoolMS]: JUICYWAY_API_KEY not set — skipping');
            return;
        }

        // Load all known student account numbers for fast in-memory matching.
        // Avoids N+1 queries inside the deposit loop.
        $knownAccounts = ParentGuardian::whereNotNull('juicyway_account_number')
            ->pluck('juicyway_account_number')
            ->flip(); // flip so we can use isset() for O(1) lookup

        if ($knownAccounts->isEmpty()) {
            Log::info('PollJuicyWayDeposits[SchoolMS]: no provisioned accounts yet — skipping');
            return;
        }

        $processed = 0;
        $posted    = 0;
        $after     = null;

        do {
            $url      = rtrim($baseUrl, '/') . '/deposits?limit=15' . ($after ? "&after={$after}" : '');
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => $apiKey,
                    'Accept'        => 'application/json',
                ])
                ->get($url);

            if (! $response->successful()) {
                $status = $response->status();

                // 401 = JuicyWay intermittent auth failure — throw so the
                // queue retries automatically rather than silently giving up.
                // This ensures missed deposits are picked up on next attempt.
                if ($status === 401) {
                    throw new \RuntimeException(
                        "JuicyWay API returned 401 — possible intermittent auth issue. Will retry."
                    );
                }

                Log::error('PollJuicyWayDeposits[SchoolMS]: API error', [
                    'status' => $status,
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return;
            }

            $body     = $response->json();
            $deposits = $body['data'] ?? [];
            $after    = $body['pagination']['after'] ?? null;

            foreach ($deposits as $deposit) {
                $processed++;
                $wasPosted = $this->processDeposit($deposit, $knownAccounts, $feeService);
                if ($wasPosted) $posted++;
            }

            // Stop paginating once we find a page with no new deposits
            if ($posted === 0 && $after) break;

        } while ($after !== null);

        if ($posted > 0) {
            Log::info("PollJuicyWayDeposits[SchoolMS]: {$processed} checked, {$posted} new payments recorded");
        }
    }

    private function processDeposit(
        array      $deposit,
        \Illuminate\Support\Collection $knownAccounts,
        FeeService $feeService
    ): bool {
        // Only settled credit deposits
        if (($deposit['status'] ?? '') !== 'settled') return false;
        if (($deposit['type']   ?? '') !== 'credit')  return false;

        $accountNumber = $deposit['payment_method']['account_number'] ?? null;
        $amountKobo    = (int) ($deposit['amount'] ?? 0);
        $amountNgn     = $amountKobo / 100;
        $reference     = $deposit['reference'] ?? null;
        $senderName    = $deposit['sender_name'] ?? 'Unknown Sender';
        $depositId     = $deposit['id'] ?? null;
        $depositedAt   = $deposit['created_at'] ?? now()->toISOString();

        if (! $accountNumber || $amountNgn <= 0 || ! $reference) return false;

        // Only process accounts we know about — skip all other PayGrid customers
        if (! isset($knownAccounts[$accountNumber])) return false;

        // ── Idempotency ───────────────────────────────────────────────────
        if (FeePayment::where('reference', $reference)->exists()) {
            return false; // already processed
        }

        // ── Find parent and student ───────────────────────────────────────
        $parent = ParentGuardian::where('juicyway_account_number', $accountNumber)
            ->with(['user', 'students'])
            ->first();

        if (! $parent || ! $parent->user) {
            Log::warning("PollJuicyWayDeposits[SchoolMS]: no parent/user for account {$accountNumber}");
            return false;
        }

        $student = $parent->students->first();
        if (! $student) {
            Log::warning("PollJuicyWayDeposits[SchoolMS]: parent {$parent->id} has no linked student");
            return false;
        }

        // ── Find unpaid/partial invoices — oldest first (FIFO) ────────────
        $invoices = FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with(['items', 'payments', 'term.session'])
            ->orderBy('id', 'asc')
            ->get();

        if ($invoices->isEmpty()) {
            Log::info("PollJuicyWayDeposits[SchoolMS]: ₦{$amountNgn} received for student {$student->id} but no unpaid invoices", [
                'account' => $accountNumber,
                'ref'     => $reference,
            ]);
            // Still notify PayGrid so the inflow appears in Nurtureville's ledger
            $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt);
            return true;
        }

        // ── Resolve system actor for audit trail ────────────────────────────
        // We pass recorded_by explicitly to FeeService::recordPayment() so
        // the queue worker never needs auth()->setUser() — which pollutes
        // the audit trail by making automated payments look like manual ones.
        $systemActorId = \App\Models\User::where('user_type', 'super_admin')
            ->orWhere('user_type', 'admin')
            ->orderBy('id')
            ->value('id');

        // ── Apply deposit across invoices (FIFO) ──────────────────────────
        $remaining       = $amountNgn;
        $settledInvoices = [];

        DB::transaction(function () use (
            $invoices, &$remaining, &$settledInvoices,
            $reference, $feeService, $systemActorId, $student
        ) {
            foreach ($invoices as $invoice) {
                if ($remaining <= 0) break;

                $balance = (float) (string) $invoice->balance;
                if ($balance <= 0) continue;

                $isLast  = $invoices->last()->id === $invoice->id;
                $toApply = (float) (string) (($isLast && $remaining > $balance) ? $remaining : min($remaining, $balance));

                $feeService->recordPayment(
                    invoice:    $invoice,
                    amount:     $toApply,
                    method:     'JuicyWay Transfer',
                    reference:  $reference,
                    recordedBy: $systemActorId,
                    source:     'automation',
                );

                $remaining -= $toApply;
                $settledInvoices[] = $invoice->fresh();

                Log::info("PollJuicyWayDeposits[SchoolMS]: ₦{$toApply} applied to invoice {$invoice->id}", [
                    'student'   => $student->id,
                    'status'    => $invoice->fresh()->status,
                    'reference' => $reference,
                ]);
            }
        });

        // ── Notify PayGrid — post to Nurtureville's ledger ─────────────────
        $this->notifyPayGrid($amountNgn, $reference, $accountNumber, $senderName, $depositId, $depositedAt);

        // ── Email the parent ───────────────────────────────────────────────
        if ($parent->user && ! empty($settledInvoices)) {
            try {
                $parent->user->notify(new PaymentReceivedNotification(
                    student:         $student,
                    amountPaid:      $amountNgn,
                    senderName:      $senderName,
                    reference:       $reference,
                    settledInvoices: $settledInvoices,
                ));
            } catch (\Throwable $e) {
                Log::warning("PollJuicyWayDeposits[SchoolMS]: email failed for parent {$parent->id}: {$e->getMessage()}");
            }
        }

        Log::info("PollJuicyWayDeposits[SchoolMS]: ₦{$amountNgn} fully processed for student {$student->id}", [
            'account'          => $accountNumber,
            'reference'        => $reference,
            'invoices_settled' => count($settledInvoices),
            'sender'           => $senderName,
        ]);

        return true;
    }

    /**
     * Notify PayGrid that a student fee payment has been received.
     * PayGrid will post a journal entry to Nurtureville's ledger (DR 1110 / CR 2199).
     *
     * Fire-and-forget: failure is logged but never affects SchoolMS payment recording.
     * PayGrid uses the same `reference` as its idempotency key, so safe to retry.
     */
    private function notifyPayGrid(
        float   $amountNgn,
        string  $reference,
        string  $accountNumber,
        string  $senderName,
        ?string $depositId,
        string  $depositedAt,
    ): void {
        $url    = config('services.paygrid.api_base_url', '');
        $apiKey = config('services.paygrid.api_key', '');

        if (empty($url) || empty($apiKey)) {
            Log::warning('PollJuicyWayDeposits[SchoolMS]: PAYGRID_API_BASE_URL or PAYGRID_API_KEY not set — skipping PayGrid notification');
            return;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ])
                ->post(rtrim($url, '/') . '/api/inflows', [
                    'reference'      => $reference,
                    'amount_ngn'     => $amountNgn,
                    'account_number' => $accountNumber,
                    'account_label'  => $senderName,
                    'sender_name'    => $senderName,
                    'deposit_id'     => $depositId,
                    'deposited_at'   => $depositedAt,
                    'source'         => 'schoolms',
                ]);

            if ($response->successful()) {
                Log::info("PollJuicyWayDeposits[SchoolMS]: PayGrid notified for ref {$reference}");
            } else {
                Log::warning("PollJuicyWayDeposits[SchoolMS]: PayGrid notification failed", [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                    'ref'    => $reference,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("PollJuicyWayDeposits[SchoolMS]: PayGrid notification exception: {$e->getMessage()}", [
                'ref' => $reference,
            ]);
        }
    }

    /**
     * Called when the job permanently fails after all retries.
     * Sends an email alert to all admin users so no payment goes unnoticed.
     */
    public function failed(\Throwable $e): void
    {
        Log::critical('PollJuicyWayDepositsJob: permanently failed — ' . $e->getMessage());

        try {
            $admins = \App\Models\User::whereIn('user_type', ['super_admin', 'admin'])
                ->where('is_active', true)
                ->get();

            foreach ($admins as $admin) {
                \Illuminate\Support\Facades\Mail::raw(
                    "ALERT: The automated school fees payment detection job has permanently failed.\n\n" .
                    "Error: " . $e->getMessage() . "\n\n" .
                    "This means student fee payments made via bank transfer may NOT be " .
                    "automatically recorded until this is resolved.\n\n" .
                    "Please check the queue logs and restart the payments worker:\n" .
                    "sudo supervisorctl restart nurtureville-payments:*\n\n" .
                    "Time: " . now()->format('d M Y, g:ia') . " (Africa/Lagos)",
                    fn($message) => $message
                        ->to($admin->email)
                        ->subject('⚠️ URGENT: Payment Detection Job Failed — Nurtureville SchoolMS')
                );
            }
        } catch (\Throwable $mailError) {
            Log::error('PollJuicyWayDepositsJob: failed to send failure alert — ' . $mailError->getMessage());
        }
    }
}
