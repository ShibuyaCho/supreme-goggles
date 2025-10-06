<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_requires_auth(): void
    {
        $this->postJson('/api/auth/email/verification-notification')->assertStatus(401);
    }

    public function test_resend_sends_notification(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'U', 'email' => 'u@example.com', 'password' => bcrypt('x'),
            'role' => 'cashier', 'permissions' => ['pos:*'], 'is_active' => true,
        ]);
        Sanctum::actingAs($user, ['auth:*']);

        $this->postJson('/api/auth/email/verification-notification')->assertOk();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_signed_link_verifies_user(): void
    {
        $user = User::create([
            'name' => 'V', 'email' => 'v@example.com', 'password' => bcrypt('x'),
            'role' => 'cashier', 'permissions' => ['pos:*'], 'is_active' => true,
        ]);
        $this->assertNull($user->email_verified_at);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $res = $this->get($url);
        $res->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }
}
