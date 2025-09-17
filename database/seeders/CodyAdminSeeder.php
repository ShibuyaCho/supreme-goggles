<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;

class CodyAdminSeeder extends Seeder
{
    /**
     * Seed a specific admin user/employee for production.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $primaryEmail = env('CODY_ADMIN_EMAIL', 'thccodys@gmail.com');
            $legacyEmails = ['smith.cody@yahoo.com'];
            $useRandom = app()->environment('production') && !env('CODY_ADMIN_PASSWORD');
            $password = env('CODY_ADMIN_PASSWORD') ?: ($useRandom ? bin2hex(random_bytes(9)) . '!' : 'Hms2019!');
            $pinPlain = env('CODY_ADMIN_PIN') ?: ($useRandom ? str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT) : '3732');
            $employeeCode = env('CODY_ADMIN_EMPLOYEE_CODE', 'emp01');

            // Upsert User (migrate legacy email to primary if found)
            $user = User::query()->where('email', $primaryEmail)->first();
            if (!$user) {
                $user = User::query()->whereIn('email', $legacyEmails)->first();
            }
            if (!$user) {
                $user = new User();
                $user->email = $primaryEmail;
            } else if ($user->email !== $primaryEmail) {
                $user->email = $primaryEmail;
            }
            $user->name = 'Cody Smith';
            $user->role = 'admin';
            $user->permissions = ['*'];
            $user->is_active = true;
            $user->email_verified_at = now();
            $user->password = Hash::make($password);
            $user->save();

            // Upsert Employee (prefer existing by user or employee_id)
            $employee = Employee::query()->where('user_id', $user->id)->first();
            if (!$employee) {
                $employee = Employee::query()->where('employee_id', $employeeCode)->orWhereIn('email', array_merge([$primaryEmail], $legacyEmails))->first();
            }
            if (!$employee) {
                $employee = new Employee();
                $employee->user_id = $user->id;
                $employee->employee_id = $employeeCode;
            }

            $employee->user_id = $user->id;
            $employee->employee_id = $employeeCode;
            $employee->first_name = 'Cody';
            $employee->last_name = 'Smith';
            $employee->email = $primaryEmail;
            $employee->phone = $employee->phone ?? '';
            $employee->role = 'admin';
            $employee->permissions = ['*'];
            $employee->hourly_rate = $employee->hourly_rate ?? 30.00;
            $employee->hire_date = $employee->hire_date ?? now();
            $employee->is_active = true;
            $employee->pin = Hash::make($pinPlain);
            $employee->save();

            // Link back to user
            if ((int)($user->employee_id ?? 0) !== (int)$employee->id) {
                $user->employee_id = $employee->id;
                $user->save();
            }

            // Cleanup: remove any stale user/employee with legacy email
            $legacyUser = User::query()->where('email', 'smith.cody@yahoo.com')->first();
            if ($legacyUser && $legacyUser->id !== $user->id) {
                Employee::query()->where('user_id', $legacyUser->id)->delete();
                $legacyUser->delete();
            }
            Employee::query()->where('email', 'smith.cody@yahoo.com')->where('user_id', '!=', $user->id)->delete();

            if (isset($this->command)) {
                $this->command->info('Seeded Cody admin user/employee.');
                if (!empty($useRandom)) {
                    $this->command->warn('Auto-generated credentials for production (rotate immediately):');
                    $this->command->info('Email: ' . $primaryEmail);
                    $this->command->info('Password: ' . $password);
                    $this->command->info('PIN: ' . $pinPlain);
                }
            }
        });
    }
}
