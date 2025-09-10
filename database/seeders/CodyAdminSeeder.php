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
            $primaryEmail = 'smith.cody@yahoo.com';
            $legacyEmails = ['thccodys@gmail.com'];
            $password = 'Hms2019!';
            $pinPlain = '3732';
            $employeeCode = 'emp01';

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
        });
    }
}
