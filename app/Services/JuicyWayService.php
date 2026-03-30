<?php

namespace App\Services;

use App\Models\FeeInvoice;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JuicyWayService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $businessId;

    public function __construct()
    {
        $this->baseUrl    = rtrim(config('services.juicyway.base_url'), '/');
        $this->apiKey     = config('services.juicyway.api_key', '');
        $this->businessId = config('services.juicyway.business_id', '');
    }

    // ── Payment Links ─────────────────────────────────────────────────────────

    /**
     * Create a JuicyWay payment link for a fee invoice.
     *
     * Returns an array with keys: id, url, reference
     * Throws \RuntimeException on API failure.
     */
    public function createPaymentLink(FeeInvoice $invoice): array
    {
        $invoice->loadMissing(['student.parents.user', 'term.session', 'items.feeItem']);

        // Build reference — must be unique, max 50 chars, alphanumeric + hyphens
        $reference = "INV-{$invoice->id}-T{$invoice->term_id}";

        // Find the primary parent with a portal account for customer details
        $primaryParent = $invoice->student->parents
            ->filter(fn($p) => $p->user !== null)
            ->first();

        $parentUser = $primaryParent?->user;

        // Amount must be in kobo (JuicyWay minor unit)
        $amountKobo = (int) round($invoice->balance * 100);

        $studentName   = $invoice->student->full_name;
        $termLabel     = "{$invoice->term->name} Term {$invoice->term->session->name}";
        $description   = "School Fees — {$termLabel} — {$studentName}";

        // Split parent name into first/last for JuicyWay customer object
        $nameParts = $parentUser
            ? explode(' ', $parentUser->name, 2)
            : ['Parent', 'Guardian'];

        $payload = [
            'amount'      => $amountKobo,
            'currency'    => 'NGN',
            'reference'   => $reference,
            'description' => $description,
            'customer'    => [
                'first_name'   => $nameParts[0],
                'last_name'    => $nameParts[1] ?? $nameParts[0],
                'email'        => $parentUser?->email ?? '',
                'phone_number' => $primaryParent?->phone
                    ? '+234' . ltrim($primaryParent->phone, '0')
                    : null,
            ],
        ];

        // Remove null values from customer object
        $payload['customer'] = array_filter($payload['customer'], fn($v) => $v !== null);

        Log::info('JuicyWay: creating payment link', [
            'reference' => $reference,
            'amount'    => $amountKobo,
            'invoice'   => $invoice->id,
        ]);

        $response = $this->post('/payment-links', $payload);

        // Flexible response parsing — handle both data.url and data.link
        $data = $response['data'] ?? $response;

        $url = $data['url']          // most likely
            ?? $data['link']         // alternative key name
            ?? $data['checkout_url'] // another alternative
            ?? null;

        if (! $url) {
            throw new \RuntimeException(
                'JuicyWay payment link created but no URL in response: ' . json_encode($response)
            );
        }

        return [
            'id'        => $data['id']        ?? null,
            'url'       => $url,
            'reference' => $data['reference'] ?? $reference,
        ];
    }

    /**
     * Deactivate a payment link once an invoice is fully paid.
     * Prevents overpayment. Fire-and-forget — failure is logged but not fatal.
     */
    public function deactivatePaymentLink(string $linkId): void
    {
        if (! $linkId) return;

        try {
            $this->patch("/payment-links/{$linkId}", ['status' => 'inactive']);
            Log::info("JuicyWay: deactivated payment link {$linkId}");
        } catch (\Throwable $e) {
            Log::warning("JuicyWay: failed to deactivate link {$linkId}: " . $e->getMessage());
        }
    }

    // ── Checksum Verification ─────────────────────────────────────────────────

    /**
     * Verify the checksum on an inbound JuicyWay webhook event.
     *
     * Algorithm (from JuicyWay docs):
     *   1. Sort data keys alphabetically
     *   2. JSON-encode the sorted data (no extra whitespace)
     *   3. Concatenate: "{event}|{encoded_data}"
     *   4. HMAC-SHA256 of that string using JUICYWAY_BUSINESS_ID as key
     *   5. Hex-encode and UPPERCASE
     *   6. Compare with received checksum using hash_equals()
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

        if (empty($checksum)) {
            return false;
        }

        // Keys MUST be sorted alphabetically — JuicyWay is strict about this
        ksort($data);

        $encodedData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $message     = "{$event}|{$encodedData}";
        $expected    = strtoupper(hash_hmac('sha256', $message, $this->businessId));

        return hash_equals($expected, strtoupper($checksum));
    }

    // ── HTTP helpers ──────────────────────────────────────────────────────────

    protected function post(string $path, array $data): array
    {
        return $this->request('POST', $path, $data);
    }

    protected function patch(string $path, array $data): array
    {
        return $this->request('PATCH', $path, $data);
    }

    /**
     * Make an authenticated request to the JuicyWay API.
     *
     * Retries once on 429 (rate limit) with a 2-second delay.
     * Throws \RuntimeException on non-2xx responses.
     */
    protected function request(string $method, string $path, array $data = []): array
    {
        $url = $this->baseUrl . $path;

        $response = Http::withToken($this->apiKey)
            ->withHeaders(['Accept' => 'application/json'])
            ->timeout(30)
            ->retry(2, 2000, fn(\Exception $e, Response $r) => $r->status() === 429)
            ->{strtolower($method)}($url, $data);

        if ($response->failed()) {
            $body   = $response->body();
            $status = $response->status();

            Log::error("JuicyWay API error [{$status}] {$method} {$path}", [
                'response' => $body,
            ]);

            throw new \RuntimeException(
                "JuicyWay API returned {$status}: {$body}"
            );
        }

        return $response->json() ?? [];
    }
}
