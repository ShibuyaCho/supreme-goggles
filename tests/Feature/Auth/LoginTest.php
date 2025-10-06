<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_password_login_returns_token(): void
    {
        $user = User::create([
            'name' => 'L', 'email' => 'login@example.com', 'password' => Hash::make('Str0ngPass!'),
            'role' => 'cashier', 'permissions' => ['pos:*'], 'is_active' => true,
        ]);

        $res = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com', 'password' => 'Str0ngPass!',
        ]);
        $res->assertOk()->assertJsonStructure(['token','user' => ['id','email']]);
    }

    public function test_pin_login_returns_token(): void
    {
        $employee = Employee::create([
            'employee_id' => 'E12345', 'first_name' => 'P', 'last_name' => 'In', 'email' => 'pin@example.com',
            'pin' => Hash::make('1234'), 'role' => 'cashier', 'permissions' => ['pos:*'], 'is_active' => true,
        ]);
        $user = User::create([
            'name' => 'P In', 'email' => 'pin@example.com', 'employee_id' => $employee->id,
            'password' => Hash::make('random'), 'role' => 'cashier', 'permissions' => ['pos:*'], 'is_active' => true,
        ]);

        $res = $this->postJson('/api/auth/pin-login', [ 'employee_id' => 'E12345', 'pin' => '1234' ]);
        $res->assertOk()->assertJsonStructure(['token','employee' => ['id','employee_id']]);
    }
}
