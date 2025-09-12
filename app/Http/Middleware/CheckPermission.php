<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'error' => 'Account is inactive'
            ], 403);
        }

        // Admin bypass: admins have full access
        if (($user->role ?? null) === 'admin') {
            return $next($request);
        }

        // Direct user permission (supports wildcards)
        if ($this->hasPermission($user->permissions ?? [], $permission)) {
            return $next($request);
        }

        // Role-based permissions from POS settings
        $settings = Cache::get('pos_settings', []);
        $role = $user->role ?? null;
        $rolePerms = [];
        if ($role && isset($settings['role_permissions']) && is_array($settings['role_permissions'])) {
            $rolePerms = $settings['role_permissions'][$role] ?? [];
        } else {
            // Safe defaults
            $defaults = [
                'admin' => ['*'],
                'manager' => ['pos:*','products:*','customers:*','sales:*','analytics:read','deals:*','employees:read','metrc:access','metrc:sync','reports:read','reports:export'],
                'inventory' => ['products:*','metrc:access','metrc:sync','analytics:read'],
                'budtender' => ['pos:*','products:read','customers:read','sales:create','analytics:read'],
                'cashier' => ['pos:*','products:read','sales:create','products:print','analytics:read']
            ];
            $rolePerms = $defaults[$role] ?? [];
        }

        if ($this->hasPermission($rolePerms, $permission)) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Insufficient permissions',
            'required_permission' => $permission,
            'user_permissions' => $user->permissions ?? [],
            'role' => $role,
        ], 403);
    }

    private function hasPermission(array $perms, string $required): bool
    {
        if (in_array('*', $perms, true)) return true;
        if (in_array($required, $perms, true)) return true;
        // Support wildcard namespace, e.g., "products:*"
        $parts = explode(':', $required, 2);
        if (count($parts) === 2) {
            [$ns, $act] = $parts;
            if (in_array($ns . ':*', $perms, true)) return true;
        }
        return false;
    }
}
