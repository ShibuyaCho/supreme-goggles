<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage examples on routes:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin|manager')
     *   ->middleware('role:admin,manager') // also supported
     */
    public function handle(Request $request, Closure $next, ...$roleArgs)
    {
        // Use the current authenticated user (web or sanctum), do NOT redirect.
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated'); // not logged in
        }

        // Support both "role:admin|manager" and "role:admin,manager"
        $allowed = $this->normalizeRoles($roleArgs);   // -> ['admin','manager',...]

        // Use spatie/permission if present, otherwise fall back to simple checks
        $has = $this->userHasAnyRole($user, $allowed);

        if (! $has) {
            abort(403, 'Forbidden'); // authenticated but not authorized (avoid redirect loop)
        }

        return $next($request);
    }

    /**
     * Normalize role strings from middleware args into a flat, lowercase array.
     *
     * @param  array<int,string>  $roleArgs
     * @return array<int,string>
     */
    protected function normalizeRoles(array $roleArgs): array
    {
        // Flatten by splitting on both "|" and "," for convenience.
        $parts = [];
        foreach ($roleArgs as $arg) {
            // Route middleware may pass everything as a single string
            // e.g. "admin|manager" or "admin,manager"
            $chunks = preg_split('/[|,]/', (string) $arg, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $parts = array_merge($parts, $chunks);
        }

        // Trim + lowercase + unique
        $parts = array_values(array_unique(array_map(
            fn($r) => strtolower(trim($r)),
            $parts
        )));

        return $parts;
    }

    /**
     * Determine if the user has ANY of the allowed roles.
     *
     * Supports:
     *  - Spatie: $user->hasAnyRole(...)
     *  - $user->role   = 'admin'
     *  - $user->roles  = ['admin','manager']  (array or Collection)
     *  - $user->roles  = relation where each item has 'name' or 'slug'
     */
    protected function userHasAnyRole($user, array $allowed): bool
    {
        if (empty($allowed)) {
            return true; // no roles specified means "allow"
        }

        // spatie/laravel-permission
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($allowed);
        }

        // Single role string column: users.role = 'admin'
        if (isset($user->role) && is_string($user->role)) {
            return in_array(strtolower($user->role), $allowed, true);
        }

        // roles attribute/relationship: array/collection of strings or models
        if (isset($user->roles)) {
            $roles = $user->roles;

            // If it's a relation/collection of models, extract common fields
            if (is_iterable($roles)) {
                $names = [];
                foreach ($roles as $r) {
                    if (is_string($r)) {
                        $names[] = strtolower($r);
                    } elseif (is_array($r)) {
                        $names[] = strtolower($r['name'] ?? $r['slug'] ?? '');
                    } elseif (is_object($r)) {
                        $names[] = strtolower($r->name ?? $r->slug ?? '');
                    }
                }
                $names = array_filter($names);
                return ! empty(array_intersect($names, $allowed));
            }

            // If it's a scalar string
            if (is_string($roles)) {
                return in_array(strtolower($roles), $allowed, true);
            }
        }

        // Default deny if we cannot determine roles
        return false;
    }
}
