export type PaymentMethodType = {
    value: number;
    label: string;
};

export type PaymentStatusType = {
    value: number;
    label: string;
};

export enum PaymentMethodEnum {
    CASH = 1,
    CARD = 2,
}

export enum PaymentStatusEnum {
    PENDING = 1,
    PAID = 2,
    FAILED = 3,
}
