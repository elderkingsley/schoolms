<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPayGridInflowJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PayGridInflowController
 *
 * Receives signed POST notifications from PayGrid when a deposit
 * is posted to Nurtureville's ledger (i.e. a parent has paid school fees
 * into a JuicyWay virtual account).
 *
 * Security: HMAC-SHA256 signature verified using PAYGRID_WEBHOOK_SECRET
 * from .env — shared secret set by PayGrid when configuring the webhook.
 *
 * Must respond within 15 seconds (PayGrid's timeout). All actual work
 * is dispatched to ProcessPayGridInflowJob.
 */
class PayGridInflowController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-PayGrid-Signature', '');
        $event     = $request->header('X-PayGrid-Event', 'unknown');

        // ── Step 1: Verify signature ──────────────────────────────────────
        $secret   = config('services.paygrid.webhook_secret', '');
        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);

        if (empty($secret)) {
            Log::error('PayGridInflow: PAYGRID_WEBHOOK_SECRET not set in .env');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        if (! hash_equals($expected, $signature)) {
            Log::warning('PayGridInflow: invalid signature', [
                'event' => $event,
                'ip'    => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // ── Step 2: Validate payload ──────────────────────────────────────
        $payload = $request->json()->all();

        if (($payload['event'] ?? '') !== 'inflow.posted') {
            // Unknown event type — acknowledge but don't process
            Log::info("PayGridInflow: unhandled event '{$payload['event']}' — ignored.");
            return response()->json(['status' => 'event_not_handled'], 200);
        }

        $accountNumber = $payload['account_number'] ?? null;
        $amountNgn     = $payload['amount_ngn']     ?? null;
        $reference     = $payload['reference']      ?? null;

        if (! $accountNumber || ! $amountNgn || ! $reference) {
            Log::warning('PayGridInflow: missing required fields', [
                'account_number' => $accountNumber,
                'amount_ngn'     => $amountNgn,
                'reference'      => $reference,
            ]);
            return response()->json(['error' => 'Missing required fields'], 422);
        }

        // ── Step 3: Dispatch job — respond fast ───────────────────────────
        ProcessPayGridInflowJob::dispatch($payload);

        Log::info('PayGridInflow: dispatched to queue', [
            'account_number' => $accountNumber,
            'amount_ngn'     => $amountNgn,
            'reference'      => $reference,
        ]);

        return response()->json(['status' => 'ok'], 200);
    }
}
