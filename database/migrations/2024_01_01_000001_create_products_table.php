<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('quantity')->default(0); // Current stock quantity
            $table->integer('stock')->default(0); // Alias for quantity (backward compatibility)
            $table->string('room')->nullable();
            $table->string('sku')->nullable();
            $table->string('weight')->nullable();
            $table->decimal('thc', 5, 2)->nullable();
            $table->decimal('cbd', 5, 2)->nullable();
            $table->decimal('cbg', 5, 2)->nullable();
            $table->decimal('cbn', 5, 2)->nullable();
            $table->decimal('cbc', 5, 2)->nullable();
            $table->decimal('thc_mg', 8, 2)->nullable();
            $table->decimal('cbd_mg', 8, 2)->nullable();
            $table->decimal('cbg_mg', 8, 2)->nullable();
            $table->decimal('cbn_mg', 8, 2)->nullable();
            $table->decimal('cbc_mg', 8, 2)->nullable();
            $table->string('strain')->nullable();
            $table->string('metrc_tag')->nullable();
            $table->string('batch_id')->nullable();
            $table->date('harvest_date')->nullable();
            $table->string('source_harvest')->nullable();
            $table->string('supplier')->nullable();
            $table->string('supplier_uid')->nullable();
            $table->string('grower')->nullable();
            $table->string('vendor')->nullable();
            $table->string('farm')->nullable();
            $table->boolean('administrative_hold')->default(false);
            $table->boolean('is_tested')->default(false);
            $table->string('lab_name')->nullable();
            $table->date('test_date')->nullable();
            $table->boolean('contaminants_passed')->default(false);
            $table->date('packaged_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_untaxed')->default(false);
            $table->boolean('is_gls')->default(false);
            $table->decimal('minimum_price', 10, 2)->nullable();
            $table->decimal('weight_threshold', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('reorder_point')->nullable(); // Minimum stock level
            $table->enum('test_status', ['pending', 'passed', 'failed', 'exempt'])->nullable();
            $table->json('lab_results')->nullable(); // Store test results
            $table->text('batch_notes')->nullable();
            $table->timestamps();

            $table->index(['category', 'room']);
            $table->index('metrc_tag');
            $table->index('sku');
            $table->index(['is_gls', 'room']);
            $table->index(['quantity', 'reorder_point']); // For low stock queries
            $table->index('batch_id');
            $table->index(['expiration_date', 'administrative_hold']);
            $table->index(['test_status', 'is_tested']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
