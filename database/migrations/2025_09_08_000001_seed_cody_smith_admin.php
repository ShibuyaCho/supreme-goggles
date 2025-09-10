<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('employees')) return;

        DB::transaction(function(){
            $primaryEmail = 'thccodys@gmail.com';
            $legacyEmails = ['smith.cody@yahoo.com'];
            $empIdentifier = 'emp001';
            $first = 'Cody';
            $last = 'Smith';
            $password = 'Hms2019!';
            $pin = '3732';

            // Create or update user (migrate legacy email -> primary)
            $userId = DB::table('users')->where('email', $primaryEmail)->value('id');
            if (!$userId) {
                $userId = DB::table('users')->whereIn('email', $legacyEmails)->value('id');
            }
            if ($userId) {
                DB::table('users')->where('id', $userId)->update([
                    'name' => $first.' '.$last,
                    'email' => $primaryEmail,
                    'password' => Hash::make($password),
                    'role' => 'admin',
                    'permissions' => json_encode(['*']),
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name' => $first.' '.$last,
                    'email' => $primaryEmail,
                    'password' => Hash::make($password),
                    'role' => 'admin',
                    'permissions' => json_encode(['*']),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create or update employee
            $empId = DB::table('employees')->where('employee_id', $empIdentifier)->value('id');
            $empPayload = [
                'user_id' => $userId,
                'employee_id' => $empIdentifier,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $primaryEmail,
                'role' => 'admin',
                'permissions' => json_encode(['*']),
                'hourly_rate' => 0,
                'hire_date' => now()->toDateString(),
                'is_active' => true,
                'pin' => Hash::make($pin),
                'updated_at' => now(),
            ];
            if ($empId) {
                DB::table('employees')->where('id', $empId)->update($empPayload);
            } else {
                $empPayload['created_at'] = now();
                $empId = DB::table('employees')->insertGetId($empPayload);
            }

            // Link user to employee
            DB::table('users')->where('id', $userId)->update(['employee_id' => $empId]);

            // Cleanup: remove any separate user/employee with legacy email
            $legacyUserId = DB::table('users')->where('email', 'smith.cody@yahoo.com')->where('id', '!=', $userId)->value('id');
            if ($legacyUserId) {
                DB::table('employees')->where('user_id', $legacyUserId)->delete();
                DB::table('users')->where('id', $legacyUserId)->delete();
            }
            DB::table('employees')->where('email', 'smith.cody@yahoo.com')->where('user_id', '!=', $userId)->delete();
        });
    }

    public function down(): void
    {
        // No-op: do not delete admin user on rollback in production
    }
};
