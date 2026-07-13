import { CategoryOptionType } from '@/types/category';

export type CartType = {
    id: number;
    user: { id: number; name: string; email: string; };
    guest_token: string;
    items: CartItemType[];
};

export type CartItemType = {
    id: number;
    cart_id: number;
    item_id: number;
    item_type: number;
    item: CartDishType | CartDrinkType;
    meat: MeatItemType;
    removed_ingredients: RemovedIngredientType[];
    quantity: number;
    unit_price: number;
    total_inc_vat: number;
    notes: string;
};

export type CartDishType = {
    id: number;
    name: string;
    main_image: string;
    price: number;
    category: CategoryOptionType;
};

export type CartDrinkType = {
    id: number;
    name: string;
    main_image: string;
    price: number;
    category: CategoryOptionType;
};

export type MeatItemType = {
    id: number;
    name: string;
    extra_price: number;
    main_image: string;
};

export type RemovedIngredientType = {
    id: number;
    name: string;
    main_image: string;
};

export type AddDishToCartType = {
    item_id: number;
    item_type: ItemTypeEnum;
    meat_id: number;
    quantity: number;
    removed_ingredients?: number[];
    notes?: string;
};

export type AddDrinkToCartType = {
    item_id: number;
    item_type: ItemTypeEnum;
    quantity: number;
    notes?: string;
};

export enum ItemTypeEnum {
    DISH = 1,
    DRINK = 2,
}
