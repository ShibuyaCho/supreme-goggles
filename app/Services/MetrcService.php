<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MetrcService
{
    protected $baseUrl;
    protected $userKey;
    protected $vendorKey;
    protected $facilityLicense;

    public function __construct()
    {
        $this->baseUrl = config('services.metrc.base_url', 'https://api-or.metrc.com');
        $this->userKey = env('METRC_USER_KEY');
        $this->vendorKey = env('METRC_VENDOR_KEY');
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

        $url = $this->baseUrl . $endpoint;
        
        $response = Http::withBasicAuth($this->userKey, $this->vendorKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);

        switch (strtoupper($method)) {
            case 'GET':
                $response = $response->get($url, $data);
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
            $error = $response->json('message') ?? 'METRC API request failed';
            Log::error('METRC API Error', [
                'url' => $url,
                'method' => $method,
                'status' => $response->status(),
                'error' => $error,
                'response' => $response->body()
            ]);
            throw new \Exception("METRC API Error: $error");
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

            // Always include facility license number when available
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
            throw $e;
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
                    'Item' => $product->category,
                    'Quantity' => $product->quantity,
                    'UnitOfMeasure' => $product->unit ?: 'Grams',
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
}
