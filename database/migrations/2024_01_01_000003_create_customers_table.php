<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name'); // Full name or display name
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->json('address')->nullable(); // Store address as JSON
            $table->string('member_id')->unique()->nullable(); // Loyalty member ID
            $table->string('medical_card_number')->nullable();
            $table->date('medical_card_issue_date')->nullable();
            $table->date('medical_card_expiration_date')->nullable();
            $table->string('medical_card_physician')->nullable();
            $table->enum('customer_type', ['recreational', 'medical', 'both'])->default('recreational');
            $table->boolean('is_patient')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_oregon_resident')->default(true);
            $table->boolean('is_veteran')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('data_retention_consent')->default(false);
            $table->text('notes')->nullable();
            $table->date('join_date')->nullable();
            $table->timestamp('last_visit')->nullable();
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->integer('total_visits')->default(0);
            $table->integer('loyalty_points')->default(0); // Current points balance
            $table->integer('points_balance')->default(0); // Alias for loyalty_points
            $table->integer('points_earned')->default(0); // Lifetime points earned
            $table->integer('points_redeemed')->default(0); // Lifetime points redeemed
            $table->date('loyalty_join_date')->nullable();
            $table->enum('loyalty_tier', ['Bronze', 'Silver', 'Gold', 'Platinum'])->default('Bronze');
            $table->enum('tier', ['Bronze', 'Silver', 'Gold', 'Platinum'])->default('Bronze'); // Alias
            $table->json('preferred_products')->nullable(); // Store preferred product categories/IDs
            $table->json('daily_purchases')->nullable(); // Oregon limits tracking
            $table->timestamps();

            $table->index('member_id');
            $table->index('medical_card_number');
            $table->index(['phone', 'email']);
            $table->index(['customer_type', 'is_active']);
            $table->index(['loyalty_tier', 'total_spent']);
            $table->index('last_visit');
            $table->index(['is_patient', 'medical_card_expiration_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
