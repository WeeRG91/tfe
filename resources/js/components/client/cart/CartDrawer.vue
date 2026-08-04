<script setup lang="ts">
import { getSpicyLevelLabels } from '@/lib/const';
import { formatPrice } from '@/lib/utils';
import { useCartStore } from '@/stores/cart';
import { ItemTypeEnum } from '@/types/cart';
import {
    Coffee,
    CreditCard,
    Minus,
    Plus,
    ShoppingBag,
    Trash2,
    Utensils,
    X,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
}>();

const { t } = useI18n();
const spicyLevelLabels = computed(() => getSpicyLevelLabels(t));

const cartStore = useCartStore();
const { items } = storeToRefs(cartStore);

const loadingRemoveItemId = ref<number | null>(null);

const dishItems = computed(() =>
    items.value.filter((item) => item.item_type === ItemTypeEnum.DISH),
);

const drinkItems = computed(() =>
    items.value.filter((item) => item.item_type === ItemTypeEnum.DRINK),
);

const subtotalDishes = computed(() => {
    return dishItems.value.reduce(
        (sum, item) => sum + (item.total_inc_vat || 0),
        0,
    );
});

const subtotalDrinks = computed(() => {
    return drinkItems.value.reduce(
        (sum, item) => sum + (item.total_inc_vat || 0),
        0,
    );
});

const subtotal = computed(() => {
    return subtotalDishes.value + subtotalDrinks.value;
});

const removeItemFromCart = async (cartItemId: number) => {
    loadingRemoveItemId.value = cartItemId;

    try {
        await cartStore.removeItem(cartItemId);
    } catch (error) {
        console.log('Failed to remove item: ', error);
    } finally {
        loadingRemoveItemId.value = null;
    }
};

const updateQuantity = async (
    cartItemId: number,
    action: 'increase' | 'decrease',
) => {
    try {
        await cartStore.updateQuantity(cartItemId, action);
    } catch (error) {
        console.log('Failed to update quantity: ', error);
    }
};

onMounted(() => {
    if (props.open) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Transition name="fade">
        <div
            v-if="props.open"
            class="fixed inset-0 z-40 bg-gradient-to-b from-black/60 to-black/40 backdrop-blur-sm"
            @click="onClose"
        />
    </Transition>

    <Transition name="slide">
        <aside
            v-if="props.open"
            class="fixed top-0 right-0 z-50 flex h-full w-full max-w-md flex-col bg-gradient-to-b from-white to-gray-50 shadow-2xl"
        >
            <div
                class="relative flex items-center justify-between border-b border-gray-100 bg-white/80 px-6 py-5 backdrop-blur-sm"
            >
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-red-100 p-2">
                        <ShoppingBag class="h-5 w-5 text-red-500" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ t('cart.cartDrawer.title') }}
                    </h2>
                    <span
                        v-if="items.length"
                        class="rounded-full bg-red-500 px-2 py-0.5 text-xs font-semibold text-white"
                    >
                        {{ items.length }}
                    </span>
                </div>

                <button
                    @click="props.onClose"
                    class="rounded-full p-2 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-600"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div
                    v-if="items.length === 0"
                    class="flex h-full flex-col items-center justify-center px-4"
                >
                    <div class="mb-4 rounded-full bg-gray-100 p-6">
                        <ShoppingBag class="h-12 w-12 text-gray-400" />
                    </div>
                    <p class="mb-2 text-xl font-semibold text-gray-600">
                        {{ t('cart.cartDrawer.empty.title') }}
                    </p>
                    <p class="text-center text-gray-400">
                        {{ t('cart.cartDrawer.empty.description') }}
                    </p>
                </div>

                <div v-else class="space-y-6">
                    <div v-if="dishItems.length > 0" class="space-y-3">
                        <div
                            class="sticky top-0 z-10 bg-gray-50/95 px-4 py-2 backdrop-blur-sm"
                        >
                            <div class="flex items-center gap-2">
                                <Utensils class="h-4 w-4 text-red-500" />
                                <h3
                                    class="text-sm font-semibold tracking-wide text-gray-600 uppercase"
                                >
                                    {{ t('cart.cartDrawer.dishes') }}
                                </h3>
                                <span class="text-xs text-gray-400">
                                    {{
                                        t(
                                            'cart.cartDrawer.items',
                                            dishItems.length,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-0 divide-y divide-gray-100">
                            <div
                                v-for="item in dishItems"
                                :key="item.id"
                                class="group relative bg-white p-4 transition-all hover:bg-gray-50"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="relative h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100"
                                    >
                                        <img
                                            :src="item.item.main_image"
                                            :alt="item.item.name"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                                        />
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                                        ></div>
                                    </div>

                                    <div class="flex-1">
                                        <div
                                            class="mb-2 flex items-start justify-between gap-2"
                                        >
                                            <h3
                                                class="line-clamp-2 flex-1 text-base font-semibold text-gray-800"
                                            >
                                                {{ item.item.name }}
                                            </h3>
                                            <span
                                                v-if="
                                                    loadingRemoveItemId ===
                                                    item.id
                                                "
                                                class="h-4 w-4 animate-spin rounded-full border-2 border-red-500 border-t-transparent p-1.5"
                                            ></span>
                                            <button
                                                v-else
                                                @click.prevent="
                                                    removeItemFromCart(item.id)
                                                "
                                                class="rounded-full p-1 text-gray-400 opacity-0 transition-all group-hover:opacity-100 hover:bg-red-50 hover:text-red-500"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>

                                        <div
                                            class="mb-3 flex w-full items-baseline justify-between"
                                        >
                                            <div
                                                class="flex items-baseline gap-2"
                                            >
                                                <p
                                                    class="text-lg font-bold text-red-500"
                                                >
                                                    €{{
                                                        formatPrice(
                                                            item.unit_price,
                                                        )
                                                    }}
                                                </p>
                                            </div>

                                            <div
                                                class="flex items-center gap-2 rounded-lg bg-gray-50 p-1"
                                            >
                                                <button
                                                    @click.prevent="
                                                        updateQuantity(
                                                            item.id,
                                                            'decrease',
                                                        )
                                                    "
                                                    :disabled="
                                                        item.quantity <= 1
                                                    "
                                                    class="flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 bg-white transition-all hover:border-red-300 hover:bg-red-50 hover:text-red-500 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    <Minus
                                                        class="h-2 w-2 text-gray-600"
                                                    />
                                                </button>
                                                <span
                                                    class="min-w-[32px] text-center text-sm font-semibold text-gray-800"
                                                >
                                                    {{ item.quantity }}
                                                </span>
                                                <button
                                                    @click.prevent="
                                                        updateQuantity(
                                                            item.id,
                                                            'increase',
                                                        )
                                                    "
                                                    class="flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 bg-white transition-all hover:border-red-300 hover:bg-red-50 hover:text-red-500"
                                                >
                                                    <Plus
                                                        class="h-2 w-2 text-gray-600"
                                                    />
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-2 space-y-1.5">
                                            <div
                                                class="flex items-center gap-1.5"
                                            >
                                                <span
                                                    class="text-xs font-medium text-gray-500"
                                                >
                                                    {{
                                                        t(
                                                            'common.spicyLevel.title',
                                                        )
                                                    }}:
                                                </span>
                                                <div
                                                    class="text-xs font-medium text-gray-600"
                                                >
                                                    <span
                                                        :class="[
                                                            item.spicy_level ===
                                                                0 &&
                                                                'text-green-600',
                                                            item.spicy_level ===
                                                                1 &&
                                                                'text-yellow-600',
                                                            item.spicy_level ===
                                                                2 &&
                                                                'text-orange-600',
                                                            item.spicy_level ===
                                                                3 &&
                                                                'text-red-600',
                                                        ]"
                                                    >
                                                        {{
                                                            spicyLevelLabels[
                                                                item.spicy_level
                                                            ]
                                                        }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div
                                                v-if="item.meat"
                                                class="flex items-center gap-1.5"
                                            >
                                                <span
                                                    class="text-xs font-medium text-gray-500"
                                                >
                                                    {{
                                                        t(
                                                            'cart.cartDrawer.meat',
                                                        )
                                                    }}:
                                                </span>
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <span
                                                        class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700"
                                                    >
                                                        {{ item.meat.name }}
                                                    </span>
                                                    <span
                                                        class="text-xs font-medium text-amber-600"
                                                    >
                                                        +€{{ item.meat.extra_price }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div
                                                v-if="
                                                    item.removed_ingredients
                                                        ?.length
                                                "
                                                class="flex items-center gap-1.5"
                                            >
                                                <span
                                                    class="text-xs font-medium text-gray-500"
                                                    >{{
                                                        t(
                                                            'cart.cartDrawer.removed',
                                                        )
                                                    }}:</span
                                                >
                                                <div
                                                    class="flex flex-wrap gap-1"
                                                >
                                                    <span
                                                        v-if="
                                                            item
                                                                .removed_ingredients[0]
                                                        "
                                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600"
                                                    >
                                                        {{
                                                            item
                                                                .removed_ingredients[0]
                                                                .name
                                                        }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            item
                                                                .removed_ingredients[1]
                                                        "
                                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600"
                                                    >
                                                        +{{
                                                            item
                                                                .removed_ingredients
                                                                .length - 1
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="mt-3 border-t border-gray-100 pt-2"
                                        >
                                            <div
                                                class="flex items-center justify-between text-xs"
                                            >
                                                <span class="text-gray-500"
                                                    >{{
                                                        t(
                                                            'cart.cartDrawer.itemTotal',
                                                        )
                                                    }}:</span
                                                >
                                                <span
                                                    class="text-sm font-semibold text-gray-800"
                                                    >€{{
                                                        formatPrice(
                                                            item.total_inc_vat,
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="drinkItems.length > 0" class="space-y-3">
                        <div
                            class="sticky top-0 z-10 bg-gray-50/95 px-4 py-2 backdrop-blur-sm"
                        >
                            <div class="flex items-center gap-2">
                                <Coffee class="h-4 w-4 text-blue-500" />
                                <h3
                                    class="text-sm font-semibold tracking-wide text-gray-600 uppercase"
                                >
                                    {{ t('cart.cartDrawer.drinks') }}
                                </h3>
                                <span class="text-xs text-gray-400">
                                    {{
                                        t(
                                            'cart.cartDrawer.items',
                                            drinkItems.length,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-0 divide-y divide-gray-100">
                            <div
                                v-for="item in drinkItems"
                                :key="item.id"
                                class="group relative bg-white p-4 transition-all hover:bg-gray-50"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="relative h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100"
                                    >
                                        <img
                                            :src="item.item.main_image"
                                            :alt="item.item.name"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                                        />
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                                        ></div>
                                    </div>

                                    <div class="flex-1">
                                        <div
                                            class="mb-2 flex items-start justify-between gap-2"
                                        >
                                            <h3
                                                class="line-clamp-2 flex-1 text-base font-semibold text-gray-800"
                                            >
                                                {{ item.item.name }}
                                            </h3>
                                            <span
                                                v-if="
                                                    loadingRemoveItemId ===
                                                    item.id
                                                "
                                                class="h-4 w-4 animate-spin rounded-full border-2 border-red-500 border-t-transparent p-1.5"
                                            ></span>
                                            <button
                                                v-else
                                                @click.prevent="
                                                    removeItemFromCart(item.id)
                                                "
                                                class="rounded-full p-1 text-gray-400 opacity-0 transition-all group-hover:opacity-100 hover:bg-red-50 hover:text-red-500"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>

                                        <div
                                            class="mb-3 flex w-full items-baseline justify-between"
                                        >
                                            <p
                                                class="text-lg font-bold text-blue-500"
                                            >
                                                €{{ item.unit_price }}
                                            </p>

                                            <div
                                                class="flex items-center gap-2 rounded-lg bg-gray-50 p-1"
                                            >
                                                <button
                                                    @click.prevent="
                                                        updateQuantity(
                                                            item.id,
                                                            'decrease',
                                                        )
                                                    "
                                                    :disabled="
                                                        item.quantity <= 1
                                                    "
                                                    class="flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 bg-white transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    <Minus
                                                        class="h-2 w-2 text-gray-600"
                                                    />
                                                </button>
                                                <span
                                                    class="min-w-[32px] text-center text-sm font-semibold text-gray-800"
                                                >
                                                    {{ item.quantity }}
                                                </span>
                                                <button
                                                    @click.prevent="
                                                        updateQuantity(
                                                            item.id,
                                                            'increase',
                                                        )
                                                    "
                                                    class="flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 bg-white transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-500"
                                                >
                                                    <Plus
                                                        class="h-2 w-2 text-gray-600"
                                                    />
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            class="mt-3 border-t border-gray-100 pt-2"
                                        >
                                            <div
                                                class="flex items-center justify-between text-xs"
                                            >
                                                <span class="text-gray-500"
                                                    >{{
                                                        t(
                                                            'cart.cartDrawer.itemTotal',
                                                        )
                                                    }}:</span
                                                >
                                                <span
                                                    class="text-sm font-semibold text-gray-800"
                                                    >€{{
                                                        item.total_inc_vat
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 bg-white/95 backdrop-blur-sm">
                <div class="space-y-2 p-4">
                    <div
                        class="flex justify-between p-3 text-base font-bold text-gray-800"
                    >
                        <span>{{ t('cart.cartDrawer.total') }}</span>
                        <span class="text-red-500"
                            >€{{ formatPrice(subtotal) }}</span
                        >
                    </div>

                    <button
                        @click="$inertia.visit('/cart/checkout')"
                        class="group relative w-full overflow-hidden rounded-xl bg-gradient-to-r from-red-500 to-red-600 py-3 text-white transition-all hover:shadow-lg hover:shadow-red-200"
                    >
                        <span
                            class="relative z-10 flex items-center justify-center gap-2 font-semibold"
                        >
                            <CreditCard class="h-5 w-5" />
                            {{ t('cart.cartDrawer.checkout') }}
                        </span>
                        <div
                            class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-red-600 to-red-700 transition-transform duration-300 group-hover:translate-x-0"
                        ></div>
                    </button>

                    <p class="text-center text-xs text-gray-400">
                        {{ t('cart.cartDrawer.securePayment') }}
                    </p>
                </div>
            </div>
        </aside>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}

.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #f9a8a8;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #ef4444;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
