<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Employee;

class ProductionUserSeeder extends Seeder
{
    /**
     * Run the production database seeds.
     * 
     * IMPORTANT: Change these credentials before running in production!
     */
    public function run(): void
    {
        $this->command->info('Creating production users...');
        
        // Generate secure random passwords
        $adminPassword = $this->generateSecurePassword();
        $managerPassword = $this->generateSecurePassword();
        
        // Create initial admin user with secure credentials
        $adminUser = User::create([
            'name' => 'System Administrator',
            'email' => env('ADMIN_EMAIL', 'admin@' . env('APP_DOMAIN', 'yourdomain.com')),
            'password' => Hash::make($adminPassword),
            'role' => 'admin',
            'permissions' => ['*'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create admin employee record with secure PIN
        $adminPin = $this->generateSecurePin();
        $adminEmployee = Employee::create([
            'user_id' => $adminUser->id,
            'employee_id' => 'ADM' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => $adminUser->email,
            'phone' => env('ADMIN_PHONE', ''),
            'role' => 'admin',
            'permissions' => ['*'],
            'hourly_rate' => 30.00,
            'hire_date' => now(),
            'is_active' => true,
            'pin' => Hash::make($adminPin),
        ]);

        $adminUser->update(['employee_id' => $adminEmployee->id]);

        // Create manager user with secure credentials
        $managerUser = User::create([
            'name' => 'Store Manager',
            'email' => env('MANAGER_EMAIL', 'manager@' . env('APP_DOMAIN', 'yourdomain.com')),
            'password' => Hash::make($managerPassword),
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

        $managerPin = $this->generateSecurePin();
        $managerEmployee = Employee::create([
            'user_id' => $managerUser->id,
            'employee_id' => 'MGR' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'first_name' => 'Store',
            'last_name' => 'Manager',
            'email' => $managerUser->email,
            'phone' => env('MANAGER_PHONE', ''),
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
            'hourly_rate' => 25.00,
            'hire_date' => now(),
            'is_active' => true,
            'pin' => Hash::make($managerPin),
        ]);

        $managerUser->update(['employee_id' => $managerEmployee->id]);

        $this->command->info('Production users created successfully!');
        $this->command->info('=== IMPORTANT: SAVE THESE CREDENTIALS ===');
        $this->command->warn('Admin Credentials:');
        $this->command->warn('Email: ' . $adminUser->email);
        $this->command->warn('Password: ' . $adminPassword);
        $this->command->warn('Employee ID: ' . $adminEmployee->employee_id);
        $this->command->warn('PIN: ' . $adminPin);
        $this->command->warn('');
        $this->command->warn('Manager Credentials:');
        $this->command->warn('Email: ' . $managerUser->email);
        $this->command->warn('Password: ' . $managerPassword);
        $this->command->warn('Employee ID: ' . $managerEmployee->employee_id);
        $this->command->warn('PIN: ' . $managerPin);
        $this->command->warn('');
        $this->command->error('CHANGE THESE PASSWORDS IMMEDIATELY AFTER FIRST LOGIN!');
        $this->command->error('Set ADMIN_EMAIL and MANAGER_EMAIL in your .env file');
    }

    /**
     * Generate a secure random password
     */
    private function generateSecurePassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $password = '';
        
        // Ensure at least one character from each set
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Fill the rest randomly
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Generate a secure random PIN
     */
    private function generateSecurePin(): string
    {
        return str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
    }
}
