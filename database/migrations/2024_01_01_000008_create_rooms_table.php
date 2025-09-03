<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('room_id')->unique();
            $table->enum('type', ['production', 'storage', 'processing', 'sales'])->default('storage');
            $table->boolean('is_active')->default(true);
            $table->integer('max_capacity')->nullable();
            $table->integer('current_stock')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
