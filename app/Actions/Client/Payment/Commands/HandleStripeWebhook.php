<?php

namespace App\Actions\Client\Payment\Commands;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class HandleStripeWebhook
{
    /**
     * @param Request $request
     * @return string[]
     */
    public function execute(Request $request): array
    {
        $payload = $request->getContent();
        $signature = $request->header('stripe-signature');

        if (!$signature) {
            return ['message' => 'Missing signature'];
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return [
                'message' => 'Invalid signature: ' . $e->getMessage(),
            ];
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->handleSuccess($event->data->object),
            'payment_intent.payment_failed' => $this->handleFailure($event->data->object),
            default => null,
        };

        return [
            'message' => 'Payment confirmed',
        ];
    }

    /**
     * @param $paymentIntent
     * @return void
     */
    private function handleSuccess($paymentIntent): void
    {
        $order = Order::query()->findOrFail($paymentIntent->metadata->order_id);

        $order->update([
            'payment_status' => PaymentStatusEnum::PAID,
            'status' => OrderStatusEnum::CONFIRMED,
            'confirmed_at' => now(),
            'paid_at' => now(),
        ]);

        $earnedPoints = floor($order->total * 3);

        LoyaltyPointTransaction::query()->create([
            'user_id' => auth()->user()->id,
            'order_id' => $order->id,
            'points' => $earnedPoints,
            'type' => 'earned',
            'description' => 'Points earned from order #' . $order->order_number,
        ]);
    }

    /**
     * @param $paymentIntent
     * @return void
     */
    private function handleFailure($paymentIntent): void
    {
        $order = Order::query()->findOrFail($paymentIntent->metadata->order_id);

        $order->update([
            'payment_status' => PaymentStatusEnum::FAILED,
        ]);
    }
}
