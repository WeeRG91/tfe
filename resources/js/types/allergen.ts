export type AllergenType = {
    id: number;
    name: string;
    description: string | null;
    main_image: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
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

export type ShowAllergenType = {
    id: number;
    name: string;
    main_image: string;
};
