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
            RateLimiter::hit($key, 300); // 5 minute lockout
            
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

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

        $employee = Employee::where('employee_id', $request->employee_id)
            ->where('is_active', true)
            ->first();

        if (!$employee || !Hash::check($request->pin, $employee->pin)) {
            return response()->json([
                'error' => 'Invalid employee ID or PIN'
            ], 401);
        }

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
        }

        // Update last login
        $employee->update(['last_login' => now()]);
        $user->updateLastLogin();

        // Generate limited token for POS operations
        $abilities = ['pos:*', 'products:read', 'customers:read', 'sales:create'];
        $token = $user->generateApiToken('POS Terminal', $abilities);

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

        // Generate a unique employee identifier
        do {
            $generatedId = strtoupper(Str::random(6));
        } while (Employee::where('employee_id', $generatedId)->exists());

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
