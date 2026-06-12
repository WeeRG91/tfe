import { CategoryOptionType } from '@/types/category';

export type DrinkType = {
    id: number;
    name: string;
    main_image: string;
    description: string | null;
    price: number;
    category: CategoryOptionType;
    is_available: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};

export type ClientDrinkType = {
    id: number;
    name: string;
    main_image: string;
    description: string;
    price: number;
    category: CategoryOptionType;
    is_available: string;
};

export type EditDrinkType = {
    id: number;
    name: string;
    category: number;
    description: string;
    price: number;
    main_image: string;
    images: { id: number; path: string }[];
};

export type DrinkErrorType = {
    name?: string;
    description?: string;
    price?: string;
    category?: string;
};

export const ALCOHOL_CATEGORIES = ['Beer', 'Wine', 'Cocktail'] as const;

export type AlcoholCategory = (typeof ALCOHOL_CATEGORIES)[number];

export enum DrinkAvailabilityEnum {
    AVAILABLE = 'Available',
    UNAVAILABLE = 'Unavailable',
}

