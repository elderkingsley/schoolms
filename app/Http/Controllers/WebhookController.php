<?php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function paystack(Request $request, PaymentService $service)
    {
        // Verify webhook signature
        $hash = hash_hmac('sha512', $request->getContent(), config('services.paystack.secret_key'));

        if ($hash !== $request->header('x-paystack-signature')) {
            return response('Invalid signature', 401);
        }

        $payload = $request->all();
        $event   = $payload['event'] ?? '';

        if ($event === 'charge.success') {
            $reference = $payload['data']['reference'] ?? null;
            if ($reference) {
                $service->fulfillPayment('paystack', $reference, $payload);
            }
        }

        return response('OK', 200);
    }

    public function monnify(Request $request, PaymentService $service)
    {
        // Verify Monnify webhook hash
        $computedHash = hash('sha512', config('services.monnify.secret_key') . $request->getContent());

        if ($computedHash !== $request->header('monnify-signature')) {
            Log::warning('Monnify webhook: invalid signature');
            return response('Invalid signature', 401);
        }

        $eventType = $request->json('eventType');

        if ($eventType === 'SUCCESSFUL_TRANSACTION') {
            $reference = $request->json('eventData.paymentReference');
            if ($reference) {
                $service->fulfillPayment('monnify', $reference, $request->all());
            }
        }

        return response('OK', 200);
    }
}
