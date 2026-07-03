<?php

namespace App\Enums\Permissions;

enum OrderPermissionEnum: string
{
    case ORDER_VIEW = 'order.view';
    case ORDER_UPDATE = 'order.update';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_VIEW => 'View orders',
            self::ORDER_UPDATE => 'Update orders',
        };
    }

    public static function getOrderPermissions(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
