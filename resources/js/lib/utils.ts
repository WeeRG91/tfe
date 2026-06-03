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
} from 'lucide-vue-next';
import { twMerge } from 'tailwind-merge';
import { FunctionalComponent } from 'vue';
import { PaymentMethodEnum, PaymentStatusEnum } from '@/types/payment';

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
