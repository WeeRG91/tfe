export type MeatType = {
    id: number;
    name: string;
    description: string;
    extra_price: number;
    main_image: string;
    created_at: string;
    updated_at: string;
};

export type EditMeatType = {
    id: number;
    name: string;
    description: string;
    extra_price: number;
    images: { id: number; path: string }[];
};

export type MeatErrorType = {
    name: string;
    description: string;
    extra_price: string;
};
