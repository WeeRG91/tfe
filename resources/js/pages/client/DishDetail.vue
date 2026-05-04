<script setup lang="ts">
import AnimatedButton from '@/components/AnimatedButton.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { ClientDishType } from '@/types/dish';
import { Head } from '@inertiajs/vue3';
import AddDishToCartModal from '@/components/client/cart/AddDishToCartModal.vue';
import { ref } from 'vue';

const props = defineProps<{
    dish: ClientDishType;
}>();

const isAddModalOpen = ref<boolean>(false);

const openAddModal = () => {
    isAddModalOpen.value = true;
};
</script>

<template>
    <Head :title="props.dish.name" />

    <ClientLayout>
        <section
            class="relative mx-auto flex h-full w-full max-w-7xl flex-col px-4 py-12 sm:px-6 lg:px-8 lg:py-16"
        >
            <div
                class="relative z-10 mb-6 flex items-center gap-2 text-sm text-gray-500"
            >
                <button
                    @click="$inertia.visit('/menu')"
                    class="cursor-pointer transition-colors hover:text-red-500"
                >
                    Menu
                </button>
                <span>/</span>
                <span class="font-medium text-gray-900">{{
                    props.dish.name
                }}</span>
            </div>

            <div
                class="relative z-10 grid flex-1 gap-10 md:grid-cols-2 lg:gap-12"
            >
                <div class="overflow-hidden rounded-2xl bg-gray-100 shadow-lg">
                    <img
                        :src="props.dish.main_image"
                        :alt="props.dish.name"
                        class="h-full w-full object-cover transition duration-700 hover:scale-105"
                    />
                </div>

                <div class="flex flex-col">
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full px-4 py-1.5 text-sm font-semibold shadow-sm"
                            :class="[props.dish.category.color, 'text-white']"
                        >
                            {{ props.dish.category.label }}
                        </span>

                        <span
                            class="rounded-full border px-4 py-1.5 text-sm font-semibold shadow-sm"
                            :class="
                                props.dish.is_available === 'Available'
                                    ? 'border-green-500 bg-green-50 text-green-700'
                                    : 'border-gray-300 bg-gray-50 text-gray-500'
                            "
                        >
                            <span class="inline-flex items-center gap-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        v-if="
                                            props.dish.is_available ===
                                            'Available'
                                        "
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex h-2 w-2 rounded-full"
                                        :class="
                                            props.dish.is_available ===
                                            'Available'
                                                ? 'bg-green-500'
                                                : 'bg-gray-400'
                                        "
                                    ></span>
                                </span>
                                {{ props.dish.is_available }}
                            </span>
                        </span>
                    </div>

                    <h1
                        class="mb-3 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl"
                    >
                        {{ props.dish.name }}
                    </h1>

                    <div class="mb-6 border-l-4 border-gray-200 pl-4">
                        <p class="leading-relaxed text-gray-600">
                            {{ props.dish.description }}
                        </p>
                    </div>

                    <div class="mb-8 flex items-baseline gap-2">
                        <span
                            class="text-3xl font-bold text-red-600 sm:text-4xl"
                            >€ {{ props.dish.price.toFixed(2) }}</span
                        >
                        <span class="text-sm text-gray-400">incl. VAT</span>
                    </div>

                    <div v-if="props.dish.ingredients.length" class="mb-8">
                        <div class="mb-4 flex items-center gap-2">
                            <span
                                class="text-sm font-semibold tracking-wide text-gray-400 uppercase"
                                >Ingredients</span
                            >
                            <div
                                class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent"
                            ></div>
                        </div>

                        <div class="flex flex-wrap gap-5 pl-1 sm:pl-0">
                            <div
                                v-for="ingredient in props.dish.ingredients"
                                :key="ingredient.id"
                                class="group relative flex flex-col items-center gap-2 transition-transform duration-200 hover:-translate-y-1"
                            >
                                <div class="relative">
                                    <img
                                        :src="ingredient.main_image"
                                        :alt="ingredient.name"
                                        class="h-14 w-14 rounded-full border-2 border-white object-cover shadow-md ring-1 ring-gray-200 transition-all duration-300 group-hover:shadow-lg"
                                    />

                                    <div
                                        v-if="ingredient.allergen"
                                        class="absolute -top-1 -right-1"
                                    >
                                        <div class="group/allergen relative">
                                            <div
                                                class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-amber-300 text-xs font-bold text-white shadow-md transition-transform hover:scale-110"
                                            >
                                                !
                                            </div>
                                            <div
                                                class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 flex w-max -translate-x-1/2 translate-y-2 scale-95 items-center gap-2 rounded-xl bg-white px-3 py-1.5 text-xs font-medium text-gray-800 opacity-0 shadow-xl transition-all duration-200 group-hover/allergen:translate-y-0 group-hover/allergen:scale-100 group-hover/allergen:opacity-100"
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
                                                <span class="whitespace-nowrap"
                                                    >Contains:
                                                    {{
                                                        ingredient.allergen.name
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="text-center text-xs font-medium text-gray-600 group-hover:text-gray-900"
                                >
                                    {{ ingredient.name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1"></div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:gap-4">
                        <AnimatedButton @click="openAddModal" text="Add to cart" />

                        <button
                            class="inline-flex cursor-pointer items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-sm transition-all duration-200 hover:bg-gray-50 hover:shadow-md active:scale-95"
                            @click="$inertia.visit('/menu/dishes')"
                        >
                            Back to menu
                        </button>
                    </div>

                    <div
                        class="mt-6 flex items-center gap-4 text-xs text-gray-400"
                    >
                        <span class="flex items-center gap-1"
                            >⭐ 4.8 (24 reviews)</span
                        >
                        <span>•</span>
                        <span>🔥 150+ orders this week</span>
                    </div>
                </div>
            </div>
        </section>

        <AddDishToCartModal
            v-if="props.dish"
            :dish="props.dish"
            :open="isAddModalOpen"
            @close="isAddModalOpen = false"
        />
    </ClientLayout>
</template>

<style scoped></style>
