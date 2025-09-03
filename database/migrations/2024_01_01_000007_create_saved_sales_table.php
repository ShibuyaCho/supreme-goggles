<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_sales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_name');
            $table->enum('customer_type', ['rec', 'medical'])->default('rec');
            $table->json('customer_info')->nullable();
            $table->json('cart_items');
            $table->json('cart_discount')->nullable();
            $table->json('selected_loyalty_customer')->nullable();
            $table->integer('total_items');
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_sales');
    }
};
