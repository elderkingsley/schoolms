<?php
// Deploy to: app/Http/Controllers/JuicyWayWebhookController.php

namespace App\Http\Controllers;

use App\Jobs\ProcessJuicyWayDepositJob;
use App\Jobs\ProcessJuicyWayPaymentJob;
use App\Services\JuicyWayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JuicyWayWebhookController extends Controller
{
    /**
     * Handles all incoming JuicyWay webhook events.
     *
     * Supported events:
     *   deposit.received          — parent made a bank transfer to their NUBAN (real-time)
     *   payment.session.succeeded — parent paid via a JuicyWay payment link
     *
     * All other events are logged and acknowledged but not processed.
     *
     * The controller must respond within 10 seconds — all heavy work is
     * dispatched to the queue. A 200 response is always returned after
     * checksum verification to prevent JuicyWay from retrying endlessly.
     */
    public function handle(Request $request, JuicyWayService $juicyWay): JsonResponse
    {
        $rawBody   = $request->getContent();
        $payload   = $request->json()->all();
        $event     = $payload['event']             ?? 'unknown';
        // deposit.received uses data.id as the unique identifier (no data.reference)
        // payment.session.succeeded uses data.reference
        $reference = $payload['data']['reference'] ?? $payload['data']['id'] ?? null;
        $logId     = (string) Str::uuid();

        // ── Step 1: Log raw event immediately ─────────────────────────────
        DB::table('juicyway_webhook_events')->insert([
            'id'              => $logId,
            'event_type'      => $event,
            'reference'       => $reference,
            'payload'         => $rawBody,
            'signature_valid' => false,
            'received_at'     => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // ── Step 2: Verify checksum ────────────────────────────────────────
        if (! $juicyWay->verifyChecksum($payload)) {
            Log::warning('JuicyWay webhook: invalid checksum', [
                'event'     => $event,
                'reference' => $reference,
                'ip'        => $request->ip(),
            ]);

            DB::table('juicyway_webhook_events')->where('id', $logId)->update([
                'processing_error' => 'Invalid checksum',
                'updated_at'       => now(),
            ]);

            return response()->json(['error' => 'Invalid checksum'], 401);
        }

        DB::table('juicyway_webhook_events')->where('id', $logId)
            ->update(['signature_valid' => true, 'updated_at' => now()]);

        // ── Step 3: Route by event type ───────────────────────────────────
        try {
            match ($event) {
                'deposit.received'          => $this->handleDeposit($payload, $logId),
                'payment.session.succeeded' => $this->handlePaymentSession($payload, $logId),
                default                     => $this->handleUnknown($event, $logId),
            };
        } catch (\Throwable $e) {
            Log::error('JuicyWay webhook: dispatch failed — ' . $e->getMessage(), [
                'event'     => $event,
                'reference' => $reference,
            ]);

            DB::table('juicyway_webhook_events')->where('id', $logId)->update([
                'processing_error' => $e->getMessage(),
                'updated_at'       => now(),
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    // ── Event handlers ────────────────────────────────────────────────────────

    /**
     * Handle deposit.received — NUBAN bank transfer detected.
     *
     * Dispatches ProcessJuicyWayDepositJob to the payments queue for
     * immediate processing. PollJuicyWayDepositsJob remains as a safety
     * net and will skip this reference via idempotency if already processed.
     */
    private function handleDeposit(array $payload, string $logId): void
    {
        $depositId     = $payload['data']['id']                                  ?? null;
        $accountNumber = $payload['data']['beneficiary']['account_number']       ?? null;
        $amountKobo    = $payload['data']['amount']                              ?? 0;
        $amountNgn     = $amountKobo / 100;

        ProcessJuicyWayDepositJob::dispatch($payload, $logId)
            ->onQueue('payments');

        DB::table('juicyway_webhook_events')->where('id', $logId)->update([
            'processed_at' => now(),
            'updated_at'   => now(),
        ]);

        Log::info('JuicyWay webhook: deposit.received dispatched to queue', [
            'deposit_id' => $depositId,
            'account'    => $accountNumber,
            'amount'     => "NGN {$amountNgn}",
            'log_id'     => $logId,
        ]);
    }

    /**
     * Handle payment.session.succeeded — parent paid via a JuicyWay payment link.
     */
    private function handlePaymentSession(array $payload, string $logId): void
    {
        $reference = $payload['data']['reference'] ?? null;

        ProcessJuicyWayPaymentJob::dispatch($payload, $logId);

        DB::table('juicyway_webhook_events')->where('id', $logId)->update([
            'processed_at' => now(),
            'updated_at'   => now(),
        ]);

        Log::info('JuicyWay webhook: payment.session.succeeded dispatched to queue', [
            'reference' => $reference,
            'log_id'    => $logId,
        ]);
    }

    /**
     * Handle any unrecognised event — log and acknowledge, do not process.
     */
    private function handleUnknown(string $event, string $logId): void
    {
        Log::info("JuicyWay webhook: unhandled event type '{$event}' — logged only.");

        DB::table('juicyway_webhook_events')->where('id', $logId)->update([
            'processed_at' => now(),
            'updated_at'   => now(),
        ]);
    }
}
