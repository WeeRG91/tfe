<?php

namespace App\Enums\Permissions;

enum MeatPermissionEnum: string
{
    case MEAT_VIEW = 'meat.view';
    case MEAT_CREATE = 'meat.create';
    case MEAT_UPDATE = 'meat.update';
    case MEAT_DELETE = 'meat.delete';
    case MEAT_RESTORE = 'meat.restore';

    public function label(): string
    {
        return match ($this) {
            self::MEAT_VIEW => 'View meats',
            self::MEAT_CREATE => 'Create meats',
            self::MEAT_UPDATE => 'Update meats',
            self::MEAT_DELETE => 'Delete meats',
            self::MEAT_RESTORE => 'Restore meats',
        };
    }

    public static function getMeatPermissions(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
