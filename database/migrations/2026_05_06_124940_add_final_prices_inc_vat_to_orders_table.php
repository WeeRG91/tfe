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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('final_food_inc_vat', 8, 2)->after('vat_food_amount')->default(0);
            $table->decimal('final_drinks_inc_vat', 8, 2)->after('vat_drinks_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('final_food_inc_vat');
            $table->dropColumn('final_drinks_inc_vat');
        });
    }
};
