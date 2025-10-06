<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_user(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'permissions' => ['*'],
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin, ['*']);

        $payload = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Str0ngPass!',
            'password_confirmation' => 'Str0ngPass!',
            'role' => 'cashier',
            'permissions' => ['pos:*'],
        ];

        $res = $this->postJson('/api/auth/register', $payload);
        $res->assertCreated();
        $this->assertDatabaseHas('users', [ 'email' => 'new@example.com', 'role' => 'cashier' ]);
    }

    public function test_non_admin_cannot_register_user(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'permissions' => ['pos:*'],
            'is_active' => true,
        ]);

        Sanctum::actingAs($user, ['pos:*']);

        $payload = [
            'name' => 'New User',
            'email' => 'new2@example.com',
            'password' => 'Str0ngPass!',
            'password_confirmation' => 'Str0ngPass!',
            'role' => 'cashier',
        ];

        $res = $this->postJson('/api/auth/register', $payload);
        $res->assertStatus(403);
        $this->assertDatabaseMissing('users', [ 'email' => 'new2@example.com' ]);
    }
}
