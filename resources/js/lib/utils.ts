import { OrderStatusEnum, OrderTypeEnum } from '@/types/order';
import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import {
    AlertCircle,
    Banknote,
    CheckCircle,
    Clock,
    CreditCard,
    LucideProps,
    Package,
    ShoppingBag,
    Truck,
    Utensils,
    CookingPot,
    BookOpenCheck,
    CircleX,
    Salad,
    Wine,
    Gift,
    Bell,
} from 'lucide-vue-next';
import { twMerge } from 'tailwind-merge';
import { FunctionalComponent } from 'vue';
import { PaymentMethodEnum, PaymentStatusEnum } from '@/types/payment';
import { NotifiableTypeEnum, NotificationTypeEnum } from '@/types/notification';
import order from '@/routes/order';
import menu from '@/routes/menu';

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

export function formatPrice(price: number) {
    return price?.toFixed(2);
}

export const formatDate = (date: string | null) => {
    if (!date) return 'Not set';
    return new Date(date).toLocaleString([], {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

export const formatDateShort = (date: string | null) => {
    if (!date) return 'Not set';
    return new Date(date).toLocaleDateString();
};

export const formatTime = (date: string | null) => {
    if (!date) return 'Not set';
    return new Date(date).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
};

export const formatDateForHumans = (date: string | null) => {
    if (!date) return 'Not set';

    const d = new Date(date);
    if (isNaN(d.getTime())) return 'Invalid date';

    const diff = Date.now() - d.getTime();

    const mins = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins} min ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;

    return d.toLocaleDateString();
};

export const formatAddress = (address: any) => {
    if (!address) return '';
    return `${address.street}, ${address.city}, ${address.country}, ${address.postal_code}`;
};

export const getOrderStatusVariant = (status: OrderStatusEnum) => {
    const variants: Record<OrderStatusEnum, string> = {
        [OrderStatusEnum.PENDING]: 'bg-yellow-100 text-yellow-800',
        [OrderStatusEnum.CONFIRMED]: 'bg-blue-100 text-blue-800',
        [OrderStatusEnum.PREPARING]: 'bg-yellow-100 text-yellow-800',
        [OrderStatusEnum.READY]: 'bg-purple-100 text-purple-800',
        [OrderStatusEnum.DELIVERING]: 'bg-purple-100 text-purple-800',
        [OrderStatusEnum.COMPLETED]: 'bg-green-100 text-green-800',
        [OrderStatusEnum.CANCELLED]: 'bg-red-100 text-red-800',
    };
    return variants[status] || 'bg-gray-100 text-gray-800';
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
        [PaymentStatusEnum.PENDING]: 'bg-yellow-100 text-yellow-800',
        [PaymentStatusEnum.PAID]: 'bg-green-100 text-green-800',
        [PaymentStatusEnum.FAILED]: 'bg-red-100 text-red-800',
    };
    return variants[status] || 'bg-gray-100 text-gray-800';
};

export const getPaymentStatusIcon = (status: PaymentStatusEnum) => {
    const variants: Record<PaymentStatusEnum, FunctionalComponent<LucideProps>> = {
        [PaymentStatusEnum.PENDING]: Clock,
        [PaymentStatusEnum.PAID]: CheckCircle,
        [PaymentStatusEnum.FAILED]: AlertCircle,
    };
    return variants[status] || 'bg-gray-100 text-gray-800';
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
        [NotificationTypeEnum.ORDER_CONFIRMED]: 'bg-green-100',
        [NotificationTypeEnum.ORDER_READY]: 'bg-blue-100',
        [NotificationTypeEnum.ORDER_DELIVERING]: 'bg-yellow-100',
        [NotificationTypeEnum.ORDER_COMPLETED]: 'bg-purple-100',
        [NotificationTypeEnum.ORDER_CANCELLED]: 'bg-red-100',
        [NotificationTypeEnum.DISH_CREATED]: 'bg-blue-100',
        [NotificationTypeEnum.DRINK_CREATED]: 'bg-blue-100',
        [NotificationTypeEnum.PROMOTION_CREATED]: 'bg-pink-100',
        [NotificationTypeEnum.SYSTEM_ANNOUNCEMENT]: 'bg-red-100',
    };
    return colors[type] || 'bg-gray-100';
};

export const getNotificationIconColor = (type: NotificationTypeEnum) => {
    const colors: Record<NotificationTypeEnum, string> = {
        [NotificationTypeEnum.ORDER_CONFIRMED]: 'text-green-600',
        [NotificationTypeEnum.ORDER_READY]: 'text-blue-600',
        [NotificationTypeEnum.ORDER_DELIVERING]: 'text-yellow-600',
        [NotificationTypeEnum.ORDER_COMPLETED]: 'text-purple-600',
        [NotificationTypeEnum.ORDER_CANCELLED]: 'text-red-600',
        [NotificationTypeEnum.DISH_CREATED]: 'text-blue-600',
        [NotificationTypeEnum.DRINK_CREATED]: 'text-blue-600',
        [NotificationTypeEnum.PROMOTION_CREATED]: 'text-pink-600',
        [NotificationTypeEnum.SYSTEM_ANNOUNCEMENT]: 'text-red-600',
    };
    return colors[type] || 'text-gray-600';
};

export const notificationRoutes: Record<NotifiableTypeEnum, (id: number) => string> = {
    [NotifiableTypeEnum.ORDER]: (id) => order.orderDetails(id).url,
    [NotifiableTypeEnum.DISH]: (id) => menu.showDish(id).url,
    [NotifiableTypeEnum.DRINK]: () => menu.drink().url,
};
