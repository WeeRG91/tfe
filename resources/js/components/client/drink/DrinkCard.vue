<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import { ClientDrinkType, DrinkAvailabilityEnum } from '@/types/drink';

const props = defineProps<{
    drink: ClientDrinkType;
}>();

const emit = defineEmits<{
    add: [drink: ClientDrinkType];
}>();
</script>

<template>
    <div
        class="group flex h-full cursor-pointer overflow-hidden rounded-md border bg-white transition-all duration-300"
        :class="{
            'opacity-60 grayscale hover:opacity-70':
                props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
            'hover:scale-[1.02] hover:shadow-lg':
                props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
            'cursor-not-allowed': props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
        }"
    >
        <div class="relative h-full w-44 overflow-hidden">
            <img
                :src="props.drink.main_image"
                :alt="props.drink.name"
                class="h-full w-full object-cover transition duration-500"
                :class="{
                    'group-hover:scale-100':
                        props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                    'group-hover:scale-105':
                        props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                }"
            />
        </div>

        <div class="flex flex-1 flex-col px-4 py-3">
            <div class="flex items-start justify-between gap-2">
                <h3
                    class="text-md leading-tight font-semibold uppercase transition-colors"
                    :class="{
                        'text-gray-400':
                            props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                    }"
                >
                    {{ props.drink.name }}
                </h3>
                <span
                    class="font-semibold whitespace-nowrap transition-colors"
                    :class="{
                        'text-gray-400':
                            props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                        'text-red-500':
                            props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                    }"
                >
                    €{{ formatPrice(props.drink.price) }}
                </span>
            </div>

            <p
                class="mt-1 line-clamp-3 text-sm transition-colors"
                :class="{
                    'text-gray-400': props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                    'text-gray-600': props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                }"
            >
                {{ props.drink.description }}
            </p>

            <div class="mt-auto">
                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <span
                            v-for="i in 4"
                            :key="i"
                            class="text-sm transition-colors"
                            :class="{
                                'text-gray-300':
                                    props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                                'text-yellow-400':
                                    props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                            }"
                        >
                            ★
                        </span>
                        <span
                            class="ml-1 text-xs transition-colors"
                            :class="{
                                'text-gray-400':
                                    props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                                'text-gray-500':
                                    props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                            }"
                        >
                            (4)
                        </span>
                    </div>

                    <div
                        class="flex items-center gap-1 text-xs transition-colors"
                        :class="{
                            'text-gray-400':
                                props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                            'text-gray-500':
                                props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                        }"
                    >
                        <span
                            :class="{
                                'text-gray-400':
                                    props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                                'text-red-700':
                                    props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                            }"
                        >
                            ♥
                        </span>
                        <span>0</span>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <span
                        class="text-xs tracking-widest uppercase transition-colors"
                        :class="{
                            'text-gray-400':
                                props.drink.is_available === DrinkAvailabilityEnum.UNAVAILABLE,
                            'text-gray-500':
                                props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE,
                        }"
                    >
                        {{ props.drink.category.label }}
                    </span>

                    <button
                        v-if="props.drink.is_available === DrinkAvailabilityEnum.AVAILABLE"
                        @click.stop="emit('add', props.drink)"
                        class="text-sm text-red-500 transition-all duration-300 hover:translate-x-1 hover:text-red-600"
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
