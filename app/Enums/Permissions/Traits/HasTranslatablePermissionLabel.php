<?php

namespace App\Enums\Permissions\Traits;

trait HasTranslatablePermissionLabel
{
    public function label(): string
    {
        return __("permissions.permissions.$this->value");
    }
}
