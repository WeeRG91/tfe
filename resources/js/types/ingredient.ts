export type IngredientType = {
    id: number;
    name: string;
    allergen: { id: number; name: string; main_image: string };
    description: string;
    main_image: string;
    created_at: string;
    updated_at: string;
};

export type EditIngredientType = {
    id: number;
    name: string;
    description: string;
    allergen: number;
    main_image: string;
    images: { id: number; path: string }[];
};

export type IngredientOptionType = {
    value: number;
    label: string;
};

export type IngredientErrorType = {
    name: string;
    description: string;
    allergen: string;
};
