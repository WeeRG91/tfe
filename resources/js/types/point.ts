export type LoyaltyPointTransactionType = {
    id: number;
    type: string;
    points: number;
    description: string;
    created_at: string;
};

export enum PointTypeEnum {
    EARNED = 'Earned',
    REDEEMED = 'Redeemed',
    REFUNDED = 'Refunded',
}

export enum FilterPointEnum {
    ALL = 'all',
    EARNED = 'earned',
    REDEEMED = 'redeemed',
    REFUNDED = 'refunded',
}
