export type DishType = {
    id: number;
    name: string;
    image: string;
    images: {id: number, path: string}[];
    description: string;
    price: number;
    ingredients: IngredientType[];
    category: CategoryEnumType;
    is_available: string;
    created_at: string;
    updated_at: string;
}

export type IngredientType = {
    id: number;
    name: string;
    allergen: {id: number, name: string, image: string};
}

export type CategoryEnumType = {
    value: number;
    label: string;
    color: string;
}

export type EditDishType = {
    id: number;
    name: string;
    category: number;
    description: string;
    price: number;
    ingredients: {id: number}[];
    images: {id: number, path: string}[];
}

export type CategoryOptionType = {
    value: number;
    label: string;
    color: string;
};

export type IngredientOptionType = {
    value: number;
    label: string;
};

export type ErrorType = {
    name?: string;
    description?: string;
    price?: string;
    ingredients?: string;
    category?: string;
}
