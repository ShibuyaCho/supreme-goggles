<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return null; // <-- no web redirect for API
        }
        return route('login'); // define a real web login route if you need it
    }


    /**
     * Handle unauthenticated user for API requests
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            abort(response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please login to access this resource'
            ], 401));
        }

        parent::unauthenticated($request, $guards);
    }
}
