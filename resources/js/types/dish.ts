export type DishType = {
    id: number;
    name: string;
    description: string;
    price: number;
    category: string;
    is_available: boolean;
    created_at: number;
    updated_at: number;
}

export type CategoryType = {
    value: number;
    label: string;
};

export type IngredientType = {
    value: number;
    label: string;
};
