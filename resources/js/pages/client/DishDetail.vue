<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { ShowDishType } from '@/types/dish';
import { Head } from '@inertiajs/vue3';
import AnimatedButton from '@/components/AnimatedButton.vue';

const props = defineProps<{
    dish: ShowDishType;
}>();

console.log(props.dish);
</script>

<template>
    <Head :title="props.dish.name" />

    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-8">
            <p class="mb-2 text-sm tracking-widest text-red-500 uppercase">
                [ Dish Detail ]
            </p>

            <h1 class="mb-4 text-4xl font-semibold uppercase md:text-5xl">
                {{ props.dish.name }}
            </h1>

            <div class="mb-6 flex flex-wrap items-center gap-2">
                <span
                    class="rounded-full px-3 py-1 text-sm font-semibold text-white"
                    :class="props.dish.category.color"
                >
                    {{ props.dish.category.label }}
                </span>

                <span
                    class="rounded-full border px-3 py-1 text-sm font-semibold"
                    :class="
                        props.dish.is_available === 'Available'
                            ? 'border-green-500 text-green-500'
                            : 'border-gray-400 text-gray-400'
                    "
                >
                    {{ props.dish.is_available }}
                </span>
            </div>

            <div class="grid gap-8 md:grid-cols-2">
                <div class="overflow-hidden rounded-md">
                    <img
                        :src="props.dish.main_image"
                        :alt="props.dish.name"
                        class="h-[300px] w-full object-cover transition-all duration-700 sm:h-[450px]"
                    />
                </div>

                <div class="flex flex-col justify-between">
                    <div>
                        <p class="mb-6 text-gray-600">
                            {{ props.dish.description }}
                        </p>

                        <p class="mb-6 text-2xl font-semibold text-red-500">
                            € {{ props.dish.price.toFixed(2) }}
                        </p>

                        <div v-if="props.dish.ingredients.length" class="mb-6">
                            <p
                                class="mb-2 text-sm tracking-widest text-gray-400 uppercase"
                            >
                                [ Ingredients ]
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <div
                                    v-for="ingredient in props.dish.ingredients"
                                    :key="ingredient.id"
                                    class="relative flex flex-col items-center gap-1"
                                    :title="ingredient.name"
                                >
                                    <img
                                        :src="ingredient.main_image"
                                        :alt="ingredient.name"
                                        class="h-16 w-16 rounded-full border object-cover opacity-50 transition-all duration-500 hover:opacity-100"
                                    />

                                    <div class="text-xs text-gray-500">
                                        {{ ingredient.name }}
                                    </div>

                                    <div
                                        v-if="ingredient.allergen"
                                        class="absolute -top-2 -right-0"
                                    >
                                        <div class="group relative">
                                            <div
                                                class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-white"
                                            >
                                                !
                                            </div>
                                            <div
                                                class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 flex w-max -translate-x-1/2 translate-y-1 scale-95 items-center gap-2 rounded-lg bg-yellow-50 px-2 py-1 text-xs text-gray-700 opacity-0 shadow-lg transition-all duration-200 group-hover:translate-y-0 group-hover:scale-100 group-hover:opacity-100"
                                            >
                                                <img
                                                    :src="
                                                        ingredient.allergen
                                                            .main_image
                                                    "
                                                    :alt="
                                                        ingredient.allergen.name
                                                    "
                                                    class="h-5 w-5 rounded-full object-cover"
                                                />
                                                <div
                                                    class="font-semibold whitespace-nowrap"
                                                >
                                                    {{
                                                        ingredient.allergen.name
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <AnimatedButton text="Add to cart" />

                        <button
                            class="rounded-md border px-6 py-2 text-sm transition hover:bg-gray-100"
                            @click="$inertia.visit('/menu')"
                        >
                            Back to menu
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>

<style scoped>

</style>
