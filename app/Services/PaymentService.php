<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\PaymentReference;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    // ─────────────────────────────────────────
    // PAYSTACK — Card / Bank Transfer
    // ─────────────────────────────────────────

    public function initializePaystack(FeeInvoice $invoice, string $email): array
    {
        $reference = 'PS-' . strtoupper(Str::random(12));
        $amountKobo = (int) ($invoice->balance * 100); // Paystack uses kobo

        $response = Http::withToken(config('services.paystack.secret_key'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email'     => $email,
                'amount'    => $amountKobo,
                'reference' => $reference,
                'callback_url' => route('payment.paystack.callback'),
                'metadata'  => [
                    'invoice_id' => $invoice->id,
                    'student_id' => $invoice->student_id,
                ],
            ]);

        if ($response->successful() && $response->json('status')) {
            PaymentReference::create([
                'fee_invoice_id' => $invoice->id,
                'provider'       => 'paystack',
                'reference'      => $reference,
                'amount'         => $invoice->balance,
                'status'         => 'pending',
            ]);

            return [
                'success'      => true,
                'payment_url'  => $response->json('data.authorization_url'),
                'reference'    => $reference,
            ];
        }

        return ['success' => false, 'message' => $response->json('message')];
    }

    public function verifyPaystack(string $reference): bool
    {
        $response = Http::withToken(config('services.paystack.secret_key'))
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->successful() && $response->json('data.status') === 'success') {
            $this->fulfillPayment('paystack', $reference, $response->json());
            return true;
        }

        return false;
    }

    // ─────────────────────────────────────────
    // MONNIFY — Virtual Account (Dedicated NUBAN)
    // ─────────────────────────────────────────

    public function createMonnifyVirtualAccount(FeeInvoice $invoice, string $parentName, string $email): array
    {
        $token = $this->getMonnifyToken();

        if (!$token) return ['success' => false, 'message' => 'Could not authenticate with Monnify'];

        $reference = 'MN-' . strtoupper(Str::random(12));

        $response = Http::withToken($token)
            ->post(config('services.monnify.base_url') . '/api/v2/bank-transfer/reserved-accounts', [
                'accountReference'    => $reference,
                'accountName'         => 'Nurtureville — ' . $parentName,
                'currencyCode'        => 'NGN',
                'contractCode'        => config('services.monnify.contract_code'),
                'customerEmail'       => $email,
                'customerName'        => $parentName,
                'getAllAvailableBanks' => false,
                'preferredBanks'      => ['035', '058'], // Wema (ALAT) + GTBank
                'metaData'            => [
                    'invoice_id' => (string) $invoice->id,
                    'student_id' => (string) $invoice->student_id,
                ],
            ]);

        if ($response->successful() && $response->json('requestSuccessful')) {
            $accounts = $response->json('responseBody.accounts');
            $primary  = $accounts[0] ?? null;

            PaymentReference::create([
                'fee_invoice_id'        => $invoice->id,
                'provider'              => 'monnify',
                'reference'             => $reference,
                'virtual_account_number'=> $primary['accountNumber'] ?? null,
                'virtual_account_bank'  => $primary['bankName'] ?? null,
                'virtual_account_name'  => $primary['accountName'] ?? null,
                'amount'                => $invoice->balance,
                'status'                => 'pending',
            ]);

            return [
                'success'        => true,
                'accounts'       => $accounts,
                'reference'      => $reference,
            ];
        }

        return ['success' => false, 'message' => $response->json('responseMessage')];
    }

    protected function getMonnifyToken(): ?string
    {
        $apiKey    = config('services.monnify.api_key');
        $secretKey = config('services.monnify.secret_key');
        $encoded   = base64_encode("{$apiKey}:{$secretKey}");

        $response = Http::withHeaders(['Authorization' => "Basic {$encoded}"])
            ->post(config('services.monnify.base_url') . '/api/v1/auth/login');

        return $response->json('responseBody.accessToken');
    }

    // ─────────────────────────────────────────
    // SHARED — Fulfill a successful payment
    // ─────────────────────────────────────────

    public function fulfillPayment(string $provider, string $reference, array $payload): void
    {
        $payRef = PaymentReference::where('reference', $reference)
            ->where('provider', $provider)
            ->first();

        if (!$payRef || $payRef->status === 'success') return;

        $payRef->update([
            'status'           => 'success',
            'paid_at'          => now(),
            'provider_response'=> $payload,
        ]);

        // Record in fee payments
        app(FeeService::class)->recordPayment(
            $payRef->invoice,
            $payRef->amount,
            $provider === 'paystack' ? 'Card' : 'Bank Transfer',
            $reference
        );
    }
}
