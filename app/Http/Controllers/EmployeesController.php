<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

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
            'worker_permit' => 'nullable|string|max:100',
            'metrc_api_key' => 'nullable|string|max:255',
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
            'role' => $this->mapPositionToRole($request->position ?? ''),
            'hire_date' => $request->hire_date,
            'hourly_rate' => $request->hourly_rate,
            'worker_permit' => $request->worker_permit,
            'metrc_api_key' => $request->metrc_api_key,
            'permissions' => $request->permissions,
            'pin' => Hash::make(str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT))
        ]);

        // Sync or create linked user with employee role/permissions
        try {
            $user = \App\Models\User::firstOrCreate(
                ['employee_id' => $employee->id],
                [
                    'name' => $employee->full_name,
                    'email' => $employee->email,
                    'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                    'is_active' => true,
                ]
            );
            $user->update([
                'role' => $employee->role,
                'permissions' => $employee->permissions,
            ]);
            if (!$employee->user_id || (int) $employee->user_id !== (int) $user->id) {
                $employee->forceFill(['user_id' => $user->id])->save();
            }
        } catch (\Throwable $e) {
            // ignore sync errors
        }

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
            'worker_permit' => 'nullable|string|max:100',
            'metrc_api_key' => 'nullable|string|max:255',
            'permissions' => 'required|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $updateData = $request->only([
            'first_name', 'last_name', 'email', 'phone',
            'department', 'position', 'hourly_rate', 'worker_permit', 'metrc_api_key'
        ]);
        // Normalize role persistence
        $updateData['role'] = $request->get('role')
            ? strtolower($request->get('role'))
            : $this->mapPositionToRole($request->get('position', $employee->position ?? ''));

        $updateData['permissions'] = $request->permissions;
        
        $employee->update($updateData);

        // Sync linked user role/permissions/active flag after update
        try {
            $user = \App\Models\User::where('employee_id', $employee->id)->first();
            if ($user) {
                $user->update([
                    'role' => $employee->role,
                    'permissions' => $employee->permissions,
                    'is_active' => $employee->isActive(),
                ]);
                if (!$employee->user_id || (int) $employee->user_id !== (int) $user->id) {
                    $employee->forceFill(['user_id' => $user->id])->save();
                }
            }
        } catch (\Throwable $e) {
            // ignore sync errors
        }

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
    
    public function clockStatus($id)
    {
        $employee = Employee::findOrFail($id);
        $this->authorizeEmployeeAction($employee);
        $key = $this->clockCacheKey($employee->id);
        $data = \Illuminate\Support\Facades\Cache::get($key, null);
        $clockedIn = (bool)($data['clocked_in'] ?? false);
        $clockInAt = $data['clock_in_at'] ?? null;
        return response()->json([
            'clocked_in' => $clockedIn,
            'clock_in_at' => $clockInAt,
        ]);
    }

    public function clockIn($id)
    {
        $employee = Employee::findOrFail($id);
        $this->authorizeEmployeeAction($employee);
        $key = $this->clockCacheKey($employee->id);
        $data = \Illuminate\Support\Facades\Cache::get($key, []);
        if (!empty($data['clocked_in'])) {
            return response()->json(['message' => 'Already clocked in', 'clocked_in' => true, 'clock_in_at' => $data['clock_in_at'] ?? now()], 200);
        }
        $payload = [
            'clocked_in' => true,
            'clock_in_at' => now()->toIso8601String(),
        ];
        \Illuminate\Support\Facades\Cache::put($key, $payload, now()->addDays(7));
        return response()->json(['message' => 'Clocked in', 'clocked_in' => true, 'clock_in_at' => $payload['clock_in_at']]);
    }

    public function clockOut($id)
    {
        $employee = Employee::findOrFail($id);
        $this->authorizeEmployeeAction($employee);
        $key = $this->clockCacheKey($employee->id);
        $data = \Illuminate\Support\Facades\Cache::get($key, []);
        if (empty($data['clocked_in'])) {
            return response()->json(['message' => 'Not clocked in', 'clocked_in' => false], 200);
        }
        $clockInAt = isset($data['clock_in_at']) ? \Carbon\Carbon::parse($data['clock_in_at']) : now();
        $clockOutAt = now();
        $duration = $clockOutAt->diffInSeconds($clockInAt);
        $entry = [
            'in' => $clockInAt->toIso8601String(),
            'out' => $clockOutAt->toIso8601String(),
            'seconds' => $duration,
        ];
        $history = $data['history'] ?? [];
        $history[] = $entry;
        \Illuminate\Support\Facades\Cache::put($key, [
            'clocked_in' => false,
            'clock_in_at' => null,
            'history' => $history,
        ], now()->addDays(30));
        return response()->json(['message' => 'Clocked out', 'clocked_in' => false, 'entry' => $entry]);
    }

    private function clockCacheKey($employeeId): string
    {
        return 'employee_clock:'.$employeeId;
    }

    private function authorizeEmployeeAction(Employee $employee): void
    {
        $user = auth()->user();
        if (!$user) abort(401);
        // Allow self or manager/admin
        $isSelf = $user->employee && $user->employee->id === $employee->id;
        $isManager = $user->isAdmin() || $user->isManager();
        if (!$isSelf && !$isManager) abort(403, 'Not authorized');
    }

    public function resetPin($id)
    {
        $employee = Employee::findOrFail($id);
        $newPin = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        $employee->update(['pin' => Hash::make($newPin)]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your Cannabis POS PIN has been reset. Your new PIN is: {$newPin}\nIf you did not request this, please contact your manager immediately.",
                function ($message) use ($employee) {
                    $message->to($employee->email)->subject('Your POS PIN has been reset');
                }
            );
        } catch (\Throwable $e) {
            // Continue without failing if mail driver isn't configured
        }

        return response()->json([
            'message' => 'PIN reset and email sent to employee'
        ]);
    }

    public function sendPasswordReset($id)
    {
        $employee = Employee::findOrFail($id);
        $status = Password::broker('employees')->sendResetLink(['email' => $employee->email]);
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        }
        return response()->json(['error' => __($status)], 400);
    }

    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');
        return view('employees.reset-password', ['token' => $token, 'email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::broker('employees')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($employee, $password) {
                $employee->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('employees.index')->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
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

    private function mapPositionToRole(string $position): string
    {
        $p = strtolower(trim($position));
        if ($p === 'admin' || $p === 'administrator') return 'admin';
        if ($p === 'manager' || $p === 'general manager' || $p === 'assistant manager') return 'manager';
        if ($p === 'inventory' || $p === 'inventory manager' || $p === 'inventory specialist') return 'inventory';
        if ($p === 'budtender') return 'budtender';
        if ($p === 'cashier' || $p === 'sales' || $p === 'sales associate') return 'cashier';
        return 'cashier';
    }
}
