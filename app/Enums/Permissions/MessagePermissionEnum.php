<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum MessagePermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case MESSAGE_VIEW = 'message.view';
    case MESSAGE_UPDATE = 'message.update';
    case MESSAGE_SEND = 'message.send';
    case MESSAGE_UNSEND = 'message.unsend';
    case MESSAGE_DELETE = 'message.delete';

}
