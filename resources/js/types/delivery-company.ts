export type CompanyDeliveryDateType = {
    id: number;
    delivery_date: string;
    is_available: boolean;
};

export type DeliveryCompanyType = {
    id: number;
    name: string;
    is_active: boolean;
    minimum_advance_days: number;
    dates: CompanyDeliveryDateType[];
    earliest_delivery_date: string;
};
