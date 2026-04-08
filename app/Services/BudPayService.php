<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BudPayService — SchoolMS
 *
 * Provisions dedicated permanent NUBANs for parents via BudPay's
 * Virtual Account API. Simpler than JuicyWay — only 2 steps, and
 * the NUBAN is returned synchronously (no polling needed).
 *
 * Auth: Authorization: Bearer <secret_key>
 * Base URL: https://api.budpay.com/api/v2
 *
 * Provisioning flow (per parent):
 *   Step 1 — POST /customer
 *     Creates a BudPay customer in the child's name.
 *     Returns customer_code (e.g. "CUS_abc123").
 *
 *   Step 2 — POST /dedicated_virtual_account
 *     Assigns a permanent NUBAN to that customer.
 *     Returns account_number, bank.name, bank.bank_code immediately.
 *     No polling needed — BudPay provisions synchronously.
 *
 * Deposit detection: webhooks only.
 *   BudPay fires POST to our /api/budpay/webhook endpoint.
 *   Payload: notify="transaction", notifyType="successful",
 *            data.type="dedicated_nuban"
 *   Verified via: hash_hmac('sha512', json_encode($data), $secretKey)
 *   compared against the "budpay-signature" header.
 */
class BudPayService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.budpay.secret_key', '');
        $this->baseUrl   = rtrim(config('services.budpay.base_url', 'https://api.budpay.com/api/v2'), '/');
    }

    /**
     * Step 1 — Create a BudPay customer in the child's name.
     *
     * Customer is created in the student's name (not the parent's) so
     * the NUBAN account name reads "Nurtureville / Uchechi Smart"
     * making it unambiguously linked to one student on the bank statement.
     *
     * Handles "Customer already exist" gracefully — fetches the existing
     * customer by email and returns their customer_code so step 2 can proceed.
     *
     * @return string customer_code (e.g. "CUS_abc123")
     */
    public function createCustomer(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
    ): string {
        try {
            $response = $this->post('/customer', [
                'email'      => $email,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $this->normalisePhone($phone),
            ]);

            $customerCode = $response['data']['customer_code'] ?? null;

            if (empty($customerCode)) {
                throw new \RuntimeException(
                    'BudPay: customer_code missing from response — ' . json_encode($response)
                );
            }

            return $customerCode;

        } catch (\RuntimeException $e) {
            // BudPay returns 401 + "Customer already exist" when the email
            // was already registered. Fetch the existing customer instead.
            if (str_contains($e->getMessage(), 'already exist')) {
                Log::info("BudPay: customer already exists for {$email} — fetching existing record.");
                return $this->fetchCustomerCodeByEmail($email);
            }

            throw $e;
        }
    }

    /**
     * Fetch an existing BudPay customer's code by email.
     * Used as a recovery path when createCustomer() finds the email already registered.
     *
     * BudPay doesn't have a direct "get by email" endpoint, so we use
     * GET /customer which lists all customers, then filter by email.
     *
     * @return string customer_code
     */
    public function fetchCustomerCodeByEmail(string $email): string
    {
        if (empty($this->secretKey)) {
            throw new \RuntimeException('BudPay: BUDPAY_SECRET_KEY is not set in .env');
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Accept'        => 'application/json',
            ])
            ->get($this->baseUrl . '/customer');

        if (! $response->successful()) {
            throw new \RuntimeException(
                'BudPay: failed to fetch customer list — HTTP ' . $response->status()
            );
        }

        $customers = $response->json()['data'] ?? [];

        foreach ($customers as $customer) {
            if (strtolower($customer['email'] ?? '') === strtolower($email)) {
                $code = $customer['customer_code'] ?? null;
                if ($code) {
                    Log::info("BudPay: found existing customer_code {$code} for {$email}");
                    return $code;
                }
            }
        }

        throw new \RuntimeException(
            "BudPay: customer with email {$email} not found in customer list after 'already exist' error"
        );
    }

    /**
     * Step 2 — Assign a permanent dedicated NUBAN to the customer.
     *
     * BudPay provisions the account synchronously — the account_number
     * is returned immediately in the response. No polling needed.
     *
     * @return array ['account_number', 'bank_name', 'bank_code']
     */
    public function createDedicatedAccount(string $customerCode): array
    {
        $response = $this->post('/dedicated_virtual_account', [
            'customer' => $customerCode,
        ]);

        $data        = $response['data'] ?? [];
        $accountNumber = (string) ($data['account_number'] ?? '');
        $bankName      = $data['bank']['name']      ?? 'Wema Bank';
        $bankCode      = $data['bank']['bank_code'] ?? '';

        if (empty($accountNumber)) {
            throw new \RuntimeException(
                'BudPay: account_number missing from dedicated account response — ' . json_encode($response)
            );
        }

        Log::info("BudPay: NUBAN provisioned — {$accountNumber} ({$bankName}) for customer {$customerCode}");

        return [
            'account_number' => $accountNumber,
            'bank_name'      => $bankName,
            'bank_code'      => $bankCode,
        ];
    }

    /**
     * Verify a webhook payload using BudPay's HMAC-SHA-512 signature.
     *
     * BudPay sends the signature in the "budpay-signature" request header.
     * The signature is: hash_hmac('sha512', json_encode($data), $secretKey)
     * where $data is the "data" object from the webhook payload.
     */
    public function verifyWebhookSignature(string $signature, array $data): bool
    {
        if (empty($this->secretKey)) {
            Log::warning('BudPay: BUDPAY_SECRET_KEY not configured — webhook verification skipped');
            return false;
        }

        $computed = hash_hmac('sha512', json_encode($data), $this->secretKey);

        return hash_equals($computed, strtolower($signature));
    }

    // ── HTTP helpers ─────────────────────────────────────────────────────────

    private function post(string $path, array $body): array
    {
        if (empty($this->secretKey)) {
            throw new \RuntimeException('BudPay: BUDPAY_SECRET_KEY is not set in .env');
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->post($this->baseUrl . $path, $body);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $status    = $response->status();
        $errorBody = $response->json() ?? [];
        $message   = $errorBody['message']
            ?? $errorBody['error']
            ?? "HTTP {$status}";

        Log::error("BudPay [{$status}] POST {$path}", ['response' => $errorBody]);
        throw new \RuntimeException("BudPay: {$message} (HTTP {$status})");
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '234' . substr($digits, 1);
        }
        return '+' . ltrim($digits, '+');
    }
}
