<?php

namespace App\Actions\Client\Order\Commands;

use App\Actions\Client\Payment\Commands\finalizeRefundedOrder;
use App\Actions\Client\Payment\Commands\RefundStripeOrder;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\OrderCancelledBroadcast;
use App\Events\StatusOrderUpdated;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

readonly class CancelOrder
{
    public function __construct(
        private RefundStripeOrder $refundStripeOrder,
        private FinalizeRefundedOrder $finalizeRefundedOrder,
    ) {}

    /**
     * @param Order $order
     * @return void
     * @throws Throwable
     * @throws ValidationException
     */
    public function execute(Order $order): void
    {
        if (! in_array($order->status, [
            OrderStatusEnum::PENDING,
            OrderStatusEnum::CONFIRMED
        ], true)) {
            throw ValidationException::withMessages([
                'order' => __('messages.orders.cannot_cancel'),
            ]);
        }

        $isPaidStripeOrder =
            $order->payment_method !== PaymentMethodEnum::CASH
            && $order->payment_status === PaymentStatusEnum::PAID;

        if ($isPaidStripeOrder) {
            $this->refundStripeOrder->execute($order);

            $order->refresh();

            if ($order->stripe_refund_status !== 'succeeded') {
                throw ValidationException::withMessages([
                    'order' => __('messages.orders.refund_processing'),
                ]);
            }
        }

        if ($order->payment_method === PaymentMethodEnum::CASH) {
           $this->finalizeRefundedOrder->execute($order);
        }
    }
}
