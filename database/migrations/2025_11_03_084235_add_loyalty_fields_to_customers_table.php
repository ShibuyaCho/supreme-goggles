<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'loyalty_member_id')) {
                $table->string('loyalty_member_id')->nullable()->after('is_medical_patient');
            }
            if (!Schema::hasColumn('customers', 'loyalty_points')) {
                $table->integer('loyalty_points')->default(0)->after('loyalty_member_id');
            }
            if (!Schema::hasColumn('customers', 'loyalty_join_date')) {
                $table->date('loyalty_join_date')->nullable()->after('loyalty_points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'loyalty_join_date')) {
                $table->dropColumn('loyalty_join_date');
            }
            if (Schema::hasColumn('customers', 'loyalty_points')) {
                $table->dropColumn('loyalty_points');
            }
            if (Schema::hasColumn('customers', 'loyalty_member_id')) {
                $table->dropColumn('loyalty_member_id');
            }
        });
    }
};
