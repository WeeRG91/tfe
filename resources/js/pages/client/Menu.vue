<script setup lang="ts">
import AddToCartModal from '@/components/client/cart/AddToCartModal.vue';
import DishCard from '@/components/client/menu/DishCard.vue';
import DishCardSkeleton from '@/components/client/menu/DishCardSkeleton.vue';
import EmptyDishList from '@/components/client/menu/EmptyDishList.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { CategoryOptionType } from '@/types/category';
import { ClientDishType } from '@/types/dish';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    categories: CategoryOptionType[];
}>();

const dishes = ref<ClientDishType[]>([]);
const selectedCategory = ref<number | null>(null);
const isLoading = ref<boolean>(false);
const hasLoaded = ref<boolean>(false);
const selectedDish = ref<ClientDishType | null>(null);
const isAddModalOpen = ref<boolean>(false);

const openAddModal = (dish: ClientDishType) => {
    selectedDish.value = dish;
    isAddModalOpen.value = true;
};

const loadingDishes = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get<ClientDishType[]>(
            '/menu/dishes/get-dishes',
            {
                params: { category: selectedCategory.value },
            },
        );
        dishes.value = response.data;
    } catch (error) {
        console.log(error);
        toast.error('Failed to load more dishes.');
    } finally {
        isLoading.value = false;
        hasLoaded.value = true;
    }
};

onMounted(() => {
    loadingDishes();
});

watch(selectedCategory, () => {
    loadingDishes();
});
</script>

<template>
    <Head title="Menu Page" />
    <ClientLayout>
        <section class="mx-auto max-w-6xl px-6 py-4">
            <div class="mb-4">
                <p class="text-sm tracking-widest text-red-500 uppercase">
                    [ Our Menu ]
                </p>
                <h1 class="text-4xl font-semibold uppercase md:text-5xl">
                    Discover Our Dishes
                </h1>
            </div>

            <div class="mb-4 flex flex-wrap items-center gap-4">
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
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <DishCardSkeleton v-for="i in 6" :key="i" />
                </div>

                <div
                    v-else-if="hasLoaded && !dishes.length"
                    key="empty-list"
                    class="flex min-h-[500px] items-center justify-center sm:min-h-[600px]"
                >
                    <EmptyDishList />
                </div>

                <div
                    v-else
                    key="content"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <DishCard
                        v-for="dish in dishes"
                        :key="dish.id"
                        :dish="dish"
                        @add="openAddModal"
                    />
                </div>
            </Transition>
        </section>

        <AddToCartModal
            v-if="selectedDish"
            :dish="selectedDish"
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
