<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales','store_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('store_id')->nullable()->after('employee_id');
                $table->index(['store_id','created_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales','store_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex(['store_id','created_at']);
                $table->dropColumn('store_id');
            });
        }
    }
};
