<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('sale_id')->nullable()->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->enum('type', ['earned', 'redeemed', 'adjusted', 'expired', 'bonus']);
            $table->integer('points_before');
            $table->integer('points_changed');
            $table->integer('points_after');
            $table->decimal('dollar_value', 10, 2)->nullable(); // Value in dollars for redemptions
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->date('expires_at')->nullable(); // For points that expire
            $table->timestamps();

            $table->index(['customer_id', 'type']);
            $table->index('sale_id');
            $table->index('created_at');
            $table->index(['expires_at', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
