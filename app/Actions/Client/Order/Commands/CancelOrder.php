<?php

namespace App\Actions\Client\Order\Commands;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class CancelOrder
{

    /**
     * @param User $user
     * @param Order $order
     * @return void
     * @throws Throwable
     */
    public function execute(User $user, Order $order): void
    {
        DB::transaction(function () use ($user, $order) {

            $order->update([
                'status' => OrderStatusEnum::CANCELLED->value,
                'cancelled_at' => now(),
            ]);


            $transaction = LoyaltyPointTransaction::query()
                ->where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->where(
                    'type',
                    LoyaltyPointTransactionTypeEnum::REDEEMED->value
                )
                ->first();


            if ($transaction) {
                LoyaltyPointTransaction::query()->create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'points' => $transaction->points,
                    'type' => LoyaltyPointTransactionTypeEnum::REFUNDED->value,
                    'description' =>
                        'Points refunded for order #' . $order->order_number,
                ]);
            }
        });
    }
}
