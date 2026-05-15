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
            $table->decimal('subtotal', 8, 2)->after('total_inc_vat')->default(0);
            $table->decimal('discount_rate', 5, 2)->after('subtotal')->default(0);
            $table->decimal('discount_total', 8, 2)->after('discount_rate')->default(0);
            $table->decimal('vat_food_rate', 5, 2)->after('discount_total')->default(12);
            $table->decimal('vat_food_amount', 8, 2)->after('vat_food_rate')->default(0);
            $table->decimal('vat_drinks_rate', 5, 2)->after('vat_food_amount')->default(21);
            $table->decimal('vat_drinks_amount', 8, 2)->after('vat_drinks_rate')->default(0);
            $table->decimal('vat_total', 8, 2)->after('vat_drinks_amount')->default(0);
            $table->decimal('delivery_fee', 8, 2)->after('vat_total')->default(0);
            $table->json('vat_breakdown')->after('delivery_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount_rate',
                'discount_total',
                'vat_food_rate',
                'vat_food_amount',
                'vat_drinks_rate',
                'vat_drinks_amount',
                'vat_total',
                'delivery_fee',
            ]);
        });
    }
};
