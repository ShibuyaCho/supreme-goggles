<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetrcService;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MetrcController extends Controller
{
    protected $metrcService;

    public function __construct(MetrcService $metrcService)
    {
        $this->metrcService = $metrcService;
    }

    /**
     * Test METRC connection
     */
    public function testConnection(Request $request)
    {
        try {
            $result = $this->metrcService->testConnection();
            
            return response()->json([
                'configured' => $this->metrcService->isConfigured(),
                'connection_test' => $result,
                'facility_license' => config('services.metrc.facility_license'),
                'environment' => config('services.metrc.base_url'),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'METRC connection test failed',
                'message' => $e->getMessage(),
                'configured' => $this->metrcService->isConfigured()
            ], 500);
        }
    }

    /**
     * Get package details by tag
     */
    public function getPackageDetails(Request $request, string $packageTag)
    {
        try {
            $details = $this->metrcService->getPackageDetails($packageTag);
            
            return response()->json([
                'package' => $details,
                'retrieved_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve package details',
                'message' => $e->getMessage(),
                'package_tag' => $packageTag
            ], 500);
        }
    }

    /**
     * Get all packages
     */
    public function getAllPackages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_modified_start' => 'nullable|date',
            'last_modified_end' => 'nullable|date|after_or_equal:last_modified_start'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $packages = $this->metrcService->getAllPackages(
                $request->last_modified_start,
                $request->last_modified_end
            );

            return response()->json([
                'packages' => $packages,
                'count' => count($packages),
                'retrieved_at' => now()->toISOString(),
                'filters' => [
                    'last_modified_start' => $request->last_modified_start,
                    'last_modified_end' => $request->last_modified_end
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve packages',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import active METRC packages into inventory (exclude zero-quantity)
     */
    public function importActivePackages(Request $request)
    {
        try {
            $packages = $this->metrcService->getAllPackages();
            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ((array)$packages as $pkg) {
                $qty = (int)($pkg['Quantity'] ?? $pkg['quantity'] ?? 0);
                if ($qty <= 0) { $skipped++; continue; }

                $label = $pkg['Label'] ?? $pkg['label'] ?? null;
                if (!$label) { $skipped++; continue; }

                // Derive fields safely
                $item = $pkg['Item'] ?? [];
                $itemName = is_array($item) ? ($item['Name'] ?? $item['name'] ?? null) : null;
                $category = is_array($item) ? ($item['Category'] ?? $item['category'] ?? null) : ($pkg['Category'] ?? $pkg['category'] ?? null);
                $uom = $pkg['UnitOfMeasure'] ?? $pkg['unitOfMeasure'] ?? $pkg['unit_of_measure'] ?? '';
                $packagedDate = $pkg['PackagedDate'] ?? $pkg['packagedDate'] ?? null;
                $expDate = $pkg['ExpirationDate'] ?? $pkg['expirationDate'] ?? null;
                $vendor = $pkg['SourceFacilityLicenseNumber'] ?? $pkg['SourceFacility'] ?? null;

                $data = [
                    'name' => $itemName ?: ($pkg['ProductName'] ?? $pkg['productName'] ?? ('METRC Package ' . $label)),
                    'category' => $category ?: 'Unknown',
                    'price' => 0,
                    'cost' => 0,
                    'sku' => $label,
                    'weight' => $uom ?: 'Units',
                    'room' => 'Inventory',
                    'supplier' => $vendor ?: 'METRC',
                    'vendor' => $vendor ?: 'METRC',
                    'packaged_date' => $packagedDate ? date('Y-m-d', strtotime($packagedDate)) : null,
                    'expiration_date' => $expDate ? date('Y-m-d', strtotime($expDate)) : null,
                    'metrc_tag' => $label,
                    'quantity' => $qty,
                ];

                // Pull lab results to auto-fill potency and test info
                $pkgId = $pkg['Id'] ?? $pkg['PackageId'] ?? null;
                if ($pkgId) {
                    $labResp = $this->metrcService->getLabTestResults($pkgId, null, 20);
                    if ($labResp) {
                        $parsed = $this->metrcService->parseLabResults($labResp);
                        $data = array_merge($data, array_filter($parsed, function($v){ return $v !== null; }));
                    }
                }

                $existing = Product::where('metrc_tag', $label)->first();
                if ($existing) {
                    $existing->fill($data);
                    $existing->save();
                    $updated++;
                } else {
                    Product::create($data);
                    $imported++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'METRC packages imported successfully',
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total_processed' => $imported + $updated + $skipped,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to import METRC packages',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get incoming transfers
     */
    public function getIncomingTransfers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_modified_start' => 'nullable|date',
            'last_modified_end' => 'nullable|date|after_or_equal:last_modified_start'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transfers = $this->metrcService->getIncomingTransfers(
                $request->last_modified_start,
                $request->last_modified_end
            );

            return response()->json([
                'transfers' => $transfers,
                'count' => is_array($transfers) ? count($transfers) : 0,
                'retrieved_at' => now()->toISOString(),
                'filters' => [
                    'last_modified_start' => $request->last_modified_start,
                    'last_modified_end' => $request->last_modified_end
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve incoming transfers',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update package status
     */
    public function updatePackageStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_tag' => 'required|string',
            'status' => 'required|string|in:Active,InTransit,Inactive,Disposed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->metrcService->updatePackageStatus(
                $request->package_tag,
                $request->status
            );
            
            Log::info('METRC package status updated', [
                'package_tag' => $request->package_tag,
                'new_status' => $request->status,
                'user_id' => $request->user()->id
            ]);
            
            return response()->json([
                'message' => 'Package status updated successfully',
                'package_tag' => $request->package_tag,
                'new_status' => $request->status,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update package status',
                'message' => $e->getMessage(),
                'package_tag' => $request->package_tag
            ], 500);
        }
    }

    /**
     * Change package location
     */
    public function changePackageLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_tag' => 'required|string',
            'location' => 'required|string',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->metrcService->changePackageLocation(
                $request->package_tag,
                $request->location,
                $request->notes ?? ''
            );
            
            Log::info('METRC package location changed', [
                'package_tag' => $request->package_tag,
                'new_location' => $request->location,
                'notes' => $request->notes,
                'user_id' => $request->user()->id
            ]);
            
            return response()->json([
                'message' => 'Package location updated successfully',
                'package_tag' => $request->package_tag,
                'new_location' => $request->location,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to change package location',
                'message' => $e->getMessage(),
                'package_tag' => $request->package_tag
            ], 500);
        }
    }

    /**
     * Create new package
     */
    public function createPackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag' => 'required|string|unique:products,metrc_tag',
            'item' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit_of_measure' => 'required|string',
            'packaged_date' => 'required|date',
            'location' => 'nullable|string',
            'note' => 'nullable|string|max:500',
            'is_production_batch' => 'boolean',
            'production_batch_number' => 'nullable|string',
            'is_trade_sample' => 'boolean',
            'is_donation' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $packageData = [
                'Tag' => $request->tag,
                'Item' => $request->item,
                'Quantity' => $request->quantity,
                'UnitOfMeasure' => $request->unit_of_measure,
                'PackagedDate' => $request->packaged_date,
                'Location' => $request->location ?? 'Main Room',
                'Note' => $request->note ?? '',
                'IsProductionBatch' => $request->is_production_batch ?? false,
                'ProductionBatchNumber' => $request->production_batch_number,
                'IsTradeSample' => $request->is_trade_sample ?? false,
                'IsDonation' => $request->is_donation ?? false
            ];

            $result = $this->metrcService->createPackage($packageData);
            
            Log::info('METRC package created', [
                'package_data' => $packageData,
                'user_id' => $request->user()->id
            ]);
            
            return response()->json([
                'message' => 'Package created successfully',
                'package_tag' => $request->tag,
                'result' => $result
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create package',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync product with METRC
     */
    public function syncProduct(Request $request, Product $product)
    {
        try {
            $result = $this->metrcService->syncProduct($product);
            
            Log::info('Product synced with METRC', [
                'product_id' => $product->id,
                'metrc_tag' => $product->metrc_tag,
                'user_id' => $request->user()->id
            ]);
            
            return response()->json([
                'message' => 'Product synced with METRC successfully',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'metrc_tag' => $product->metrc_tag
                ],
                'metrc_data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to sync product with METRC',
                'message' => $e->getMessage(),
                'product_id' => $product->id
            ], 500);
        }
    }

    /**
     * Create sales receipt in METRC
     */
    public function createSalesReceipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_datetime' => 'required|date',
            'sales_customer_type' => 'required|string|in:Consumer,Patient,Caregiver',
            'patient_license_number' => 'nullable|string',
            'caregiver_license_number' => 'nullable|string',
            'transactions' => 'required|array|min:1',
            'transactions.*.package_label' => 'required|string',
            'transactions.*.quantity' => 'required|numeric|min:0.01',
            'transactions.*.unit_of_measure' => 'required|string',
            'transactions.*.total_amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $salesData = [
                'SalesDateTime' => $request->sales_datetime,
                'SalesCustomerType' => $request->sales_customer_type,
                'PatientLicenseNumber' => $request->patient_license_number,
                'CaregiverLicenseNumber' => $request->caregiver_license_number,
                'Transactions' => $request->transactions
            ];

            $result = $this->metrcService->createSalesReceipt($salesData);
            
            Log::info('METRC sales receipt created', [
                'sales_data' => $salesData,
                'user_id' => $request->user()->id
            ]);
            
            return response()->json([
                'message' => 'Sales receipt created successfully',
                'receipt_number' => $result['ReceiptNumber'] ?? null,
                'result' => $result
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create sales receipt',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create METRC sales receipt from internal Sale ID
     */
    public function createReceiptFromSale(Request $request, Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return response()->json([
                'error' => 'Only completed sales can be pushed to METRC'
            ], 400);
        }

        $transactions = [];
        foreach ($sale->saleItems as $item) {
            $product = $item->product;
            $packageLabel = $product?->metrc_tag ?: ($item->metrc_tag ?: $product?->sku);
            if (!$packageLabel) {
                return response()->json([
                    'error' => 'Missing METRC package tag for one or more items',
                    'item_id' => $item->id,
                ], 422);
            }

            $weightSold = (float)($item->weight_sold ?? 0);
            if ($weightSold > 0) {
                $quantity = $weightSold;
                $uom = 'Grams';
            } else {
                $quantity = (float)$item->quantity;
                $uom = 'Each';
            }

            $transactions[] = [
                'package_label' => $packageLabel,
                'quantity' => $quantity,
                'unit_of_measure' => $uom,
                'total_amount' => (float)$item->total_price,
            ];
        }

        $customerType = strtolower($sale->customer_type) === 'medical' ? 'Patient' : 'Consumer';
        $patientLicense = $sale->customer?->medical_card_number ?: null;

        $payload = [
            'sales_datetime' => $sale->created_at->toIso8601String(),
            'sales_customer_type' => $customerType,
            'patient_license_number' => $patientLicense,
            'caregiver_license_number' => null,
            'transactions' => $transactions,
        ];

        $request->merge($payload);
        return $this->createSalesReceipt($request);
    }

    /**
     * Get sales receipts from METRC
     */
    public function createSalesDeliveries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deliveries' => 'required|array|min:1',
            'deliveries.*.sales_datetime' => 'required|date',
            'deliveries.*.sales_customer_type' => 'required|string|in:Consumer,Patient,Caregiver',
            'deliveries.*.patient_license_number' => 'nullable|string',
            'deliveries.*.transactions' => 'required|array|min:1',
            'deliveries.*.transactions.*.package_label' => 'required|string',
            'deliveries.*.transactions.*.quantity' => 'required|numeric|min:0.01',
            'deliveries.*.transactions.*.unit_of_measure' => 'required|string',
            'deliveries.*.transactions.*.total_amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deliveries = [];
            foreach ($request->deliveries as $d) {
                $deliveries[] = [
                    'SalesDateTime' => \Carbon\Carbon::parse($d['sales_datetime'])->format('Y-m-d\TH:i:s.000'), // local, no TZ
                    'SalesCustomerType' => $d['sales_customer_type'],
                    'PatientLicenseNumber' => $d['patient_license_number'] ?? null,
                    'Transactions' => array_map(function($t){
                        return [
                            'PackageLabel' => $t['package_label'],
                            'Quantity' => (float)$t['quantity'],
                            'UnitOfMeasure' => $t['unit_of_measure'],
                            'TotalAmount' => (float)$t['total_amount'],
                            'QrCodes' => $t['qr_codes'] ?? null,
                        ];
                    }, $d['transactions'])
                ];
            }

            $result = $this->metrcService->createSalesDeliveries($deliveries);

            Log::info('METRC sales deliveries created', [ 'deliveries_count' => count($deliveries), 'user_id' => $request->user()->id ]);

            return response()->json([
                'message' => 'Sales deliveries created successfully',
                'result' => $result
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create sales deliveries',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createDeliveriesFromSale(Request $request, Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return response()->json(['error' => 'Only completed sales can be pushed to METRC'], 400);
        }

        $customerType = strtolower($sale->customer_type) === 'medical' ? 'Patient' : 'Consumer';
        $patientLicense = $sale->customer?->medical_card_number ?: null;

        $transactions = [];
        foreach ($sale->saleItems as $item) {
            $product = $item->product;
            $label = $product?->metrc_tag ?: ($item->metrc_tag ?: $product?->sku);
            if (!$label) {
                return response()->json(['error' => 'Missing METRC package tag for one or more items', 'item_id' => $item->id], 422);
            }
            $weightSold = (float)($item->weight_sold ?? 0);
            $transactions[] = [
                'package_label' => $label,
                'quantity' => $weightSold > 0 ? $weightSold : (float)$item->quantity,
                'unit_of_measure' => $weightSold > 0 ? 'Grams' : 'Each',
                'total_amount' => (float)$item->total_price
            ];
        }

        $payload = [
            'deliveries' => [[
                'sales_datetime' => $sale->created_at->format('Y-m-d\TH:i:s.000'),
                'sales_customer_type' => $customerType,
                'patient_license_number' => $patientLicense,
                'transactions' => $transactions
            ]]
        ];

        $request->merge($payload);
        return $this->createSalesDeliveries($request);
    }

    public function getSalesReceipts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_date_start' => 'required|date',
            'sales_date_end' => 'required|date|after_or_equal:sales_date_start'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $receipts = $this->metrcService->getSalesReceipts(
                $request->sales_date_start,
                $request->sales_date_end
            );
            
            return response()->json([
                'receipts' => $receipts,
                'count' => count($receipts),
                'date_range' => [
                    'start' => $request->sales_date_start,
                    'end' => $request->sales_date_end
                ],
                'retrieved_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve sales receipts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get facility details
     */
    public function getFacilityDetails(Request $request)
    {
        try {
            $details = $this->metrcService->getFacilityDetails();
            
            return response()->json([
                'facility' => $details,
                'retrieved_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve facility details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item categories
     */
    public function getItemCategories(Request $request)
    {
        try {
            $categories = $this->metrcService->getItemCategories();
            
            return response()->json([
                'categories' => $categories,
                'count' => count($categories),
                'retrieved_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve item categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get package history
     */
    public function getPackageHistory(Request $request, string $packageTag)
    {
        try {
            $history = $this->metrcService->getPackageHistory($packageTag);
            
            return response()->json([
                'package_tag' => $packageTag,
                'history' => $history,
                'retrieved_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve package history',
                'message' => $e->getMessage(),
                'package_tag' => $packageTag
            ], 500);
        }
    }

    /**
     * Sync inventory with METRC using chronological lastModified windows and outgoing transfers
     */
    public function syncInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'window_days' => 'nullable|integer|min:1|max:31'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $start = $request->get('start') ? now()->parse($request->get('start')) : (cache('metrc_sync_cursor') ? now()->parse(cache('metrc_sync_cursor')) : now()->subDays(30));
        $end = $request->get('end') ? now()->parse($request->get('end')) : now();
        $windowDays = (int)($request->get('window_days', 7));

        $summary = [
            'windows_processed' => 0,
            'active_processed' => 0,
            'inactive_processed' => 0,
            'outgoing_transfers' => 0,
            'deliveries_processed' => 0,
            'transfer_packages_processed' => 0,
            'products_created' => 0,
            'products_updated' => 0,
            'products_deactivated' => 0,
        ];

        try {
            $cursor = $start->clone();
            while ($cursor->lt($end)) {
                $windowStart = $cursor->clone();
                $windowEnd = $cursor->clone()->addDays($windowDays);
                if ($windowEnd->gt($end)) { $windowEnd = $end->clone(); }

                // 1) Active packages -> upsert
                $active = (array) $this->metrcService->getAllPackages($windowStart->toIso8601String(), $windowEnd->toIso8601String());
                foreach ($active as $pkg) {
                    $qty = (int)($pkg['Quantity'] ?? $pkg['quantity'] ?? 0);
                    $label = $pkg['Label'] ?? $pkg['label'] ?? null;
                    if (!$label) { continue; }

                    $item = $pkg['Item'] ?? [];
                    $itemName = is_array($item) ? ($item['Name'] ?? $item['name'] ?? null) : null;
                    $category = is_array($item) ? ($item['Category'] ?? $item['category'] ?? null) : ($pkg['Category'] ?? $pkg['category'] ?? null);
                    $uom = $pkg['UnitOfMeasure'] ?? $pkg['unitOfMeasure'] ?? $pkg['unit_of_measure'] ?? '';
                    $packagedDate = $pkg['PackagedDate'] ?? $pkg['packagedDate'] ?? null;
                    $expDate = $pkg['ExpirationDate'] ?? $pkg['expirationDate'] ?? null;
                    $vendor = $pkg['SourceFacilityLicenseNumber'] ?? $pkg['SourceFacility'] ?? null;

                    $data = [
                        'name' => $itemName ?: ($pkg['ProductName'] ?? $pkg['productName'] ?? ('METRC Package ' . $label)),
                        'category' => $category ?: 'Unknown',
                        'price' => 0,
                        'cost' => 0,
                        'sku' => $label,
                        'weight' => $uom ?: 'Units',
                        'unit' => $uom ?: 'Each',
                        'room' => 'Inventory',
                        'supplier' => $vendor ?: 'METRC',
                        'vendor' => $vendor ?: 'METRC',
                        'packaged_date' => $packagedDate ? date('Y-m-d', strtotime($packagedDate)) : null,
                        'expiration_date' => $expDate ? date('Y-m-d', strtotime($expDate)) : null,
                        'metrc_tag' => $label,
                        'quantity' => $qty,
                    ];

                    // Pull lab results for this package and merge
                    $pkgId = $pkg['Id'] ?? $pkg['PackageId'] ?? null;
                    if ($pkgId) {
                        $labResp = $this->metrcService->getLabTestResults($pkgId, null, 20);
                        if ($labResp) {
                            $parsed = $this->metrcService->parseLabResults($labResp);
                            $data = array_merge($data, array_filter($parsed, function($v){ return $v !== null; }));
                        }
                    }

                    $existing = Product::where('metrc_tag', $label)->first();
                    if ($existing) { $existing->fill($data)->save(); $summary['products_updated']++; }
                    else { Product::create($data); $summary['products_created']++; }
                    $summary['active_processed']++;
                }

                // 2) Inactive packages -> set quantity to 0
                $inactive = (array) $this->metrcService->getInactivePackages($windowStart->toIso8601String(), $windowEnd->toIso8601String());
                foreach ($inactive as $pkg) {
                    $label = $pkg['Label'] ?? $pkg['label'] ?? null;
                    if (!$label) { continue; }
                    $p = Product::where('metrc_tag', $label)->first();
                    if ($p && $p->quantity > 0) { $p->update(['quantity' => 0]); $summary['products_deactivated']++; }
                    $summary['inactive_processed']++;
                }

                // 3) Outgoing transfers -> deliveries -> packages
                $outgoing = (array) $this->metrcService->getOutgoingTransfers($windowStart->toIso8601String(), $windowEnd->toIso8601String());
                foreach ($outgoing as $transfer) {
                    $summary['outgoing_transfers']++;
                    $deliveries = (array) $this->metrcService->getTransferDeliveries($transfer['Id'] ?? $transfer['id']);
                    foreach ($deliveries as $delivery) {
                        $summary['deliveries_processed']++;
                        $packages = (array) $this->metrcService->getDeliveryPackages($delivery['Id'] ?? $delivery['id']);
                        foreach ($packages as $pkg) {
                            $summary['transfer_packages_processed']++;
                            $label = $pkg['PackageLabel'] ?? $pkg['Label'] ?? $pkg['packageLabel'] ?? null;
                            if (!$label) { continue; }
                            $p = Product::where('metrc_tag', $label)->first();
                            if ($p && $p->quantity > 0) { $p->update(['quantity' => 0]); $summary['products_deactivated']++; }
                        }
                    }
                }

                $summary['windows_processed']++;
                $cursor = $windowEnd->clone();
                cache(['metrc_sync_cursor' => $cursor->toIso8601String()], now()->addDays(7));
            }

            return response()->json([
                'success' => true,
                'message' => 'METRC inventory sync completed',
                'summary' => $summary,
                'synced_range' => [ 'start' => $start->toIso8601String(), 'end' => $end->toIso8601String() ],
                'next_cursor' => cache('metrc_sync_cursor')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
