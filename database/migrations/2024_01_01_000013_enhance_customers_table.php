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
        Schema::table('customers', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('customers', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('customers', 'join_date')) {
                $table->date('join_date')->nullable();
            }
            if (!Schema::hasColumn('customers', 'points_earned')) {
                $table->integer('points_earned')->default(0);
            }
            if (!Schema::hasColumn('customers', 'points_redeemed')) {
                $table->integer('points_redeemed')->default(0);
            }
            if (!Schema::hasColumn('customers', 'tier')) {
                $table->string('tier')->default('Bronze');
            }
            if (!Schema::hasColumn('customers', 'is_medical_patient')) {
                $table->boolean('is_medical_patient')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'join_date',
                'points_earned',
                'points_redeemed',
                'tier',
                'is_medical_patient'
            ]);
        });
    }
};
