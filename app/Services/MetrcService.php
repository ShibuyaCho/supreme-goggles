<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class MetrcService
{
    protected $baseUrl;
    // $vendorKey: Integrator (software) API key; $userKey: User API key
    protected $userKey;
    protected $vendorKey;
    protected $facilityLicense;

    public function __construct()
    {
        $this->baseUrl = config('services.metrc.base_url', 'https://api-or.metrc.com');
        $this->userKey = env('METRC_USER_KEY');
        $this->vendorKey = env('METRC_INTEGRATOR_KEY') ?: env('METRC_VENDOR_KEY');
        $this->facilityLicense = env('METRC_FACILITY');

        // Fallback to cached settings if env not populated yet
        if (empty($this->userKey) || empty($this->vendorKey) || empty($this->facilityLicense)) {
            $cached = Cache::get('pos_settings', []);
            $this->userKey = $this->userKey ?: ($cached['metrc_user_key'] ?? null);
            $this->vendorKey = $this->vendorKey ?: ($cached['metrc_vendor_key'] ?? null);
            $this->facilityLicense = $this->facilityLicense ?: ($cached['metrc_facility'] ?? null);
        }

        // Prefer per-user METRC key from authenticated employee when available
        try {
            if (Auth::check()) {
                $emp = optional(Auth::user())->employee;
                if ($emp && !empty($emp->metrc_api_key)) {
                    $this->userKey = $emp->metrc_api_key;
                }
            }
        } catch (\Throwable $e) {
            // Ignore if auth is not available in context
        }
    }

    /**
     * Check if METRC is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->userKey) && !empty($this->vendorKey) && !empty($this->facilityLicense);
    }

    /**
     * Make authenticated request to METRC API
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [])
    {
        if (!$this->isConfigured()) {
            throw new \Exception('METRC is not properly configured');
        }

        $url = rtrim($this->baseUrl, '/') . $endpoint;

        // Per METRC docs: Basic base64("user_api_key:integrator_api_key")
        $buildClient = function($username, $password) {
            return Http::withBasicAuth($username, $password)
                ->acceptJson()
                ->timeout(45)
                ->retry(3, 250)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ]);
        };

        $attempt = function($client) use ($method, $url, $data) {
            $m = strtoupper($method);
            switch ($m) {
                case 'GET':
                    $u = $url;
                    if (!empty($data)) {
                        $query = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
                        $u = strpos($u, '?') === false ? ($u . '?' . $query) : ($u . '&' . $query);
                    }
                    return $client->get($u);
                case 'POST':
                    return $client->post($url, $data);
                case 'PUT':
                    return $client->put($url, $data);
                case 'DELETE':
                    return $client->delete($url, $data);
                default:
                    throw new \Exception("Unsupported HTTP method: $m");
            }
        };

        // First attempt: userKey as username, vendorKey as password (documented for OR)
        $response = $attempt($buildClient($this->userKey, $this->vendorKey));

        // If unauthorized, try reversed order as fallback for environments configured differently
        if (in_array($response->status(), [401, 403])) {
            Log::warning('METRC auth failed with user:vendor; retrying with vendor:user');
            $retryResp = $attempt($buildClient($this->vendorKey, $this->userKey));
            if ($retryResp->successful()) {
                $response = $retryResp;
            } else {
                // Keep the more descriptive response body if any
                if ($retryResp->status() >= 400) { $response = $retryResp; }
            }
        }

        if (!$response->successful()) {
            $status = $response->status();
            $json = $response->json();
            $error = is_array($json) ? ($json['message'] ?? ($json[0]['message'] ?? 'METRC API request failed')) : 'METRC API request failed';
            Log::error('METRC API Error', [
                'url' => $url,
                'method' => $method,
                'status' => $status,
                'error' => $error,
                'response' => $response->body()
            ]);
            throw new \Exception("METRC API Error ({$status}): {$error}");
        }

        return $response->json();
    }

    /**
     * Get package details by tag
     */
    public function getPackageDetails(string $packageTag)
    {
        try {
            $cacheKey = "metrc_package_{$packageTag}";
            
            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($packageTag) {
                $params = [];
                if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
                return $this->makeRequest('GET', "/packages/v1/{$packageTag}", $params);
            });

        } catch (\Exception $e) {
            Log::error('Error fetching METRC package details', [
                'package_tag' => $packageTag,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update package status
     */
    public function updatePackageStatus(string $packageTag, string $status)
    {
        try {
            $data = [
                'Label' => $packageTag,
                'PackageState' => $status,
                'ActualDate' => now()->toISOString()
            ];

            $endpoint = '/packages/v1/change/package/status';
            if (!empty($this->facilityLicense)) { $endpoint .= '?licenseNumber=' . rawurlencode($this->facilityLicense); }
            $result = $this->makeRequest('POST', $endpoint, [$data]);
            
            // Clear cache for this package
            Cache::forget("metrc_package_{$packageTag}");
            
            return $result;

        } catch (\Exception $e) {
            Log::error('Error updating METRC package status', [
                'package_tag' => $packageTag,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Change package location
     */
    public function changePackageLocation(string $packageTag, string $location, string $notes = '')
    {
        try {
            $data = [
                'Label' => $packageTag,
                'Location' => $location,
                'MoveDate' => now()->toISOString(),
                'Notes' => $notes
            ];

            $endpoint = '/packages/v1/change/locations';
            if (!empty($this->facilityLicense)) { $endpoint .= '?licenseNumber=' . rawurlencode($this->facilityLicense); }
            $result = $this->makeRequest('POST', $endpoint, [$data]);
            
            // Clear cache for this package
            Cache::forget("metrc_package_{$packageTag}");
            
            return $result;

        } catch (\Exception $e) {
            Log::error('Error changing METRC package location', [
                'package_tag' => $packageTag,
                'location' => $location,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Finish/destroy package
     */
    public function finishPackage(string $packageTag, string $reason)
    {
        try {
            $data = [
                'Label' => $packageTag,
                'ActualDate' => now()->toISOString(),
                'ReasonNote' => $reason
            ];

            $endpoint = '/packages/v1/finish';
            if (!empty($this->facilityLicense)) { $endpoint .= '?licenseNumber=' . rawurlencode($this->facilityLicense); }
            $result = $this->makeRequest('POST', $endpoint, [$data]);
            
            // Clear cache for this package
            Cache::forget("metrc_package_{$packageTag}");
            
            return $result;

        } catch (\Exception $e) {
            Log::error('Error finishing METRC package', [
                'package_tag' => $packageTag,
                'reason' => $reason,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create new package
     */
    public function createPackage(array $packageData)
    {
        try {
            $requiredFields = ['Tag', 'PackagedDate', 'Item', 'Quantity'];
            
            foreach ($requiredFields as $field) {
                if (!isset($packageData[$field])) {
                    throw new \Exception("Missing required field: $field");
                }
            }

            $data = array_merge([
                'ActualDate' => now()->toISOString(),
                'Location' => 'Main Room',
                'PatientLicenseNumber' => null,
                'Note' => '',
                'IsProductionBatch' => false,
                'ProductionBatchNumber' => null,
                'IsTradeSample' => false,
                'IsDonation' => false
            ], $packageData);

            $endpoint = '/packages/v1/create';
            if (!empty($this->facilityLicense)) { $endpoint .= '?licenseNumber=' . rawurlencode($this->facilityLicense); }
            return $this->makeRequest('POST', $endpoint, [$data]);

        } catch (\Exception $e) {
            Log::error('Error creating METRC package', [
                'package_data' => $packageData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get all packages for facility
     */
    public function getAllPackages(string $lastModifiedStart = null, string $lastModifiedEnd = null)
    {
        $buildParams = function($includeLicense = true, $altKey = null) use ($lastModifiedStart, $lastModifiedEnd) {
            $p = [];
            if ($includeLicense && !empty($this->facilityLicense)) {
                $key = $altKey ?: 'licenseNumber';
                $p[$key] = $this->facilityLicense;
            }
            if ($lastModifiedStart) { $p['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $p['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            return $p;
        };

        $paginateV2 = function($endpoint, $params) {
            $page = 1; $pageSize = 20; $all = [];
            do {
                $pageParams = $params + ['pageNumber' => $page, 'pageSize' => $pageSize];
                $raw = $this->makeRequest('GET', $endpoint, $pageParams);
                $data = isset($raw['Data']) && is_array($raw['Data']) ? $raw['Data'] : (is_array($raw) ? $raw : []);
                if (!empty($data)) {
                    foreach ($data as $row) { $all[] = $row; }
                }
                $totalPages = $raw['TotalPages'] ?? null;
                if ($totalPages && $page < $totalPages) { $page++; } else { break; }
            } while (true);
            return $all;
        };

        try {
            // Try v2 with licenseNumber
            $all = $paginateV2('/packages/v2/active', $buildParams(true));
            if (count($all) > 0) return $all;

            // Try v2 without license filter (some tenants scope by API key)
            $all = $paginateV2('/packages/v2/active', $buildParams(false));
            if (count($all) > 0) return $all;

            // Try v2 with alternate param name
            $all = $paginateV2('/packages/v2/active', $buildParams(true, 'license'));
            if (count($all) > 0) return $all;
        } catch (\Exception $e) {
            Log::warning('v2 active packages failed, attempting v1 fallback', ['error' => $e->getMessage()]);
        }

        // v1 fallbacks
        foreach ([[true,null],[false,null],[true,'license']] as [$withLicense, $altKey]) {
            try {
                $raw = $this->makeRequest('GET', '/packages/v1/active', $buildParams($withLicense, $altKey));
                $data = isset($raw['Data']) && is_array($raw['Data']) ? $raw['Data'] : (is_array($raw) ? $raw : []);
                if (is_array($data) && count($data) > 0) return $data;
            } catch (\Exception $e) {
                Log::warning('v1 active packages variant failed', ['withLicense' => $withLicense, 'altKey' => $altKey, 'error' => $e->getMessage()]);
            }
        }

        // As a last resort return empty array
        return [];
    }

    public function getInactivePackages(string $lastModifiedStart = null, string $lastModifiedEnd = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            $page = 1; $pageSize = 20; $all = [];
            do {
                $pageParams = $params + ['pageNumber' => $page, 'pageSize' => $pageSize];
                $raw = $this->makeRequest('GET', '/packages/v2/inactive', $pageParams);
                $data = isset($raw['Data']) && is_array($raw['Data']) ? $raw['Data'] : (is_array($raw) ? $raw : []);
                foreach ($data as $row) { $all[] = $row; }
                $totalPages = $raw['TotalPages'] ?? null;
                if ($totalPages && $page < $totalPages) { $page++; } else { break; }
            } while (true);
            return $all;
        } catch (\Exception $e) {
            Log::warning('v2 inactive packages failed, attempting v1 fallback', ['error' => $e->getMessage()]);
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            $raw = $this->makeRequest('GET', '/packages/v1/inactive', $params);
            return isset($raw['Data']) && is_array($raw['Data']) ? $raw['Data'] : (is_array($raw) ? $raw : []);
        }
    }

    public function getOutgoingTransfers(string $lastModifiedStart = null, string $lastModifiedEnd = null, ?int $pageNumber = null, ?int $pageSize = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            if ($pageNumber !== null) { $params['pageNumber'] = $pageNumber; }
            if ($pageSize !== null) { $params['pageSize'] = min(20, max(1, $pageSize)); }
            return $this->makeRequest('GET', '/transfers/v2/outgoing', $params);
        } catch (\Exception $e) {
            Log::warning('v2 outgoing transfers failed, attempting v1 fallback', ['error' => $e->getMessage()]);
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            return $this->makeRequest('GET', '/transfers/v1/outgoing', $params);
        }
    }

    public function getTransferDeliveries(int|string $transferId)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            return $this->makeRequest('GET', "/transfers/v1/{$transferId}/deliveries", $params);
        } catch (\Exception $e) {
            Log::error('Error fetching METRC transfer deliveries', [ 'transfer_id' => $transferId, 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    public function getDeliveryPackages(int|string $deliveryId)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            return $this->makeRequest('GET', "/transfers/v1/deliveries/{$deliveryId}/packages", $params);
        } catch (\Exception $e) {
            Log::error('Error fetching METRC delivery packages', [ 'delivery_id' => $deliveryId, 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    /**
     * Get lab test results for a package (paginated optional)
     */
    public function getLabTestResults(int|string $packageId, ?int $pageNumber = null, ?int $pageSize = null)
    {
        try {
            $params = [
                'packageId' => $packageId,
                'licenseNumber' => $this->facilityLicense,
            ];
            if ($pageNumber !== null) { $params['pageNumber'] = $pageNumber; }
            if ($pageSize !== null) { $params['pageSize'] = min(20, max(1, $pageSize)); }
            return $this->makeRequest('GET', '/labtests/v2/results', $params);
        } catch (\Exception $e) {
            Log::error('Error fetching METRC lab test results', [ 'package_id' => $packageId, 'error' => $e->getMessage() ]);
            return null; // don't block inventory sync if lab fetch fails
        }
    }

    /**
     * Parse lab results into product fields
     */
    public function parseLabResults($labResponse): array
    {
        $data = [
            'is_tested' => false,
            'test_status' => null,
            'test_date' => null,
            'lab_name' => null,
            'contaminants_passed' => null,
            'lab_results' => null,
            'thc' => null,
            'cbd' => null,
            'cbn' => null,
            'cbg' => null,
            'cbc' => null,
        ];

        if (empty($labResponse)) {
            return $data;
        }

        $records = $labResponse['Data'] ?? (is_array($labResponse) ? $labResponse : []);
        if (!is_array($records)) { return $data; }

        $overallPass = null; $anyReleased = false; $labName = null; $testDate = null;
        $nonCannabinoidAllPassed = true; $hasNonCannabinoid = false;

        foreach ($records as $rec) {
            if (!is_array($rec)) { continue; }
            $overallPass = $rec['OverallPassed'] ?? $overallPass;
            $labName = $rec['LabFacilityName'] ?? $labName;
            $testDate = $rec['TestPerformedDate'] ?? $testDate;
            $released = $rec['ResultReleased'] ?? false; $anyReleased = $anyReleased || $released;

            $type = strtolower((string)($rec['TestTypeName'] ?? ''));
            $level = $rec['TestResultLevel'] ?? null;

            // Map cannabinoids
            if ($level !== null) {
                if (str_contains($type, 'total thc') || $type === 'thc') { $data['thc'] = (float)$level; }
                if (str_contains($type, 'total cbd') || $type === 'cbd') { $data['cbd'] = (float)$level; }
                if ($type === 'cbn' || str_contains($type, 'total cbn')) { $data['cbn'] = (float)$level; }
                if ($type === 'cbg' || str_contains($type, 'total cbg')) { $data['cbg'] = (float)$level; }
                if ($type === 'cbc' || str_contains($type, 'total cbc')) { $data['cbc'] = (float)$level; }
            }

            // Track contaminants pass (microbiologicals, pesticides, etc.)
            if (!in_array($type, ['thc','cbd','cbn','cbg','cbc']) && !str_contains($type, 'cannabinoid')) {
                $hasNonCannabinoid = true;
                $testPassed = $rec['TestPassed'] ?? null;
                if ($testPassed === false) { $nonCannabinoidAllPassed = false; }
            }
        }

        $data['is_tested'] = count($records) > 0 && $anyReleased;
        if ($overallPass === true) { $data['test_status'] = 'passed'; }
        elseif ($overallPass === false) { $data['test_status'] = 'failed'; }
        else { $data['test_status'] = $anyReleased ? 'passed' : null; }
        $data['test_date'] = $testDate ? date('Y-m-d', strtotime($testDate)) : null;
        $data['lab_name'] = $labName;
        $data['contaminants_passed'] = $hasNonCannabinoid ? $nonCannabinoidAllPassed : null;
        $data['lab_results'] = $records;

        return $data;
    }

    /**
     * Get incoming transfers for facility
     */
    public function getIncomingTransfers(string $lastModifiedStart = null, string $lastModifiedEnd = null, ?int $pageNumber = null, ?int $pageSize = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            if ($pageNumber !== null) { $params['pageNumber'] = $pageNumber; }
            if ($pageSize !== null) { $params['pageSize'] = min(20, max(1, $pageSize)); }
            return $this->makeRequest('GET', '/transfers/v2/incoming', $params);
        } catch (\Exception $e) {
            Log::warning('v2 incoming transfers failed, attempting v1 fallback', ['error' => $e->getMessage()]);
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            return $this->makeRequest('GET', '/transfers/v1/incoming', $params);
        }
    }

    /**
     * Get package history
     */
    public function getPackageHistory(string $packageTag)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            return $this->makeRequest('GET', "/packages/v1/{$packageTag}/history", $params);

        } catch (\Exception $e) {
            Log::error('Error fetching METRC package history', [
                'package_tag' => $packageTag,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create sales receipt
     */
    public function createSalesReceipt(array $salesData)
    {
        try {
            $requiredFields = ['SalesDateTime', 'SalesCustomerType', 'Transactions'];

            foreach ($requiredFields as $field) {
                if (!isset($salesData[$field])) {
                    throw new \Exception("Missing required field: $field");
                }
            }

            $endpoint = '/sales/v1/receipts';
            if (!empty($this->facilityLicense)) { $endpoint .= '?licenseNumber=' . rawurlencode($this->facilityLicense); }
            return $this->makeRequest('POST', $endpoint, [$salesData]);

        } catch (\Exception $e) {
            Log::error('Error creating METRC sales receipt', [
                'sales_data' => $salesData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create sales deliveries (v2). SalesDateTime must be local facility time without timezone.
     */
    public function createSalesDeliveries(array $deliveries)
    {
        try {
            // Ensure correctly formatted local timestamps
            foreach ($deliveries as &$d) {
                if (isset($d['SalesDateTime'])) {
                    $dt = \Carbon\Carbon::parse($d['SalesDateTime']);
                    $d['SalesDateTime'] = $dt->format('Y-m-d\TH:i:s.000'); // no TZ suffix
                }
            }
            unset($d);

            $endpoint = '/sales/v2/deliveries';
            if (!empty($this->facilityLicense)) {
                $endpoint .= '?licenseNumber=' . rawurlencode($this->facilityLicense);
            }

            return $this->makeRequest('POST', $endpoint, $deliveries);
        } catch (\Exception $e) {
            Log::error('Error creating METRC sales deliveries', [
                'deliveries' => $deliveries,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get sales receipts
     */
    public function getSalesReceipts(string $salesDateStart, string $salesDateEnd)
    {
        try {
            $params = [
                'salesDateStart' => $salesDateStart,
                'salesDateEnd' => $salesDateEnd
            ];

            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            return $this->makeRequest('GET', '/sales/v1/receipts', $params);

        } catch (\Exception $e) {
            Log::error('Error fetching METRC sales receipts', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Retail ID: get packages info for a list of labels
     */
    public function getRetailIdPackagesInfo(array $packageLabels)
    {
        try {
            $endpoint = '/retailid/v2/packages/info';
            if (!empty($this->facilityLicense)) {
                $endpoint .= '?licenseNumber=' . rawurlencode($this->facilityLicense);
            }
            $payload = [ 'packageLabels' => array_values(array_unique(array_filter($packageLabels))) ];
            if (empty($payload['packageLabels'])) { return ['Packages' => []]; }
            return $this->makeRequest('POST', $endpoint, $payload);
        } catch (\Exception $e) {
            Log::error('Error fetching Retail ID packages info', [ 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    /**
     * Get available package tags (premium)
     */
    public function getAvailablePackageTags(?int $pageNumber = null, ?int $pageSize = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) {
                $params['licenseNumber'] = $this->facilityLicense;
            }
            if ($pageNumber !== null) { $params['pageNumber'] = $pageNumber; }
            if ($pageSize !== null) { $params['pageSize'] = min(20, max(1, $pageSize)); }
            return $this->makeRequest('GET', '/tags/v2/package/available', $params);
        } catch (\Exception $e) {
            Log::error('Error fetching available METRC package tags', [ 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    /**
     * Get available plant tags (premium)
     */
    public function getAvailablePlantTags(?int $pageNumber = null, ?int $pageSize = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) {
                $params['licenseNumber'] = $this->facilityLicense;
            }
            if ($pageNumber !== null) { $params['pageNumber'] = $pageNumber; }
            if ($pageSize !== null) { $params['pageSize'] = min(20, max(1, $pageSize)); }
            return $this->makeRequest('GET', '/tags/v2/plant/available', $params);
        } catch (\Exception $e) {
            Log::error('Error fetching available METRC plant tags', [ 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    /**
     * Get facility details
     */
    public function getFacilityDetails()
    {
        try {
            $cacheKey = "metrc_facility_{$this->facilityLicense}";

            return Cache::remember($cacheKey, now()->addHours(1), function () {
                return $this->makeRequest('GET', '/facilities/v1');
            });

        } catch (\Exception $e) {
            Log::error('Error fetching METRC facility details', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get strain by ID (v2) optionally scoped by facility license
     */
    public function getStrainById(int|string $id, ?string $licenseNumber = null)
    {
        try {
            $cacheKey = "metrc_strain_{$id}_" . ($licenseNumber ?: $this->facilityLicense ?: 'all');
            return Cache::remember($cacheKey, now()->addHours(6), function () use ($id, $licenseNumber) {
                $endpoint = "/strains/v2/{$id}";
                $params = [];
                $license = $licenseNumber ?: $this->facilityLicense;
                if (!empty($license)) { $params['licenseNumber'] = $license; }
                return $this->makeRequest('GET', $endpoint, $params);
            });
        } catch (\Exception $e) {
            Log::error('Error fetching METRC strain', [ 'strain_id' => $id, 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    /**
     * Get item by ID (v2) optionally scoped by facility license
     */
    public function getItemById(int|string $id, ?string $licenseNumber = null)
    {
        try {
            $cacheKey = "metrc_item_{$id}_" . ($licenseNumber ?: $this->facilityLicense ?: 'all');
            return Cache::remember($cacheKey, now()->addHours(6), function () use ($id, $licenseNumber) {
                $endpoint = "/items/v2/{$id}";
                $params = [];
                $license = $licenseNumber ?: $this->facilityLicense;
                if (!empty($license)) { $params['licenseNumber'] = $license; }
                return $this->makeRequest('GET', $endpoint, $params);
            });
        } catch (\Exception $e) {
            Log::error('Error fetching METRC item', [ 'item_id' => $id, 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    /**
     * Get active items list (v2) with optional pagination and lastModified filters
     */
    public function getActiveItems(?string $lastModifiedStart = null, ?string $lastModifiedEnd = null, ?int $pageNumber = null, ?int $pageSize = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            if ($pageNumber !== null) { $params['pageNumber'] = $pageNumber; }
            if ($pageSize !== null) { $params['pageSize'] = min(20, max(1, $pageSize)); }
            return $this->makeRequest('GET', '/items/v2/active', $params);
        } catch (\Exception $e) {
            Log::warning('v2 active items failed, attempting v1 fallback', ['error' => $e->getMessage()]);
            $params = [];
            if (!empty($this->facilityLicense)) { $params['licenseNumber'] = $this->facilityLicense; }
            if ($lastModifiedStart) { $params['lastModifiedStart'] = $this->toUtcZulu($lastModifiedStart); }
            if ($lastModifiedEnd) { $params['lastModifiedEnd'] = $this->toUtcZulu($lastModifiedEnd); }
            return $this->makeRequest('GET', '/items/v1/active', $params);
        }
    }

    /**
     * Test METRC connection
     */
    public function testConnection()
    {
        try {
            $facilities = $this->makeRequest('GET', '/facilities/v1');
            
            return [
                'success' => true,
                'message' => 'METRC connection successful',
                'facilities_count' => count($facilities)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'METRC connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get item categories
     */
    public function getItemCategories()
    {
        try {
            $cacheKey = 'metrc_item_categories';
            
            return Cache::remember($cacheKey, now()->addHours(6), function () {
                return $this->makeRequest('GET', '/items/v1/categories');
            });

        } catch (\Exception $e) {
            Log::error('Error fetching METRC item categories', [
                'error' => $e->getMessage()
            ]);
            // Fallback to common Oregon categories
            return [
                ['Name' => 'Flower'],
                ['Name' => 'Pre-Rolls'],
                ['Name' => 'Concentrates'],
                ['Name' => 'Extracts'],
                ['Name' => 'Edibles'],
                ['Name' => 'Topicals'],
                ['Name' => 'Tinctures'],
                ['Name' => 'Vape Cartridges'],
                ['Name' => 'Vape Pens'],
                ['Name' => 'Inhalable Cannabinoids'],
                ['Name' => 'Clones'],
                ['Name' => 'Immature Plants'],
                ['Name' => 'Seeds'],
                ['Name' => 'Shake/Trim'],
                ['Name' => 'Kief'],
                ['Name' => 'Accessories'],
            ];
        }
    }

    /**
     * Sync product with METRC
     */
    public function syncProduct($product)
    {
        try {
            if (!$product->metrc_tag) {
                // Prefer real available METRC tags; fallback to generated
                $selectedTag = null;
                try {
                    $tags = $this->getAvailablePackageTags();
                    $list = (isset($tags['Data']) && is_array($tags['Data'])) ? $tags['Data'] : (is_array($tags) ? $tags : []);
                    if (!empty($list)) {
                        $first = $list[0];
                        $selectedTag = $first['Label'] ?? $first['label'] ?? null;
                    }
                } catch (\Throwable $e) {
                    // ignore; may be premium or unavailable
                }
                $selectedTag = $selectedTag ?: $this->generatePackageTag();

                // Create new METRC package for product
                $packageData = [
                    'Tag' => $selectedTag,
                    'PackagedDate' => now()->toISOString(),
                    'Item' => $this->mapCategoryToMetrc($product->category),
                    'Quantity' => $product->quantity,
                    'UnitOfMeasure' => $this->mapUnitToMetrc($product->unit ?? null),
                    'PatientLicenseNumber' => null,
                    'Note' => "Product: {$product->name}",
                    'IsProductionBatch' => false,
                    'IsTradeSample' => false,
                    'IsDonation' => false
                ];

                $result = $this->createPackage($packageData);
                
                // Update product with METRC tag
                $product->metrc_tag = $packageData['Tag'];
                $product->save();

                return $result;
            } else {
                // Update existing package
                return $this->getPackageDetails($product->metrc_tag);
            }

        } catch (\Exception $e) {
            Log::error('Error syncing product with METRC', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate unique METRC package tag
     */
    private function generatePackageTag()
    {
        $prefix = config('services.metrc.tag_prefix', '1A4');
        $suffix = strtoupper(substr(uniqid(), -8));

        return $prefix . $suffix;
    }

    /**
     * Normalize local category names into METRC item categories
     */
    private function mapCategoryToMetrc(?string $category): string
    {
        $c = strtolower(trim((string)$category));
        $map = [
            'plants (clones)' => 'Immature Plants',
            'clones' => 'Immature Plants',
            'immature plants' => 'Immature Plants',
            'seeds' => 'Seeds',
            'inhalable cannabinoid' => 'Inhalable Cannabinoids',
            'patches' => 'Topicals',
            'apparel' => 'Accessories',
            'paraphernalia' => 'Accessories',
            'vapes' => 'Vape Cartridges',
            'extracts' => 'Concentrates',
        ];
        if (isset($map[$c])) {
            return $map[$c];
        }
        return $category ? ucwords($category) : 'Accessories';
    }

    private function mapUnitToMetrc(?string $unit): string
    {
        $u = strtolower(trim((string)$unit));
        $map = [
            'each' => 'Each',
            'unit' => 'Each',
            'units' => 'Each',
            'gram' => 'Grams',
            'grams' => 'Grams',
            'g' => 'Grams',
            'fluid oz.' => 'Fluid Ounces',
            'fluid oz' => 'Fluid Ounces',
            'fl oz' => 'Fluid Ounces',
            'ounce' => 'Fluid Ounces',
            'ounces' => 'Fluid Ounces',
            'milliliter' => 'Milliliters',
            'milliliters' => 'Milliliters',
            'ml' => 'Milliliters',
        ];
        return $map[$u] ?? 'Each';
    }

    /**
     * Infer strain type from percentages or genetics string
     */
    public function inferStrainType($indicaPercentage = null, $sativaPercentage = null, $genetics = null): ?string
    {
        $i = is_numeric($indicaPercentage) ? (float)$indicaPercentage : null;
        $s = is_numeric($sativaPercentage) ? (float)$sativaPercentage : null;
        if ($i !== null && $s !== null) {
            if ($i >= 60 && $s < 40) return 'Indica-dominant';
            if ($s >= 60 && $i < 40) return 'Sativa-dominant';
            return 'Hybrid';
        }
        $g = strtolower((string)$genetics);
        if (str_contains($g, 'indica') && !str_contains($g, 'sativa')) return 'Indica';
        if (str_contains($g, 'sativa') && !str_contains($g, 'indica')) return 'Sativa';
        if ($g) return 'Hybrid';
        return null;
    }

    private function toUtcZulu(string $dt): string
    {
        try {
            $c = \Carbon\Carbon::parse($dt)->utc();
            return $c->format('Y-m-d\TH:i:s\Z');
        } catch (\Throwable $e) {
            return $dt;
        }
    }
}
