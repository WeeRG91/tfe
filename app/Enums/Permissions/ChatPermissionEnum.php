<?php

namespace App\Enums\Permissions;

enum ChatPermissionEnum: string
{
    case CHAT_VIEW = 'chat.view';
    case CHAT_DELETE = 'chat.delete';

    public function label(): string
    {
        return match ($this) {
            self::CHAT_VIEW => 'View chats',
            self::CHAT_DELETE => 'Delete chats',
        };
    }
}
