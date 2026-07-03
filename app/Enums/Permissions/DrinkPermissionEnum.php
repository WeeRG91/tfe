<?php

namespace App\Enums\Permissions;

enum DrinkPermissionEnum: string
{
    case DRINK_VIEW = 'drink.view';
    case DRINK_CREATE = 'drink.create';
    case DRINK_UPDATE = 'drink.update';
    case DRINK_DELETE = 'drink.delete';
    case DRINK_RESTORE = 'drink.restore';

    public function label(): string
    {
        return match ($this) {
            self::DRINK_VIEW => 'View drinks',
            self::DRINK_CREATE => 'Create drinks',
            self::DRINK_UPDATE => 'Update drinks',
            self::DRINK_DELETE => 'Delete drinks',
            self::DRINK_RESTORE => 'Restore drinks',
        };
    }

    public static function getDrinkPermissions(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
