<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessKorapayWebhookJob;
use App\Services\KorapayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * KorapayWebhookController
 *
 * Receives webhook events from Korapay for dedicated virtual account payments.
 *
 * Korapay fires POST to /api/korapay/webhook on:
 *   charge.success — payment received on a virtual bank account
 *   charge.failed  — payment failed (logged only, no action needed)
 *
 * Verification: HMAC-SHA256 of json_encode($data) using the secret key,
 * compared against the x-korapay-signature header.
 *
 * Must respond with HTTP 200 within the timeout window.
 * Korapay retries for up to 72 hours on non-200 responses.
 */
class KorapayWebhookController extends Controller
{
    public function handle(Request $request, KorapayService $korapay): JsonResponse
    {
        $rawBody         = $request->getContent();
        $payload         = $request->json()->all();
        $event           = $payload['event']              ?? 'unknown';
        $data            = $payload['data']               ?? [];
        $reference       = $data['reference']             ?? null;
        $accountRef      = $data['virtual_bank_account_details']['virtual_bank_account']['account_reference']
                            ?? null;
        $logId           = (string) Str::uuid();

        // ── Step 1: Log raw event immediately ─────────────────────────────
        DB::table('korapay_webhook_events')->insert([
            'id'                => $logId,
            'event_type'        => $event,
            'reference'         => $reference,
            'account_reference' => $accountRef,
            'payload'           => $rawBody,
            'signature_valid'   => false,
            'received_at'       => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ── Step 2: Verify signature ───────────────────────────────────────
        $signature = $request->header('x-korapay-signature', '');

        if (! $korapay->verifyWebhookSignature($signature, $data)) {
            Log::warning('Korapay webhook: invalid signature', [
                'event'     => $event,
                'reference' => $reference,
                'ip'        => $request->ip(),
            ]);

            DB::table('korapay_webhook_events')->where('id', $logId)->update([
                'processing_error' => 'Invalid signature',
                'updated_at'       => now(),
            ]);

            // Return 200 anyway — Korapay docs say always return 200
            // Log the failure for investigation
            return response()->json(['status' => 'invalid_signature'], 200);
        }

        DB::table('korapay_webhook_events')->where('id', $logId)
            ->update(['signature_valid' => true, 'updated_at' => now()]);

        // ── Step 3: Only process charge.success on virtual bank accounts ───
        $isVbaPayment = $event === 'charge.success'
            && isset($data['virtual_bank_account_details']);

        if (! $isVbaPayment) {
            Log::info("Korapay webhook: unhandled event '{$event}' — logged only.");

            DB::table('korapay_webhook_events')->where('id', $logId)->update([
                'processed_at' => now(),
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'event_not_handled'], 200);
        }

        // ── Step 4: Dispatch to queue — respond fast ───────────────────────
        try {
            ProcessKorapayWebhookJob::dispatch($payload, $logId);

            DB::table('korapay_webhook_events')->where('id', $logId)->update([
                'processed_at' => now(),
                'updated_at'   => now(),
            ]);

            Log::info("Korapay webhook: charge.success dispatched to queue", [
                'reference'         => $reference,
                'account_reference' => $accountRef,
                'log_id'            => $logId,
            ]);

        } catch (\Throwable $e) {
            Log::error('Korapay webhook: dispatch failed — ' . $e->getMessage(), [
                'reference' => $reference,
            ]);

            DB::table('korapay_webhook_events')->where('id', $logId)->update([
                'processing_error' => $e->getMessage(),
                'updated_at'       => now(),
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
