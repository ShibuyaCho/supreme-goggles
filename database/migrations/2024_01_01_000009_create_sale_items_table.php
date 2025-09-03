<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->string('product_name'); // Store product name at time of sale
            $table->string('product_category');
            $table->string('product_sku')->nullable();
            $table->decimal('quantity', 8, 3);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_type')->nullable(); // 'percentage' or 'fixed'
            $table->string('discount_reason')->nullable();
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('cost_per_unit', 10, 2)->nullable();
            $table->decimal('margin_amount', 10, 2)->nullable();
            $table->string('metrc_tag')->nullable();
            $table->string('batch_id')->nullable();
            $table->decimal('weight_sold', 8, 3)->nullable();
            $table->decimal('thc_content', 5, 2)->nullable();
            $table->decimal('cbd_content', 5, 2)->nullable();
            $table->boolean('lab_tested')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'product_id']);
            $table->index('metrc_tag');
            $table->index('batch_id');
            $table->index('product_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
