<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('deals', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('deals', 'frequency')) {
                $table->enum('frequency', ['always', 'daily', 'weekly', 'monthly', 'custom'])->default('always');
            }
            if (!Schema::hasColumn('deals', 'day_of_week')) {
                $table->string('day_of_week')->nullable();
            }
            if (!Schema::hasColumn('deals', 'day_of_month')) {
                $table->integer('day_of_month')->nullable();
            }
            if (!Schema::hasColumn('deals', 'minimum_purchase_type')) {
                $table->enum('minimum_purchase_type', ['dollars', 'grams'])->default('dollars');
            }
            if (!Schema::hasColumn('deals', 'email_customers')) {
                $table->boolean('email_customers')->default(false);
            }
            if (!Schema::hasColumn('deals', 'loyalty_only')) {
                $table->boolean('loyalty_only')->default(false);
            }
            if (!Schema::hasColumn('deals', 'medical_only')) {
                $table->boolean('medical_only')->default(false);
            }
            if (!Schema::hasColumn('deals', 'current_uses')) {
                $table->integer('current_uses')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'frequency',
                'day_of_week',
                'day_of_month',
                'minimum_purchase_type',
                'email_customers',
                'loyalty_only',
                'medical_only',
                'current_uses'
            ]);
        });
    }
};
