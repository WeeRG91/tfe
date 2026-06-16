<?php

namespace App\Actions\Client\Payment\Commands;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\OrderPlacedBroadcast;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\User;
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
        $user = User::query()->findOrFail($paymentIntent->metadata->user_id);

        $order->update([
            'payment_status' => PaymentStatusEnum::PAID,
            'status' => OrderStatusEnum::CONFIRMED,
            'confirmed_at' => now(),
            'paid_at' => now(),
        ]);

        $order->refresh();

        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

        if ($order->confirmed_at !== null) {
            event(new OrderPlacedBroadcast($order));
        }

        $earnedPoints = floor($order->total_inc_vat * 3);

        LoyaltyPointTransaction::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'points' => $earnedPoints,
            'type' => LoyaltyPointTransactionTypeEnum::EARNED->value,
            'description' => 'Points earned from order #' . $order->order_number,
        ]);

        User::query()->update([
            'loyalty_points' => $user->loyalty_points + $earnedPoints,
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
