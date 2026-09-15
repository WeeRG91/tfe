export type PickupSlot = {
    value: string;
    label: string;
};

export type PickupAvailabilityPeriod = {
    position: number;
    opens_at: string;
    closes_at: string;
    last_pickup_at: string;
}

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

export type PickupAvailabilityResponse = {
    data: {
        pickup: PickupAvailability;
    };
};
