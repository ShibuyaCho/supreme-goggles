<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'error' => 'Insufficient permissions',
                'required_permission' => $permission,
                'user_permissions' => $user->permissions ?? []
            ], 403);
        }

        return $next($request);
    }
}
