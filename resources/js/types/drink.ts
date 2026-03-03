export type DrinkType = {
    id: number;
    name: string;
    main_image: string;
    images: { id: number; path: string }[];
    description: string;
    price: number;
    category: CategoryEnumType;
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
    images: { id: number; path: string }[];
}

export type CategoryEnumType = {
    value: number;
    label: string;
    color: string;
}

export type ErrorType = {
    name?: string;
    description?: string;
    price?: string;
    category?: string;
}
