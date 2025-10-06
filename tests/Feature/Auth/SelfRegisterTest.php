<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelfRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_register_disabled_by_default(): void
    {
        $payload = [
            'name' => 'Cashier One',
            'email' => 'cashier1@example.com',
            'password' => 'Str0ngPass!',
            'password_confirmation' => 'Str0ngPass!',
            'pin' => '1234',
        ];
        $res = $this->postJson('/api/auth/self-register', $payload);
        $res->assertStatus(403);
    }

    public function test_self_register_enabled_creates_user_and_employee(): void
    {
        $this->app['config']->set('auth.allow_self_register', true);

        $payload = [
            'name' => 'Cashier Two',
            'email' => 'cashier2@example.com',
            'password' => 'Str0ngPass!',
            'password_confirmation' => 'Str0ngPass!',
            'pin' => '4321',
        ];
        $res = $this->postJson('/api/auth/self-register', $payload);
        $res->assertCreated()->assertJsonStructure(['token','user' => ['id','email']]);
        $this->assertDatabaseHas('users', [ 'email' => 'cashier2@example.com', 'role' => 'cashier' ]);
        $this->assertDatabaseHas('employees', [ 'email' => 'cashier2@example.com' ]);
    }
}
