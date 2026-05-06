<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\User;

class HandleLoyaltyPoints
{
    /**
     * @param User $user
     * @param Order $order
     * @param int $usedPoints
     * @param float $finalTotal
     * @param bool $isCash
     * @return void
     */
    public function execute(
        User $user,
        Order $order,
        int $usedPoints,
        float $finalTotal,
        bool $isCash
    ): void
    {
        if ($usedPoints > 0) {
            LoyaltyPointTransaction::query()->create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'points' => -$usedPoints,
                'type' => 'redeemed',
                'description' => 'Used points for order #' . $order->order_number,
            ]);

            User::query()->update([
                'loyalty_points' => $user->loyalty_points - $usedPoints,
            ]);
        }

        if ($isCash) {
            $earnedPoints = floor($finalTotal * 3);

            LoyaltyPointTransaction::query()->create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'points' => $earnedPoints,
                'type' => 'earned',
                'description' => 'Points earned from order #' . $order->order_number,
            ]);

            User::query()->update([
                'loyalty_points' => $user->loyalty_points + $earnedPoints,
            ]);
        }
    }
}
