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

    public static function getChatPermissions(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
