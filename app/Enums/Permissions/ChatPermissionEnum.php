<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum ChatPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case CHAT_VIEW = 'chat.view';
    case CHAT_DELETE = 'chat.delete';
}
