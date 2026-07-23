<?php

namespace App\Enums;

enum LoyaltyPointTransactionTypeEnum: int
{
    case EARNED = 1;
    case REDEEMED = 2;
    case REFUNDED = 3;
    case REVERSED = 4;

    public function label(): string
    {
        return match ($this) {
            self::EARNED => 'Earned',
            self::REDEEMED => 'Redeemed',
            self::REFUNDED => 'Refunded',
            self::REVERSED => 'Reversed',
        };
    }
}
