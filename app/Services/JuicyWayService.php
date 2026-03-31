<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * JuicyWayService for SchoolMS
 *
 * Mirrors PayGrid's proven JuicyWayService exactly.
 * Auth: Authorization: API_KEY (no Bearer prefix).
 *
 * Virtual account provisioning per parent row (one per child):
 *   1. createCustomer()  → uses the CHILD'S name (for reconciliation clarity)
 *                          email/phone stay the parent's (JuicyWay identity requirement)
 *   2. createWallet()    → handles duplicate_currency_wallet gracefully
 *   3. addBankAccount()  → polls until NUBAN appears; idempotent on retry
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

    // ── Step 2: Create an NGN wallet — handles duplicate gracefully ───────────

    /**
     * Creates an NGN wallet for the given JuicyWay customer.
     *
     * JuicyWay only allows one NGN wallet per customer. If a previous job
     * created the customer but crashed before saving the wallet_id, the next
     * attempt gets HTTP 400 "duplicate_currency_wallet". We recover by
     * fetching the existing wallet instead of failing.
     *
     * @return array ['wallet_id' => string, 'account_id' => string]
     */
    public function createWallet(string $customerId): array
    {
        try {
            $response = $this->post('/wallets', [
                'currency'    => 'NGN',
                'customer_id' => $customerId,
            ]);

            return [
                'wallet_id'  => $response['data']['id'],
                'account_id' => $response['data']['account_id'],
            ];

        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'duplicate_currency_wallet')) {
                Log::info("JuicyWay: wallet already exists for customer {$customerId} — fetching existing.");
                return $this->findWalletByCustomer($customerId);
            }
            throw $e;
        }
    }

    /**
     * Fetch the existing NGN wallet for a customer from GET /wallets.
     * Called when createWallet() hits the duplicate error.
     */
    private function findWalletByCustomer(string $customerId): array
    {
        $page = null;

        do {
            $url      = $page ? "/wallets?page={$page}" : '/wallets';
            $response = $this->get($url);

            foreach (($response['data'] ?? []) as $wallet) {
                if (($wallet['customer_id'] ?? '') === $customerId
                    && ($wallet['currency']    ?? '') === 'NGN') {
                    return [
                        'wallet_id'  => $wallet['id'],
                        'account_id' => $wallet['account_id'] ?? $wallet['id'],
                    ];
                }
            }

            $page = $response['meta']['next_page'] ?? null;

        } while ($page);

        throw new \RuntimeException(
            "JuicyWay: duplicate_currency_wallet for customer {$customerId} — could not retrieve existing wallet."
        );
    }

    // ── Step 3: Attach a bank account (NUBAN) to the wallet ──────────────────

    /**
     * Provisions a NUBAN on the wallet and polls until it appears.
     * Idempotent: if a bank account already exists it is returned immediately.
     *
     * @return array ['account_number', 'bank_name', 'bank_code']
     */
    public function addBankAccount(string $walletId): array
    {
        // Idempotency: return immediately if account already exists
        $existing = $this->getExistingBankAccount($walletId);
        if ($existing) {
            Log::info("JuicyWay: bank account already exists on wallet {$walletId} — reusing.");
            return $existing;
        }

        $this->post("/wallets/{$walletId}/payment-method", [
            'type' => 'bank_account',
        ]);

        // Poll up to 6 × 5 s = 30 s for the NUBAN to appear
        for ($i = 0; $i < 6; $i++) {
            sleep(5);
            $account = $this->getExistingBankAccount($walletId);
            if ($account) {
                return $account;
            }
            Log::info("JuicyWay: waiting for NUBAN on wallet {$walletId} (attempt " . ($i + 1) . "/6)");
        }

        throw new \RuntimeException(
            "JuicyWay: no payment method appeared after 30 s for wallet {$walletId}."
        );
    }

    /**
     * Returns the first bank account on a wallet, or null if none exists yet.
     */
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
            Log::warning("JuicyWay: getExistingBankAccount failed for wallet {$walletId}: " . $e->getMessage());
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
            'Authorization' => $this->apiKey,  // No "Bearer" — PayGrid confirmed
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
