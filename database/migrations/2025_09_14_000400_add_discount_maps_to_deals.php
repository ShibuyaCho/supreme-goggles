<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'category_discounts')) {
                $table->json('category_discounts')->nullable()->after('applicable_categories');
            }
            if (!Schema::hasColumn('deals', 'item_discounts')) {
                $table->json('item_discounts')->nullable()->after('specific_items');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 'category_discounts')) {
                $table->dropColumn('category_discounts');
            }
            if (Schema::hasColumn('deals', 'item_discounts')) {
                $table->dropColumn('item_discounts');
            }
        });
    }
};
