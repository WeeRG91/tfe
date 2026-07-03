<?php

namespace App\Enums\Permissions;

enum AllergenPermissionEnum: string
{
    case ALLERGEN_VIEW ='allergen.view';
    case ALLERGEN_CREATE = 'allergen.create';
    case ALLERGEN_UPDATE = 'allergen.update';
    case ALLERGEN_DELETE = 'allergen.delete';
     case ALLERGEN_RESTORE = 'allergen.restore';

     public function label(): string
     {
         return match ($this) {
             self::ALLERGEN_VIEW => 'View allergens',
             self::ALLERGEN_CREATE => 'Create allergens',
             self::ALLERGEN_UPDATE => 'Update allergens',
             self::ALLERGEN_DELETE => 'Delete allergens',
             self::ALLERGEN_RESTORE => 'Restore allergens',
         };
     }

     public static function getAllergenPermissions(): array
     {
         return array_map(fn ($case) => [
             'value' => $case->value,
             'label' => $case->label(),
         ], self::cases());
     }
}
