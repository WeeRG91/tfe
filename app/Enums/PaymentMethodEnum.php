<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum PaymentMethodEnum: int
{
    case CASH = 1;
    case CARD = 2;

    public function key(): string
    {
        return match ($this) {
            self::CASH => 'cash',
            self::CARD => 'card',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
        };
    }

    public function translatedLabel(): string
    {
        return __('messages.enums.payment_method.' . $this->key());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getPaymentMethods(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'key' => $case->key(),
            'label' => $case->label(),
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
