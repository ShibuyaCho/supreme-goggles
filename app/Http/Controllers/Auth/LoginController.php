<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __construct()
    {
        // 👇 Use the web guard explicitly
        $this->middleware('guest:web')->only(['showLoginForm', 'login']);
        $this->middleware('auth:web')->only(['logout']);

        // If you really need the API endpoints, fence them off from web:
        $this->middleware('guest')->only(['apiLogin']);   // token-based, no session
        $this->middleware('auth')->only(['apiLogout']);   // token-based, no session
    }

    protected function guard()
    {
        return Auth::guard('web');
    }

    /** Show the web login form */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /** Session-based web login */
    public function login(Request $request)
    {
        $this->ensureIsNotRateLimited($request);

        $validator = Validator::make($request->all(), [
            'email'    => ['required','email','max:255'],
            'password' => ['required','string','min:6'],
            'remember' => ['sometimes','boolean'],
        ]);

        if ($validator->fails()) {
            $this->hitRateLimiter($request);
            return back()->withErrors($validator)->withInput($request->only('email'));
        }

        $data = $validator->validated();
        $remember = (bool)($data['remember'] ?? false);

        // 👇 Attempt on web guard (creates session)
        if (! $this->guard()->attempt(['email' => $data['email'], 'password' => $data['password']], $remember)) {
            $this->hitRateLimiter($request);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        // (Optional) If you want an API token for XHRs, you can keep this.
        // But it does not influence web session auth.
        // $token = $this->issueApiToken($this->guard()->user());

        return redirect()->intended(route('pos.index'))->with('status', 'Welcome back!');
    }

    /** Session logout */
    public function logout(Request $request)
    {
        // Revoke any personal access token if you created one earlier (safe if none)
        $this->revokeApiToken(Auth::guard('web')->user());

        // Logout the session guard
        Auth::guard('web')->logout();

        // --- Server-side session ---
        // Completely invalidate
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Session::flush();

        // --- Client-side cookies (must match domain/path/flags) ---
        $sessionCookieName = config('session.cookie', 'laravel_session');
        $recallerName      = Auth::guard('web')->getRecallerName(); // e.g. remember_web_xxx

        $domain   = config('session.domain');            // null or ".example.test"
        $path     = config('session.path', '/');         // usually "/"
        $secure   = (bool) config('session.secure', false);
        $httpOnly = true;
        $sameSite = config('session.same_site', 'lax');  // 'lax' | 'strict' | 'none'

        // Helper to forget with correct attributes
        $forget = function (string $name) use ($domain, $path, $secure, $httpOnly, $sameSite) {
            // Laravel's Cookie::forget($name) doesn't let you set domain/path/samesite.
            // Overwrite with an expired cookie that matches the original attributes.
            Cookie::queue(cookie()->forget($name));
            Cookie::queue(cookie(
                name:     $name,
                value:    null,
                minutes:  -525600, // 1 year ago
                path:     $path,
                domain:   $domain,
                secure:   $secure,
                httpOnly: $httpOnly,
                raw:      false,
                sameSite: $sameSite
            ));
        };

        $forget($sessionCookieName);
        $forget($recallerName);       // <-- critical, kills remember_me
        $forget('XSRF-TOKEN');        // if Sanctum/SPA is used

        // (Optional) also nuke any custom app cookies you might have set
        // $forget('your_custom_cookie');

        return redirect()->route('login')->with('status', 'Logged out successfully.');
    }


    /** ---------- API endpoints (token-based, no session) ---------- */

    public function apiLogin(Request $request)
    {
        $this->ensureIsNotRateLimited($request);

        $validator = Validator::make($request->all(), [
            'email'    => ['required','email','max:255'],
            'password' => ['required','string','min:6'],
        ]);

        if ($validator->fails()) {
            $this->hitRateLimiter($request);
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $creds = $validator->validated();
        $user = \App\Models\User::where('email', $creds['email'])->first();

        if (! $user || ! Hash::check($creds['password'], $user->password)) {
            $this->hitRateLimiter($request);
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        RateLimiter::clear($this->throttleKey($request));

        $token = $this->issueApiToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Authenticated',
            'token'   => $token,
            'user'    => ['id'=>$user->id, 'name'=>$user->name, 'email'=>$user->email],
        ]);
    }

    public function apiLogout(Request $request)
    {
        $user = $request->user() ?: Auth::user();
        if ($user) $this->revokeApiToken($user);

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    // ----- helpers (unchanged) -----
    protected function issueApiToken($user): string
    {
        if (method_exists($user, 'createToken')) {
            return $user->createToken('pos-web')->plainTextToken;
        }
        $token = Str::random(60);
        $user->api_token = hash('sha256', $token);
        $user->save();
        return $token;
    }

    protected function revokeApiToken($user): void
    {
        if (! $user) return;
        if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
            return;
        }
        if (isset($user->api_token)) {
            $user->api_token = null;
            $user->save();
        }
    }

    protected function throttleKey(Request $request): string
    {
        return 'login:' . strtolower($request->input('email')) . '|' . $request->ip();
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);
        if (! RateLimiter::tooManyAttempts($key, 5)) return;
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'email' => [__('Too many attempts. Please try again in :seconds seconds.', ['seconds' => $seconds])],
        ])->status(429);
    }

    protected function hitRateLimiter(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), 60);
    }
}
