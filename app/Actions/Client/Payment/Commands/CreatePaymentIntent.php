<?php

namespace App\Actions\Client\Payment\Commands;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CreatePaymentIntent
{
    /**
     * @throws ApiErrorException
     */
    public function execute(Order $order): array
    {
        $user = auth()->user();

        abort_unless(
            $user && $order->user_id === $user->id,
            403,
            __('messages.payment.not_allowed'),
        );

        if ($order->payment_status === PaymentStatusEnum::PAID) {
            abort(409, __('messages.payment.already_paid'));
        }

        Stripe::setApiKey(config('services.stripe.secret_key'));

        try {
            if ($order->stripe_payment_intent_id) {
                $paymentIntent = PaymentIntent::retrieve(
                    $order->stripe_payment_intent_id
                );
            } else {
                $paymentIntent = PaymentIntent::create(
                    [
                        'amount' => (int) round(
                            $order->total_inc_vat * 100
                        ),

                        'currency' => 'eur',

                        'payment_method_types' => [
                            'card',
                            'bancontact',
                        ],

                        'metadata' => [
                            'order_id' => (string) $order->id,
                            'user_id' => (string) $order->user_id,
                            'order_number' => $order->order_number,
                        ],
                    ],
                    [
                        'idempotency_key' => "order-payment-{$order->id}",
                    ],
                );

                $order->update([
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ]);
            }
        } catch (ApiErrorException $e) {
            logger()->error('Stripe PaymentIntent error', [
                'order_id' => $order->id,
                'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                __('messages.payment.initialization_failed'),
                previous: $e,
            );
        }

        return [
            'message' => __('messages.payment.success'),
            'client_secret' => $paymentIntent->client_secret,
        ];
    }
}
