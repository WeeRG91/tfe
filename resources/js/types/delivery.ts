export enum DeliveryTypeEnum {
    OWN_ADDRESS = 'own_address',
    COMPANY = 'company',
}

export type CompanyDeliveryOptionType = {
    id: number;
    name: string;
    minimum_advance_days: number;
    dates: string[];
};

export type DeliveryOptionsType = {
    own_address: {
        fee: string;
        postal_codes: string[];
    };
    company: {
        enabled: boolean;
        fee: string;
        companies: CompanyDeliveryOptionType[];
    };
};
