<?php

use App\Enums\Permissions\AdminPermissionEnum;
use App\Enums\Permissions\AllergenPermissionEnum;
use App\Enums\Permissions\ChatPermissionEnum;
use App\Enums\Permissions\DishPermissionEnum;
use App\Enums\Permissions\DrinkPermissionEnum;
use App\Enums\Permissions\ImagePermissionEnum;
use App\Enums\Permissions\IngredientPermissionEnum;
use App\Enums\Permissions\MeatPermissionEnum;
use App\Enums\Permissions\MessagePermissionEnum;
use App\Enums\Permissions\OrderPermissionEnum;
use App\Enums\Permissions\PermissionCategoryEnum;
use App\Enums\Permissions\RolePermissionEnum;
use App\Enums\Permissions\UserPermissionEnum;
use App\Services\PermissionEnumResolver;
use Tests\TestCase;

uses(TestCase::class);

it('has a translated label for every permission and category in every supported locale', function () {
    $permissionEnums = [
        AdminPermissionEnum::class,
        AllergenPermissionEnum::class,
        ChatPermissionEnum::class,
        DishPermissionEnum::class,
        DrinkPermissionEnum::class,
        ImagePermissionEnum::class,
        IngredientPermissionEnum::class,
        MeatPermissionEnum::class,
        MessagePermissionEnum::class,
        OrderPermissionEnum::class,
        RolePermissionEnum::class,
        UserPermissionEnum::class,
    ];

    foreach (array_keys(config('locales.supported')) as $locale) {
        app()->setLocale($locale);

        foreach ($permissionEnums as $enum) {
            foreach ($enum::cases() as $permission) {
                expect($permission->label())
                    ->not->toBe("permissions.permissions.{$permission->value}")
                    ->and(PermissionEnumResolver::label($permission->value))
                    ->toBe($permission->label());
            }
        }

        foreach (PermissionCategoryEnum::cases() as $category) {
            expect($category->label())
                ->not->toStartWith('permissions.categories.');
        }
    }
});

it('returns an unknown permission value as a safe fallback', function () {
    expect(PermissionEnumResolver::label('custom.permission'))->toBe('custom.permission');
});
