<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // products
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'store_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('store_id')->default('default')->index();
            });
            try { DB::table('products')->whereNull('store_id')->update(['store_id' => 'default']); } catch (\Throwable $e) {}
        }
        // employees
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'store_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('store_id')->default('default')->index();
            });
            try { DB::table('employees')->whereNull('store_id')->update(['store_id' => 'default']); } catch (\Throwable $e) {}
        }
        // sales
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'store_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('store_id')->default('default')->index();
            });
            try { DB::table('sales')->whereNull('store_id')->update(['store_id' => 'default']); } catch (\Throwable $e) {}
        }
        // deals
        if (Schema::hasTable('deals') && !Schema::hasColumn('deals', 'store_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->string('store_id')->default('default')->index();
            });
            try { DB::table('deals')->whereNull('store_id')->update(['store_id' => 'default']); } catch (\Throwable $e) {}
        }
        // rooms
        if (Schema::hasTable('rooms') && !Schema::hasColumn('rooms', 'store_id')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('store_id')->default('default')->index();
            });
            try { DB::table('rooms')->whereNull('store_id')->update(['store_id' => 'default']); } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'store_id')) {
            Schema::table('products', function (Blueprint $table) { $table->dropColumn('store_id'); });
        }
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'store_id')) {
            Schema::table('employees', function (Blueprint $table) { $table->dropColumn('store_id'); });
        }
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'store_id')) {
            Schema::table('sales', function (Blueprint $table) { $table->dropColumn('store_id'); });
        }
        if (Schema::hasTable('deals') && Schema::hasColumn('deals', 'store_id')) {
            Schema::table('deals', function (Blueprint $table) { $table->dropColumn('store_id'); });
        }
        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'store_id')) {
            Schema::table('rooms', function (Blueprint $table) { $table->dropColumn('store_id'); });
        }
    }
};
