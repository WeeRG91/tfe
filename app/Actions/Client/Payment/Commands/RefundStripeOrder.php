<?php

namespace App\Actions\Client\Payment\Commands;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;
use Throwable;

class RefundStripeOrder
{
    /**
     * @throws Throwable
     */
    public function execute(Order $order): void
    {
        if ($order->payment_status !== PaymentStatusEnum::PAID) {
            throw ValidationException::withMessages([
                'order' => __('messages.orders.only_paid_can_be_refunded'),
            ]);
        }

        if (! $order->stripe_payment_intent_id) {
            throw ValidationException::withMessages([
                'order' => __('messages.orders.stripe_payment_not_found'),
            ]);
        }

        $order->update([
            'payment_status' => PaymentStatusEnum::REFUND_PENDING,
        ]);

        Stripe::setApiKey(config('services.stripe.secret_key'));

        try {
            $refund = Refund::create(
                [
                    'payment_intent' => $order->stripe_payment_intent_id,

                    'reason' => 'requested_by_customer',

                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'user_id' => (string) $order->user_id,
                    ],
                ],
                [
                    'idempotency_key' => "order-refund-{$order->id}",
                ],
            );

            $paymentStatus = PaymentStatusEnum::fromStripeRefundStatus(
                $refund->status
            );

            $order->update([
                'stripe_refund_id' => $refund->id,
                'stripe_refund_status' => $refund->status,
                'payment_status' => $paymentStatus,
            ]);
        } catch (ApiErrorException $e) {
            logger()->error('Unable to confirm Stripe refund state', [
                'order_id' => $order->id,
                'payment_intent_id' => $order->stripe_payment_intent_id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                __('messages.payment.refund_verification'),
                previous: $e,
            );
        }
    }
}
