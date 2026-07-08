<?php

namespace App\Enums\Permissions;

enum MessagePermissionEnum: string
{
    case MESSAGE_VIEW = 'message.view';
    case MESSAGE_UPDATE = 'message.update';
    case MESSAGE_SEND = 'message.send';
    case MESSAGE_UNSEND = 'message.unsend';
    case MESSAGE_DELETE = 'message.delete';

    public function label(): string
    {
        return match ($this) {
            self::MESSAGE_VIEW => 'View messages',
            self::MESSAGE_UPDATE => 'Update messages',
            self::MESSAGE_SEND => 'Send messages',
            self::MESSAGE_UNSEND => 'Unsend messages',
            self::MESSAGE_DELETE => 'Delete messages',
        };
    }
}
