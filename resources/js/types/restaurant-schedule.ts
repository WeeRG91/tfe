export type RestaurantHourType = {
    id: number;
    weekday: number;
    weekday_key: string;
    is_open: boolean;
    opens_at: string | null;
    closes_at: string | null;
    last_pickup_at: string | null;
};

export type RestaurantClosureType = {
    id: number;
    is_all_day: boolean;
    starts_at: string;
    ends_at: string;
    starts_on: string | null;
    ends_on: string | null;
    reason: string | null;
    public_message: string | null;
    created_by: string | null;
};
