<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'fixed_price']);
            $table->decimal('adjustment_value', 10, 2); // Percentage, fixed amount, or fixed price
            $table->json('applicable_categories')->nullable(); // Which product categories this applies to
            $table->decimal('minimum_quantity', 8, 3)->nullable(); // Minimum quantity for tier pricing
            $table->decimal('maximum_quantity', 8, 3)->nullable(); // Maximum quantity for tier pricing
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority tiers apply first
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->json('customer_types')->nullable(); // ['recreational', 'medical'] or null for all
            $table->json('loyalty_tiers')->nullable(); // Which loyalty tiers this applies to
            $table->timestamps();

            $table->index(['is_active', 'priority']);
            $table->index(['valid_from', 'valid_until']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
    }
};
