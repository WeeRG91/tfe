export type NotificationType = {
    id: number;
    user_id: number;
    notifiable_id: number;
    notifiable_type: NotifiableTypeEnum;
    type: NotificationTypeEnum;
    title: string;
    message: string;
    data: NotificationDataType;
    read_at: string;
    created_at: string;
};

export type NotificationDataType =
    | { order_number: string }
    | { dish_name: string }
    | object;

export enum NotifiableTypeEnum {
    DISH = 'dish',
    DRINK = 'drink',
    ORDER = 'order',
}

export enum NotificationTypeEnum {
    ORDER_CONFIRMED = 1,
    ORDER_READY = 2,
    ORDER_DELIVERING = 3,
    ORDER_COMPLETED = 4,
    ORDER_CANCELLED = 5,
    DISH_CREATED = 6,
    DRINK_CREATED = 7,
    PROMOTION_CREATED = 8,
    SYSTEM_ANNOUNCEMENT = 9,
}

export enum FilterNotificationEnum {
    ALL = 'all',
    READ = 'read',
    UNREAD = 'unread',
}
