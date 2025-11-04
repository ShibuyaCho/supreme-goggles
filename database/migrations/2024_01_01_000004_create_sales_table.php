<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique(); // Human-readable sale number
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->enum('customer_type', ['recreational', 'medical'])->default('recreational');
            $table->json('customer_info')->nullable(); // Store customer details for the sale
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'debit', 'credit', 'check', 'store_credit']);
            $table->string('payment_reference')->nullable(); // Last 4 digits for card payments
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->decimal('change_given', 10, 2)->nullable();
            $table->enum('status', ['pending', 'completed', 'voided', 'refunded'])->default('pending');
            $table->string('void_reason')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('employees');
            $table->timestamp('voided_at')->nullable();
            $table->integer('loyalty_points_earned')->default(0);
            $table->integer('loyalty_points_used')->default(0);
            $table->json('cart_items')->nullable(); // Store cart snapshot
            $table->json('applied_deals')->nullable(); // Store applied deals/promotions
            $table->decimal('tax_rate', 5, 4)->default(0.20); // Store tax rate used
            $table->text('notes')->nullable();
            $table->boolean('receipt_printed')->default(false);
            $table->boolean('synced_to_metrc')->default(false);
            $table->timestamp('metrc_sync_date')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['employee_id', 'created_at']);
            $table->index(['customer_id', 'created_at']);
            $table->index('sale_number');
            $table->index('payment_method');
            $table->index(['synced_to_metrc', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
