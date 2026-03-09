import { CategoryOptionType } from '@/types/category';

export type DrinkType = {
    id: number;
    name: string;
    main_image: string;
    description: string;
    price: number;
    category: CategoryOptionType;
    is_available: string;
    created_at: string;
    updated_at: string;
}

export type EditDrinkType = {
    id: number;
    name: string;
    category: number;
    description: string;
    price: number;
    main_image: string;
    images: { id: number; path: string }[];
}

export type DrinkErrorType = {
    name?: string;
    description?: string;
    price?: string;
    category?: string;
}
