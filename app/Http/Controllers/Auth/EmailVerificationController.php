<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    // Simple notice page
    public function notice(Request $request)
    {
        if ($request->user() && $request->user()->hasVerifiedEmail()) {
            return redirect('/');
        }
        return view('auth.verify');
    }

    // Resend verification email (auth:sanctum)
    public function send(Request $request)
    {
        $user = $request->user();
        if (!$user || !($user instanceof MustVerifyEmail)) {
            return response()->json(['error' => 'User not eligible for verification'], 400);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent']);
    }

    // Verify link callback (no auth required, signed route)
    public function verify(Request $request, $id, $hash)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['error' => 'Invalid or expired link'], 401);
        }
        $user = User::findOrFail($id);
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['error' => 'Invalid verification hash'], 403);
        }
        if ($user->hasVerifiedEmail()) {
            return redirect('/?verified=1');
        }
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        return redirect('/?verified=1');
    }
}
