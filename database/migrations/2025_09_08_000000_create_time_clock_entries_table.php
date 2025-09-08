<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('time_clock_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamp('clock_in');
            $table->timestamp('clock_out')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at')->nullable();
            $table->json('adjustments')->nullable();
            $table->string('source')->default('pos'); // pos|api|admin
            $table->timestamps();

            $table->index(['employee_id','clock_in']);
            $table->index(['employee_id','clock_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_clock_entries');
    }
};
