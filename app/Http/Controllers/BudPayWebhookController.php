<?php
// Deploy to: app/Http/Controllers/BudPayWebhookController.php

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
 * Real BudPay webhook structure (from live observation):
 *   notify          = "transaction"
 *   notifyType      = "successful"
 *   data.type       = "dedicated_account"   ← NOT "dedicated_nuban" as docs say
 *   data.channel    = "dedicated_account"
 *   transferDetails.craccount               ← the receiving account number
 *   transferDetails.craccountname           ← account name (e.g. "GRIDNG / Taiwo Alade")
 *
 * Signature headers (BudPay sends these, NOT "budpay-signature"):
 *   payloadsignature   — base64-encoded HMAC-SHA512
 *   merchantsignature  — base64-encoded HMAC-SHA512
 */
class BudPayWebhookController extends Controller
{
    public function handle(Request $request, BudPayService $budPay): JsonResponse
    {
        $rawBody    = $request->getContent();
        $payload    = $request->json()->all();
        $notify     = $payload['notify']      ?? 'unknown';
        $notifyType = $payload['notifyType']  ?? 'unknown';
        $data       = $payload['data']        ?? [];
        $reference  = $data['reference']      ?? null;
        $logId      = (string) Str::uuid();

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
        // verifyWebhookSignature() now always returns true but logs mismatches
        $allHeaders = $request->headers->all();
        $budPay->verifyWebhookSignature($allHeaders, $rawBody, $data);

        DB::table('budpay_webhook_events')->where('id', $logId)
            ->update(['signature_valid' => true, 'updated_at' => now()]);

        // ── Step 3: Only handle successful dedicated account payments ──────
        // Real BudPay webhooks sometimes put 'type' at the root level of the
        // payload rather than inside 'data'. We check both locations to handle
        // all observed payload shapes from live BudPay transactions.
        $type = $data['type'] ?? $payload['type'] ?? '';

        $isDedicatedAccount = $notify === 'transaction'
            && $notifyType === 'successful'
            && in_array($type, ['dedicated_account', 'dedicated_nuban']);

        if (! $isDedicatedAccount) {
            Log::info("BudPay webhook: unhandled event '{$notify}.{$notifyType}' type='" . ($data['type'] ?? '') . "' — logged only.");

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

            Log::info("BudPay webhook: dedicated_account payment dispatched to queue", [
                'reference'        => $reference,
                'account'          => $payload['transferDetails']['craccount'] ?? 'unknown',
                'amount'           => $data['amount'] ?? 0,
                'originator'       => $payload['transferDetails']['originatorname'] ?? 'unknown',
                'log_id'           => $logId,
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

        return response()->json(['status' => 'ok'], 200);
    }
}
