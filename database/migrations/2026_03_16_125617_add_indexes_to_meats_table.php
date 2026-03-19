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
        Schema::table('meats', function (Blueprint $table) {
            $table->index('name');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meats', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('updated_at');
            $table->dropIndex('deleted_at');
        });
    }
};
