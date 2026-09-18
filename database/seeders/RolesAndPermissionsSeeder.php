<?php

namespace Database\Seeders;

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
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionGroups = [
            PermissionCategoryEnum::ADMIN->value => AdminPermissionEnum::cases(),
            PermissionCategoryEnum::ALLERGEN->value => AllergenPermissionEnum::cases(),
            PermissionCategoryEnum::CHAT->value => ChatPermissionEnum::cases(),
            PermissionCategoryEnum::DISH->value => DishPermissionEnum::cases(),
            PermissionCategoryEnum::DRINK->value => DrinkPermissionEnum::cases(),
            PermissionCategoryEnum::IMAGE->value => ImagePermissionEnum::cases(),
            PermissionCategoryEnum::INGREDIENT->value => IngredientPermissionEnum::cases(),
            PermissionCategoryEnum::MEAT->value => MeatPermissionEnum::cases(),
            PermissionCategoryEnum::MESSAGE->value => MessagePermissionEnum::cases(),
            PermissionCategoryEnum::ORDER->value => OrderPermissionEnum::cases(),
            PermissionCategoryEnum::ROLE->value => RolePermissionEnum::cases(),
            PermissionCategoryEnum::USER->value => UserPermissionEnum::cases(),
        ];

        foreach ($permissionGroups as $category => $permissions) {
            foreach ($permissions as $permission) {
                Permission::query()->firstOrCreate(
                    ['name' => $permission->value],
                    ['category' => $category]
                );
            }
        }

        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $admin = Role::findOrCreate('Admin', 'web');

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions(Permission::all());

        $superUser = User::firstWhere('email', 'super_admin@example.com');
        $user = User::firstWhere('email', 'admin@example.com');

        $superUser?->syncRoles([$superAdmin]);
        $user?->syncRoles([$admin]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
