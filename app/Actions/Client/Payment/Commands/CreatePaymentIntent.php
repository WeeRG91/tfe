<?php

namespace App\Actions\Client\Payment\Commands;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CreatePaymentIntent
{
    /**
     * @param Order $order
     * @return array
     * @throws ApiErrorException
     */
    public function execute(Order $order): array
    {
        if ($order->user_id !== auth()->user()->id) {
            return [
                'message' => 'Unauthorized',
                'client_secret' => null,
            ];
        }

        if ($order->payment_status === PaymentStatusEnum::PAID) {
            return [
                'message' => 'Order already paid',
                'client_secret' => null,
            ];
        }

        Stripe::setApiKey(config('services.stripe.secret_key'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($order->total * 100),
                'currency' => 'eur',
                'payment_method_types' => ['card', 'bancontact'],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
        } catch (ApiErrorException $e) {
            logger()->error('Stripe error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Unable to initialize payment');
        }

        return [
            'message' => 'Payment success',
            'client_secret' => $paymentIntent->client_secret,
        ];
    }
}
