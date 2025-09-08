<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Make dates nullable
            if (Schema::hasColumn('deals', 'start_date')) {
                $table->date('start_date')->nullable()->change();
            }
            if (Schema::hasColumn('deals', 'end_date')) {
                $table->date('end_date')->nullable()->change();
            }
            // Ensure 'value' column exists and is decimal
            if (!Schema::hasColumn('deals', 'value') && Schema::hasColumn('deals', 'discount_value')) {
                $table->decimal('value', 8, 2)->nullable()->after('type');
            }
            // Relax type to string to avoid enum mismatch
            if (Schema::hasColumn('deals', 'type')) {
                $table->string('type', 50)->change();
            }
            // Ensure applicable_categories json exists
            if (!Schema::hasColumn('deals', 'applicable_categories') && Schema::hasColumn('deals', 'categories')) {
                $table->json('applicable_categories')->nullable()->after('end_date');
            }
            // Ensure medical_only exists
            if (!Schema::hasColumn('deals', 'medical_only')) {
                $table->boolean('medical_only')->default(false)->after('loyalty_only');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // No-op safe down
        });
    }
};
