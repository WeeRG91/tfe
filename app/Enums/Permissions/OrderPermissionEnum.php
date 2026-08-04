<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum OrderPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case ORDER_VIEW = 'order.view';
    case ORDER_UPDATE = 'order.update';
    case ORDER_CANCEL = 'order.cancel';

}
