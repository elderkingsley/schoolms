<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\ParentGuardian;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * JuicyWayService for SchoolMS
 *
 * Mirrors PayGrid's proven JuicyWayService exactly — same auth header format
 * (Authorization: API_KEY, no Bearer), same endpoint paths, same response
 * parsing. The school payment flow adds one method on top: createPaymentSession()
 * which creates a payment session for a specific invoice amount.
 *
 * Virtual account provisioning per student (called from ProvisionStudentWalletJob):
 *   1. createCustomer()          → juicyway_customer_id   stored on parents record
 *   2. createWallet()            → juicyway_wallet_id     stored on parents record
 *   3. addBankAccount()          → NUBAN, bank name       stored on parents record
 *
 * The NUBAN is then included in every invoice email so parents can pay by
 * bank transfer directly into the student's dedicated virtual account.
 * JuicyWay fires a webhook when a deposit arrives — SchoolMS matches it to
 * the invoice by account_number and records the payment automatically.
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

    /**
     * @return string  The JuicyWay customer UUID
     * @throws \RuntimeException
     */
    public function createCustomer(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $street  = '1 School Road',
        string $city    = 'Lagos',
        string $state   = 'Lagos'
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

    // ── Step 2: Create an NGN wallet for the customer ─────────────────────────

    /**
     * @return array  ['wallet_id' => string, 'account_id' => string]
     * @throws \RuntimeException
     */
    public function createWallet(string $juicywayCustomerId): array
    {
        $response = $this->post('/wallets', [
            'currency'    => 'NGN',
            'customer_id' => $juicywayCustomerId,
        ]);

        return [
            'wallet_id'  => $response['data']['id'],
            'account_id' => $response['data']['account_id'],
        ];
    }

    // ── Step 3: Attach a bank account (NUBAN) to the wallet ──────────────────

    /**
     * Triggers NUBAN provisioning and polls until the account number appears.
     * JuicyWay provisions asynchronously — we poll up to 6 times × 5 seconds.
     *
     * @return array  ['account_number', 'bank_name', 'bank_code']
     * @throws \RuntimeException
     */
    public function addBankAccount(string $walletId): array
    {
        $this->post("/wallets/{$walletId}/payment-method", [
            'type' => 'bank_account',
        ]);

        // Poll until NUBAN appears — mirrors PayGrid exactly
        for ($i = 0; $i < 6; $i++) {
            sleep(5);

            $response = $this->get("/wallets/{$walletId}");
            $methods  = $response['data']['payment_methods'] ?? [];

            if (! empty($methods)) {
                $method = $methods[0];
                return [
                    'account_number' => $method['account_number'],
                    'bank_name'      => $method['bank_name'] ?? 'Unknown Bank',
                    'bank_code'      => $method['bank_code'] ?? '',
                ];
            }

            Log::info("JuicyWay: waiting for NUBAN on wallet {$walletId} (attempt " . ($i + 1) . "/6)");
        }

        throw new \RuntimeException(
            "JuicyWay returned no payment methods after addBankAccount for wallet {$walletId}."
        );
    }

    // ── Checksum verification ────────────────────────────────────────────────

    /**
     * Verify the checksum on an inbound JuicyWay webhook event.
     * Algorithm: HMAC-SHA256(event|sorted_json(data), business_id) → UPPERCASE hex
     */
    public function verifyChecksum(array $payload): bool
    {
        if (empty($this->businessId)) {
            Log::warning('JuicyWay: JUICYWAY_BUSINESS_ID not configured — checksum verification skipped');
            return false;
        }

        $checksum = $payload['checksum'] ?? '';
        $event    = $payload['event']    ?? '';
        $data     = $payload['data']     ?? [];

        if (empty($checksum)) return false;

        ksort($data);
        $encodedData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $expected    = strtoupper(hash_hmac('sha256', "{$event}|{$encodedData}", $this->businessId));

        return hash_equals($expected, strtoupper($checksum));
    }

    // ── Private HTTP helpers ─────────────────────────────────────────────────

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

        $client = Http::timeout(60)
            ->withHeaders([
                'Authorization' => $this->apiKey,  // No "Bearer" — PayGrid confirmed this
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

        // Extract the most useful error message
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

        Log::error("JuicyWay [{$status}] {$method} {$path}", [
            'response' => $errorBody,
        ]);

        throw new \RuntimeException("JuicyWay: {$message} (HTTP {$status})");
    }

    /**
     * Normalise Nigerian phone numbers to E.164 (+234...).
     * Mirrors PayGrid's normalisePhone exactly.
     */
    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '234' . substr($digits, 1);
        }

        return '+' . ltrim($digits, '+');
    }
}
