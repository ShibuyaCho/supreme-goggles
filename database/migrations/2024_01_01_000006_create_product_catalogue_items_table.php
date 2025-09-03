<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_catalogue_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('base_price', 10, 2);
            $table->decimal('base_cost', 10, 2)->nullable();
            $table->string('default_weight');
            $table->string('strain')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_template')->default(true);
            $table->string('location_id');
            $table->string('location_name');
            $table->boolean('is_active')->default(true);
            $table->string('default_room')->nullable();
            $table->string('supplier')->nullable();
            $table->string('vendor')->nullable();
            $table->string('grower')->nullable();
            $table->string('farm')->nullable();
            $table->string('default_sku_pattern')->nullable();
            $table->json('cannabinoid_profile')->nullable();
            $table->timestamps();

            $table->index(['location_id', 'is_active']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_catalogue_items');
    }
};
