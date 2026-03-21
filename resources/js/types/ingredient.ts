import { ShowAllergenType } from '@/types/allergen';

export type IngredientType = {
    id: number;
    name: string;
    allergen: { id: number; name: string; main_image: string } | null;
    description: string | null;
    main_image: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
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

export type ShowIngredientType = {
    id: number;
    name: string;
    main_image: string;
    allergen: ShowAllergenType;
};
