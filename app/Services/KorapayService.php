<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * KorapayService — SchoolMS
 *
 * Provisions permanent dedicated virtual bank accounts (NUBANs) for
 * parents via Korapay's Virtual Bank Account API.
 *
 * Key differences from BudPay/JuicyWay:
 *   - Single API call — no customer creation step, no wallet step.
 *     One POST returns a permanent NUBAN immediately.
 *   - Bank selection — pass bank_code to choose the issuing bank.
 *     We use Wema Bank (035) by default.
 *   - BVN required — the school director's BVN is passed once per
 *     account creation as the KYC identity. This is a CBN requirement
 *     handled entirely server-side and never exposed to parents.
 *   - permanent: true — explicitly permanent, no expiry.
 *
 * Auth: Authorization: Bearer <secret_key>
 * Base URL: https://api.korapay.com/merchant/api/v1
 *
 * Provisioning: POST /virtual-bank-account
 *   Request:
 *     account_name      — student full name (so bank statement is unambiguous)
 *     account_reference — our unique reference (parent ID + student ID)
 *     permanent         — true
 *     bank_code         — "035" (Wema Bank)
 *     customer.name     — student full name
 *     customer.email    — parent email
 *     kyc.bvn           — school director's BVN (from config)
 *
 *   Response:
 *     data.account_number  — the permanent NUBAN
 *     data.bank_name       — "Wema Bank"
 *     data.bank_code       — "035"
 *     data.account_status  — "active"
 *
 * Webhook: POST /api/korapay/webhook
 *   Event: "charge.success"
 *   Signature: x-korapay-signature header
 *   Verified: hash_hmac('sha256', json_encode($data), $secretKey)
 *
 * Deposit detection:
 *   data.virtual_bank_account_details.virtual_bank_account.account_reference
 *   matches the account_reference we stored on the parent record.
 */
class KorapayService
{
    private string $secretKey;
    private string $baseUrl;
    private string $bvn;
    private string $bankCode;

    public function __construct()
    {
        $this->secretKey = config('services.korapay.secret_key', '');
        $this->baseUrl   = rtrim(config('services.korapay.base_url', 'https://api.korapay.com/merchant/api/v1'), '/');
        $this->bvn       = config('services.korapay.bvn', '');
        $this->bankCode  = config('services.korapay.bank_code', '035'); // Wema Bank default
    }

    /**
     * Create a permanent dedicated virtual bank account for a parent.
     *
     * The account is created in the student's name so the bank statement
     * reads "Nurtureville / Uchechi Smart" — unambiguously linked to one
     * student. The parent's email is attached for Korapay's records.
     *
     * @param  string $accountReference  Our unique reference — stored on
     *                                   the parent record and used to match
     *                                   incoming webhook payments.
     * @return array ['account_number', 'bank_name', 'bank_code', 'account_reference']
     */
    public function createVirtualAccount(
        string $studentName,
        string $parentEmail,
        string $accountReference,
    ): array {
        if (empty($this->secretKey)) {
            throw new \RuntimeException('KORAPAY_SECRET_KEY is not set in .env');
        }

        if (empty($this->bvn)) {
            throw new \RuntimeException('KORAPAY_BVN is not set in .env');
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->post($this->baseUrl . '/virtual-bank-account', [
                'account_name'      => $studentName,
                'account_reference' => $accountReference,
                'permanent'         => true,
                'bank_code'         => $this->bankCode,
                'customer'          => [
                    'name'  => $studentName,
                    'email' => $parentEmail,
                ],
                'kyc' => [
                    'bvn' => $this->bvn,
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json()['data'] ?? [];

            $accountNumber = $data['account_number'] ?? null;
            $bankName      = $data['bank_name']      ?? 'Wema Bank';
            $bankCode      = $data['bank_code']      ?? $this->bankCode;

            if (empty($accountNumber)) {
                throw new \RuntimeException(
                    'Korapay: account_number missing from response — ' . $response->body()
                );
            }

            Log::info("Korapay: NUBAN provisioned — {$accountNumber} ({$bankName}) ref: {$accountReference}");

            return [
                'account_number'    => $accountNumber,
                'bank_name'         => $bankName,
                'bank_code'         => $bankCode,
                'account_reference' => $accountReference,
            ];
        }

        $status    = $response->status();
        $errorBody = $response->json() ?? [];
        $message   = $errorBody['message']
            ?? $errorBody['error']
            ?? "HTTP {$status}";

        // Handle duplicate reference — account already exists, fetch it
        if ($status === 400 && str_contains(strtolower($message), 'reference')) {
            Log::info("Korapay: reference {$accountReference} already exists — fetching existing account.");
            return $this->fetchVirtualAccount($accountReference);
        }

        Log::error("Korapay [{$status}] POST /virtual-bank-account", ['response' => $errorBody]);
        throw new \RuntimeException("Korapay: {$message} (HTTP {$status})");
    }

    /**
     * Fetch an existing virtual account by reference.
     * Used as recovery if the account was already created but not saved.
     *
     * @return array ['account_number', 'bank_name', 'bank_code', 'account_reference']
     */
    public function fetchVirtualAccount(string $accountReference): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Accept'        => 'application/json',
            ])
            ->get($this->baseUrl . '/virtual-bank-account/' . $accountReference);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Korapay: failed to fetch virtual account {$accountReference} — HTTP " . $response->status()
            );
        }

        $data = $response->json()['data'] ?? [];

        return [
            'account_number'    => $data['account_number']    ?? '',
            'bank_name'         => $data['bank_name']         ?? 'Wema Bank',
            'bank_code'         => $data['bank_code']         ?? $this->bankCode,
            'account_reference' => $data['account_reference'] ?? $accountReference,
        ];
    }

    /**
     * Verify a Korapay webhook signature.
     *
     * Korapay signs the data object only (not the full payload) using
     * HMAC-SHA256 with the secret key. The signature is sent in the
     * x-korapay-signature request header.
     */
    public function verifyWebhookSignature(string $signature, array $data): bool
    {
        if (empty($this->secretKey)) {
            Log::warning('Korapay: KORAPAY_SECRET_KEY not configured — webhook verification skipped');
            return false;
        }

        $computed = hash_hmac('sha256', json_encode($data), $this->secretKey);

        return hash_equals($computed, $signature);
    }

    /**
     * Generate a unique account reference for a parent.
     * Format: NV-P{parentId}-S{studentId}
     * Stable — same parent+student always produces the same reference,
     * so we can use it as an idempotency key.
     */
    public static function makeAccountReference(int $parentId, int $studentId): string
    {
        return "NV-P{$parentId}-S{$studentId}";
    }
}
