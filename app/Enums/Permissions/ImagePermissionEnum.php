<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum ImagePermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case IMAGE_UPDATE = 'image.update';
    case IMAGE_DELETE = 'image.delete';

}
