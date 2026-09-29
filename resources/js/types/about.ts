export type AboutOpeningPeriodType = {
    id: number;
    position: number;
    opens_at: string;
    closes_at: string;
};

export type AboutOpeningDayType = {
    id: number;
    weekday: number;
    weekday_key: string;
    is_open: boolean;
    periods: AboutOpeningPeriodType[];
};

export type AboutClosureType = {
    id: number;
    is_all_day: boolean;
    starts_at: string;
    ends_at: string;
    public_message: string | null;
};
