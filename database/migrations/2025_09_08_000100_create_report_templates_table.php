<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('report_type');
            $table->string('format')->default('pdf');
            $table->boolean('include_charts')->default(false);
            $table->string('orientation')->default('portrait');
            $table->string('paper_size')->default('a4');
            $table->json('config');
            $table->timestamps();

            $table->index(['user_id','report_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
