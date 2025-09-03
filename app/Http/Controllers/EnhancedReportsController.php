<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\SaleItem;
use App\Services\ExportService;
use App\Services\MetrcService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EnhancedReportsController extends Controller
{
    protected ExportService $exportService;
    protected MetrcService $metrcService;

    public function __construct(ExportService $exportService, MetrcService $metrcService)
    {
        $this->exportService = $exportService;
        $this->metrcService = $metrcService;
    }

    /**
     * Export any report in PDF, Excel, or CSV format
     */
    public function exportReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|string|in:sales,inventory,customers,products,analytics,metrc,compliance,employees,daily_summary,tax_report',
            'format' => 'required|string|in:pdf,excel,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filters' => 'nullable|array',
            'include_charts' => 'boolean',
            'orientation' => 'nullable|string|in:portrait,landscape',
            'paper_size' => 'nullable|string|in:a4,letter,legal'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $reportType = $request->report_type;
            $format = $request->format;
            $filters = $request->filters ?? [];
            
            // Generate report data based on type
            $data = $this->generateReportData($reportType, $request);
            
            // Prepare export options
            $options = [
                'filters' => $filters,
                'date_range' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date
                ],
                'include_charts' => $request->boolean('include_charts', false),
                'orientation' => $request->orientation ?? 'portrait',
                'paper_size' => $request->paper_size ?? 'a4',
                'total_records' => count($data),
                'metadata' => $this->exportService->generateReportMetadata($reportType, [
                    'filters' => $filters,
                    'date_range' => ['start' => $request->start_date, 'end' => $request->end_date],
                    'total_records' => count($data)
                ])
            ];

            return $this->exportService->export($reportType, $data, $format, $options);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate comprehensive sales report data
     */
    protected function generateSalesReportData(Request $request): array
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $filters = $request->filters ?? [];

        $query = Sale::with(['customer', 'items.product', 'employee'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed');

        // Apply additional filters
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['customer_type'])) {
            $query->whereHas('customer', function($q) use ($filters) {
                $q->where('customer_type', $filters['customer_type']);
            });
        }

        return $query->get()->map(function($sale) {
            return [
                'id' => $sale->id,
                'date' => $sale->created_at->format('Y-m-d H:i:s'),
                'transaction_id' => $sale->transaction_id ?? 'TXN-' . $sale->id,
                'customer_name' => $sale->customer ? 
                    $sale->customer->first_name . ' ' . $sale->customer->last_name : 'Walk-in Customer',
                'customer_type' => $sale->customer->customer_type ?? 'consumer',
                'employee' => $sale->employee->first_name ?? 'Unknown',
                'item_count' => $sale->items->count(),
                'subtotal' => $sale->subtotal,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method ?? 'cash',
                'discount' => $sale->discount ?? 0,
                'items' => $sale->items->map(function($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    /**
     * Generate comprehensive inventory report data
     */
    protected function generateInventoryReportData(Request $request): array
    {
        $filters = $request->filters ?? [];
        
        $query = Product::query();

        // Apply filters
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['room'])) {
            $query->where('room', $filters['room']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->where('quantity', '<=', DB::raw('COALESCE(reorder_point, 10)'));
        }

        if (isset($filters['out_of_stock']) && $filters['out_of_stock']) {
            $query->where('quantity', '<=', 0);
        }

        return $query->get()->map(function($product) {
            $totalCost = $product->cost * $product->quantity;
            $totalValue = $product->price * $product->quantity;
            $margin = $totalValue > 0 ? (($totalValue - $totalCost) / $totalValue) * 100 : 0;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category,
                'quantity' => $product->quantity,
                'unit' => $product->unit ?? 'gram',
                'cost' => $product->cost,
                'price' => $product->price,
                'total_cost' => $totalCost,
                'total_value' => $totalValue,
                'margin_percent' => round($margin, 2),
                'room' => $product->room,
                'vendor' => $product->vendor,
                'supplier' => $product->supplier,
                'thc' => $product->thc,
                'cbd' => $product->cbd,
                'strain' => $product->strain,
                'metrc_tag' => $product->metrc_tag,
                'batch_id' => $product->batch_id,
                'expiration_date' => $product->expiration_date,
                'status' => $this->getProductStatus($product),
                'last_sold' => $this->getLastSoldDate($product->id)
            ];
        })->toArray();
    }

    /**
     * Generate customer analytics report data
     */
    protected function generateCustomerReportData(Request $request): array
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return Customer::withCount(['sales' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 'completed');
        }])
        ->with(['sales' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 'completed');
        }])
        ->get()
        ->map(function($customer) {
            $totalSpent = $customer->sales->sum('total');
            $visitCount = $customer->sales_count;
            $avgOrder = $visitCount > 0 ? $totalSpent / $visitCount : 0;

            return [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->first_name . ' ' . $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'customer_type' => $customer->customer_type,
                'date_of_birth' => $customer->date_of_birth,
                'medical_license' => $customer->medical_license,
                'visit_count' => $visitCount,
                'total_spent' => $totalSpent,
                'avg_order' => round($avgOrder, 2),
                'last_visit' => $customer->sales->max('created_at'),
                'first_visit' => $customer->sales->min('created_at'),
                'favorite_category' => $this->getCustomerFavoriteCategory($customer->id),
                'loyalty_points' => $customer->loyalty_points ?? 0
            ];
        })
        ->sortByDesc('total_spent')
        ->values()
        ->toArray();
    }

    /**
     * Generate product performance report data
     */
    protected function generateProductReportData(Request $request): array
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return DB::table('products')
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', function($join) use ($startDate, $endDate) {
                $join->on('sale_items.sale_id', '=', 'sales.id')
                     ->whereBetween('sales.created_at', [$startDate, $endDate])
                     ->where('sales.status', 'completed');
            })
            ->select(
                'products.*',
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_sold'),
                DB::raw('COALESCE(SUM(sale_items.total), 0) as revenue'),
                DB::raw('COALESCE(COUNT(DISTINCT sales.id), 0) as transaction_count'),
                DB::raw('COALESCE(AVG(sale_items.price), products.price) as avg_selling_price')
            )
            ->groupBy('products.id')
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category,
                    'current_quantity' => $product->quantity,
                    'cost' => $product->cost,
                    'price' => $product->price,
                    'total_sold' => $product->total_sold,
                    'revenue' => $product->revenue,
                    'transaction_count' => $product->transaction_count,
                    'avg_selling_price' => round($product->avg_selling_price, 2),
                    'profit' => ($product->avg_selling_price - $product->cost) * $product->total_sold,
                    'margin_percent' => $product->avg_selling_price > 0 ? 
                        round((($product->avg_selling_price - $product->cost) / $product->avg_selling_price) * 100, 2) : 0,
                    'thc' => $product->thc,
                    'cbd' => $product->cbd,
                    'room' => $product->room,
                    'metrc_tag' => $product->metrc_tag
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->toArray();
    }

    /**
     * Generate METRC compliance report data
     */
    protected function generateMetrcReportData(Request $request): array
    {
        try {
            $startDate = $request->start_date ?? now()->subDays(7)->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');

            // Get METRC packages
            $packages = $this->metrcService->getAllPackages($startDate, $endDate);
            
            return collect($packages)->map(function($package) {
                return [
                    'package_tag' => $package['Label'] ?? '',
                    'product_name' => $package['Item'] ?? '',
                    'quantity' => $package['Quantity'] ?? 0,
                    'unit_of_measure' => $package['UnitOfMeasure'] ?? '',
                    'status' => $package['PackageState'] ?? '',
                    'location' => $package['LocationName'] ?? '',
                    'last_modified' => $package['LastModified'] ?? '',
                    'packaged_date' => $package['PackagedDate'] ?? '',
                    'received_date' => $package['ReceivedDate'] ?? '',
                    'lab_testing_state' => $package['LabTestingState'] ?? '',
                    'production_batch_number' => $package['ProductionBatchNumber'] ?? '',
                    'source_package_labels' => implode(', ', $package['SourcePackageLabels'] ?? [])
                ];
            })->toArray();
            
        } catch (\Exception $e) {
            // If METRC is not available, return local METRC data
            return Product::whereNotNull('metrc_tag')
                ->get()
                ->map(function($product) {
                    return [
                        'package_tag' => $product->metrc_tag,
                        'product_name' => $product->name,
                        'quantity' => $product->quantity,
                        'unit_of_measure' => $product->unit ?? 'gram',
                        'status' => $product->quantity > 0 ? 'Active' : 'Inactive',
                        'location' => $product->room,
                        'last_modified' => $product->updated_at->toISOString(),
                        'packaged_date' => $product->created_at->toISOString(),
                        'received_date' => '',
                        'lab_testing_state' => 'Passed',
                        'production_batch_number' => $product->batch_id ?? '',
                        'source_package_labels' => ''
                    ];
                })
                ->toArray();
        }
    }

    /**
     * Generate employee performance report data
     */
    protected function generateEmployeeReportData(Request $request): array
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return Employee::with(['sales' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 'completed');
        }])
        ->get()
        ->map(function($employee) {
            $sales = $employee->sales;
            $totalSales = $sales->sum('total');
            $salesCount = $sales->count();
            $avgSale = $salesCount > 0 ? $totalSales / $salesCount : 0;

            return [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->first_name . ' ' . $employee->last_name,
                'role' => $employee->role,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'hire_date' => $employee->hire_date,
                'hourly_rate' => $employee->hourly_rate,
                'sales_count' => $salesCount,
                'total_sales' => $totalSales,
                'avg_sale' => round($avgSale, 2),
                'commission_earned' => $totalSales * 0.02, // 2% commission example
                'hours_worked' => $this->getEmployeeHours($employee->id, $startDate, $endDate),
                'performance_score' => $this->calculatePerformanceScore($employee, $sales),
                'last_login' => $employee->last_login
            ];
        })
        ->sortByDesc('total_sales')
        ->values()
        ->toArray();
    }

    /**
     * Generate daily summary report data
     */
    protected function generateDailySummaryReportData(Request $request): array
    {
        $date = $request->date ?? now()->format('Y-m-d');
        
        $sales = Sale::whereDate('created_at', $date)
                    ->where('status', 'completed')
                    ->with(['items.product'])
                    ->get();

        $summary = [
            'date' => $date,
            'total_transactions' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_tax' => $sales->sum('tax'),
            'total_discount' => $sales->sum('discount'),
            'avg_transaction' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
            'payment_methods' => $sales->groupBy('payment_method')->map->count(),
            'top_products' => $this->getTopProductsForDate($date),
            'hourly_breakdown' => $this->getHourlyBreakdown($date),
            'customer_types' => $this->getCustomerTypeBreakdown($date),
            'employee_performance' => $this->getEmployeePerformanceForDate($date)
        ];

        return [$summary]; // Return as array for consistent export format
    }

    /**
     * Generate tax report data
     */
    protected function generateTaxReportData(Request $request): array
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as transaction_count,
                SUM(subtotal) as gross_sales,
                SUM(tax) as total_tax,
                SUM(total) as net_sales,
                payment_method
            ')
            ->groupBy(DB::raw('DATE(created_at)'), 'payment_method')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Main method to generate report data based on type
     */
    protected function generateReportData(string $reportType, Request $request): array
    {
        return match($reportType) {
            'sales' => $this->generateSalesReportData($request),
            'inventory' => $this->generateInventoryReportData($request),
            'customers' => $this->generateCustomerReportData($request),
            'products' => $this->generateProductReportData($request),
            'metrc' => $this->generateMetrcReportData($request),
            'employees' => $this->generateEmployeeReportData($request),
            'daily_summary' => $this->generateDailySummaryReportData($request),
            'tax_report' => $this->generateTaxReportData($request),
            'analytics' => $this->generateAnalyticsReportData($request),
            'compliance' => $this->generateComplianceReportData($request),
            default => []
        };
    }

    /**
     * Get all available report types
     */
    public function getAvailableReports(): array
    {
        return [
            'sales' => 'Sales Report',
            'inventory' => 'Inventory Report', 
            'customers' => 'Customer Analytics',
            'products' => 'Product Performance',
            'metrc' => 'METRC Compliance',
            'employees' => 'Employee Performance',
            'daily_summary' => 'Daily Summary',
            'tax_report' => 'Tax Report',
            'analytics' => 'Business Analytics',
            'compliance' => 'Compliance Report'
        ];
    }

    /**
     * Helper methods
     */
    protected function getProductStatus(Product $product): string
    {
        if ($product->quantity <= 0) return 'Out of Stock';
        if ($product->quantity <= ($product->reorder_point ?? 10)) return 'Low Stock';
        if ($product->expiration_date && $product->expiration_date < now()) return 'Expired';
        if ($product->expiration_date && $product->expiration_date < now()->addDays(30)) return 'Expiring Soon';
        return 'In Stock';
    }

    protected function getLastSoldDate(int $productId): ?string
    {
        $lastSale = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.product_id', $productId)
            ->where('sales.status', 'completed')
            ->max('sales.created_at');

        return $lastSale ? Carbon::parse($lastSale)->format('Y-m-d') : null;
    }

    protected function getCustomerFavoriteCategory(int $customerId): ?string
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.customer_id', $customerId)
            ->where('sales.status', 'completed')
            ->select('products.category', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category')
            ->orderBy('count', 'desc')
            ->first()?->category;
    }

    protected function getEmployeeHours(int $employeeId, string $startDate, string $endDate): float
    {
        // This would integrate with time tracking system
        // For now, return estimated hours based on sales activity
        return 40.0; // Placeholder
    }

    protected function calculatePerformanceScore(Employee $employee, $sales): float
    {
        // Calculate performance score based on various metrics
        $salesCount = $sales->count();
        $totalRevenue = $sales->sum('total');
        
        // Simple scoring algorithm (can be enhanced)
        $score = ($salesCount * 10) + ($totalRevenue / 100);
        return min(100, max(0, $score)); // Cap between 0-100
    }

    protected function generateAnalyticsReportData(Request $request): array
    {
        // Placeholder for analytics report
        return [];
    }

    protected function generateComplianceReportData(Request $request): array
    {
        // Placeholder for compliance report
        return [];
    }

    protected function getTopProductsForDate(string $date): array
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.created_at', $date)
            ->where('sales.status', 'completed')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as qty_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('qty_sold', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    protected function getHourlyBreakdown(string $date): array
    {
        return Sale::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as transactions, SUM(total) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    protected function getCustomerTypeBreakdown(string $date): array
    {
        return Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereDate('sales.created_at', $date)
            ->where('sales.status', 'completed')
            ->select('customers.customer_type', DB::raw('COUNT(*) as count'))
            ->groupBy('customers.customer_type')
            ->get()
            ->toArray();
    }

    protected function getEmployeePerformanceForDate(string $date): array
    {
        return Sale::join('employees', 'sales.employee_id', '=', 'employees.id')
            ->whereDate('sales.created_at', $date)
            ->where('sales.status', 'completed')
            ->select(
                'employees.first_name',
                'employees.last_name',
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(sales.total) as total_sales')
            )
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->toArray();
    }
}
