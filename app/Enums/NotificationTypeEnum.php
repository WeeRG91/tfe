<?php

namespace App\Enums;

enum NotificationTypeEnum: int
{
    case ORDER_CONFIRMED = 1;
    case ORDER_READY = 2;
    case ORDER_DELIVERING = 3;
    case ORDER_COMPLETED = 4;
    case ORDER_CANCELLED = 5;
    case DISH_CREATED = 6;
    case DRINK_CREATED = 7;
    case PROMOTION_CREATED = 8;
    case SYSTEM_ANNOUNCEMENT = 9;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ORDER_CONFIRMED => 'Order Confirmed',
            self::ORDER_READY => 'Order Ready',
            self::ORDER_DELIVERING => 'Order Delivering',
            self::ORDER_COMPLETED => 'Order Completed',
            self::ORDER_CANCELLED => 'Order Cancelled',
            self::DISH_CREATED => 'New Dish Available',
            self::DRINK_CREATED => 'New Drink Available',
            self::PROMOTION_CREATED => 'New Promotion',
            self::SYSTEM_ANNOUNCEMENT => 'Announcement',
        };
    }
}
