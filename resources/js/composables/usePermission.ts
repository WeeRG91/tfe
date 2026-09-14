import {
    AllergenPermissionEnum,
    DishPermissionEnum,
    DrinkPermissionEnum,
    IngredientPermissionEnum,
    MeatPermissionEnum,
    RolePermissionEnum,
    UserPermissionEnum,
} from '@/types/permission';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermission() {
    const page = usePage();

    const permissions = computed(() => page.props.auth.permissions ?? []);

    const can = (permission: string) => {
        return permissions.value.includes(permission);
    };

    const searchPermissions = [
        AllergenPermissionEnum.ALLERGEN_UPDATE,
        DishPermissionEnum.DISH_UPDATE,
        DrinkPermissionEnum.DRINK_UPDATE,
        IngredientPermissionEnum.INGREDIENT_UPDATE,
        MeatPermissionEnum.MEAT_UPDATE,
        RolePermissionEnum.ROLE_UPDATE,
        UserPermissionEnum.USER_VIEW,
    ];

    const canSearch = () => {
        return searchPermissions.some((permission) => can(permission));
    };

    return {
        can,
        canSearch,
    };
}
