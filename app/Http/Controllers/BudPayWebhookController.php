<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBudPayWebhookJob;
use App\Services\BudPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * BudPayWebhookController
 *
 * Receives webhook events from BudPay for dedicated virtual account payments.
 *
 * BudPay fires POST to /api/budpay/webhook when:
 *   - A payment lands on a dedicated NUBAN (notify="transaction", type="dedicated_nuban")
 *
 * Verification: HMAC-SHA-512 signature in the "budpay-signature" header,
 * computed as hash_hmac('sha512', json_encode($data), $secretKey).
 *
 * Pattern mirrors JuicyWayWebhookController exactly:
 *   1. Log raw payload immediately
 *   2. Verify signature
 *   3. Dispatch to queue for processing
 *   4. Return 200 immediately
 */
class BudPayWebhookController extends Controller
{
    public function handle(Request $request, BudPayService $budPay): JsonResponse
    {
        $rawBody   = $request->getContent();
        $payload   = $request->json()->all();
        $notify    = $payload['notify']     ?? 'unknown';
        $notifyType = $payload['notifyType'] ?? 'unknown';
        $data      = $payload['data']        ?? [];
        $reference = $data['reference']      ?? null;
        $logId     = (string) Str::uuid();

        // ── Step 1: Log raw event immediately ─────────────────────────────
        DB::table('budpay_webhook_events')->insert([
            'id'              => $logId,
            'event_type'      => $notify . '.' . $notifyType,
            'reference'       => $reference,
            'payload'         => $rawBody,
            'signature_valid' => false,
            'received_at'     => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // ── Step 2: Verify signature ───────────────────────────────────────
        $signature = $request->header('budpay-signature', '');

        if (! $budPay->verifyWebhookSignature($signature, $data)) {
            Log::warning('BudPay webhook: invalid signature', [
                'notify'    => $notify,
                'reference' => $reference,
                'ip'        => $request->ip(),
            ]);

            DB::table('budpay_webhook_events')->where('id', $logId)->update([
                'processing_error' => 'Invalid signature',
                'updated_at'       => now(),
            ]);

            // Return 200 anyway — BudPay retries on non-200 responses
            // We log the failure for investigation but don't block
            return response()->json(['status' => 'invalid_signature'], 200);
        }

        DB::table('budpay_webhook_events')->where('id', $logId)
            ->update(['signature_valid' => true, 'updated_at' => now()]);

        // ── Step 3: Only handle successful dedicated NUBAN payments ────────
        $isDedicatedNuban = $notify === 'transaction'
            && $notifyType === 'successful'
            && ($data['type'] ?? '') === 'dedicated_nuban';

        if (! $isDedicatedNuban) {
            Log::info("BudPay webhook: unhandled event '{$notify}.{$notifyType}' — logged only.");

            DB::table('budpay_webhook_events')->where('id', $logId)->update([
                'processed_at' => now(),
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'event_not_handled'], 200);
        }

        // ── Step 4: Dispatch to queue — respond fast ───────────────────────
        try {
            ProcessBudPayWebhookJob::dispatch($payload, $logId);

            DB::table('budpay_webhook_events')->where('id', $logId)->update([
                'processed_at' => now(),
                'updated_at'   => now(),
            ]);

            Log::info("BudPay webhook: dedicated_nuban payment dispatched to queue", [
                'reference' => $reference,
                'log_id'    => $logId,
            ]);

        } catch (\Throwable $e) {
            Log::error('BudPay webhook: dispatch failed — ' . $e->getMessage(), [
                'reference' => $reference,
            ]);

            DB::table('budpay_webhook_events')->where('id', $logId)->update([
                'processing_error' => $e->getMessage(),
                'updated_at'       => now(),
            ]);
        }

        // Always return 200 — BudPay will retry on non-200
        return response()->json(['status' => 'ok'], 200);
    }
}
