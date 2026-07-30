<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum LoyaltyPointTransactionTypeEnum: int
{
    case EARNED = 1;
    case REDEEMED = 2;
    case REFUNDED = 3;
    case REVERSED = 4;

    public function key(): string
    {
        return match ($this) {
            self::EARNED => 'earned',
            self::REDEEMED => 'redeemed',
            self::REFUNDED => 'refunded',
            self::REVERSED => 'reversed',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::EARNED => 'Earned',
            self::REDEEMED => 'Redeemed',
            self::REFUNDED => 'Refunded',
            self::REVERSED => 'Reversed',
        };
    }

    public static function getStatuses(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'key' => $case->key(),
            'label' => $case->label(),
        ], self::cases());
    }

    public static function getStatus(self $case): array
    {
        return Arr::first(
            self::getStatuses(),
            fn($item) => $item['value'] === $case->value
        );
    }
}
