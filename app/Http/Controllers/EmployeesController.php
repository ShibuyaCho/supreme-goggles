<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class EmployeesController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $departmentFilter = $request->get('department', 'all');
        $statusFilter = $request->get('status', 'all');
        
        $query = Employee::query();
        
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('first_name', 'like', "%{$searchQuery}%")
                  ->orWhere('last_name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%")
                  ->orWhere('employee_id', 'like', "%{$searchQuery}%");
            });
        }
        
        if ($departmentFilter !== 'all') {
            $query->where('department', $departmentFilter);
        }
        
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        
        $employees = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'employees' => $employees->items(),
                'meta' => [
                    'current_page' => $employees->currentPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                    'last_page' => $employees->lastPage(),
                ],
            ]);
        }

        return view('employees.index', compact(
            'employees',
            'searchQuery',
            'departmentFilter',
            'statusFilter'
        ));
    }
    
    public function create()
    {
        return view('employees.create');
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string|max:20',
            'employee_id' => 'required|string|max:50|unique:employees,employee_id',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'hire_date' => 'required|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'permissions' => 'required|array',
            'password' => 'required|string|min:8|confirmed'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $employee = Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'hire_date' => $request->hire_date,
            'hourly_rate' => $request->hourly_rate,
            'permissions' => json_encode($request->permissions),
            'password' => Hash::make($request->password),
            'status' => 'active',
            'pin' => str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT)
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Employee created successfully',
                'employee' => $employee
            ]);
        }
        
        return redirect()->route('employees.index')
                        ->with('success', 'Employee created successfully');
    }
    
    public function show($id)
    {
        $employee = Employee::with(['sales', 'clockEntries'])->findOrFail($id);
        return view('employees.show', compact('employee'));
    }
    
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }
    
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'required|string|max:20',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'permissions' => 'required|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $updateData = $request->only([
            'first_name', 'last_name', 'email', 'phone',
            'department', 'position', 'hourly_rate'
        ]);
        
        $updateData['permissions'] = json_encode($request->permissions);
        
        $employee->update($updateData);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Employee updated successfully',
                'employee' => $employee
            ]);
        }
        
        return redirect()->route('employees.index')
                        ->with('success', 'Employee updated successfully');
    }
    
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Check if employee has any sales
        if ($employee->sales()->exists()) {
            return response()->json([
                'error' => 'Cannot delete employee with existing sales records'
            ], 400);
        }
        
        $employee->delete();
        
        return response()->json([
            'message' => 'Employee deleted successfully'
        ]);
    }
    
    public function toggleStatus($id)
    {
        $employee = Employee::findOrFail($id);
        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        
        $employee->update(['status' => $newStatus]);
        
        return response()->json([
            'message' => "Employee {$newStatus} successfully",
            'employee' => $employee
        ]);
    }
    
    public function resetPin($id)
    {
        $employee = Employee::findOrFail($id);
        $newPin = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        $employee->update(['pin' => $newPin]);
        
        return response()->json([
            'message' => 'PIN reset successfully',
            'new_pin' => $newPin
        ]);
    }
    
    public function performance(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $sales = $employee->sales()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();
        
        $performance = [
            'total_sales' => $sales->sum('total'),
            'total_transactions' => $sales->count(),
            'average_order_value' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
            'sales_by_day' => $sales->groupBy(function($sale) {
                return $sale->created_at->format('Y-m-d');
            })->map(function($daySales) {
                return [
                    'transactions' => $daySales->count(),
                    'revenue' => $daySales->sum('total')
                ];
            })
        ];
        
        return response()->json($performance);
    }
}
