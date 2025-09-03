<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['percentage', 'fixed', 'bogo', 'bulk'])->default('percentage');
            $table->decimal('discount_value', 8, 2);
            $table->json('categories')->nullable();
            $table->json('specific_items')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->enum('frequency', ['always', 'daily', 'weekly', 'monthly', 'custom'])->default('always');
            $table->string('day_of_week')->nullable();
            $table->integer('day_of_month')->nullable();
            $table->boolean('email_customers')->default(false);
            $table->boolean('loyalty_only')->default(false);
            $table->decimal('minimum_purchase', 10, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
            $table->index('frequency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
