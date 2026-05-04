<script setup lang="ts">
import { ClientDrinkType } from '@/types/drink';
import { formatPrice } from '@/lib/utils';

const props = defineProps<{
    drink: ClientDrinkType;
}>();

defineEmits<{
    (e: 'add', drink: ClientDrinkType): void;
}>();
</script>

<template>
    <div
        class="group flex h-full cursor-pointer overflow-hidden rounded-md border bg-white transition hover:shadow-lg"
    >
        <div class="relative h-full w-44 overflow-hidden">
            <img
                :src="props.drink.main_image"
                :alt="props.drink.name"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />

            <span
                class="absolute top-2 left-2 rounded-full px-2 py-1 text-xs font-semibold"
                :class="
                    props.drink.is_available === 'Available'
                        ? 'bg-green-100 text-green-600'
                        : 'bg-gray-200 text-gray-500'
                "
            >
                {{ props.drink.is_available }}
            </span>
        </div>

        <div class="flex flex-1 flex-col px-4 py-3">
            <div class="flex items-start justify-between gap-2">
                <h3 class="text-md leading-tight font-semibold uppercase">
                    {{ props.drink.name }}
                </h3>
                <span class="font-semibold whitespace-nowrap text-red-500">
                    €{{ formatPrice(props.drink.price) }}
                </span>
            </div>

            <p class="mt-1 line-clamp-3 text-sm text-gray-600">
                {{ props.drink.description }}
            </p>

            <div class="mt-auto">
                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <span
                            v-for="i in 5"
                            :key="i"
                            class="text-sm"
                            :class="
                                i <= 4 ? 'text-yellow-400' : 'text-gray-300'
                            "
                        >
                            ★
                        </span>
                        <span class="ml-1 text-xs text-gray-400">(4)</span>
                    </div>

                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="text-red-700">♥</span>
                        <span>0</span>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <span
                        class="text-xs tracking-widest text-gray-400 uppercase"
                    >
                        {{ props.drink.category.label }}
                    </span>

                    <button
                        @click.stop="$emit('add', props.drink)"
                        class="text-sm text-red-500 transition group-hover:translate-x-1"
                    >
                        Add <span class="animate-arrow inline-block">→</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
