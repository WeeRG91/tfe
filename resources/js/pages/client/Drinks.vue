<script setup lang="ts">
import AddDrinkToCartModal from '@/components/client/cart/AddDrinkToCartModal.vue';
import DrinkCard from '@/components/client/drink/DrinkCard.vue';
import DrinkCardSkeleton from '@/components/client/drink/DrinkCardSkeleton.vue';
import EmptyDrinkList from '@/components/client/drink/EmptyDrinkList.vue';
import { useRestaurantOrdering } from '@/composables/useRestaurantOrdering';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { AppPageProps } from '@/types';
import { CategoryOptionType } from '@/types/category';
import { ClientDrinkType } from '@/types/drink';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const { t } = useI18n();
const { canStartOrdering } = useRestaurantOrdering();
const page = usePage<AppPageProps>();

const props = defineProps<{
    categories: CategoryOptionType[];
}>();

const drinks = ref<ClientDrinkType[]>([]);
const selectedCategory = ref<number | null>(null);
const isLoading = ref<boolean>(false);
const hasLoaded = ref<boolean>(false);
const selectedDrink = ref<ClientDrinkType | null>(null);
const isAddModalOpen = ref<boolean>(false);

const openAddModal = async (drink: ClientDrinkType) => {
    if (!(await canStartOrdering())) {
        return;
    }

    if (!page.props.auth.user) {
        router.visit('/login');

        return;
    }

    selectedDrink.value = drink;
    isAddModalOpen.value = true;
};

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
        toast.error(t('drinks.loadError'));
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
    <Head :title="t('drinks.pageTitle')" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ {{ t('drinks.eyebrow') }} ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    {{ t('drinks.heading') }}
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
                    {{ t('drinks.categories.all') }}
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
                    {{ t(`drinks.categories.${category.key}`) }}
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
                        @add="openAddModal"
                    />
                </div>
            </Transition>
        </section>

        <AddDrinkToCartModal
            v-if="selectedDrink"
            :drink="selectedDrink"
            :open="isAddModalOpen"
            @close="isAddModalOpen = false"
        />
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
