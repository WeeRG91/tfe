import { CategoryOptionType } from '@/types/category';
import { ShowIngredientType } from '@/types/ingredient';
import { LocaleType } from '@/types/locale';

export type DishTranslationForm = {
    name: string;
    description: string;
}

export type DishTranslations = Record<
    LocaleType,
    DishTranslationForm
>

export type DishType = {
    id: number;
    name: string;
    main_image: string;
    description: string;
    price: number;
    default_spicy_level: number;
    category: CategoryOptionType;
    is_available: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};

export type EditDishType = {
    id: number;
    name: string;
    description: string;
    category: number;
    price: number;
    default_spicy_level: number;
    meats: { id: number }[];
    ingredients: { id: number }[];
    main_image: string;
    images: { id: number; path: string }[];
};

export type DishErrorType = {
    name?: string;
    description?: string;
    price?: string;
    default_spicy_level?: string;
    meats?: string;
    ingredients?: string;
    category?: string;
};

export type ClientDishType = {
    id: number;
    name: string;
    main_image: string;
    description: string | null;
    price: number;
    default_spicy_level: number;
    category: CategoryOptionType;
    is_available: string;
    ingredients: ShowIngredientType[];
    meats: {
        id: number;
        name: string;
        extra_price: number;
        main_image: string;
    }[];
    rating_average: number;
    rating_count: number;
};

export enum DishAvailabilityEnum {
    AVAILABLE = 'Available',
    UNAVAILABLE = 'Unavailable',
}
