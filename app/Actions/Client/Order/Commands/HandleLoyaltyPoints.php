<?php

namespace App\Actions\Client\Order\Commands;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class HandleLoyaltyPoints
{
    /**
     * @param User $user
     * @param Order $order
     * @param int $usedPoints
     * @param float $finalTotal
     * @param bool $isCash
     * @return void
     * @throws ValidationException
     */
    public function execute(
        User $user,
        Order $order,
        int $usedPoints,
        float $finalTotal,
        bool $isCash
    ): void
    {
        $lockedUser = User::query()
            ->whereKey($user->id)
            ->lockForUpdate()
            ->firstOrFail();

        $pointBalance = $this->getPointBalance($lockedUser);

        if ($usedPoints > $pointBalance) {
            throw ValidationException::withMessages([
                'used_points' => 'You do not have enough loyalty points.',
            ]);
        }

        if ($usedPoints > 0) {
            LoyaltyPointTransaction::query()->create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'points' => $usedPoints,
                'type' => LoyaltyPointTransactionTypeEnum::REDEEMED->value,
                'description' => 'Used points for order #' . $order->order_number,
            ]);
        }

        if ($isCash) {
            $earnedPoints = floor($finalTotal * 3);

            LoyaltyPointTransaction::query()->create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'points' => $earnedPoints,
                'type' => LoyaltyPointTransactionTypeEnum::EARNED->value,
                'description' => 'Points earned from order #' . $order->order_number,
            ]);
        }
    }

    private function getPointBalance(User $user): int
    {
        return (int) $user->loyaltyPointTransactions()
            ->get()
            ->sum(
                fn (LoyaltyPointTransaction $transaction) =>
                    match ($transaction->type) {
                        LoyaltyPointTransactionTypeEnum::EARNED,
                        LoyaltyPointTransactionTypeEnum::REFUNDED, =>
                            $transaction->points,

                        LoyaltyPointTransactionTypeEnum::REDEEMED,
                        LoyaltyPointTransactionTypeEnum::REVERSED =>
                        -$transaction->points,
                    }
            );
    }
}
