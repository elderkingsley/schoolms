<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * JuicyWayService for SchoolMS
 *
 * Auth: Authorization: API_KEY (no Bearer prefix — confirmed by PayGrid).
 *
 * Virtual account provisioning per parent row (one per child):
 *   1. createCustomer()  → uses the CHILD's name for reconciliation clarity.
 *                          Email/phone stay the parent's (JuicyWay identity).
 *   2. createWallet()    → one NGN wallet per customer. Throws RuntimeException
 *                          with "duplicate_currency_wallet" if already created.
 *   3. addBankAccount()  → polls until NUBAN appears; idempotent on retry.
 *
 * Handling duplicate_currency_wallet:
 *   JuicyWay has no list-wallets endpoint. If a job crashed between step 1
 *   and step 2, the wallet_id is lost forever. Recovery: deleteCustomer(),
 *   recreate via createCustomer(), then createWallet() again. This is safe
 *   because the orphaned customer has no wallet and no transactions.
 */
class JuicyWayService
{
    private string $apiKey;
    private string $baseUrl;
    private string $businessId;

    public function __construct()
    {
        $this->apiKey     = config('services.juicyway.api_key', '');
        $this->baseUrl    = rtrim(config('services.juicyway.base_url', 'https://api.spendjuice.com'), '/');
        $this->businessId = config('services.juicyway.business_id', '');
    }

    // ── Step 1: Create a JuicyWay customer ───────────────────────────────────

    public function createCustomer(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $street = '1 School Road',
        string $city   = 'Lagos',
        string $state  = 'Lagos'
    ): string {
        $response = $this->post('/customers', [
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'email'           => $email,
            'phone_number'    => $this->normalisePhone($phone),
            'type'            => 'individual',
            'billing_address' => [
                'line1'    => $street,
                'city'     => $city,
                'state'    => $state,
                'zip_code' => '100001',
                'country'  => 'NG',
            ],
        ]);

        return $response['data']['id'];
    }

    /**
     * Delete an orphaned JuicyWay customer.
     * Only safe when the customer has no wallet and no transactions.
     * Used by ProvisionParentWalletJob to recover from duplicate_currency_wallet.
     */
    public function deleteCustomer(string $customerId): void
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('JUICYWAY_API_KEY is not set in .env');
        }

        $response = Http::timeout(30)->withHeaders([
            'Authorization' => $this->apiKey,
            'Accept'        => 'application/json',
        ])->delete($this->baseUrl . "/customers/{$customerId}");

        // 204 = success; 404 = already gone — both are fine
        if (! in_array($response->status(), [204, 404])) {
            throw new \RuntimeException(
                "JuicyWay: failed to delete customer {$customerId} — HTTP {$response->status()}"
            );
        }

        Log::info("JuicyWay: deleted orphaned customer {$customerId}");
    }

    // ── Step 2: Create an NGN wallet ─────────────────────────────────────────

    /**
     * @return array ['wallet_id' => string, 'account_id' => string]
     * @throws \RuntimeException — including "duplicate_currency_wallet" which
     *         the caller (ProvisionParentWalletJob) catches and recovers from.
     */
    public function createWallet(string $customerId): array
    {
        $response = $this->post('/wallets', [
            'currency'    => 'NGN',
            'customer_id' => $customerId,
        ]);

        return [
            'wallet_id'  => $response['data']['id'],
            'account_id' => $response['data']['account_id'],
        ];
    }

    // ── Step 3: Provision a NUBAN on the wallet ───────────────────────────────

    /**
     * Idempotent: returns an existing bank account immediately if one already
     * exists (handles job retries at step 3 safely).
     * Polls up to 6 × 5 s = 30 s for JuicyWay to provision the NUBAN.
     *
     * @return array ['account_number', 'bank_name', 'bank_code']
     */
    public function addBankAccount(string $walletId): array
    {
        // Return immediately if already provisioned (idempotency on retry)
        $existing = $this->getExistingBankAccount($walletId);
        if ($existing) {
            Log::info("JuicyWay: bank account already exists on wallet {$walletId} — reusing.");
            return $existing;
        }

        $this->post("/wallets/{$walletId}/payment-method", ['type' => 'bank_account']);

        // Poll up to 12 × 10 s = 120 s.
        // JuicyWay provisions NUBANs asynchronously and can take anywhere from
        // a few seconds to over a minute depending on their banking partner load.
        for ($i = 0; $i < 12; $i++) {
            sleep(10);
            $account = $this->getExistingBankAccount($walletId);
            if ($account) {
                return $account;
            }
            Log::info("JuicyWay: waiting for NUBAN on wallet {$walletId} (attempt " . ($i + 1) . "/12)");
        }

        throw new \RuntimeException(
            "JuicyWay: no payment method appeared after 120s for wallet {$walletId}."
        );
    }

    private function getExistingBankAccount(string $walletId): ?array
    {
        try {
            $response = $this->get("/wallets/{$walletId}");
            $methods  = $response['data']['payment_methods'] ?? [];
            if (! empty($methods)) {
                $m = $methods[0];
                return [
                    'account_number' => $m['account_number'],
                    'bank_name'      => $m['bank_name'] ?? 'Unknown Bank',
                    'bank_code'      => $m['bank_code'] ?? '',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("JuicyWay: getExistingBankAccount failed for {$walletId}: " . $e->getMessage());
        }
        return null;
    }

    // ── Checksum verification ────────────────────────────────────────────────

    public function verifyChecksum(array $payload): bool
    {
        if (empty($this->businessId)) {
            Log::warning('JuicyWay: JUICYWAY_BUSINESS_ID not configured — verification skipped');
            return false;
        }

        $checksum = $payload['checksum'] ?? '';
        $event    = $payload['event']    ?? '';
        $data     = $payload['data']     ?? [];

        if (empty($checksum)) return false;

        ksort($data);
        $encoded  = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $expected = strtoupper(hash_hmac('sha256', "{$event}|{$encoded}", $this->businessId));

        return hash_equals($expected, strtoupper($checksum));
    }

    // ── HTTP helpers ─────────────────────────────────────────────────────────

    private function post(string $path, array $body): array
    {
        return $this->request('POST', $path, $body);
    }

    private function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    private function request(string $method, string $path, array $data = []): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('JUICYWAY_API_KEY is not set in .env');
        }

        $client = Http::timeout(60)->withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ]);

        $url      = $this->baseUrl . $path;
        $response = $method === 'GET'
            ? $client->get($url)
            : $client->post($url, $data);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $status    = $response->status();
        $errorBody = $response->json() ?? [];

        $message = $errorBody['error']['message']
            ?? $errorBody['message']
            ?? "HTTP {$status}";

        $fieldErrors = $errorBody['error']['errors'] ?? $errorBody['errors'] ?? null;
        if ($fieldErrors) {
            $details = [];
            foreach ((array) $fieldErrors as $field => $msgs) {
                $details[] = $field . ': ' . (is_array($msgs) ? implode(', ', $msgs) : $msgs);
            }
            $message .= ' — ' . implode('; ', $details);
        }

        Log::error("JuicyWay [{$status}] {$method} {$path}", ['response' => $errorBody]);
        throw new \RuntimeException("JuicyWay: {$message} (HTTP {$status})");
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
