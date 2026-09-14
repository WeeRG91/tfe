export type PickupSlot = {
    value: string;
    label: string;
};

export type PickupAvailabilityDate = {
    date: string;
    weekday: number;
    available: boolean;
    opens_at: string | null;
    closes_at: string | null;
    last_pickup_at: string | null;
    slots: PickupSlot[];
};

export type PickupAvailability = {
    generated_at: string;
    timezone: string;
    slot_interval_minutes: number;
    minimum_preparation_minutes: number;
    dates: PickupAvailabilityDate[];
};

export type PickupAvailabilityResponse = {
    data: {
        pickup: PickupAvailability;
    };
};
