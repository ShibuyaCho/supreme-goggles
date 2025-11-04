<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('metrc_configs', function (Blueprint $table) {
            $table->string('integrator_key_last4', 4)->nullable();
            $table->string('user_key_last4', 4)->nullable();
        });
    }

    public function down()
    {
        Schema::table('metrc_configs', function (Blueprint $table) {
            $table->dropColumn(['integrator_key_last4', 'user_key_last4']);
        });
    }
};
