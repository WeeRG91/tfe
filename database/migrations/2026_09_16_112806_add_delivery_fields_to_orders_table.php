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
            $table->string('delivery_type')
                ->nullable()
                ->after('type');
            $table->foreignId('delivery_company_id')
                ->nullable()
                ->after('delivery_type')
                ->constrained('delivery_companies')
                ->nullOnDelete();
            $table->string('delivery_company_name')
                ->nullable()
                ->after('delivery_company_id');
            $table
                ->date('delivery_date')
                ->nullable()
                ->after('delivery_company_name');

            $table->index([
                'delivery_type',
                'delivery_date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['delivery_type', 'delivery_date']);

            $table->dropConstrainedForeignId('delivery_company_id');

            $table->dropColumn(['delivery_type', 'delivery_company_name', 'delivery_date']);
        });
    }
};
