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

    public static function getColor(self $case): string
    {
        return match ($case) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::CONFIRMED => 'bg-blue-100 text-blue-800',
            self::PREPARING => 'bg-purple-100 text-purple-800',
            self::READY => 'bg-green-100 text-green-800',
            self::DELIVERING => 'bg-orange-100 text-orange-800',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
        };
    }

    public static function getStatuses(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => self::getColor($case),
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
