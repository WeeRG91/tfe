<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum AdminPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case ADMIN_ACCESS = 'admin.access';
    case DASHBOARD_VIEW = 'dashboard.view';
}
