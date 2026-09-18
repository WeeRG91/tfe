<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum OrderTypeEnum: int
{
    case DINE_IN = 1;
    case TAKEAWAY = 2;
    case DELIVERY = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function key(): string
    {
        return match ($this) {
            self::DINE_IN => 'dinein',
            self::TAKEAWAY => 'takeaway',
            self::DELIVERY => 'delivery',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DINE_IN => 'Dine-in',
            self::TAKEAWAY => 'Takeaway',
            self::DELIVERY => 'Delivery',
        };
    }

    public function translatedLabel(): string
    {
        return __('messages.enums.order_type.'.$this->key());
    }

    public static function getTypes(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'key' => $case->key(),
            'label' => $case->label(),
        ], self::cases());
    }

    public static function getType(self $case): array
    {
        return Arr::first(
            self::getTypes(),
            fn ($item) => $item['value'] === $case->value
        );
    }
}
