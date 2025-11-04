<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('metrc_configs', function (Blueprint $table) {
            $table->id();
            // If you have multi-tenant: add $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('environment', ['sandbox','production'])->default('sandbox');
            $table->string('base_url'); // e.g. https://api-mi.metrc.com or your sandbox URL
            $table->text('integrator_key');   // encrypted
            $table->text('user_key');         // encrypted
            $table->string('facility_license')->nullable(); // also often required by calls
            $table->boolean('enabled_live_sync')->default(false);
            $table->boolean('is_active')->default(true);    // which row to use if you store multiple configs
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable(); // 'ok' | 'fail'
            $table->text('last_test_message')->nullable();
            $table->timestamps();

            $table->index(['environment', 'is_active']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('metrc_configs');
    }
};
?>