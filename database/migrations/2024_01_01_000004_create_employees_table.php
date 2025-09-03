<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->enum('role', ['manager', 'cashier', 'budtender', 'inventory', 'admin'])->default('cashier');
            $table->json('permissions')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('schedule')->nullable();
            $table->json('availability')->nullable();
            $table->text('notes')->nullable();
            $table->string('pin')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index(['is_active', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
