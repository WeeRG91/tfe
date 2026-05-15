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
            $table->dropColumn('discount_rate');
            $table->dropColumn('vat_food_rate');
            $table->dropColumn('vat_food_amount');
            $table->dropColumn('vat_drinks_rate');
            $table->dropColumn('vat_drinks_amount');
            $table->dropColumn('net_total');
            $table->dropColumn('final_food_inc_vat');
            $table->dropColumn('final_drinks_inc_vat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_rate', 5, 2)->after('subtotal')->default(0);
            $table->decimal('vat_food_rate', 5, 2)->after('discount_total')->default(12);
            $table->decimal('vat_food_amount', 8, 2)->after('vat_food_rate')->default(0);
            $table->decimal('vat_drinks_rate', 5, 2)->after('vat_food_amount')->default(21);
            $table->decimal('vat_drinks_amount', 8, 2)->after('vat_drinks_rate')->default(0);
            $table->decimal('net_total', 8, 2)->after('delivery_fee')->default(0);
            $table->decimal('final_food_inc_vat', 8, 2)->after('vat_food_amount')->default(0);
            $table->decimal('final_drinks_inc_vat', 8, 2)->after('vat_drinks_amount')->default(0);
        });
    }
};
