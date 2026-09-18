export type PickupSlot = {
    value: string;
    label: string;
};

export type PickupAvailabilityPeriod = {
    position: number;
    opens_at: string;
    closes_at: string;
    last_pickup_at: string;
};

export type PickupAvailabilityDate = {
    date: string;
    weekday: number;
    available: boolean;
    periods: PickupAvailabilityPeriod[];
    slots: PickupSlot[];
};

export type PickupAvailability = {
    generated_at: string;
    timezone: string;
    slot_interval_minutes: number;
    minimum_preparation_minutes: number;
    dates: PickupAvailabilityDate[];
};

export type RestaurantCurrentAvailability = {
    is_open: boolean;
    accepting_orders: boolean;
    status:
        | 'open'
        | 'closed_day'
        | 'outside_opening_hours'
        | 'exceptionally_closed';
    message: string | null;
    checked_at: string;
    timezone: string;
    next_open_at: string | null;
};

export type PickupAvailabilityResponse = {
    data: {
        current: RestaurantCurrentAvailability;
        pickup: PickupAvailability;
    };
};
