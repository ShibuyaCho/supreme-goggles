<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // If you have lookup/table seeders that Users/Employees depend on,
        // call them here first, in order. e.g.:
        // $this->call([
        //     StatesTableSeeder::class,
        //     RolesTableSeeder::class,
        // ]);

        Schema::disableForeignKeyConstraints();
        DB::beginTransaction();

        try {
            if (App::environment('production')) {
                // Secure creds, env-driven emails → use in real deploys
                $this->call(ProductionUserSeeder::class);
            } else {
                // Deterministic local/demo creds
                $this->call(UserSeeder::class);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
