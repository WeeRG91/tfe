<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum OrderStatusEnum: int
{
    case PENDING = 1;
    case CONFIRMED = 2;
    case PREPARING = 3;
    case READY = 4;
    case DELIVERING = 5;
    case COMPLETED = 6;
    case CANCELLED = 7;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function key(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::CONFIRMED => 'confirmed',
            self::PREPARING => 'preparing',
            self::READY => 'ready',
            self::DELIVERING => 'delivering',
            self::COMPLETED => 'completed',
            self::CANCELLED => 'cancelled',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PREPARING => 'Preparing',
            self::READY => 'Ready',
            self::DELIVERING => 'Delivering',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function translatedLabel(): string
    {
        return __('messages.enums.order_status.' . $this->key());
    }

    public static function finalStatuses(): array
    {
        return [
            self::COMPLETED->value,
            self::CANCELLED->value,
        ];
    }

    public static function activeStatuses(): array
    {
        return array_diff(self::values(), self::finalStatuses());
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
