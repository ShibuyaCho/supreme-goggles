<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $filterType = $request->get('type', 'all');
        $filterActive = $request->get('active', 'all');
        $selectedTab = $request->get('tab', 'customers');
        
        $query = Customer::query();
        
        // Apply search filter
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('first_name', 'like', "%{$searchQuery}%")
                  ->orWhere('last_name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%")
                  ->orWhere('phone', 'like', "%{$searchQuery}%")
                  ->orWhere('loyalty_member_id', 'like', "%{$searchQuery}%");
            });
        }
        
        // Apply type filter
        if ($filterType !== 'all') {
            $query->where('customer_type', $filterType);
        }
        
        // Apply active filter
        if ($filterActive !== 'all') {
            $query->where('is_active', $filterActive === 'active');
        }
        
        $customers = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get analytics data for analytics tab
        $stats = $this->getCustomerStats();
        
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
        
        $customer = Customer::create($customerData);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Customer created successfully',
                'customer' => $customer
            ]);
        }
        
        return redirect()->route('customers.index')
                        ->with('success', 'Customer created successfully');
    }
    
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->loadPurchaseHistory();
        
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
        
        $customer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'customer_type' => $request->customer_type,
            'address' => json_encode($request->address ?? []),
            'notes' => $request->notes,
            'is_veteran' => $request->is_veteran ?? false
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Customer updated successfully',
                'customer' => $customer
            ]);
        }
        
        return redirect()->route('customers.index')
                        ->with('success', 'Customer updated successfully');
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
