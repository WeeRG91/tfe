export type Paginated<T> = {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Link[];
    next_page_url: string;
    path: string;
    per_page: number;
    prev_page_url: string;
    to: number;
    total: number;
}

export type Link = {
    active: boolean;
    label: string;
    page: number;
    url: string;
}

export type DishType = {
    id: number;
    name: string;
    image: string;
    images: {id: number, path: string}[];
    description: string;
    price: number;
    ingredients: Ingredients[];
    category: CategoryEnum;
    is_available: string;
    created_at: number;
    updated_at: number;
}

export type Ingredients = {
    id: number;
    name: string;
    image: string;
}

export type CategoryEnum = {
    value: number;
    label: string;
    color: string;
}

export type EditDishType = {
    id: number;
    name: string;
    category: number;
    description: string;
    price: number;
    ingredients: {id: number}[];
    images: {id: number, path: string}[];
}

export type CategoryType = {
    value: number;
    label: string;
    color: string;
};

export type IngredientType = {
    value: number;
    label: string;
};
