<?php

namespace App\Enums\Permissions;

enum OrderPermissionEnum: string
{
    case ORDER_VIEW = 'order.view';
    case ORDER_UPDATE = 'order.update';
    case ORDER_CANCEL = 'order.cancel';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_VIEW => 'View orders',
            self::ORDER_UPDATE => 'Update orders',
        };
    }
}
