export type LoyaltyPointTransactionType = {
    id: number;
    type: PointTypeType;
    points: number;
    description: string;
    created_at: string;
};

export type PointTypeType ={
    value: number;
    key: string;
    label: string;
}

export enum PointTypeEnum {
    EARNED = 'Earned',
    REDEEMED = 'Redeemed',
    REFUNDED = 'Refunded',
    REVERSED = 'Reversed',
}

export enum FilterPointEnum {
    ALL = 'all',
    EARNED = 'earned',
    REDEEMED = 'redeemed',
    REFUNDED = 'refunded',
    REVERSED = 'reversed',
}
