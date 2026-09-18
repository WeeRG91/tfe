<?php

namespace App\Actions\Client\Payment\Commands;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Events\OrderCancelledBroadcast;
use App\Events\StatusOrderUpdated;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class finalizeRefundedOrder
{
    /**
     * @throws Throwable
     */
    public function execute(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status !== OrderStatusEnum::CANCELLED) {
                $order->update([
                    'status' => OrderStatusEnum::CANCELLED,
                    'cancelled_at' => now(),
                ]);
            }

            $this->refundRedeemedPoints($order);
            $this->reverseEarnedPoints($order);

            event(new StatusOrderUpdated($order));

            if ($order->user_id === auth()->user()->id) {
                event(new OrderCancelledBroadcast($order));
            }
        });
    }

    public function refundRedeemedPoints(Order $order): void
    {
        $redeemedTransaction =
            LoyaltyPointTransaction::query()
                ->where('order_id', $order->id)
                ->where(
                    'type',
                    LoyaltyPointTransactionTypeEnum::REDEEMED->value
                )
                ->first();

        if (! $redeemedTransaction) {
            return;
        }

        LoyaltyPointTransaction::query()->firstOrCreate(
            [
                'order_id' => $order->id,
                'type' => LoyaltyPointTransactionTypeEnum::REFUNDED->value,
            ],
            [
                'user_id' => $order->user_id,
                'points' => $redeemedTransaction->points,
                'description' => "Points refunded for order #{$order->order_number}",
            ],
        );
    }

    public function reverseEarnedPoints(Order $order): void
    {
        $earnedTransaction =
            LoyaltyPointTransaction::query()
                ->where('order_id', $order->id)
                ->where(
                    'type',
                    LoyaltyPointTransactionTypeEnum::EARNED->value
                )
                ->first();

        if (! $earnedTransaction) {
            return;
        }

        LoyaltyPointTransaction::query()->firstOrCreate(
            [
                'order_id' => $order->id,
                'type' => LoyaltyPointTransactionTypeEnum::REVERSED->value,
            ],
            [
                'user_id' => $order->user_id,
                'points' => $earnedTransaction->points,
                'description' => "Points reversed for refunded order #{$order->order_number}",
            ],
        );
    }
}
