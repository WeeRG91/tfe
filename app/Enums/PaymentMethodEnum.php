<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum PaymentMethodEnum: int
{
    case CASH = 1;
    case CARD = 2;

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getColor(self $case): string
    {
        return match ($case) {
            self::CASH => 'bg-green-500/75',
            self::CARD => 'bg-blue-500/75',
        };
    }

    public static function getPaymentMethods(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => self::getColor($case),
        ], self::cases());
    }

    public static function getPaymentMethod(self $case):array
    {
        return Arr::first(
            self::getPaymentMethods(),
            fn($item) => $item['value'] === $case->value,
        );
    }
}
