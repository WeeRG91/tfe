<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import { useCartStore } from '@/stores/cart';
import { ItemTypeEnum } from '@/types/cart';
import { ClientDrinkType } from '@/types/drink';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const { t } = useI18n();

const props = defineProps<{
    drink: ClientDrinkType;
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const cartStore = useCartStore();
const { isLoading } = storeToRefs(cartStore);

const quantity = ref<number>(1);
const notes = ref<string>('');
const error = ref<string>('');

const totalPrice = computed(() => {
    const base = props.drink.price ?? 0;

    return base * quantity.value;
});

const addToCart = async () => {
    try {
        const response = await cartStore.addDrink({
            item_id: props.drink.id,
            item_type: ItemTypeEnum.DRINK,
            quantity: quantity.value,
            notes: notes.value,
        });

        emit('close');

        toast.success(response.message);
    } catch (error) {
        console.log(error);
        toast.error(t('drinks.addDrinkModal.addFailed'));
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) quantity.value--;
};

const incrementQuantity = () => {
    quantity.value++;
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
            quantity.value = 1;
            notes.value = '';
            error.value = '';
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Teleport to="body">
        <Transition name="client-modal" appear>
            <div
                v-if="open"
                @click="$emit('close')"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    @click.stop
                    class="client-modal-panel relative flex w-full max-w-md flex-col rounded-2xl border border-border bg-card text-card-foreground shadow-xl"
                    style="max-height: 90vh"
                >
                    <div class="relative flex-shrink-0">
                        <img
                            :src="drink.main_image"
                            :alt="drink.name"
                            class="h-32 w-full rounded-t-2xl object-cover"
                        />
                        <button
                            @click="$emit('close')"
                            class="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-full bg-popover/90 text-popover-foreground hover:bg-popover"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4">
                        <div class="mb-3">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-bold uppercase">
                                    {{ drink.name }}
                                </h2>
                                <span class="text-lg font-bold text-primary">
                                    €{{ formatPrice(drink.price) }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h3
                                class="mb-2 text-xs font-semibold tracking-wider text-card-foreground uppercase"
                            >
                                {{
                                    t(
                                        'drinks.addDrinkModal.specialInstructions',
                                    )
                                }}
                            </h3>
                            <div class="relative">
                                <textarea
                                    v-model="notes"
                                    rows="1"
                                    :placeholder="
                                        t(
                                            'drinks.addDrinkModal.specialInstructionsPlaceholder',
                                        )
                                    "
                                    class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground transition placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none"
                                    :class="{
                                        'border-primary ring-2 ring-primary/20':
                                            notes.length > 0,
                                    }"
                                ></textarea>
                                <div
                                    v-if="notes.length > 0"
                                    class="absolute right-2 bottom-2 text-xs text-muted-foreground"
                                >
                                    {{ notes.length }}/500
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h3
                                class="mb-2 text-xs font-semibold tracking-wider text-card-foreground uppercase"
                            >
                                {{ t('drinks.addDrinkModal.quantity') }}
                            </h3>
                            <div class="flex items-center gap-3">
                                <button
                                    @click="decrementQuantity"
                                    :disabled="quantity <= 1"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-input bg-background text-foreground transition hover:border-primary hover:bg-primary/10 hover:text-primary disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    −
                                </button>
                                <span
                                    class="w-10 text-center text-lg font-semibold"
                                >
                                    {{ quantity }}
                                </span>
                                <button
                                    @click="incrementQuantity"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-input bg-background text-foreground transition hover:border-primary hover:bg-primary/10 hover:text-primary"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-border p-5 pt-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-xs tracking-wider text-muted-foreground uppercase"
                            >
                                {{ t('drinks.addDrinkModal.total') }}
                            </span>
                            <span class="text-2xl font-bold text-primary">
                                €{{ totalPrice.toFixed(2) }}
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="emit('close')"
                                class="flex-1 rounded-lg border border-input bg-background py-3 text-sm font-medium text-foreground transition hover:bg-accent hover:text-accent-foreground"
                            >
                                {{ t('drinks.addDrinkModal.cancel') }}
                            </button>
                            <button
                                @click="addToCart"
                                class="group relative flex-1 overflow-hidden rounded-lg bg-gradient-to-r from-primary to-primary/80 py-3 text-primary-foreground transition-all hover:shadow-lg hover:shadow-primary/20"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center"
                                >
                                    <span
                                        v-if="isLoading"
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground border-t-transparent text-primary-foreground"
                                    ></span>
                                    <span v-else>
                                        {{
                                            t('drinks.addDrinkModal.addToCart')
                                        }}
                                        <span
                                            class="animate-arrow ml-2 inline-block"
                                            >→</span
                                        >
                                    </span>
                                </span>
                                <div
                                    class="absolute inset-0 -translate-x-full transform bg-gradient-to-r from-primary/90 to-primary transition-transform duration-300 group-hover:translate-x-0"
                                ></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.animate-arrow {
    transition: transform 0.2s ease;
}

button:hover .animate-arrow {
    transform: translateX(4px);
}

.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: var(--border) var(--muted);
}

.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: var(--muted);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: var(--muted-foreground);
}

.fixed {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.client-modal-enter-active,
.client-modal-leave-active {
    transition: opacity 200ms ease;
}

.client-modal-enter-active .client-modal-panel,
.client-modal-leave-active .client-modal-panel {
    transition:
        opacity 200ms ease,
        transform 200ms ease;
}

.client-modal-enter-from,
.client-modal-leave-to {
    opacity: 0;
}

.client-modal-enter-from .client-modal-panel,
.client-modal-leave-to .client-modal-panel {
    opacity: 0;
    transform: translateY(-1rem) scale(0.95);
}
</style>
