<?php

namespace App\Enums\Permissions;

use Illuminate\Support\Arr;

enum AdminPermissionEnum: string
{
    case ADMIN_ACCESS = 'admin.access';
    case DASHBOARD_VIEW = 'dashboard.view';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN_ACCESS => 'Access the admin panel',
            self::DASHBOARD_VIEW => 'View the dashboard',
        };
    }
}
