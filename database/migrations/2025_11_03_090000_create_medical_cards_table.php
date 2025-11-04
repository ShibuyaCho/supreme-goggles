<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_cards', function (Blueprint $table) {
            $table->id();

            // Link each card to one customer
            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            // Basic identification details
            $table->string('card_number')->nullable()->index();
            $table->boolean('is_patient')->default(true); // false => caregiver
            $table->string('state')->nullable();          // optional state/region field
            $table->date('issue_date')->nullable();
            $table->date('expires_at')->nullable();

            // Administrative metadata
            $table->string('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_cards');
    }
};
