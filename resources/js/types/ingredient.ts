export type IngredientType = {
    id: number;
    name: string;
    allergen: string;
    description: string;
    image: string;
    images: {id: number, path: string}[];
    created_at: string;
    updated_at: string;
}

export type EditIngredientType = {
    id: number;
    name: string;
    description: string;
    allergen: number;
    images: {id: number, path: string}[];
}

export type AllergenOptionType = {
    value: number;
    label: string;
}

export type ErrorType = {
    name: string;
    description: string;
    allergen: string;
}
