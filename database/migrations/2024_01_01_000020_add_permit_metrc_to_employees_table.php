<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'worker_permit')) {
                $table->string('worker_permit')->nullable()->after('hourly_rate');
            }
            if (!Schema::hasColumn('employees', 'metrc_api_key')) {
                $table->string('metrc_api_key')->nullable()->after('worker_permit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'metrc_api_key')) {
                $table->dropColumn('metrc_api_key');
            }
            if (Schema::hasColumn('employees', 'worker_permit')) {
                $table->dropColumn('worker_permit');
            }
        });
    }
};
