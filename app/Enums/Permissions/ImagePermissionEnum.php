<?php

namespace App\Enums\Permissions;

enum ImagePermissionEnum: string
{
    case IMAGE_UPDATE = 'image.update';
    case IMAGE_DELETE = 'image.delete';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE_UPDATE => 'Update images',
            self::IMAGE_DELETE => 'Delete images',
        };
    }
}
