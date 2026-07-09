export type PermissionType = {
    id: number;
    name: string;
    category: string;
};

export enum AdminPermissionEnum {
    ADMIN_ACCESS = 'admin.access',
    DASHBOARD_VIEW = 'dashboard.view',
}

export enum AllergenPermissionEnum {
    ALLERGEN_VIEW = 'allergen.view',
    ALLERGEN_CREATE = 'allergen.create',
    ALLERGEN_UPDATE = 'allergen.update',
    ALLERGEN_DELETE = 'allergen.delete',
    ALLERGEN_RESTORE = 'allergen.restore',
}

export enum ChatPermissionEnum {
    CHAT_VIEW = 'chat.view',
    CHAT_DELETE = 'chat.delete',
}

export enum DishPermissionEnum {
    DISH_VIEW = 'dish.view',
    DISH_CREATE = 'dish.create',
    DISH_UPDATE = 'dish.update',
    DISH_DELETE = 'dish.delete',
    DISH_RESTORE = 'dish.restore',
}

export enum DrinkPermissionEnum {
    DRINK_VIEW = 'drink.view',
    DRINK_CREATE = 'drink.create',
    DRINK_UPDATE = 'drink.update',
    DRINK_DELETE = 'drink.delete',
    DRINK_RESTORE = 'drink.restore',
}

export enum ImagePermissionEnum {
    IMAGE_UPDATE = 'image.update',
    IMAGE_DELETE = 'image.delete',
}

export enum IngredientPermissionEnum {
    INGREDIENT_VIEW = 'ingredient.view',
    INGREDIENT_CREATE = 'ingredient.create',
    INGREDIENT_UPDATE = 'ingredient.update',
    INGREDIENT_DELETE = 'ingredient.delete',
    INGREDIENT_RESTORE = 'ingredient.restore',
}

export enum MeatPermissionEnum {
    MEAT_VIEW = 'meat.view',
    MEAT_CREATE = 'meat.create',
    MEAT_UPDATE = 'meat.update',
    MEAT_DELETE = 'meat.delete',
    MEAT_RESTORE = 'meat.restore',
}

export enum MessagePermissionEnum {
    MESSAGE_VIEW = 'message.view',
    MESSAGE_UPDATE = 'message.update',
    MESSAGE_SEND = 'message.send',
    MESSAGE_UNSEND = 'message.unsend',
    MESSAGE_DELETE = 'message.delete',
}

export enum OrderPermissionEnum {
    ORDER_VIEW = 'order.view',
    ORDER_UPDATE = 'order.update',
}

export enum RolePermissionEnum {
    ROLE_VIEW = 'role.view',
    ROLE_CREATE = 'role.create',
    ROLE_UPDATE = 'role.update',
    ROLE_DELETE = 'role.delete',
}

export enum UserPermissionEnum {
    USER_VIEW = 'user.view',
    USER_CREATE = 'user.create',
    USER_UPDATE = 'user.update',
    USER_DELETE = 'user.delete',
    USER_RESTORE = 'user.restore',
}
