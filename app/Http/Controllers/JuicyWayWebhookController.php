<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessJuicyWayPaymentJob;
use App\Services\JuicyWayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JuicyWayWebhookController extends Controller
{
    public function handle(Request $request, JuicyWayService $juicyWay): JsonResponse
    {
        $rawBody  = $request->getContent(); // raw bytes — used for logging
        $payload  = $request->json()->all();
        $event    = $payload['event']              ?? 'unknown';
        $reference = $payload['data']['reference'] ?? null;
        $logId    = (string) Str::uuid();

        // ── Step 1: Log raw event immediately ─────────────────────────────
        // We log before any verification so we have a record even if something
        // goes wrong. This is essential for debugging payment disputes.
        DB::table('juicyway_webhook_events')->insert([
            'id'              => $logId,
            'event_type'      => $event,
            'reference'       => $reference,
            'payload'         => $rawBody,
            'signature_valid' => false, // updated below if it passes
            'received_at'     => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // ── Step 2: Verify checksum ────────────────────────────────────────
        // Must use the parsed payload (not raw body) — JuicyWay signs the data
        // object, not the full raw body bytes. See spec Section 5.3.
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

        // Mark signature as valid in the log
        DB::table('juicyway_webhook_events')->where('id', $logId)
            ->update(['signature_valid' => true, 'updated_at' => now()]);

        // ── Step 3: Only handle payment.session.succeeded ─────────────────
        // Log other events but don't process them.
        if ($event !== 'payment.session.succeeded') {
            Log::info("JuicyWay webhook: unhandled event type '{$event}' — logged only.");

            DB::table('juicyway_webhook_events')->where('id', $logId)->update([
                'processed_at' => now(),
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'event_not_handled'], 200);
        }

        // ── Step 4: Dispatch to queue — respond fast ──────────────────────
        // The controller must respond within 10 seconds (JuicyWay requirement).
        // All actual DB work happens in ProcessJuicyWayPaymentJob.
        try {
            ProcessJuicyWayPaymentJob::dispatch($payload, $logId);

            DB::table('juicyway_webhook_events')->where('id', $logId)->update([
                'processed_at' => now(),
                'updated_at'   => now(),
            ]);

            Log::info("JuicyWay webhook: {$event} dispatched to queue", [
                'reference' => $reference,
                'log_id'    => $logId,
            ]);

        } catch (\Throwable $e) {
            // Log but still return 200 — if we return 5xx, JuicyWay retries,
            // which just fails again. Accept and investigate manually.
            Log::error('JuicyWay webhook: dispatch failed — ' . $e->getMessage(), [
                'reference' => $reference,
            ]);

            DB::table('juicyway_webhook_events')->where('id', $logId)->update([
                'processing_error' => $e->getMessage(),
                'updated_at'       => now(),
            ]);
        }

        // ── Step 5: Always return 200 within 10 seconds ───────────────────
        return response()->json(['status' => 'ok'], 200);
    }
}
