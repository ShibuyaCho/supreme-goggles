<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;

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

        // Default: hide inactive unless explicitly requested or searching
        if ($statusFilter !== 'all') {
            $normalized = strtolower((string)$statusFilter);
            if (in_array($normalized, ['active','inactive'], true)) {
                $query->where('is_active', $normalized === 'active');
            } else {
                // For other custom statuses if present in schema
                $query->where('status', $normalized);
            }
        } else if (!$searchQuery) {
            $query->where(function($q){
                $q->where('is_active', true)->orWhereNull('is_active');
            });
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
        // Authorization: only admin/manager
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isManager())) {
            abort(403, 'Unauthorized');
        }

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
        
        $hashedPassword = Hash::make($request->password);
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
            'pin' => Hash::make(str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT)),
            'password' => $hashedPassword,
        ]);

        // Sync or create linked user with employee role/permissions and same login password
        try {
            $user = \App\Models\User::firstOrCreate(
                ['employee_id' => $employee->id],
                [
                    'name' => $employee->full_name,
                    'email' => $employee->email,
                    'password' => $hashedPassword,
                    'is_active' => true,
                ]
            );
            $updates = [
                'role' => $employee->role,
                'permissions' => $employee->permissions,
            ];
            // If user already existed, ensure their password matches the provided one
            if ($user && !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                $updates['password'] = $hashedPassword;
            }
            $user->update($updates);
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
        // Authorization: only admin/manager
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isManager())) {
            abort(403, 'Unauthorized');
        }

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

        // Owner hardening: never allow owner accounts to be demoted or deactivated
        try {
            $ownerEmails = ['thccodys@gmail.com', 'smith.cody@yahoo.com'];
            if (in_array(strtolower((string)$employee->email), $ownerEmails, true)) {
                if ($employee->role !== 'admin' || $employee->permissions !== ['*'] || !$employee->is_active) {
                    $employee->update(['role' => 'admin', 'permissions' => ['*'], 'is_active' => true]);
                }
            }
        } catch (\Throwable $e) {}

        // Sync linked user role/permissions/active flag after update
        try {
            $user = \App\Models\User::where('employee_id', $employee->id)->first();
            if ($user) {
                $updates = [
                    'role' => $employee->role,
                    'permissions' => $employee->permissions,
                    'is_active' => $employee->isActive(),
                ];
                // Owner hardening mirrors to user as well
                $ownerEmails = ['thccodys@gmail.com', 'smith.cody@yahoo.com'];
                if (in_array(strtolower((string)$user->email), $ownerEmails, true)) {
                    $updates['role'] = 'admin';
                    $updates['permissions'] = ['*'];
                    $updates['is_active'] = true;
                }
                $user->update($updates);
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
        // Authorization: only admin/manager
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isManager())) {
            abort(403, 'Unauthorized');
        }

        $employee = Employee::findOrFail($id);

        // Soft delete: deactivate and set termination date, preserve data
        if ($employee->is_active !== false || ($employee->status ?? 'active') !== 'inactive') {
            $employee->update([
                'is_active' => false,
                'termination_date' => now()->toDateString(),
            ]);
        }

        // Also deactivate linked user
        try {
            if ($employee->user_id && ($u = \App\Models\User::find($employee->user_id))) {
                $u->update(['is_active' => false]);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'message' => 'Employee deactivated successfully'
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
        $now = now();
        $payload = [
            'clocked_in' => true,
            'clock_in_at' => $now->toIso8601String(),
        ];
        \Illuminate\Support\Facades\Cache::put($key, $payload, now()->addDays(7));
        // Persist to DB if available
        try {
            if (class_exists(\App\Models\TimeClockEntry::class) && Schema::hasTable('time_clock_entries')) {
                // Ensure no open entry exists
                \App\Models\TimeClockEntry::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'clock_out' => null,
                    ],
                    [
                        'clock_in' => $now,
                        'source' => 'pos',
                    ]
                );
            }
        } catch (\Throwable $e) {
            // ignore db persistence errors
        }
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
        // Persist to DB if available
        try {
            if (class_exists(\App\Models\TimeClockEntry::class) && Schema::hasTable('time_clock_entries')) {
                $open = \App\Models\TimeClockEntry::where('employee_id', $employee->id)
                    ->whereNull('clock_out')
                    ->latest('clock_in')
                    ->first();
                if ($open) {
                    $open->update(['clock_out' => $clockOutAt]);
                } else {
                    \App\Models\TimeClockEntry::create([
                        'employee_id' => $employee->id,
                        'clock_in' => $clockInAt,
                        'clock_out' => $clockOutAt,
                        'source' => 'pos'
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // ignore db persistence errors
        }
        return response()->json(['message' => 'Clocked out', 'clocked_in' => false, 'entry' => $entry]);
    }

    private function clockCacheKey($employeeId): string
    {
        return 'employee_clock:'.$employeeId;
    }

    // List time entries (admin/manager)
    public function listTimeEntries(Request $request)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isManager())) abort(403);
        try {
            if (!class_exists(\App\Models\TimeClockEntry::class) || !Schema::hasTable('time_clock_entries')) {
                return response()->json(['error' => 'Time tracking not yet initialized'], 503);
            }
            $start = $request->get('start_date', now()->subDays(14)->toDateString());
            $end = $request->get('end_date', now()->toDateString());
            $employeeId = $request->get('employee_id');
            $q = \App\Models\TimeClockEntry::with(['employee'])
                ->whereBetween('clock_in', [\Carbon\Carbon::parse($start)->startOfDay(), \Carbon\Carbon::parse($end)->endOfDay()])
                ->orderBy('clock_in', 'desc');
            if ($employeeId) $q->where('employee_id', $employeeId);
            $entries = $q->paginate(50);
            return response()->json([
                'entries' => $entries->items(),
                'meta' => [
                    'current_page' => $entries->currentPage(),
                    'per_page' => $entries->perPage(),
                    'total' => $entries->total(),
                    'last_page' => $entries->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to load time entries'], 500);
        }
    }

    // Create manual entry (admin/manager)
    public function createTimeEntry(Request $request)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isManager())) abort(403);
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'clock_in' => 'required|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'notes' => 'nullable|string',
        ]);
        try {
            if (!class_exists(\App\Models\TimeClockEntry::class) || !Schema::hasTable('time_clock_entries')) {
                return response()->json(['error' => 'Time tracking not yet initialized'], 503);
            }
            $entry = \App\Models\TimeClockEntry::create(array_merge($data, [
                'source' => 'admin',
                'adjusted_by' => $user->id,
                'adjusted_at' => now(),
            ]));
            return response()->json(['message' => 'Entry created', 'entry' => $entry]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to create entry'], 500);
        }
    }

    // Update entry (adjust)
    public function updateTimeEntry(Request $request, $entryId)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isManager())) abort(403);
        $data = $request->validate([
            'clock_in' => 'required|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'notes' => 'nullable|string',
        ]);
        try {
            if (!class_exists(\App\Models\TimeClockEntry::class) || !Schema::hasTable('time_clock_entries')) {
                return response()->json(['error' => 'Time tracking not yet initialized'], 503);
            }
            $entry = \App\Models\TimeClockEntry::findOrFail($entryId);
            $adjustments = $entry->adjustments ?: [];
            $adjustments[] = [
                'prev' => [
                    'clock_in' => optional($entry->clock_in)->toIso8601String(),
                    'clock_out' => optional($entry->clock_out)->toIso8601String(),
                    'notes' => $entry->notes,
                ],
                'new' => [
                    'clock_in' => (string)$data['clock_in'],
                    'clock_out' => isset($data['clock_out']) ? (string)$data['clock_out'] : null,
                    'notes' => $data['notes'] ?? null,
                ],
                'by' => $user->id,
                'at' => now()->toIso8601String(),
            ];
            $entry->update(array_merge($data, [
                'adjusted_by' => $user->id,
                'adjusted_at' => now(),
                'adjustments' => $adjustments,
            ]));
            return response()->json(['message' => 'Entry updated', 'entry' => $entry->fresh()]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to update entry'], 500);
        }
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
