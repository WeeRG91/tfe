import { CategoryOptionType } from '@/types/category';

export type DishType = {
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

export type EditDishType = {
    id: number;
    name: string;
    category: number;
    description: string;
    price: number;
    meats: { id: number }[];
    ingredients: { id: number }[];
    main_image: string;
    images: { id: number; path: string }[];
};

export type DishErrorType = {
    name?: string;
    description?: string;
    price?: string;
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
    category: CategoryOptionType;
    is_available: string;
};
