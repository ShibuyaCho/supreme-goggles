<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial admin user
        $adminUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@cannabis-pos.local',
            'password' => Hash::make('admin123!'),
            'role' => 'admin',
            'permissions' => ['*'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create admin employee record
        $adminEmployee = Employee::create([
            'user_id' => $adminUser->id,
            'employee_id' => 'EMP001',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@cannabis-pos.local',
            'phone' => '555-0001',
            'role' => 'admin',
            'permissions' => ['*'],
            'hourly_rate' => 25.00,
            'hire_date' => now(),
            'is_active' => true,
            'pin' => Hash::make('1234'),
        ]);

        // Update admin user with employee ID
        $adminUser->update(['employee_id' => $adminEmployee->id]);

        // Create manager user
        $managerUser = User::create([
            'name' => 'Store Manager',
            'email' => 'manager@cannabis-pos.local',
            'password' => Hash::make('manager123!'),
            'role' => 'manager',
            'permissions' => [
                'pos:*',
                'products:*',
                'customers:*',
                'sales:*',
                'reports:*',
                'analytics:*',
                'employees:read',
                'metrc:*',
                'inventory:*',
                'deals:*',
                'loyalty:*'
            ],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $managerEmployee = Employee::create([
            'user_id' => $managerUser->id,
            'employee_id' => 'EMP002',
            'first_name' => 'Store',
            'last_name' => 'Manager',
            'email' => 'manager@cannabis-pos.local',
            'phone' => '555-0002',
            'role' => 'manager',
            'permissions' => [
                'pos:*',
                'products:*',
                'customers:*',
                'sales:*',
                'reports:*',
                'analytics:*',
                'employees:read',
                'metrc:*',
                'inventory:*',
                'deals:*',
                'loyalty:*'
            ],
            'hourly_rate' => 22.00,
            'hire_date' => now(),
            'is_active' => true,
            'pin' => Hash::make('2345'),
        ]);

        $managerUser->update(['employee_id' => $managerEmployee->id]);

        // Create cashier user
        $cashierUser = User::create([
            'name' => 'John Cashier',
            'email' => 'cashier@cannabis-pos.local',
            'password' => Hash::make('cashier123!'),
            'role' => 'cashier',
            'permissions' => [
                'pos:access',
                'pos:sales',
                'products:read',
                'customers:read',
                'customers:create',
                'sales:create',
                'sales:read'
            ],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $cashierEmployee = Employee::create([
            'user_id' => $cashierUser->id,
            'employee_id' => 'EMP003',
            'first_name' => 'John',
            'last_name' => 'Cashier',
            'email' => 'cashier@cannabis-pos.local',
            'phone' => '555-0003',
            'role' => 'cashier',
            'permissions' => [
                'pos:access',
                'pos:sales',
                'products:read',
                'customers:read',
                'customers:create',
                'sales:create',
                'sales:read'
            ],
            'hourly_rate' => 16.00,
            'hire_date' => now(),
            'is_active' => true,
            'pin' => Hash::make('3456'),
        ]);

        $cashierUser->update(['employee_id' => $cashierEmployee->id]);

        // Create budtender user
        $budtenderUser = User::create([
            'name' => 'Sarah Budtender',
            'email' => 'budtender@cannabis-pos.local',
            'password' => Hash::make('budtender123!'),
            'role' => 'budtender',
            'permissions' => [
                'pos:access',
                'pos:sales',
                'products:read',
                'customers:read',
                'customers:create',
                'customers:write',
                'sales:create',
                'sales:read',
                'loyalty:read',
                'loyalty:enroll'
            ],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $budtenderEmployee = Employee::create([
            'user_id' => $budtenderUser->id,
            'employee_id' => 'EMP004',
            'first_name' => 'Sarah',
            'last_name' => 'Budtender',
            'email' => 'budtender@cannabis-pos.local',
            'phone' => '555-0004',
            'role' => 'budtender',
            'permissions' => [
                'pos:access',
                'pos:sales',
                'products:read',
                'customers:read',
                'customers:create',
                'customers:write',
                'sales:create',
                'sales:read',
                'loyalty:read',
                'loyalty:enroll'
            ],
            'hourly_rate' => 18.00,
            'hire_date' => now(),
            'is_active' => true,
            'pin' => Hash::make('4567'),
        ]);

        $budtenderUser->update(['employee_id' => $budtenderEmployee->id]);

        // Create inventory user
        $inventoryUser = User::create([
            'name' => 'Mike Inventory',
            'email' => 'inventory@cannabis-pos.local',
            'password' => Hash::make('inventory123!'),
            'role' => 'inventory',
            'permissions' => [
                'products:*',
                'metrc:*',
                'inventory:*',
                'reports:inventory',
                'analytics:inventory'
            ],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $inventoryEmployee = Employee::create([
            'user_id' => $inventoryUser->id,
            'employee_id' => 'EMP005',
            'first_name' => 'Mike',
            'last_name' => 'Inventory',
            'email' => 'inventory@cannabis-pos.local',
            'phone' => '555-0005',
            'role' => 'inventory',
            'permissions' => [
                'products:*',
                'metrc:*',
                'inventory:*',
                'reports:inventory',
                'analytics:inventory'
            ],
            'hourly_rate' => 20.00,
            'hire_date' => now(),
            'is_active' => true,
            'pin' => Hash::make('5678'),
        ]);

        $inventoryUser->update(['employee_id' => $inventoryEmployee->id]);

        $this->command->info('Created ' . User::count() . ' users and ' . Employee::count() . ' employees');
        
        // Display login credentials
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Admin: admin@cannabis-pos.local / admin123! (PIN: 1234)');
        $this->command->info('Manager: manager@cannabis-pos.local / manager123! (PIN: 2345)');
        $this->command->info('Cashier: cashier@cannabis-pos.local / cashier123! (PIN: 3456)');
        $this->command->info('Budtender: budtender@cannabis-pos.local / budtender123! (PIN: 4567)');
        $this->command->info('Inventory: inventory@cannabis-pos.local / inventory123! (PIN: 5678)');
    }
}
