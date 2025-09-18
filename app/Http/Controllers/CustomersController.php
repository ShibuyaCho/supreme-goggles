<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\SupabaseService;
use Carbon\Carbon;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $filterType = $request->get('type', 'all');
        $filterActive = $request->get('active', 'all');
        $selectedTab = $request->get('tab', 'customers');
        
        // Load from local DB
        $eloquent = Customer::query();
        if ($searchQuery) {
            $eloquent->where(function($q) use ($searchQuery) {
                $q->where('first_name', 'like', "%{$searchQuery}%")
                  ->orWhere('last_name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%")
                  ->orWhere('phone', 'like', "%{$searchQuery}%")
                  ->orWhere('loyalty_member_id', 'like', "%{$searchQuery}%");
            });
        }
        if ($filterType !== 'all') {
            $eloquent->where('customer_type', $filterType);
        }
        if ($filterActive !== 'all') {
            $eloquent->where('is_active', $filterActive === 'active');
        }
        $local = $eloquent->orderBy('created_at', 'desc')->get();

        // Load from Supabase and merge
        $merged = collect();
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                $resp = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/customers', [ 'select' => '*' ]);
                if ($resp->ok()) {
                    $rows = $resp->json();
                    $supabase = collect(is_array($rows) ? $rows : [])
                        ->map(function($r) {
                            $attrs = [
                                'id' => $r['id'] ?? null,
                                'name' => $r['name'] ?? (($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                                'first_name' => $r['first_name'] ?? null,
                                'last_name' => $r['last_name'] ?? null,
                                'email' => $r['email'] ?? null,
                                'phone' => $r['phone'] ?? null,
                                'date_of_birth' => $r['date_of_birth'] ?? null,
                                'address' => $r['address'] ?? null,
                                'customer_type' => $r['customer_type'] ?? 'recreational',
                                'is_active' => array_key_exists('is_active',$r) ? (bool)$r['is_active'] : true,
                                'is_veteran' => (bool)($r['is_veteran'] ?? false),
                                'notes' => $r['notes'] ?? null,
                                'data_retention_consent' => (bool)($r['data_retention_consent'] ?? false),
                                'loyalty_member_id' => $r['loyalty_member_id'] ?? null,
                                'loyalty_join_date' => $r['loyalty_join_date'] ?? null,
                                'loyalty_points' => $r['loyalty_points'] ?? 0,
                                'points_earned' => $r['points_earned'] ?? 0,
                                'points_redeemed' => $r['points_redeemed'] ?? 0,
                                'loyalty_tier' => $r['loyalty_tier'] ?? ($r['tier'] ?? 'Bronze'),
                                'total_spent' => $r['total_spent'] ?? 0,
                                'total_visits' => $r['total_visits'] ?? 0,
                                'last_visit' => $r['last_visit'] ?? null,
                                'created_at' => $r['created_at'] ?? null,
                                'updated_at' => $r['updated_at'] ?? null,
                            ];
                            return new Customer($attrs);
                        });
                    $merged = $supabase;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Merge local and Supabase, dedupe by id/email/phone
        $merged = $merged->concat($local);
        $seen = [];
        $merged = $merged->filter(function($c) use (&$seen, $searchQuery, $filterType, $filterActive) {
            $key = ($c->id ?: '') . '|' . ($c->email ?: '') . '|' . ($c->phone ?: '');
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            // Apply filters consistently
            if ($filterType !== 'all' && ($c->customer_type !== $filterType)) return false;
            if ($filterActive !== 'all' && ((bool)$c->is_active !== ($filterActive === 'active'))) return false;
            if ($searchQuery) {
                $q = mb_strtolower($searchQuery);
                $hay = mb_strtolower(($c->name ?? '') . ' ' . ($c->email ?? '') . ' ' . ($c->phone ?? '') . ' ' . ($c->loyalty_member_id ?? ''));
                if (mb_strpos($hay, $q) === false) return false;
            }
            return true;
        })->values();

        // Paginate merged
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $items = $merged->forPage($page, $perPage);
        $customers = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $merged->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get analytics data for analytics tab
        $stats = $this->getCustomerStats();

        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => $customers->items(),
                'meta' => [
                    'current_page' => $customers->currentPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'last_page' => $customers->lastPage(),
                ],
            ]);
        }

        return view('customers.index', compact(
            'customers',
            'searchQuery',
            'filterType',
            'filterActive',
            'selectedTab',
            'stats'
        ));
    }
    
    public function create()
    {
        return view('customers.create');
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'customer_type' => 'required|in:recreational,medical',
            'address.street' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:255',
            'address.state' => 'nullable|string|max:2',
            'address.zip_code' => 'nullable|string|max:10',
            'is_veteran' => 'boolean',
            'notes' => 'nullable|string',
            'data_retention_consent' => 'required|boolean|accepted'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $customerData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'customer_type' => $request->customer_type,
            'address' => json_encode($request->address ?? []),
            'is_active' => true,
            'notes' => $request->notes,
            'data_retention_consent' => $request->data_retention_consent,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Auto-enroll in loyalty program if veteran
        if ($request->is_veteran) {
            $customerData['loyalty_member_id'] = 'LOY-' . str_pad(Customer::count() + 1, 6, '0', STR_PAD_LEFT);
            $customerData['loyalty_join_date'] = now();
            $customerData['loyalty_points'] = 0;
            $customerData['loyalty_tier'] = 'Bronze';
            $customerData['is_veteran'] = true;
        }
        
        $supa = app(SupabaseService::class);
        if ($supa->enabled()) {
            $payload = $customerData;
            $payload['created_at'] = now()->toIso8601String();
            $payload['updated_at'] = now()->toIso8601String();
            $resp = $supa->insert('customers', [ $payload ], ['prefer' => 'return=representation']);
            if ($resp['ok'] ?? false) {
                $rows = $resp['data'];
                $created = is_array($rows) && isset($rows[0]) ? $rows[0] : $rows;
                $customer = Customer::create($this->mapSupabaseCustomerToLocal($created));
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Customer created successfully', 'customer' => $customer]);
                }
                return redirect()->route('customers.index')->with('success', 'Customer created successfully');
            }
            if (($resp['error'] ?? null) === 'RLS_DENIED') {
                return response()->json(['error' => 'Supabase rejected the write due to Row Level Security. Please check policies for customers.'], 403);
            }
            Log::warning('Supabase create customer failed, falling back to local DB', ['status' => $resp['status'] ?? 0, 'error' => $resp['error'] ?? null]);
        }

        // Fallback: local create
        $customer = Customer::create($customerData);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Customer created locally (remote sync pending)', 'customer' => $customer]);
        }
        return redirect()->route('customers.index')->with('success', 'Customer created locally (remote sync pending)');
    }
    
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->loadPurchaseHistory();

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json([
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'customer_type' => $customer->customer_type,
                'loyalty_member_id' => $customer->loyalty_member_id,
                'loyalty_points' => $customer->loyalty_points,
            ]);
        }
        return view('customers.show', compact('customer'));
    }
    
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }
    
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'customer_type' => 'required|in:recreational,medical',
            'address.street' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:255',
            'address.state' => 'nullable|string|max:2',
            'address.zip_code' => 'nullable|string|max:10',
            'is_veteran' => 'boolean',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'customer_type' => $request->customer_type,
            'address' => json_encode($request->address ?? []),
            'notes' => $request->notes,
            'is_veteran' => $request->is_veteran ?? false
        ];

        $supa = app(SupabaseService::class);
        if ($supa->enabled()) {
            $payload = $updateData; $payload['updated_at'] = now()->toIso8601String();
            $resp = $supa->update('customers', ['id' => $customer->id], $payload, ['prefer' => 'return=representation']);
            if ($resp['ok'] ?? false) {
                $rows = $resp['data'];
                $updated = is_array($rows) && isset($rows[0]) ? $rows[0] : $rows;
                $customer->update($this->mapSupabaseCustomerToLocal($updated));
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Customer updated successfully', 'customer' => $customer]);
                }
                return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
            }
            if (($resp['error'] ?? null) === 'RLS_DENIED') {
                return response()->json(['error' => 'Supabase rejected the update due to Row Level Security. Please check policies for customers.'], 403);
            }
            Log::warning('Supabase update customer failed, falling back to local DB', ['status' => $resp['status'] ?? 0, 'error' => $resp['error'] ?? null]);
        }

        // Fallback: local update
        $customer->update($updateData);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Customer updated locally (remote sync pending)', 'customer' => $customer]);
        }
        return redirect()->route('customers.index')->with('success', 'Customer updated locally (remote sync pending)');
    }
    
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Check if customer has any sales
        if ($customer->sales()->exists()) {
            return response()->json([
                'error' => 'Cannot delete customer with existing sales records'
            ], 400);
        }
        
        $customer->delete();
        
        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }
    
    public function activate($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['is_active' => true]);
        
        return response()->json([
            'message' => 'Customer activated successfully'
        ]);
    }
    
    public function deactivate($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['is_active' => false]);
        
        return response()->json([
            'message' => 'Customer deactivated successfully'
        ]);
    }
    
    public function export(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $filterType = $request->get('type', 'all');
        $filterActive = $request->get('active', 'all');
        
        $query = Customer::query();
        
        // Apply same filters as index
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('first_name', 'like', "%{$searchQuery}%")
                  ->orWhere('last_name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%")
                  ->orWhere('phone', 'like', "%{$searchQuery}%")
                  ->orWhere('loyalty_member_id', 'like', "%{$searchQuery}%");
            });
        }
        
        if ($filterType !== 'all') {
            $query->where('customer_type', $filterType);
        }
        
        if ($filterActive !== 'all') {
            $query->where('is_active', $filterActive === 'active');
        }
        
        $customers = $query->get();
        
        // Generate CSV
        $filename = 'customers_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'First Name',
                'Last Name', 
                'Email',
                'Phone',
                'Customer Type',
                'Total Spent',
                'Total Visits',
                'Last Visit',
                'Created Date'
            ]);
            
            // Add customer data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->first_name,
                    $customer->last_name ?? '',
                    $customer->email,
                    $customer->phone,
                    $customer->customer_type,
                    $customer->total_spent ?? 0,
                    $customer->total_visits ?? 0,
                    $customer->last_visit ? Carbon::parse($customer->last_visit)->format('Y-m-d') : 'Never',
                    Carbon::parse($customer->created_at)->format('Y-m-d')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function getCustomerStats()
    {
        return [
            'total' => Customer::count(),
            'active' => Customer::where('is_active', true)->count(),
            'inactive' => Customer::where('is_active', false)->count(),
            'recreational' => Customer::where('customer_type', 'recreational')->count(),
            'medical' => Customer::where('customer_type', 'medical')->count(),
            'loyaltyMembers' => Customer::whereNotNull('loyalty_member_id')->count(),
            'veterans' => Customer::where('is_veteran', true)->count()
        ];
    }
}
