<script setup lang="ts">
import { ClientDishType } from '@/types/dish';
import { router } from '@inertiajs/vue3';
import menu from '@/routes/menu';

const props = defineProps<{
    dish: ClientDishType;
}>();

defineEmits<{
    (e: 'add', dish: ClientDishType): void;
}>();

const goToDetail = () => {
    router.visit(menu.showDish(props.dish.id).url);
};
</script>

<template>
    <div
        @click="goToDetail"
        class="group flex h-full cursor-pointer flex-col overflow-hidden rounded-md border bg-white transition hover:shadow-lg"
    >
        <div class="overflow-hidden">
            <img
                :src="props.dish.main_image"
                :alt="props.dish.name"
                class="h-48 w-full object-cover transition duration-500 group-hover:scale-105"
            />
        </div>

        <div class="flex flex-1 flex-col px-4 py-2">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold uppercase">
                    {{ props.dish.name }}
                </h3>
                <span class="font-semibold text-red-500">
                    €{{ props.dish.price }}
                </span>
            </div>

            <p class="mt-2 line-clamp-2 text-sm text-gray-600">
                {{ props.dish.description }}
            </p>

            <div class="mt-auto">
                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <span
                            v-for="i in 5"
                            :key="i"
                            class="text-sm"
                            :class="
                                i <= 3 ? 'text-yellow-400' : 'text-gray-300'
                            "
                        >
                            ★
                        </span>

                        <span class="ml-1 text-xs text-gray-400">
                            ({{ 3 }})
                        </span>
                    </div>

                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="text-red-700">♥</span>
                        <span>{{ 0 }}</span>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3">
                    <span
                        class="text-xs tracking-widest text-gray-400 uppercase"
                    >
                        {{ props.dish.category.label }}
                    </span>

                    <button
                        @click.stop="$emit('add', props.dish)"
                        class="cursor-pointer text-sm text-red-500 transition"
                    >
                        Add <span class="animate-arrow inline-block">→</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
