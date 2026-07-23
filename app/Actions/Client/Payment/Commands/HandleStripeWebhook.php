<?php

namespace App\Actions\Client\Payment\Commands;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\OrderPlacedBroadcast;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\StripeWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;

readonly class HandleStripeWebhook
{
    public function __construct(
        private FinalizeRefundedOrder
        $finalizeRefundedOrder,
    ) {}

    /**
     * @param Request $request
     * @return string[]
     * @throws Throwable
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
            'payment_intent.succeeded' => $this->handleSuccess(
                $event->id,
                $event->type,
                $event->data->object,
            ),

            'payment_intent.payment_failed' => $this->handleFailure(
                $event->id,
                $event->type,
                $event->data->object,
            ),

            'refund.updated',
            'refund.failed' => $this->handleRefundUpdate(
                $event->id,
                $event->type,
                $event->data->object,
            ),

            default => null,
        };

        return [
            'message' => 'Payment confirmed',
        ];
    }

    /**
     * @param string $eventId
     * @param string $eventType
     * @param object $paymentIntent
     * @return void
     * @throws Throwable
     */
    private function handleSuccess(
        string $eventId,
        string $eventType,
        object $paymentIntent
    ): void
    {
        DB::transaction(function () use (
            $eventId,
            $eventType,
            $paymentIntent
        ) {
            $alreadyProcessed = StripeWebhookEvent::query()
                ->where('stripe_event_id', $eventId)
                ->exists();

            if ($alreadyProcessed) {
                return;
            }

            $order = Order::query()
                ->whereKey((int) $paymentIntent->metadata->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $order->stripe_payment_intent_id === $paymentIntent->id,
                400,
                'PaymentIntent does not belong to this order.',
            );

            abort_unless(
                $order->user_id ===
                (int) $paymentIntent->metadata->user_id,
                400,
                'PaymentIntent user does not match the order.',
            );

            abort_unless(
                $paymentIntent->currency === 'eur'
                && $paymentIntent->amount_received ===
                (int) round($order->total_inc_vat * 100),
                400,
                'Payment amount does not match the order.',
            );

            if ($order->payment_status !== PaymentStatusEnum::PAID) {
                $order->update([
                    'payment_status' => PaymentStatusEnum::PAID,
                    'status' => OrderStatusEnum::CONFIRMED,
                    'confirmed_at' => now(),
                    'paid_at' => now(),
                ]);

                $earnedPoints = (int) floor($order->total_inc_vat * 3);

                LoyaltyPointTransaction::query()->firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'type' =>
                            LoyaltyPointTransactionTypeEnum::EARNED->value,
                    ],
                    [
                        'user_id' => $order->user_id,
                        'points' => $earnedPoints,
                        'description' =>
                            "Points earned from order #{$order->order_number}",
                    ],
                );
            }

            StripeWebhookEvent::query()->create([
                'stripe_event_id' => $eventId,
                'event_type' => $eventType,
            ]);
        });

        $order = Order::query()
            ->with([
                'user',
                'items.item',
                'items.meat',
                'items.removedIngredients',
                'address',
            ])
            ->findOrFail((int) $paymentIntent->metadata->order_id);

        event(new OrderPlacedBroadcast($order));
    }


    /**
     * @param string $eventId
     * @param string $eventType
     * @param object $paymentIntent
     * @return void
     * @throws Throwable
     */
    private function handleFailure(
        string $eventId,
        string $eventType,
        object $paymentIntent
    ): void
    {
        DB::transaction(function () use (
            $eventId,
            $eventType,
            $paymentIntent
        ) {
            $now = now();

            $eventInserted = StripeWebhookEvent::query()
                ->insertOrIgnore([
                    'stripe_event_id' => $eventId,
                    'event_type' => $eventType,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($eventInserted === 0) {
                return;
            }

            $orderId = $paymentIntent->metadata->order_id ?? null;
            $userId = $paymentIntent->metadata->user_id ?? null;

            abort_unless(
                is_numeric($orderId) && is_numeric($userId),
                400,
                'PaymentIntent metadata is invalid.',
            );

            $order = Order::query()
                ->whereKey((int) $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $order->stripe_payment_intent_id === $paymentIntent->id,
                400,
                'PaymentIntent does not belong to this order.',
            );

            abort_unless(
                $order->user_id === (int) $userId,
                400,
                'PaymentIntent user does not match the order.',
            );

            abort_unless(
                $paymentIntent->currency === 'eur'
                && $paymentIntent->amount ===
                (int) round($order->total_inc_vat * 100),
                400,
                'Payment amount does not match the order.',
            );

            if ($order->payment_status === PaymentStatusEnum::PAID) {
                return;
            }

            $order->update([
                'payment_status' => PaymentStatusEnum::FAILED,
            ]);
        });
    }

    /**
     * @param string $eventId
     * @param string $eventType
     * @param object $refund
     * @return void
     * @throws Throwable
     */
    private function handleRefundUpdate(
        string $eventId,
        string $eventType,
        object $refund
    ): void
    {
        DB::transaction(
            function () use (
                $eventId,
                $eventType,
                $refund
            ) {
                $now = now();

                $eventInserted = StripeWebhookEvent::query()
                    ->insertOrIgnore([
                        'stripe_event_id' => $eventId,
                        'event_type' => $eventType,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                if ($eventInserted === 0) {
                    return;
                }

                $orderId = $refund->metadata->order_id ?? null;

                abort_unless(
                    is_numeric($orderId),
                    400,
                    'Refund metadata is invalid.',
                );

                $order = Order::query()
                    ->whereKey((int) $orderId)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    $order->stripe_payment_intent_id ===
                    $refund->payment_intent,
                    400,
                    'Refund does not belong to this order.',
                );

                abort_unless(
                    $order->stripe_refund_id === null
                    || $order->stripe_refund_id === $refund->id,
                    400,
                    'Refund ID does not match the order.',
                );

                $paymentStatus =
                    PaymentStatusEnum::fromStripeRefundStatus(
                        $refund->status
                    );

                $order->update([
                    'stripe_refund_id' => $refund->id,
                    'stripe_refund_status' => $refund->status,
                    'payment_status' => $paymentStatus,
                ]);

                if ($paymentStatus === PaymentStatusEnum::REFUNDED) {
                    $this->finalizeRefundedOrder->execute($order);
                }
            }
        );
    }
}
