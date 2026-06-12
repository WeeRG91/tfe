<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import menu from '@/routes/menu';
import { ClientDishType, DishAvailabilityEnum } from '@/types/dish';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    dish: ClientDishType;
}>();

const emit = defineEmits<{
    add: [dish: ClientDishType];
}>();

const goToDetail = () => {
    if (props.dish.is_available === DishAvailabilityEnum.AVAILABLE) {
        router.visit(menu.showDish(props.dish.id).url);
    }
};
</script>

<template>
    <div
        @click="goToDetail"
        class="group flex h-full cursor-pointer flex-col overflow-hidden rounded-md border bg-white transition-all duration-300"
        :class="{
            'opacity-60 grayscale hover:opacity-70':
                props.dish.is_available === DishAvailabilityEnum.UNAVAILABLE,
            'hover:scale-[1.02] hover:shadow-lg':
                props.dish.is_available === DishAvailabilityEnum.AVAILABLE,
            'cursor-not-allowed':
                props.dish.is_available === DishAvailabilityEnum.UNAVAILABLE,
        }"
    >
        <div class="relative overflow-hidden">
            <img
                :src="props.dish.main_image"
                :alt="props.dish.name"
                class="h-48 w-full object-cover transition duration-500"
                :class="{
                    'group-hover:scale-105':
                        props.dish.is_available ===
                        DishAvailabilityEnum.AVAILABLE,
                    'group-hover:scale-100':
                        props.dish.is_available ===
                        DishAvailabilityEnum.UNAVAILABLE,
                }"
            />
        </div>

        <div class="flex flex-1 flex-col px-4 py-2">
            <div class="flex items-center justify-between">
                <h3
                    class="text-lg font-semibold uppercase transition-colors"
                    :class="{
                        'text-gray-400':
                            props.dish.is_available ===
                            DishAvailabilityEnum.UNAVAILABLE,
                    }"
                >
                    {{ props.dish.name }}
                </h3>
                <span
                    class="font-semibold transition-colors"
                    :class="{
                        'text-gray-400':
                            props.dish.is_available ===
                            DishAvailabilityEnum.UNAVAILABLE,
                        'text-red-500':
                            props.dish.is_available ===
                            DishAvailabilityEnum.AVAILABLE,
                    }"
                >
                    €{{ formatPrice(props.dish.price) }}
                </span>
            </div>

            <p
                class="mt-2 line-clamp-2 text-sm transition-colors"
                :class="{
                    'text-gray-400':
                        props.dish.is_available ===
                        DishAvailabilityEnum.UNAVAILABLE,
                    'text-gray-600':
                        props.dish.is_available ===
                        DishAvailabilityEnum.AVAILABLE,
                }"
            >
                {{ props.dish.description }}
            </p>

            <div class="mt-auto">
                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <span
                            v-for="i in 5"
                            :key="i"
                            class="text-sm transition-colors"
                            :class="{
                                'text-gray-300':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.UNAVAILABLE,
                                'text-yellow-400':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.AVAILABLE,
                            }"
                        >
                            ★
                        </span>

                        <span
                            class="ml-1 text-xs transition-colors"
                            :class="{
                                'text-gray-400':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.UNAVAILABLE,
                            }"
                        >
                            ({{ 3 }})
                        </span>
                    </div>

                    <div
                        class="flex items-center gap-1 text-xs transition-colors"
                        :class="{
                            'text-gray-400':
                                props.dish.is_available ===
                                DishAvailabilityEnum.UNAVAILABLE,
                            'text-gray-500':
                                props.dish.is_available ===
                                DishAvailabilityEnum.AVAILABLE,
                        }"
                    >
                        <span
                            :class="{
                                'text-gray-400':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.UNAVAILABLE,
                                'text-red-700':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.AVAILABLE,
                            }"
                        >
                            ♥
                        </span>
                        <span>{{ 0 }}</span>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3">
                    <span
                        class="text-xs tracking-widest uppercase transition-colors"
                        :class="{
                            'text-gray-400':
                                props.dish.is_available ===
                                DishAvailabilityEnum.UNAVAILABLE,
                        }"
                    >
                        {{ props.dish.category.label }}
                    </span>

                    <button
                        v-if="
                            props.dish.is_available ===
                            DishAvailabilityEnum.AVAILABLE
                        "
                        @click.stop="emit('add', props.dish)"
                        class="cursor-pointer text-sm text-red-500 transition-all duration-300 hover:translate-x-1 hover:text-red-600"
                    >
                        Add
                        <span
                            class="inline-block transition-transform group-hover:translate-x-1"
                            >→</span
                        >
                    </button>

                    <button
                        v-else
                        disabled
                        class="cursor-not-allowed text-sm text-gray-400"
                    >
                        Unavailable
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cursor-not-allowed {
    cursor: not-allowed;
}
</style>
