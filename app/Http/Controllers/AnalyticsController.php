<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $timeframe = $request->get('timeframe', 'today');
        $selectedTab = $request->get('tab', 'overview');
        
        // Get date range based on timeframe
        $dateRange = $this->getDateRange($timeframe, $request);
        
        // Get analytics data
        $salesData = $this->getSalesData($dateRange);
        $productData = $this->getProductData($dateRange);
        $customerData = $this->getCustomerData($dateRange);
        $inventoryData = $this->getInventoryData();
        $employeeData = $this->getEmployeeData($dateRange);
        $aspdData = $this->getASPDData($dateRange);
        $endOfDayData = $this->getEndOfDayData();
        
        return view('analytics.index', compact(
            'timeframe',
            'selectedTab',
            'salesData',
            'productData',
            'customerData',
            'inventoryData',
            'employeeData',
            'aspdData',
            'endOfDayData'
        ));
    }
    
    public function exportOverview(Request $request)
    {
        $timeframe = $request->get('timeframe', 'today');
        $dateRange = $this->getDateRange($timeframe, $request);
        $data = $this->getSalesData($dateRange);
        
        // Generate PDF or CSV export
        return response()->json(['message' => 'Export functionality would be implemented here']);
    }
    
    public function printReport(Request $request)
    {
        $type = $request->get('type', 'overview');
        $timeframe = $request->get('timeframe', 'today');
        
        // Return printable view
        return view('analytics.print', compact('type', 'timeframe'));
    }
    
    private function getDateRange($timeframe, $request)
    {
        switch ($timeframe) {
            case 'today':
                return [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
                ];
            case 'custom':
                return [
                    'start' => Carbon::parse($request->get('start_date', Carbon::today())),
                    'end' => Carbon::parse($request->get('end_date', Carbon::today()))
                ];
            default:
                return [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->endOfDay()
                ];
        }
    }
    
    private function getSalesData($dateRange)
    {
        $sales = Sale::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'completed')
                    ->get();
        
        $revenue = $sales->sum('total');
        $transactions = $sales->count();
        $customers = $sales->whereNotNull('customer_id')->count();
        $avgOrderValue = $transactions > 0 ? $revenue / $transactions : 0;
        
        // Calculate change from previous period
        $previousPeriod = $this->getPreviousPeriodData($dateRange);
        
        return [
            'revenue' => $revenue,
            'transactions' => $transactions,
            'customers' => $customers,
            'avgOrderValue' => $avgOrderValue,
            'change' => [
                'revenue' => $this->calculatePercentageChange($revenue, $previousPeriod['revenue']),
                'transactions' => $this->calculatePercentageChange($transactions, $previousPeriod['transactions']),
                'customers' => $this->calculatePercentageChange($customers, $previousPeriod['customers']),
                'avgOrderValue' => $this->calculatePercentageChange($avgOrderValue, $previousPeriod['avgOrderValue'])
            ]
        ];
    }
    
    private function getProductData($dateRange)
    {
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('sales.status', 'completed')
            ->select(
                'products.name',
                'products.category',
                DB::raw('SUM(sale_items.quantity) as sales'),
                DB::raw('SUM(sale_items.total) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get();
        
        $categoryData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('sales.status', 'completed')
            ->select(
                'products.category',
                DB::raw('SUM(sale_items.quantity) as sales'),
                DB::raw('SUM(sale_items.total) as revenue')
            )
            ->groupBy('products.category')
            ->get();
        
        // Calculate percentages
        $totalRevenue = $categoryData->sum('revenue');
        $categoryData->transform(function ($item) use ($totalRevenue) {
            $item->percentage = $totalRevenue > 0 ? ($item->revenue / $totalRevenue) * 100 : 0;
            return $item;
        });
        
        return [
            'topProducts' => $topProducts,
            'categoryData' => $categoryData
        ];
    }
    
    private function getCustomerData($dateRange)
    {
        $newCustomers = Customer::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();
        $returningCustomers = Sale::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count();
        
        $loyaltyMembers = Customer::whereNotNull('loyalty_member_id')->count();
        $medicalPatients = Customer::where('customer_type', 'medical')->count();
        $caregivers = Customer::whereHas('medicalCard', function($query) {
            $query->where('is_patient', false);
        })->count();
        
        return [
            'newCustomers' => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'loyaltyMembers' => $loyaltyMembers,
            'medicalPatients' => $medicalPatients,
            'caregivers' => $caregivers
        ];
    }
    
    private function getInventoryData()
    {
        $inventoryAlerts = Product::where(function($query) {
            $query->whereColumn('quantity', '<=', 'reorder_point')
                  ->orWhere('quantity', '<=', 0);
        })->get()->map(function($product) {
            return [
                'product' => $product->name,
                'stock' => $product->quantity,
                'reorderPoint' => $product->reorder_point ?? 10,
                'status' => $product->quantity <= 0 ? 'critical' : ($product->quantity <= ($product->reorder_point ?? 10) ? 'low' : 'good')
            ];
        });
        
        return [
            'alerts' => $inventoryAlerts
        ];
    }
    
    private function getEmployeeData($dateRange)
    {
        $employeeMetrics = Employee::with(['sales' => function($query) use ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                  ->where('status', 'completed');
        }])->get()->map(function($employee) {
            $sales = $employee->sales;
            $totalSales = $sales->sum('total');
            $transactions = $sales->count();
            $avgOrder = $transactions > 0 ? $totalSales / $transactions : 0;
            
            return [
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'sales' => $totalSales,
                'transactions' => $transactions,
                'avgOrder' => $avgOrder
            ];
        });
        
        return $employeeMetrics;
    }
    
    private function getASPDData($dateRange)
    {
        $daysInRange = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        
        $aspdData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('sales.status', 'completed')
            ->select(
                'products.id',
                'products.name',
                'products.category',
                DB::raw('SUM(sale_items.quantity) as totalSold'),
                DB::raw('SUM(sale_items.total) as totalRevenue')
            )
            ->groupBy('products.id', 'products.name', 'products.category')
            ->get()
            ->map(function($item) use ($daysInRange) {
                $aspd = $daysInRange > 0 ? $item->totalSold / $daysInRange : 0;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'totalSold' => $item->totalSold,
                    'unitsSold' => $item->totalSold,
                    'totalRevenue' => $item->totalRevenue,
                    'daysInRange' => $daysInRange,
                    'aspd' => $aspd
                ];
            })
            ->sortByDesc('aspd')
            ->values();
        
        return $aspdData;
    }
    
    private function getEndOfDayData()
    {
        $today = Carbon::today();
        $todaysSales = Sale::whereDate('created_at', $today)
                          ->where('status', 'completed')
                          ->get();
        
        $totalSales = $todaysSales->sum('total');
        $totalTax = $todaysSales->sum('tax');
        $customerCount = $todaysSales->whereNotNull('customer_id')->count();
        
        // Payment method breakdown
        $cashSales = $todaysSales->where('payment_method', 'cash')->sum('total');
        $debitSales = $todaysSales->where('payment_method', 'debit')->sum('total');
        $creditSales = $todaysSales->where('payment_method', 'credit')->sum('total');
        
        // Monthly data
        $monthlySales = Sale::whereMonth('created_at', $today->month)
                           ->whereYear('created_at', $today->year)
                           ->where('status', 'completed')
                           ->sum('total');
        
        return [
            'totalSales' => $totalSales,
            'totalTax' => $totalTax,
            'customerCount' => $customerCount,
            'cashSales' => $cashSales,
            'debitSales' => $debitSales,
            'creditSales' => $creditSales,
            'monthlySalesTotal' => $monthlySales,
            'dayOfMonth' => $today->day,
            'daysInMonth' => $today->daysInMonth,
            'storeName' => config('app.store_name', 'Cannabis Dispensary'),
            'generatedBy' => auth()->user()->name ?? 'System'
        ];
    }
    
    private function getPreviousPeriodData($dateRange)
    {
        $periodLength = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        $previousStart = $dateRange['start']->copy()->subDays($periodLength);
        $previousEnd = $dateRange['end']->copy()->subDays($periodLength);
        
        $sales = Sale::whereBetween('created_at', [$previousStart, $previousEnd])
                    ->where('status', 'completed')
                    ->get();
        
        $revenue = $sales->sum('total');
        $transactions = $sales->count();
        $customers = $sales->whereNotNull('customer_id')->count();
        $avgOrderValue = $transactions > 0 ? $revenue / $transactions : 0;
        
        return [
            'revenue' => $revenue,
            'transactions' => $transactions,
            'customers' => $customers,
            'avgOrderValue' => $avgOrderValue
        ];
    }
    
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return (($current - $previous) / $previous) * 100;
    }
}
