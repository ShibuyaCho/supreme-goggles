<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AnalyticsController extends Controller
{
    public function overview(Request $request)
    {
        $timeframe = $request->get('timeframe', 'today');
        $dateRange = $this->getDateRange($timeframe, $request);
        $salesData = $this->getSalesData($dateRange);
        $productData = $this->getProductData($dateRange);
        $employeeData = $this->getEmployeeData($dateRange);
        $open = $this->getOpenCartsData();
        $company = $this->getCompanyViewData($dateRange);
        return response()->json([
            'range' => [ 'start' => $dateRange['start']->toISOString(), 'end' => $dateRange['end']->toISOString() ],
            'sales' => $salesData,
            'categories' => $productData['categoryData'],
            'employees' => $employeeData,
            'openCarts' => $open,
            'company' => $company,
        ]);
    }

    public function endOfDay(Request $request)
    {
        return response()->json($this->getEndOfDayData());
    }

    public function companyView(Request $request)
    {
        $timeframe = $request->get('timeframe', 'today');
        $dateRange = $this->getDateRange($timeframe, $request);
        return response()->json($this->getCompanyViewData($dateRange));
    }

    private function getOpenCartsData(): array
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('saved_sales')) return ['total' => 0, 'avgMinutes' => 0, 'maxMinutes' => 0];
            $rows = DB::table('saved_sales')->select('id','created_at')->get();
            $now = Carbon::now();
            $durations = $rows->map(fn($r) => $now->diffInMinutes(Carbon::parse($r->created_at)));
            $total = $rows->count();
            $avg = $total > 0 ? round($durations->avg(), 1) : 0;
            $max = $total > 0 ? (int)$durations->max() : 0;
            return [ 'total' => $total, 'avgMinutes' => $avg, 'maxMinutes' => $max ];
        } catch (\Throwable $e) { return ['total' => 0, 'avgMinutes' => 0, 'maxMinutes' => 0]; }
    }

    private function getCompanyViewData(array $dateRange): array
    {
        if ($this->supabaseEnabled()) {
            try {
                $rows = $this->supaSalesInRange($dateRange);
                $by = [];
                foreach ($rows as $r) {
                    $sid = $r['store_id'] ?? 'default';
                    $amt = isset($r['total_amount']) ? (float)$r['total_amount'] : (float)($r['total'] ?? 0);
                    if (!isset($by[$sid])) $by[$sid] = ['tx'=>0,'rev'=>0];
                    $by[$sid]['tx'] += 1; $by[$sid]['rev'] += $amt;
                }
                $stores = [];
                foreach ($by as $sid=>$v) { $tx=$v['tx']; $rev=$v['rev']; $stores[] = ['store_id'=>$sid, 'transactions'=>$tx, 'revenue'=>$rev, 'avg'=> $tx>0?($rev/$tx):0]; }
                $overallTx = array_sum(array_column($stores, 'transactions'));
                $overallRev = array_sum(array_column($stores, 'revenue'));
                return [ 'stores'=>$stores, 'overall'=> ['transactions'=>$overallTx,'revenue'=>$overallRev,'avg'=>$overallTx>0?($overallRev/$overallTx):0], 'hasStoreDimension'=> true ];
            } catch (\Throwable $e) { /* fall back */ }
        }

        $hasStore = \Illuminate\Support\Facades\Schema::hasColumn('sales','store_id');
        $q = Sale::query()->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->where('status','completed');
        if ($hasStore) {
            $rows = $q->select('store_id', DB::raw('COUNT(*) as transactions'), DB::raw('SUM(COALESCE(total_amount, total)) as revenue'))
                ->groupBy('store_id')->get();
            $stores = $rows->map(function($r){
                $tx = (int)$r->transactions; $rev = (float)$r->revenue; $avg = $tx>0 ? ($rev/$tx) : 0;
                return [ 'store_id' => $r->store_id, 'transactions' => $tx, 'revenue' => $rev, 'avg' => $avg ];
            })->values()->all();
        } else {
            $rev = (float)$q->sum(DB::raw('COALESCE(total_amount, total)'));
            $tx = (int)$q->count();
            $avg = $tx>0 ? ($rev/$tx) : 0;
            $stores = [[ 'store_id' => 'default', 'transactions' => $tx, 'revenue' => $rev, 'avg' => $avg ]];
        }
        $overall = [ 'transactions' => array_sum(array_map(fn($s)=>$s['transactions'],$stores)), 'revenue' => array_sum(array_map(fn($s)=>$s['revenue'],$stores)) ];
        $overall['avg'] = $overall['transactions']>0 ? ($overall['revenue']/$overall['transactions']) : 0;
        return [ 'stores' => $stores, 'overall' => $overall, 'hasStoreDimension' => $hasStore ];
    }

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
    
    private function supabaseEnabled(): bool { return (bool)(env('SUPABASE_URL') && env('SUPABASE_ANON_KEY')); }

    private function supaHeaders(): array { $k = env('SUPABASE_ANON_KEY'); return ['apikey'=>$k,'Authorization'=>'Bearer '.$k,'Accept'=>'application/json']; }

    private function supaSalesInRange(array $dateRange): array
    {
        $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/sales';
        $start = $dateRange['start']->toISOString();
        $end = $dateRange['end']->toISOString();
        $resp = Http::withHeaders($this->supaHeaders())->get($url, [
            'select' => 'id,customer,customer_id,employee_id,total,total_amount,created_at,store_id,cart,payment_method,tax,discount_amount',
            'status' => 'eq.completed',
            'and' => '(created_at.gte.' . $start . ',created_at.lte.' . $end . ')',
        ]);
        if (!$resp->ok()) return [];
        $rows = $resp->json();
        return is_array($rows) ? $rows : [];
    }

    private function getSalesData($dateRange)
    {
        if ($this->supabaseEnabled()) {
            try {
                $rows = $this->supaSalesInRange($dateRange);
                if (is_array($rows) && count($rows) > 0) {
                    $revenue = 0; $transactions = 0; $customers = 0;
                    foreach ($rows as $r) {
                        $revenue += isset($r['total_amount']) ? (float)$r['total_amount'] : (float)($r['total'] ?? 0);
                        $transactions += 1;
                        if (!empty($r['customer_id']) || (!empty($r['customer']) && is_array($r['customer']))) $customers += 1;
                    }
                    $avgOrderValue = $transactions > 0 ? $revenue / $transactions : 0;
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
            } catch (\Throwable $e) { /* fall back to DB */ }
        }

        // Fallback to local DB when Supabase is disabled or has no rows
        $sales = Sale::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'completed')
                    ->get();
        $revenue = $sales->sum(function($s){ return isset($s->total_amount) ? (float)$s->total_amount : (float)($s->total ?? 0); });
        $transactions = $sales->count();
        $customers = $sales->whereNotNull('customer_id')->count();
        $avgOrderValue = $transactions > 0 ? $revenue / $transactions : 0;
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
        if ($this->supabaseEnabled()) {
            try {
                $rows = $this->supaSalesInRange($dateRange);
                if (is_array($rows) && count($rows) > 0) {
                    $byId = [];
                    $byCat = [];
                    $prodIds = [];
                    foreach ($rows as $r) {
                        $cart = isset($r['cart']) && is_array($r['cart']) ? $r['cart'] : [];
                        foreach ($cart as $i) {
                            $pid = isset($i['id']) ? (int)$i['id'] : null;
                            $qty = isset($i['quantity']) ? (float)$i['quantity'] : 1;
                            $price = isset($i['price']) ? (float)$i['price'] : 0;
                            $revenue = $qty * $price;
                            if ($pid) $prodIds[$pid] = true;
                            $byId[$pid ?? 0] = ($byId[$pid ?? 0] ?? 0) + $revenue;
                        }
                    }
                    $catMap = [];
                    if (!empty($prodIds)) {
                        $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/products';
                        $resp = Http::withHeaders($this->supaHeaders())->get($url, [ 'select' => 'id,category,name', 'id' => 'in.(' . implode(',', array_map('intval', array_keys($prodIds))) . ')' ]);
                        if ($resp->ok()) {
                            foreach ((array)$resp->json() as $p) { $catMap[(int)$p['id']] = [ 'category' => $p['category'] ?? 'Uncategorized', 'name' => $p['name'] ?? '' ]; }
                        }
                    }
                    foreach ($byId as $pid => $rev) {
                        $cat = isset($catMap[$pid]) ? ($catMap[$pid]['category'] ?? 'Uncategorized') : 'Uncategorized';
                        $byCat[$cat] = ($byCat[$cat] ?? 0) + (float)$rev;
                    }
                    $total = array_sum($byCat);
                    $categoryData = collect(array_map(function($cat,$rev) use ($total){ return (object)['category'=>$cat,'revenue'=>$rev,'percentage'=> $total>0?($rev/$total*100):0]; }, array_keys($byCat), array_values($byCat)))->values();
                    $topProducts = collect($catMap)->map(function($v,$id) use ($byId){ return [ 'name'=>$v['name'] ?: ('Product #'.$id), 'category'=>$v['category'] ?? 'Uncategorized', 'revenue'=> (float)($byId[$id] ?? 0), 'sales'=> null ]; })->sortByDesc('revenue')->take(5)->map(fn($r) => (object)$r)->values();
                    return [ 'topProducts' => $topProducts, 'categoryData' => $categoryData ];
                }
            } catch (\Throwable $e) { /* fall back to DB */ }
        }

        // Fallback to local DB when Supabase is disabled or has no rows
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('sales.status', 'completed')
            ->select(
                DB::raw('COALESCE(products.name, sale_items.product_name) as name'),
                DB::raw('COALESCE(products.category, sale_items.product_category) as category'),
                DB::raw('SUM(sale_items.quantity) as sales'),
                DB::raw('SUM(sale_items.total_price) as revenue')
            )
            ->groupBy('name', 'category')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get();
        $categoryData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('sales.status', 'completed')
            ->select(
                DB::raw('COALESCE(products.category, sale_items.product_category) as category'),
                DB::raw('SUM(sale_items.quantity) as sales'),
                DB::raw('SUM(sale_items.total_price) as revenue')
            )
            ->groupBy('category')
            ->get();
        $totalRevenue = $categoryData->sum('revenue');
        $categoryData->transform(function ($item) use ($totalRevenue) {
            $item->percentage = $totalRevenue > 0 ? ($item->revenue / $totalRevenue) * 100 : 0;
            return $item;
        });
        return [ 'topProducts' => $topProducts, 'categoryData' => $categoryData ];
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
        if ($this->supabaseEnabled()) {
            try {
                $rows = $this->supaSalesInRange($dateRange);
                $byEmp = [];
                foreach ($rows as $r) {
                    $emp = (string)($r['employee_id'] ?? '');
                    $amt = isset($r['total_amount']) ? (float)$r['total_amount'] : (float)($r['total'] ?? 0);
                    if (!$emp) continue;
                    if (!isset($byEmp[$emp])) $byEmp[$emp] = ['sales'=>0,'tx'=>0];
                    $byEmp[$emp]['sales'] += $amt; $byEmp[$emp]['tx'] += 1;
                }
                $names = [];
                if (!empty($byEmp)) {
                    $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/employees';
                    // build in.("Emp01","Emp02") for text ids
                    $list = array_map(fn($v)=>'"'.str_replace('"','\"',$v).'"', array_keys($byEmp));
                    $resp = Http::withHeaders($this->supaHeaders())->get($url, [ 'select' => 'employee_id,first_name,last_name', 'employee_id' => 'in.(' . implode(',', $list) . ')' ]);
                    if ($resp->ok()) {
                        foreach ((array)$resp->json() as $e) { $names[(string)$e['employee_id']] = trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? '')); }
                    }
                }
                return collect($byEmp)->map(function($v,$id) use ($names){ $tx=$v['tx']; $sales=$v['sales']; return [ 'name' => ($names[$id] ?? $id), 'sales' => $sales, 'transactions' => $tx, 'avgOrder' => $tx>0?($sales/$tx):0 ]; })->values();
            } catch (\Throwable $e) { /* fall back */ }
        }

        $employeeMetrics = Employee::with(['sales' => function($query) use ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                  ->where('status', 'completed');
        }])->get()->map(function($employee) {
            $sales = $employee->sales;
            $totalSales = $sales->sum(function($s){ return isset($s->total_amount) ? (float)$s->total_amount : (float)($s->total ?? 0); });
            $transactions = $sales->count();
            $avgOrder = $transactions > 0 ? $totalSales / $transactions : 0;
            return [ 'name' => $employee->first_name . ' ' . $employee->last_name, 'sales' => $totalSales, 'transactions' => $transactions, 'avgOrder' => $avgOrder ];
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
                    'aspd' => $aspd,
                    'trend' => 'stagnant',
                ];
            })
            ->sortByDesc('aspd')
            ->values();
        
        return $aspdData;
    }
    
    public function getASPDAnalytics(Request $request)
    {
        $timeframe = $request->get('timeframe', 'week');
        $dateRange = $this->getDateRange($timeframe, $request);
        $items = $this->getASPDData($dateRange);
        $daysInRange = $dateRange['start']->diffInDays($dateRange['end']) + 1;

        $categories = collect($items)
            ->groupBy('category')
            ->map(function ($rows, $category) use ($daysInRange) {
                $totalSold = $rows->sum('totalSold');
                $totalRevenue = $rows->sum('totalRevenue');
                $aspd = $daysInRange > 0 ? ($totalSold / $daysInRange) : 0;
                return [
                    'category' => $category,
                    'totalSold' => $totalSold,
                    'totalRevenue' => $totalRevenue,
                    'daysInRange' => $daysInRange,
                    'aspd' => $aspd,
                    'trend' => 'stagnant',
                    'items' => $rows->values()->all(),
                ];
            })
            ->values()
            ->sortByDesc('aspd')
            ->values();

        return response()->json([
            'daysInRange' => $daysInRange,
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    public function getASPDAnalyticsOpen(Request $request)
    {
        return $this->getASPDAnalytics($request);
    }

    private function getEndOfDayData()
    {
        if ($this->supabaseEnabled()) {
            try {
                $today = Carbon::today();
                $range = ['start'=>$today->copy()->startOfDay(),'end'=>$today->copy()->endOfDay()];
                $rows = $this->supaSalesInRange($range);
                $totalSales = 0; $totalTax = 0; $customerCount = 0; $cashSales=0; $debitSales=0; $creditSales=0;
                foreach ($rows as $r) {
                    $amt = isset($r['total_amount']) ? (float)$r['total_amount'] : (float)($r['total'] ?? 0);
                    $tax = isset($r['tax_amount']) ? (float)$r['tax_amount'] : (float)($r['tax'] ?? 0);
                    $pm = strtolower((string)($r['payment_method'] ?? 'unknown'));
                    $totalSales += $amt; $totalTax += $tax;
                    if (!empty($r['customer_id']) || (!empty($r['customer']) && is_array($r['customer']))) $customerCount++;
                    if ($pm==='cash') $cashSales += $amt; else if ($pm==='debit') $debitSales += $amt; else if ($pm==='credit') $creditSales += $amt;
                }
                $monthlyRows = Http::withHeaders($this->supaHeaders())->get(rtrim(env('SUPABASE_URL'),'/').'/rest/v1/sales', [ 'select'=>'total,total_amount,created_at', 'status'=>'eq.completed', 'and' => '(created_at.gte.' . $today->copy()->startOfMonth()->toISOString() . ',created_at.lte.' . $today->copy()->endOfMonth()->toISOString() . ')' ]);
                $monthlySales = 0; if ($monthlyRows->ok()) { foreach ((array)$monthlyRows->json() as $r) { $monthlySales += isset($r['total_amount'])?(float)$r['total_amount']:(float)($r['total']??0); } }
                return [ 'totalSales'=>$totalSales, 'totalTax'=>$totalTax, 'customerCount'=>$customerCount, 'cashSales'=>$cashSales, 'debitSales'=>$debitSales, 'creditSales'=>$creditSales, 'monthlySalesTotal'=>$monthlySales, 'dayOfMonth'=> $today->day, 'daysInMonth'=>$today->daysInMonth, 'storeName'=> config('app.store_name','Cannabis Dispensary'), 'generatedBy'=> auth()->user()->name ?? 'System' ];
            } catch (\Throwable $e) { /* fall back */ }
        }

        $today = Carbon::today();
        $todaysSales = Sale::whereDate('created_at', $today)
                          ->where('status', 'completed')
                          ->get();
        $totalSales = $todaysSales->sum(function($s){ return isset($s->total_amount) ? (float)$s->total_amount : (float)($s->total ?? 0); });
        $totalTax = $todaysSales->sum(function($s){ return isset($s->tax_amount) ? (float)$s->tax_amount : (float)($s->tax ?? 0); });
        $customerCount = $todaysSales->whereNotNull('customer_id')->count();
        $cashSales = $todaysSales->where('payment_method', 'cash')->sum(function($s){ return isset($s->total_amount) ? (float)$s->total_amount : (float)($s->total ?? 0); });
        $debitSales = $todaysSales->where('payment_method', 'debit')->sum(function($s){ return isset($s->total_amount) ? (float)$s->total_amount : (float)($s->total ?? 0); });
        $creditSales = $todaysSales->where('payment_method', 'credit')->sum(function($s){ return isset($s->total_amount) ? (float)$s->total_amount : (float)($s->total ?? 0); });
        $monthlySales = Sale::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->where('status', 'completed')->sum(function($s){ return isset($s->total_amount) ? (float)$s->total_amount : (float)($s->total ?? 0); });
        return [ 'totalSales'=>$totalSales, 'totalTax'=>$totalTax, 'customerCount'=>$customerCount, 'cashSales'=>$cashSales, 'debitSales'=>$debitSales, 'creditSales'=>$creditSales, 'monthlySalesTotal'=>$monthlySales, 'dayOfMonth'=>$today->day, 'daysInMonth'=>$today->daysInMonth, 'storeName'=> config('app.store_name','Cannabis Dispensary'), 'generatedBy'=> auth()->user()->name ?? 'System' ];
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
