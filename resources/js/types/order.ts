import { AddressType } from '@/types/address';
import { CategoryOptionType } from '@/types/category';

export type OrderType = {
    id: number;
    order_number: string;
    user: { id: number; name: string; email: string };
    type: OrderTypeType;
    table_number: string;
    pickup_time: string;
    pickup_name: string;
    pickup_phone: string;
    delivery_address: AddressType;
    status: OrderStatusType;
    payment_method: PaymentMethodType;
    payment_status: PaymentStatusType;
    confirmed_at: string;
    paid_at: string;
    prepare_at: string;
    ready_at: string;
    delivered_at: string;
    completed_at: string;
    cancelled_at: string;
    created_at: string;
    notes: string;
    subtotal: number;
    total_inc_vat: number;
    discount_total: number;
    vat_total: number;
    vat_breakdown: {vat_rate: number, vat_total: number, total_inc_vat: number}[];
    delivery_fee: number;
    items: OrderItemType[];
};

export type OrderItemType = {
    id: number;
    cart_id: number;
    item_id: number;
    item_type: string;
    item: OrderDishType | OrderDrinkType;
    meat: MeatItemType;
    removed_ingredients: RemovedIngredientType[];
    quantity: number;
    unit_price: number;
    total_inc_vat: number;
    notes: string;
};

export type OrderDishType = {
    id: number;
    name: string;
    main_image: string;
    price: number;
    category: CategoryOptionType;
};

export type OrderDrinkType = {
    id: number;
    name: string;
    main_image: string;
    price: number;
    category: CategoryOptionType;
};

export type MeatItemType = {
    id: number;
    name: string;
    extra_price: number;
    main_image: string;
};

export type RemovedIngredientType = {
    id: number;
    name: string;
    main_image: string;
};

export type PlaceOrderPayloadType = {
    cart_id: number;
    type: number;
    table_number?: string | null;
    pickup_time?: string | null;
    pickup_name?: string | null;
    pickup_phone?: string | null;
    address_id?: number | null;
    payment_method: number;
    notes?: string | null;
    used_points: number;
};

export type OrderTypeType = {
    value: number;
    label: string;
    color: string;
};

export type OrderStatusType = {
    value: number;
    label: string;
    color: string;
};

export type PaymentStatusType = {
    value: number;
    label: string;
    color: string;
};

export type PaymentMethodType = {
    value: number;
    label: string;
    color: string;
};
