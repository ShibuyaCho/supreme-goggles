<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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

        // Per METRC docs: Basic base64("integrator_api_key:user_api_key")
        $response = Http::withBasicAuth($this->vendorKey, $this->userKey)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json'
            ]);

        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
                if (!empty($data)) {
                    $query = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
                    $url = strpos($url, '?') === false ? ($url . '?' . $query) : ($url . '&' . $query);
                }
                $response = $response->get($url);
                break;
            case 'POST':
                $response = $response->post($url, $data);
                break;
            case 'PUT':
                $response = $response->put($url, $data);
                break;
            case 'DELETE':
                $response = $response->delete($url, $data);
                break;
            default:
                throw new \Exception("Unsupported HTTP method: $method");
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
                return $this->makeRequest('GET', "/packages/v1/{$packageTag}");
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

            $result = $this->makeRequest('POST', '/packages/v1/change/package/status', [$data]);
            
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

            $result = $this->makeRequest('POST', '/packages/v1/change/locations', [$data]);
            
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

            $result = $this->makeRequest('POST', '/packages/v1/finish', [$data]);
            
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

            return $this->makeRequest('POST', '/packages/v1/create', [$data]);

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
        try {
            $params = [];

            if (!empty($this->facilityLicense)) {
                $params['licenseNumber'] = $this->facilityLicense;
            }

            if ($lastModifiedStart) {
                $params['lastModifiedStart'] = $lastModifiedStart;
            }

            if ($lastModifiedEnd) {
                $params['lastModifiedEnd'] = $lastModifiedEnd;
            }

            return $this->makeRequest('GET', '/packages/v1/active', $params);

        } catch (\Exception $e) {
            Log::error('Error fetching all METRC packages', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getInactivePackages(string $lastModifiedStart = null, string $lastModifiedEnd = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) {
                $params['licenseNumber'] = $this->facilityLicense;
            }
            if ($lastModifiedStart) {
                $params['lastModifiedStart'] = $lastModifiedStart;
            }
            if ($lastModifiedEnd) {
                $params['lastModifiedEnd'] = $lastModifiedEnd;
            }
            return $this->makeRequest('GET', '/packages/v1/inactive', $params);
        } catch (\Exception $e) {
            Log::error('Error fetching METRC inactive packages', [ 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    public function getOutgoingTransfers(string $lastModifiedStart = null, string $lastModifiedEnd = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) {
                $params['licenseNumber'] = $this->facilityLicense;
            }
            if ($lastModifiedStart) {
                $params['lastModifiedStart'] = $lastModifiedStart;
            }
            if ($lastModifiedEnd) {
                $params['lastModifiedEnd'] = $lastModifiedEnd;
            }
            return $this->makeRequest('GET', '/transfers/v1/outgoing', $params);
        } catch (\Exception $e) {
            Log::error('Error fetching METRC outgoing transfers', [ 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    public function getTransferDeliveries(int|string $transferId)
    {
        try {
            return $this->makeRequest('GET', "/transfers/v1/{$transferId}/deliveries");
        } catch (\Exception $e) {
            Log::error('Error fetching METRC transfer deliveries', [ 'transfer_id' => $transferId, 'error' => $e->getMessage() ]);
            throw $e;
        }
    }

    public function getDeliveryPackages(int|string $deliveryId)
    {
        try {
            return $this->makeRequest('GET', "/transfers/v1/deliveries/{$deliveryId}/packages");
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
    public function getIncomingTransfers(string $lastModifiedStart = null, string $lastModifiedEnd = null)
    {
        try {
            $params = [];
            if (!empty($this->facilityLicense)) {
                $params['licenseNumber'] = $this->facilityLicense;
            }
            if ($lastModifiedStart) {
                $params['lastModifiedStart'] = $lastModifiedStart;
            }
            if ($lastModifiedEnd) {
                $params['lastModifiedEnd'] = $lastModifiedEnd;
            }
            return $this->makeRequest('GET', '/transfers/v1/incoming', $params);
        } catch (\Exception $e) {
            Log::error('Error fetching METRC incoming transfers', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get package history
     */
    public function getPackageHistory(string $packageTag)
    {
        try {
            return $this->makeRequest('GET', "/packages/v1/{$packageTag}/history");

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

            return $this->makeRequest('POST', '/sales/v1/receipts', [$salesData]);

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
                    $d['SalesDateTime'] = $dt->format('Y-m-d\TH:i:s.u'); // no TZ suffix
                }
            }
            unset($d);

            $params = [];
            if (!empty($this->facilityLicense)) {
                $params['licenseNumber'] = $this->facilityLicense;
            }

            return $this->makeRequest('POST', '/sales/v2/deliveries', $deliveries + $params ? $deliveries : $deliveries);
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

            return $this->makeRequest('GET', '/sales/v1/receipts', $params);

        } catch (\Exception $e) {
            Log::error('Error fetching METRC sales receipts', [
                'error' => $e->getMessage()
            ]);
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
                // Create new METRC package for product
                $packageData = [
                    'Tag' => $this->generatePackageTag(),
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
}
