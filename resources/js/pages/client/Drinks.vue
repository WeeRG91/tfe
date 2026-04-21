<script setup lang="ts">
import DrinkCard from '@/components/client/drink/DrinkCard.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { CategoryOptionType } from '@/types/category';
import { ClientDrinkType } from '@/types/drink';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import DrinkCardSkeleton from '@/components/client/drink/DrinkCardSkeleton.vue';
import EmptyDrinkList from '@/components/client/drink/EmptyDrinkList.vue';

const props = defineProps<{
    categories: CategoryOptionType[];
}>();

const drinks = ref<ClientDrinkType[]>([]);
const selectedCategory = ref<number | null>(null);
const isLoading = ref<boolean>(false);
const hasLoaded = ref<boolean>(false);

const loadingDrinks = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<ClientDrinkType[]>(
            '/menu/drinks/get-drinks',
            {
                params: { category: selectedCategory.value },
            },
        );

        drinks.value = response.data;
    } catch (error) {
        console.log(error);
        toast.error('Failed to load more drinks');
    } finally {
        isLoading.value = false;
        hasLoaded.value = true;
    }
};

onMounted(() => {
    loadingDrinks();
});

watch(selectedCategory, () => {
    loadingDrinks();
});
</script>

<template>
    <Head title="Drink Page" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4 mt-4">
            <div class="mb-8">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ Our Drinks ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    Choose Your Drinks
                </h1>
            </div>

            <div class="mb-8 flex flex-wrap items-center gap-4">
                <button
                    @click="selectedCategory = null"
                    class="rounded-full border px-4 py-1 text-sm transition-all duration-500"
                    :class="
                        selectedCategory
                            ? 'text-gray-600 hover:bg-gray-100'
                            : 'border-red-500 bg-red-500 text-white hover:bg-red-600'
                    "
                >
                    All
                </button>

                <button
                    v-for="category in props.categories"
                    :key="category.label"
                    @click="selectedCategory = category.value"
                    class="rounded-full border px-4 py-1 text-sm transition-all duration-500"
                    :class="
                        selectedCategory === category.value
                            ? 'border-red-500 bg-red-500 text-white hover:bg-red-600'
                            : 'text-gray-600 hover:bg-gray-100'
                    "
                >
                    {{ category.label }}
                </button>
            </div>

            <Transition name="fade" mode="out-in">
                <div
                    v-if="isLoading"
                    key="skeletons"
                    class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <DrinkCardSkeleton v-for="i in 6" :key="i" />
                </div>

                <div
                    v-else-if="hasLoaded && !drinks.length"
                    key="empty-list"
                    class="flex min-h-[500px] items-center justify-center sm:min-h-[600px]"
                >
                    <EmptyDrinkList />
                </div>

                <div
                    v-else
                    key="content"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <DrinkCard
                        v-for="drink in drinks"
                        :key="drink.id"
                        :drink="drink"
                    />
                </div>
            </Transition>
        </section>
    </ClientLayout>
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
</style>
