import { IngredientAllergenType } from '@/types/ingredient';

export type AllergenType = {
    id: number;
    name: string;
    description: string;
    ingredients: IngredientAllergenType[];
    main_image: string;
    images: { id: number; path: string }[];
    created_at: string;
    updated_at: string;
};

export type EditAllergenType = {
    id: number;
    name: string;
    description: string;
    ingredients: { id: number }[];
    main_image: string;
    images: { id: number; path: string }[];
};

export type AllergenOptionType = {
    value: number;
    label: string;
};

export type AllergenErrorType = {
    name: string;
    description: string;
    ingredients: string;
};
