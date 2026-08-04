<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum AllergenPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case ALLERGEN_VIEW = 'allergen.view';
    case ALLERGEN_CREATE = 'allergen.create';
    case ALLERGEN_UPDATE = 'allergen.update';
    case ALLERGEN_DELETE = 'allergen.delete';
    case ALLERGEN_RESTORE = 'allergen.restore';
}
