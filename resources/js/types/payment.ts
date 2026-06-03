export type PaymentMethodType = {
    value: number;
    label: string;
    color: string;
};

export type PaymentStatusType = {
    value: number;
    label: string;
    color: string;
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
