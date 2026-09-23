<script setup lang="ts">
import { useDateFormatter } from '@/composables/useDateFormatter';
import { useOrderStatusActionFlow } from '@/composables/useOrderStatusActionFlow';
import { getSpicyLevelLabels } from '@/lib/const';
import {
    formatAddress,
    getOrderStatusIcon,
    getOrderStatusVariant,
    getOrderTypeIcon,
    getPaymentMethodIcon,
    getPaymentStatusIcon,
    getPaymentStatusVariant,
} from '@/lib/utils';
import { OrderStatusEnum, OrderType, OrderTypeEnum } from '@/types/order';
import {
    AlertCircle,
    MapPin,
    User,
    UserCircle,
    Utensils,
    X,
    Building2,
    CalendarDays,
    Loader,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { DeliveryTypeEnum } from '@/types/delivery';

const props = defineProps<{
    order: OrderType | null;
    isOpen: boolean;
}>();

const emit = defineEmits<{
    close: [];
    'update-status': [orderId: number, newStatus: OrderStatusEnum];
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));
const { formatDate, formatDateOnly } = useDateFormatter();

const orderStatusActionId = ref<string>('');

const handleUpdateStatus = (newStatus: OrderStatusEnum, actionId: string) => {
    orderStatusActionId.value = actionId;
    if (props.order) {
        emit('update-status', props.order.id, newStatus);
    }
};

const { getOrderStatusActions } = useOrderStatusActionFlow(handleUpdateStatus);

const orderStatusActions = computed(
    () =>
        props.order &&
        getOrderStatusActions(props.order.status.value, props.order.type.value),
);

const handleClose = () => {
    emit('close');
};

const closeOnBackdrop = (e: MouseEvent) => {
    if (e.target === e.currentTarget) {
        handleClose();
    }
};

onMounted(() => {
    if (props.isOpen) {
        document.body.style.overflow = 'hidden';
        orderStatusActionId.value = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <div
        v-if="isOpen && order"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 backdrop-blur-sm"
        @click="closeOnBackdrop"
    >
        <div
            class="relative mx-2 w-full max-w-4xl rounded-lg border border-border bg-card text-card-foreground shadow-xl"
        >
            <div
                class="sticky top-0 z-10 flex items-center justify-between rounded-t-lg border-b border-border bg-card px-6 py-4"
            >
                <div class="flex items-center gap-3">
                    <component
                        :is="getOrderTypeIcon(order.type.value)"
                        :size="24"
                        class="text-card-foreground"
                    />
                    <div>
                        <h2 class="text-xl font-semibold text-card-foreground">
                            {{ order.order_number }}
                        </h2>
                        <p
                            class="flex items-center gap-2 text-sm text-card-foreground"
                        >
                            <span>
                                {{
                                    t(`confirmedOrder.types.${order.type.key}`)
                                }}
                                • {{ formatDate(order.created_at) }}
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium"
                                :class="
                                    getOrderStatusVariant(order.status.value)
                                "
                            >
                                <component
                                    :is="getOrderStatusIcon(order.status.value)"
                                    :size="14"
                                />
                                {{
                                    t(
                                        `confirmedOrder.status.${order.status.key}`,
                                    )
                                }}
                            </span>
                        </p>
                    </div>
                </div>
                <button
                    @click="handleClose"
                    class="rounded-lg p-1 text-card-foreground hover:bg-accent hover:text-accent-foreground"
                >
                    <X :size="20" />
                </button>
            </div>

            <div class="max-h-[calc(100vh-8rem)] overflow-y-auto p-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <div
                            class="rounded-lg border border-border bg-card text-card-foreground"
                        >
                            <div
                                class="rounded-t-lg border-b border-border bg-muted px-4 py-3"
                            >
                                <h3 class="font-semibold text-muted-foreground">
                                    {{ t('confirmedOrder.details.items') }}
                                </h3>
                            </div>
                            <div class="divide-y divide-border">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="p-4"
                                >
                                    <div class="flex justify-between">
                                        <div class="flex-1">
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div>
                                                    <span
                                                        class="font-medium text-card-foreground"
                                                    >
                                                        {{ item.quantity }}x
                                                        {{ item.item.name }}
                                                    </span>
                                                    <p
                                                        v-if="item.notes"
                                                        class="mt-1 text-sm text-card-foreground"
                                                    >
                                                        {{
                                                            t(
                                                                'confirmedOrder.details.item.note',
                                                            )
                                                        }}:
                                                        {{ item.notes }}
                                                    </p>
                                                </div>
                                                <span
                                                    class="font-medium text-card-foreground"
                                                >
                                                    €{{
                                                        item.total_inc_vat.toFixed(
                                                            2,
                                                        )
                                                    }}
                                                </span>
                                            </div>

                                            <div
                                                v-if="item.spicy_level"
                                                class="mt-1 text-xs text-card-foreground"
                                            >
                                                {{
                                                    t(
                                                        'confirmedOrder.spicyLevel.title',
                                                    )
                                                }}:
                                                {{
                                                    spicyLevelLabels[
                                                        item.spicy_level
                                                    ]
                                                }}
                                            </div>

                                            <div
                                                v-if="item.meat"
                                                class="mt-1 text-xs text-card-foreground"
                                            >
                                                {{
                                                    t(
                                                        'confirmedOrder.details.item.meat',
                                                    )
                                                }}: {{ item.meat.name }}
                                            </div>

                                            <div
                                                v-if="
                                                    item.removed_ingredients
                                                        ?.length
                                                "
                                                class="mt-1 text-xs text-card-foreground"
                                            >
                                                {{
                                                    t(
                                                        'confirmedOrder.details.item.removed',
                                                    )
                                                }}:
                                                {{
                                                    item.removed_ingredients
                                                        .map((i) => i.name)
                                                        .join(', ')
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-border bg-card text-card-foreground"
                        >
                            <div
                                class="rounded-t-lg border-b border-border bg-muted px-4 py-3"
                            >
                                <h3 class="font-semibold text-muted-foreground">
                                    {{ t('confirmedOrder.details.timeline') }}
                                </h3>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-primary"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            {{
                                                t(
                                                    'confirmedOrder.details.timelineStatus.created',
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs">
                                            {{ formatDate(order.created_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.confirmed_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-info"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            {{
                                                t(
                                                    'confirmedOrder.details.timelineStatus.confirmed',
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs">
                                            {{ formatDate(order.confirmed_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.prepare_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-warning"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            {{
                                                t(
                                                    'confirmedOrder.details.timelineStatus.preparationStarted',
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs">
                                            {{ formatDate(order.prepare_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.ready_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-success"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            {{
                                                t(
                                                    'confirmedOrder.details.timelineStatus.ready',
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs">
                                            {{ formatDate(order.ready_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.delivered_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-info"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            {{
                                                t(
                                                    'confirmedOrder.details.timelineStatus.delivering',
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs">
                                            {{ formatDate(order.delivered_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.completed_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-success"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            {{
                                                t(
                                                    'confirmedOrder.details.timelineStatus.completed',
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs">
                                            {{ formatDate(order.completed_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="order.cancelled_at"
                                    class="flex items-start gap-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="mt-2 h-2 w-2 rounded-full bg-destructive"
                                        ></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            {{
                                                t(
                                                    'confirmedOrder.details.timelineStatus.cancelled',
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs">
                                            {{ formatDate(order.cancelled_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="rounded-lg border border-border bg-card text-card-foreground"
                        >
                            <div
                                class="rounded-t-lg border-b border-border bg-muted px-4 py-3"
                            >
                                <h3 class="font-semibold text-muted-foreground">
                                    {{
                                        t(
                                            'confirmedOrder.details.customer.information',
                                        )
                                    }}
                                </h3>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex items-start gap-2">
                                    <User
                                        :size="16"
                                        class="mt-0.5 text-card-foreground"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-card-foreground"
                                        >
                                            {{ order.user.name }}
                                        </p>
                                        <p class="text-sm text-card-foreground">
                                            {{ order.user.email }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        order.delivery_address &&
                                        order.delivery_type !==
                                            DeliveryTypeEnum.COMPANY
                                    "
                                    class="flex items-start gap-2"
                                >
                                    <MapPin
                                        :size="16"
                                        class="mt-0.5 text-card-foreground"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-card-foreground"
                                        >
                                            {{
                                                t(
                                                    'confirmedOrder.details.customer.deliveryAddress',
                                                )
                                            }}
                                        </p>
                                        <p class="text-sm text-card-foreground">
                                            {{
                                                formatAddress(
                                                    order.delivery_address,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        order.delivery_type ===
                                            DeliveryTypeEnum.COMPANY &&
                                        order.delivery_company
                                    "
                                    class="flex items-start gap-2"
                                >
                                    <Building2
                                        :size="16"
                                        class="mt-0.5 text-card-foreground"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-medium text-card-foreground"
                                        >
                                            {{
                                                t(
                                                    'confirmedOrder.details.customer.deliveryCompany',
                                                )
                                            }}
                                        </p>

                                        <p class="text-sm text-card-foreground">
                                            {{ order.delivery_company.name }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        order.delivery_type ===
                                            DeliveryTypeEnum.COMPANY &&
                                        order.delivery_date
                                    "
                                    class="flex items-start gap-2"
                                >
                                    <CalendarDays
                                        :size="16"
                                        class="mt-0.5 text-card-foreground"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-medium text-card-foreground"
                                        >
                                            {{
                                                t(
                                                    'confirmedOrder.details.customer.deliveryDate',
                                                )
                                            }}
                                        </p>

                                        <p class="text-sm text-card-foreground">
                                            {{
                                                formatDateOnly(
                                                    order.delivery_date,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        order.type.value ===
                                        OrderTypeEnum.TAKEAWAY
                                    "
                                    class="flex items-start gap-2"
                                >
                                    <UserCircle
                                        :size="16"
                                        class="mt-0.5 text-card-foreground"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-card-foreground"
                                        >
                                            {{
                                                t(
                                                    'confirmedOrder.details.customer.pickupDetails',
                                                )
                                            }}
                                        </p>
                                        <p class="text-sm text-card-foreground">
                                            {{ order.pickup_name }}
                                        </p>
                                        <p class="text-sm text-card-foreground">
                                            {{ order.pickup_phone }}
                                        </p>
                                        <p
                                            v-if="order.pickup_time"
                                            class="text-sm text-card-foreground"
                                        >
                                            {{ formatDate(order.pickup_time) }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        order.type.value ===
                                        OrderTypeEnum.DINEIN
                                    "
                                    class="flex items-start gap-2"
                                >
                                    <Utensils
                                        :size="16"
                                        class="mt-0.5 text-card-foreground"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-card-foreground"
                                        >
                                            {{
                                                t(
                                                    'confirmedOrder.details.customer.tableNumber',
                                                )
                                            }}
                                        </p>
                                        <p class="text-sm text-card-foreground">
                                            {{
                                                t(
                                                    'confirmedOrder.details.customer.table',
                                                )
                                            }}
                                            {{ order.table_number }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="order.notes"
                                    class="flex items-start gap-2"
                                >
                                    <AlertCircle
                                        :size="16"
                                        class="mt-0.5 text-card-foreground"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-card-foreground"
                                        >
                                            {{
                                                t(
                                                    'confirmedOrder.details.notes',
                                                )
                                            }}
                                        </p>
                                        <p class="text-sm text-card-foreground">
                                            {{ order.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-border bg-card text-card-foreground"
                        >
                            <div
                                class="rounded-t-lg border-b border-border bg-muted px-4 py-3"
                            >
                                <h3 class="font-semibold text-muted-foreground">
                                    {{
                                        t(
                                            'confirmedOrder.details.payment.information',
                                        )
                                    }}
                                </h3>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-card-foreground">
                                        {{
                                            t(
                                                'confirmedOrder.details.payment.method',
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="flex items-center gap-1 text-sm font-medium text-card-foreground"
                                    >
                                        <component
                                            :is="
                                                getPaymentMethodIcon(
                                                    order.payment_method.value,
                                                )
                                            "
                                            :size="14"
                                        />
                                        {{
                                            t(
                                                `confirmedOrder.paymentMethod.${order.payment_method.key}`,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-card-foreground">
                                        {{
                                            t(
                                                'confirmedOrder.details.payment.status',
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="inline-flex gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            getPaymentStatusVariant(
                                                order.payment_status.value,
                                            )
                                        "
                                    >
                                        <component
                                            :is="
                                                getPaymentStatusIcon(
                                                    order.payment_method.value,
                                                )
                                            "
                                            :size="14"
                                        />
                                        {{
                                            t(
                                                `confirmedOrder.paymentStatus.${order.payment_status.key}`,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="order.paid_at"
                                    class="flex items-center justify-between"
                                >
                                    <span class="text-sm text-card-foreground">
                                        {{
                                            t(
                                                'confirmedOrder.details.payment.paidAt',
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="text-sm text-card-foreground"
                                        >{{ formatDate(order.paid_at) }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-border bg-card text-card-foreground"
                        >
                            <div
                                class="rounded-t-lg border-b border-border bg-muted px-4 py-3"
                            >
                                <h3 class="font-semibold text-muted-foreground">
                                    {{ t('confirmedOrder.details.summary') }}
                                </h3>
                            </div>
                            <div class="space-y-2 p-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-card-foreground">
                                        {{
                                            t(
                                                'confirmedOrder.details.price.subtotal',
                                            )
                                        }}
                                    </span>
                                    <span class="text-card-foreground"
                                        >€{{ order.subtotal.toFixed(2) }}</span
                                    >
                                </div>

                                <div
                                    v-if="order.delivery_fee > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-card-foreground">
                                        {{
                                            t(
                                                'confirmedOrder.details.price.deliveryFee',
                                            )
                                        }}
                                    </span>
                                    <span class="text-card-foreground"
                                        >€{{
                                            order.delivery_fee.toFixed(2)
                                        }}</span
                                    >
                                </div>

                                <div
                                    v-if="order.discount_total > 0"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-card-foreground">
                                        {{
                                            t(
                                                'confirmedOrder.details.price.discount',
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="inline-flex rounded-md bg-success px-2 py-0.5 text-success-foreground"
                                    >
                                        -€{{ order.discount_total.toFixed(2) }}
                                    </span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-card-foreground">
                                        {{
                                            t(
                                                'confirmedOrder.details.price.vat',
                                            )
                                        }}
                                    </span>
                                    <span class="text-card-foreground">
                                        €{{ order.vat_total.toFixed(2) }}
                                    </span>
                                </div>

                                <div
                                    v-if="order.vat_breakdown?.length"
                                    class="space-y-1 pl-4"
                                >
                                    <div
                                        v-for="vat in order.vat_breakdown"
                                        :key="vat.vat_rate"
                                        class="flex justify-between text-xs"
                                    >
                                        <span class="text-card-foreground">
                                            {{
                                                t(
                                                    'confirmedOrder.details.price.vat',
                                                )
                                            }}
                                            {{ vat.vat_rate }}%
                                        </span>
                                        <span class="text-card-foreground">
                                            €{{ vat.vat_total.toFixed(2) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="border-t border-border pt-2">
                                    <div class="flex justify-between">
                                        <span
                                            class="font-semibold text-card-foreground"
                                        >
                                            {{
                                                t(
                                                    'confirmedOrder.details.price.total',
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="text-xl font-bold text-card-foreground"
                                        >
                                            €{{
                                                order.total_inc_vat.toFixed(2)
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button
                                v-for="(btn, index) in orderStatusActions"
                                :key="index"
                                @click="btn.action"
                                class="w-full rounded-md px-4 py-2 text-sm font-medium transition-colors"
                                :class="btn.class"
                            >
                                <span
                                    v-if="orderStatusActionId === btn.id"
                                    class="flex items-center justify-center"
                                >
                                    <Loader class="h-5 w-5 animate-spin" />
                                </span>
                                <span v-else>{{ btn.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.max-h-\[calc\(100vh-8rem\)\] {
    scrollbar-width: thin;
    scrollbar-color: var(--muted-foreground) var(--muted);
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar {
    width: 8px;
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar-track {
    background: var(--muted);
    border-radius: 4px;
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar-thumb {
    background: var(--muted-foreground);
    border-radius: 4px;
}

.max-h-\[calc\(100vh-8rem\)\]::-webkit-scrollbar-thumb:hover {
    background: var(--foreground);
}
</style>
