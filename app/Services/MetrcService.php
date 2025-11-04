<?php

namespace App\Services;

use App\Models\MetrcConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MetrcService
{
    protected string $baseUrl;
    protected string $integratorKey;   // a.k.a. vendor key
    protected string $userKey;
    protected ?string $facilityLicense;
    protected bool $liveSync;

    /**
     * Build service from the globally "active" config, optionally filtered by environment.
     */
    public static function fromActive(string $environment = null): self
    {
        $q = MetrcConfig::query()->where('is_active', true);
        if ($environment !== null) {
            $q->where('environment', $environment);
        }

        $config = $q->first();
        if (!$config) {
            throw new RuntimeException('No active Metrc configuration found.');
        }

        return new self($config);
    }

    /**
     * Canonical constructor — pass a MetrcConfig row (with encrypted casts).
     * Fails fast if any required fields are empty.
     */
    public function __construct(MetrcConfig $config)
    {
        $baseUrl       = rtrim((string) $config->base_url, '/');
        $integratorKey = (string) $config->integrator_key; // decrypted by cast
        $userKey       = (string) $config->user_key;       // decrypted by cast

        $this->baseUrl         = $baseUrl;
        $this->integratorKey   = $integratorKey;
        $this->userKey         = $userKey;
        $this->facilityLicense = $config->facility_license ?: null;
        $this->liveSync        = (bool) $config->enabled_live_sync;


    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->integratorKey !== '' && $this->userKey !== '';
    }

    protected function client()
    {
        return Http::timeout(20)
            ->retry(2, 200)
            ->withBasicAuth($this->integratorKey, $this->userKey)
            ->acceptJson()
            ->asJson();
    }

    protected function request(string $method, string $endpoint, array $query = [], $body = null)
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('METRC is not configured.');
        }

        $url    = $this->baseUrl . $endpoint;
        $client = $this->client();

        $resp = match (strtoupper($method)) {
            'GET'    => $client->get($url, $query),
            'DELETE' => $client->delete($url, $query),
            'POST'   => $client->post($url, $body ?? $query),
            'PUT'    => $client->put($url, $body ?? $query),
            default  => throw new \InvalidArgumentException("Unsupported method $method"),
        };

        if (!$resp->successful()) {
            // Avoid logging secrets; include only status + endpoint + sanitized body
            Log::warning('Metrc API error', [
                'endpoint' => $endpoint,
                'status'   => $resp->status(),
                'response' => $resp->json() ?? $resp->body(),
            ]);
            $msg = $resp->json('Message') ?? $resp->json('message') ?? 'METRC API request failed';
            throw new RuntimeException($msg);
        }

        return $resp->json();
    }

    /** Lightweight connectivity check; adjust endpoint as needed */
    public function testConnection(): array
    {
        try {
            // /facilities/v1 is a common, low-cost GET; swap if you prefer a different “ping”.
            $facilities = $this->request('GET', '/facilities/v1');
            return [
                'success'           => true,
                'message'           => 'Connected',
                'facilities_count'  => is_array($facilities) ? count($facilities) : 0,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Convenience: build from a specific config row */
    public static function fromConfig(MetrcConfig $cfg): self
    {
        return new self($cfg);
    }

    /** Convenience: build from raw array (used in tests/seeding) */
    public static function fromArray(array $input): self
    {
        $cfg = new MetrcConfig([
            'base_url'         => $input['base_url'] ?? '',
            'integrator_key'   => $input['integrator_key'] ?? '',
            'user_key'         => $input['user_key'] ?? '',
            'facility_license' => $input['facility_license'] ?? null,
            'enabled_live_sync'=> (bool) ($input['enabled_live_sync'] ?? false),
        ]);

        return new self($cfg);
    }

    /** Clone with a different config */
    public function usingConfig(MetrcConfig $config): self
    {
        return new self($config);
    }

    /** Example endpoint wrappers */
    public function getFacilityDetails() { return $this->request('GET', '/facilities/v1'); }
    public function getItemCategories()  { return $this->request('GET', '/items/v1/categories'); }

    public function getAllPackages(?string $lastModifiedStart = null, ?string $lastModifiedEnd = null) {
        $query = array_filter([
            'lastModifiedStart' => $lastModifiedStart,
            'lastModifiedEnd'   => $lastModifiedEnd,
        ]);
        return $this->request('GET', '/packages/v1/active', $query);
    }

    public function getPackageDetails(string $tag) {
        return $this->request('GET', "/packages/v1/{$tag}");
    }

    public function updatePackageStatus(string $tag, string $status) {
        $payload = [[
            'Label'        => $tag,
            'PackageState' => $status,
            'ActualDate'   => now()->toIso8601String(),
        ]];
        return $this->request('POST', '/packages/v1/change/package/status', [], $payload);
    }

    public function changePackageLocation(string $tag, string $location, string $notes = '') {
        $payload = [[
            'Label'    => $tag,
            'Location' => $location,
            'MoveDate' => now()->toIso8601String(),
            'Notes'    => $notes,
        ]];
        return $this->request('POST', '/packages/v1/change/locations', [], $payload);
    }

    public function createPackage(array $packageData) {
        $payload = [array_merge([
            'ActualDate' => now()->toIso8601String(),
            'Location'   => 'Main Room',
            'Note'       => '',
        ], $packageData)];
        return $this->request('POST', '/packages/v1/create', [], $payload);
    }

    public function createSalesReceipt(array $salesData) {
        $payload = [$salesData];
        return $this->request('POST', '/sales/v1/receipts', [], $payload);
    }

    public function getSalesReceipts(string $start, string $end) {
        return $this->request('GET', '/sales/v1/receipts', [
            'salesDateStart' => $start,
            'salesDateEnd'   => $end,
        ]);
    }

    /** Gate for sync */
    public function liveSyncEnabled(): bool { return $this->liveSync; }
}
