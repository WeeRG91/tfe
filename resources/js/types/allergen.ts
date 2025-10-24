export type AllergenType = {
    id: number;
    name: string;
    description: string;
    ingredients: IngredientType[];
    image: string;
    images: {id: number, path: string}[];
    created_at: string;
    updated_at: string;
}

export type IngredientType = {
    id: number;
    name: string;
    allergen: {id: number, name: string, image: string};
}

export type EditAllergenType = {
    id: number;
    name: string;
    description: string;
    ingredients: {id: number}[];
    images: {id: number, path: string}[];
}

export type IngredientOptionType = {
    value: number;
    label: string;
}

export type ErrorType = {
    name: string;
    description: string;
    ingredients: string;
}
