import menu from '@/routes/menu';
import order from '@/routes/order';
import { NotifiableTypeEnum, NotificationTypeEnum } from '@/types/notification';
import { OrderStatusEnum, OrderTypeEnum } from '@/types/order';
import { PaymentMethodEnum, PaymentStatusEnum } from '@/types/payment';
import { LoyaltyPointTransactionType, PointTypeEnum } from '@/types/point';
import { InertiaLinkProps } from '@inertiajs/vue3';
import { type ClassValue, clsx } from 'clsx';
import {
    AlertCircle,
    Banknote,
    Bell,
    BookOpenCheck,
    CheckCircle,
    CircleX,
    Clock,
    CookingPot,
    CreditCard,
    Gift,
    LucideProps,
    Package,
    Salad,
    ShoppingBag,
    Truck,
    Utensils,
    Wine,
} from 'lucide-vue-next';
import { twMerge } from 'tailwind-merge';
import { FunctionalComponent } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function formatPrice(price: number | string | null | undefined): string {
    const numericPrice = Number(price ?? 0);

    if (!Number.isFinite(numericPrice)) {
        return '0.00';
    }

    return numericPrice.toFixed(2);
}

export const formatTime = (date: string | null) => {
    if (!date) return 'Not set';
    return new Date(date).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
};

export const formatAddress = (address: any) => {
    if (!address) return '';
    return `${address.street}, ${address.city}, ${address.country}, ${address.postal_code}`;
};

export const getOrderStatusVariant = (status: OrderStatusEnum) => {
    const variants: Record<OrderStatusEnum, string> = {
        [OrderStatusEnum.PENDING]: 'bg-warning/10 text-warning',
        [OrderStatusEnum.CONFIRMED]: 'bg-info/10 text-info',
        [OrderStatusEnum.PREPARING]: 'bg-warning/10 text-warning',
        [OrderStatusEnum.READY]: 'bg-primary/10 text-primary',
        [OrderStatusEnum.DELIVERING]: 'bg-info/10 text-info',
        [OrderStatusEnum.COMPLETED]: 'bg-success/10 text-success',
        [OrderStatusEnum.CANCELLED]: 'bg-destructive/10 text-destructive',
    };

    return variants[status] || 'bg-muted text-muted-foreground';
};

export const getOrderStatusIcon = (status: OrderStatusEnum) => {
    const icons: Record<OrderStatusEnum, FunctionalComponent<LucideProps>> = {
        [OrderStatusEnum.PENDING]: Clock,
        [OrderStatusEnum.CONFIRMED]: Clock,
        [OrderStatusEnum.PREPARING]: Package,
        [OrderStatusEnum.READY]: CheckCircle,
        [OrderStatusEnum.DELIVERING]: Truck,
        [OrderStatusEnum.COMPLETED]: CheckCircle,
        [OrderStatusEnum.CANCELLED]: AlertCircle,
    };
    return icons[status] || Clock;
};

export const getOrderTypeIcon = (type: OrderTypeEnum) => {
    const icons: Record<OrderTypeEnum, FunctionalComponent<LucideProps>> = {
        [OrderTypeEnum.TAKEAWAY]: ShoppingBag,
        [OrderTypeEnum.DELIVERY]: Truck,
        [OrderTypeEnum.DINEIN]: Utensils,
    };
    return icons[type] || Utensils;
};

export const getPaymentMethodIcon = (method: PaymentMethodEnum) => {
    const icons: Record<PaymentMethodEnum, FunctionalComponent<LucideProps>> = {
        [PaymentMethodEnum.CASH]: Banknote,
        [PaymentMethodEnum.CARD]: CreditCard,
    };
    return icons[method] || CreditCard;
};

export const getPaymentStatusVariant = (status: PaymentStatusEnum) => {
    const variants: Record<PaymentStatusEnum, string> = {
        [PaymentStatusEnum.PENDING]: 'bg-warning/10 text-warning',
        [PaymentStatusEnum.PAID]: 'bg-success/10 text-success',
        [PaymentStatusEnum.FAILED]: 'bg-destructive/10 text-destructive',
        [PaymentStatusEnum.REFUND_PENDING]: 'bg-warning/10 text-warning',
        [PaymentStatusEnum.REFUNDED]: 'bg-info/10 text-info',
        [PaymentStatusEnum.REFUND_FAILED]: 'bg-destructive/10 text-destructive',
    };

    return variants[status] || 'bg-muted text-muted-foreground';
};

export const getPaymentStatusIcon = (status: PaymentStatusEnum) => {
    const variants: Record<
        PaymentStatusEnum,
        FunctionalComponent<LucideProps>
    > = {
        [PaymentStatusEnum.PENDING]: Clock,
        [PaymentStatusEnum.PAID]: CheckCircle,
        [PaymentStatusEnum.FAILED]: AlertCircle,
        [PaymentStatusEnum.REFUND_PENDING]: Clock,
        [PaymentStatusEnum.REFUNDED]: CheckCircle,
        [PaymentStatusEnum.REFUND_FAILED]: AlertCircle,
    };
    return variants[status] || AlertCircle;
};

export const getNotificationIcon = (type: NotificationTypeEnum) => {
    const icons: Record<
        NotificationTypeEnum,
        FunctionalComponent<LucideProps>
    > = {
        [NotificationTypeEnum.ORDER_CONFIRMED]: CheckCircle,
        [NotificationTypeEnum.ORDER_READY]: CookingPot,
        [NotificationTypeEnum.ORDER_DELIVERING]: Truck,
        [NotificationTypeEnum.ORDER_COMPLETED]: BookOpenCheck,
        [NotificationTypeEnum.ORDER_CANCELLED]: CircleX,
        [NotificationTypeEnum.DISH_CREATED]: Salad,
        [NotificationTypeEnum.DRINK_CREATED]: Wine,
        [NotificationTypeEnum.PROMOTION_CREATED]: Gift,
        [NotificationTypeEnum.SYSTEM_ANNOUNCEMENT]: AlertCircle,
    };
    return icons[type] || Bell;
};

export const getNotificationIconBgColor = (type: NotificationTypeEnum) => {
    const colors: Record<NotificationTypeEnum, string> = {
        [NotificationTypeEnum.ORDER_CONFIRMED]: 'bg-success/10',
        [NotificationTypeEnum.ORDER_READY]: 'bg-info/10',
        [NotificationTypeEnum.ORDER_DELIVERING]: 'bg-warning/10',
        [NotificationTypeEnum.ORDER_COMPLETED]: 'bg-success/10',
        [NotificationTypeEnum.ORDER_CANCELLED]: 'bg-destructive/10',
        [NotificationTypeEnum.DISH_CREATED]: 'bg-primary/10',
        [NotificationTypeEnum.DRINK_CREATED]: 'bg-info/10',
        [NotificationTypeEnum.PROMOTION_CREATED]: 'bg-accent',
        [NotificationTypeEnum.SYSTEM_ANNOUNCEMENT]: 'bg-destructive/10',
    };

    return colors[type] || 'bg-muted';
};

export const getNotificationIconColor = (type: NotificationTypeEnum) => {
    const colors: Record<NotificationTypeEnum, string> = {
        [NotificationTypeEnum.ORDER_CONFIRMED]: 'text-success',
        [NotificationTypeEnum.ORDER_READY]: 'text-info',
        [NotificationTypeEnum.ORDER_DELIVERING]: 'text-warning',
        [NotificationTypeEnum.ORDER_COMPLETED]: 'text-success',
        [NotificationTypeEnum.ORDER_CANCELLED]: 'text-destructive',
        [NotificationTypeEnum.DISH_CREATED]: 'text-primary',
        [NotificationTypeEnum.DRINK_CREATED]: 'text-info',
        [NotificationTypeEnum.PROMOTION_CREATED]: 'text-accent-foreground',
        [NotificationTypeEnum.SYSTEM_ANNOUNCEMENT]: 'text-destructive',
    };

    return colors[type] || 'text-muted-foreground';
};

export const notificationRoutes: Record<
    NotifiableTypeEnum,
    (id: number) => string
> = {
    [NotifiableTypeEnum.ORDER]: (id) => order.orderDetails(id).url,
    [NotifiableTypeEnum.DISH]: (id) => menu.showDish(id).url,
    [NotifiableTypeEnum.DRINK]: () => menu.drink().url,
};

export const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

export const getUserAvatarColor = (userId: number) => {
    const colors = [
        'bg-primary text-primary-foreground',
        'bg-secondary text-secondary-foreground',
        'bg-accent text-accent-foreground',
    ];
    return colors[userId % colors.length];
};

export const getCategoryBadgeColor = (categoryValue: number): string => {
    const colors = [
        'bg-primary text-primary-foreground',
        'bg-secondary text-secondary-foreground',
        'bg-accent text-accent-foreground',
        'bg-info text-info-foreground',
        'bg-success text-success-foreground',
        'bg-warning text-warning-foreground',
    ];

    return colors[(categoryValue - 1) % colors.length] ?? colors[0]!;
};

export const getTotalPoints = (transactions: LoyaltyPointTransactionType[]) => {
    if (!transactions.length) return 0;

    return transactions.reduce((total, transaction) => {
        switch (transaction.type.label as PointTypeEnum) {
            case PointTypeEnum.EARNED:
                return total + transaction.points;
            case PointTypeEnum.REDEEMED:
                return total - transaction.points;
            case PointTypeEnum.REFUNDED:
                return total + transaction.points;
            case PointTypeEnum.REVERSED:
                return total - transaction.points;
        }
    }, 0);
};
