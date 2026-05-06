<?php

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
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
            $table->string('order_number')->after('id');
            $table->string('pickup_name')->nullable()->after('pickup_time');
            $table->string('pickup_phone')->nullable()->after('pickup_name');
            $table->unsignedInteger('payment_method')->default(PaymentMethodEnum::CASH->value)->after('status');
            $table->unsignedInteger('payment_status')->default(PaymentStatusEnum::PENDING->value)->after('payment_method');
            $table->timestamp('confirmed_at')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('confirmed_at');
            $table->timestamp('delivered_at')->nullable()->after('paid_at');
            $table->timestamp('completed_at')->nullable()->after('delivered_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->text('notes')->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_number');
            $table->dropColumn('pickup_name');
            $table->dropColumn('pickup_phone');
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_status');
            $table->dropColumn('confirmed_at');
            $table->dropColumn('paid_at');
            $table->dropColumn('delivered_at');
            $table->dropColumn('completed_at');
            $table->dropColumn('cancelled_at');
            $table->dropColumn('notes');
        });
    }
};
