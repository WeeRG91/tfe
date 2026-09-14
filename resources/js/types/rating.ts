export type ReviewType = {
    id: number;
    rating: number;
    review: string;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
        avatar: string;
    };
};

export type ReviewResultType = {
    dish_id: number;
    review: ReviewType;
    deleted_review_id: number;
    rating_average: number;
    rating_count: number;
};
