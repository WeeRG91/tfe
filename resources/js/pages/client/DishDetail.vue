<script setup lang="ts">
import AnimatedButton from '@/components/AnimatedButton.vue';
import AddDishToCartModal from '@/components/client/cart/AddDishToCartModal.vue';
import { useDateFormatter } from '@/composables/useDateFormatter';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { getInitials, getUserAvatarColor } from '@/lib/utils';
import rating from '@/routes/rating';
import { useDishStore } from '@/stores/dish';
import { ClientDishType } from '@/types/dish';
import { ReviewResultType, ReviewType } from '@/types/rating';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Flame, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    dish: ClientDishType;
}>();

const { t } = useI18n();
const { formatDateForHumans } = useDateFormatter();
const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const dishStore = useDishStore();

const ratingAverage = ref<number>(props.dish.rating_average ?? 0);
const ratingCount = ref<number>(props.dish.rating_count ?? 0);
const isAddModalOpen = ref<boolean>(false);
const showReviewForm = ref<boolean>(false);
const showEditForm = ref<boolean>(false);
const isSubmitting = ref<boolean>(false);
const isDeleting = ref<boolean>(false);
const isLoading = ref<boolean>(false);
const reviews = ref<ReviewType[]>([]);
const editingReview = ref<ReviewType | null>(null);
const dropdownOpen = ref<number | null>(null);
const hoveredStars = ref<number>(0);
const dropdownX = ref<number>(0);
const dropdownY = ref<number>(0);

const ratingPercentage = computed(() => {
    return ((props.dish.rating_average ?? 0) / 5) * 100;
});

const hasUserReviewed = computed(() => {
    if (!user.value) return false;

    return reviews.value.some((review) => review.user.id === user.value.id);
});

const reviewForm = useForm({
    rating: 0,
    review: '',
});

const editForm = useForm({
    rating: 0,
    review: '',
});

const loadReviews = async () => {
    isLoading.value = true;

    try {
        const { data } = await axios.get<ReviewType[]>(
            rating.getReviews(props.dish.id).url,
        );

        reviews.value = data;
    } catch (error) {
        console.log(error);
        toast.error(t('menu.dishDetail.messages.loadReviewsFailed'));
    } finally {
        isLoading.value = false;
    }
};

const openAddModal = () => {
    isAddModalOpen.value = true;
};

const toggleReviewForm = () => {
    if (!user.value) {
        return;
    }
    showReviewForm.value = !showReviewForm.value;
    if (showReviewForm.value) {
        reviewForm.rating = 0;
        reviewForm.review = '';
        showEditForm.value = false;
        editingReview.value = null;
    }
};

const toggleEditForm = (review: ReviewType) => {
    if (!user.value || review.user.id !== user.value.id) return;

    editingReview.value = review;
    editForm.rating = review.rating;
    editForm.review = review.review || '';
    showEditForm.value = true;
    showReviewForm.value = false;
    dropdownOpen.value = null;
};

const cancelEdit = () => {
    showEditForm.value = false;
    editingReview.value = null;
    editForm.reset();
};

const submitReview = async () => {
    if (!user.value) return;
    isSubmitting.value = true;

    try {
        const { data } = await axios.post(
            rating.store(props.dish.id).url,
            reviewForm.data(),
        );

        if (data.success) {
            showReviewForm.value = false;
        }
    } catch (error) {
        console.log(error);
        toast.error(t('menu.dishDetail.messages.saveReviewFailed'));
    } finally {
        isSubmitting.value = false;
    }
};

const updateReview = async () => {
    if (!user.value || !editingReview.value) return;

    isSubmitting.value = true;

    try {
        const { data } = await axios.patch(
            rating.update(editingReview.value.id).url,
            editForm.data(),
        );

        if (data.success) {
            showEditForm.value = false;
        }
    } catch (error) {
        console.log(error);
        toast.error(t('menu.dishDetail.messages.updateReviewFailed'));
    } finally {
        isSubmitting.value = false;
    }
};

const deleteReview = async (review: ReviewType) => {
    if (!user.value || review.user.id !== user.value.id) return;

    try {
        const { data } = await axios.delete(rating.destroy(review.id).url);

        if (data.success) {
            closeDropdown();
        }
    } catch (error) {
        console.log(error);
        toast.error(t('menu.dishDetail.messages.deleteReviewFailed'));
    }
};

const getStars = (rating: number) => {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
};

const toggleDropdown = (event: MouseEvent, reviewId: number) => {
    if (dropdownOpen.value === reviewId) {
        dropdownOpen.value = null;
        return;
    }

    const button = event.currentTarget as HTMLElement;
    const rect = button.getBoundingClientRect();

    dropdownX.value = rect.right - 160;
    dropdownY.value = rect.bottom + 8;

    dropdownOpen.value = reviewId;
};

const closeDropdown = () => {
    dropdownOpen.value = null;
};

type EcoChannel = {
    listen: (
        event: string,
        callback: (e: ReviewResultType) => void,
    ) => EcoChannel;
};

const channel = ref<EcoChannel | null>(null);
onMounted(() => {
    loadReviews();

    channel.value = window.Echo.channel('dish.rating').listen(
        '.dish-rating',
        async (e: ReviewResultType) => {
            ratingAverage.value = e.rating_average;
            ratingCount.value = e.rating_count;

            if (e.deleted_review_id) {
                reviews.value = reviews.value.filter(
                    (r) => r.id !== e.deleted_review_id,
                );
            }

            if (e.review) {
                const existingReview = reviews.value.some(
                    (r) => r.id === e.review.id,
                );

                if (!existingReview) {
                    reviews.value.unshift(e.review);
                } else {
                    const index = reviews.value.findIndex(
                        (r) => r.id === e.review.id,
                    );

                    if (index !== -1) {
                        reviews.value[index] = e.review;
                    }
                }
            }

            dishStore.updateDishRating(
                e.dish_id,
                e.rating_average,
                e.rating_count,
            );
        },
    );

    window.addEventListener('scroll', closeDropdown, true);
    window.addEventListener('resize', closeDropdown);
});

onUnmounted(() => {
    if (channel.value) {
        window.Echo.leave('dish.rating');
    }

    window.removeEventListener('scroll', closeDropdown, true);
    window.removeEventListener('resize', closeDropdown);
});
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
                    @click="$inertia.visit('/menu/dishes')"
                    class="cursor-pointer transition-colors hover:text-red-500"
                >
                    {{ t('menu.dishDetail.menu') }}
                </button>
                <span>/</span>
                <span class="font-medium text-gray-900">
                    {{ props.dish.name }}
                </span>
                <div
                    v-if="dish.default_spicy_level > 0"
                    class="flex items-center gap-1"
                >
                    <Flame
                        v-for="i in dish.default_spicy_level"
                        :key="i"
                        fill="currentColor"
                        class="h-3.5 w-3.5 transition-colors duration-200 sm:h-4 sm:w-4"
                        :class="[
                            i <= dish.default_spicy_level
                                ? [
                                      'text-green-500',
                                      'text-yellow-500',
                                      'text-orange-500',
                                      'text-red-500',
                                  ][i]
                                : 'text-gray-300',
                        ]"
                    />
                </div>
            </div>

            <div
                class="relative z-10 grid flex-1 gap-10 lg:grid-cols-2 lg:gap-12"
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

                        <div class="ml-auto flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <div class="relative inline-block">
                                    <div class="flex text-lg text-gray-300">
                                        <span
                                            v-for="star in 5"
                                            :key="`bg-${star}`"
                                            >★</span
                                        >
                                    </div>

                                    <div
                                        class="absolute top-0 left-0 overflow-hidden text-lg whitespace-nowrap text-yellow-400"
                                        :style="{
                                            width: `${ratingPercentage}%`,
                                        }"
                                    >
                                        <span
                                            v-for="star in 5"
                                            :key="`fg-${star}`"
                                            >★</span
                                        >
                                    </div>
                                </div>

                                <span class="font-semibold text-gray-900">
                                    {{ (ratingAverage ?? 0).toFixed(1) }}
                                </span>
                            </div>

                            <span class="text-sm text-gray-400">
                                ({{ ratingCount ?? 0 }}
                                {{ t('menu.dishDetail.reviews') }})
                            </span>
                        </div>
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
                        <span class="text-sm text-gray-400">
                            {{ t('menu.dishDetail.includesVat') }}
                        </span>
                    </div>

                    <div v-if="props.dish.ingredients.length" class="mb-8">
                        <div class="mb-4 flex items-center gap-2">
                            <span
                                class="text-sm font-semibold tracking-wide text-gray-400 uppercase"
                            >
                                {{ t('menu.dishDetail.ingredients') }}
                            </span>
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
                                                <span class="whitespace-nowrap">
                                                    {{
                                                        t(
                                                            'menu.dishDetail.contains',
                                                        )
                                                    }}
                                                    {{
                                                        ingredient.allergen.name
                                                    }}
                                                </span>
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
                        <AnimatedButton
                            @click="openAddModal"
                            :text="t('menu.dishDetail.addToCart')"
                        />

                        <button
                            class="inline-flex cursor-pointer items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-sm transition-all duration-200 hover:bg-gray-50 hover:shadow-md active:scale-95"
                            @click="$inertia.visit('/menu/dishes')"
                        >
                            {{ t('menu.dishDetail.backToMenu') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-16 border-t border-gray-200 pt-12">
                <div
                    class="mb-8 flex flex-wrap items-center justify-between gap-4"
                >
                    <div>
                        <h2
                            class="flex items-center gap-2 text-2xl font-bold text-gray-900"
                        >
                            {{ t('menu.dishDetail.reviewSection.title') }}
                            <span
                                class="inline-flex items-center justify-center rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-semibold text-red-600"
                            >
                                {{ ratingCount || 0 }}
                            </span>
                        </h2>
                        <div class="mt-1 flex items-center gap-3">
                            <div class="relative inline-block">
                                <div class="flex text-2xl text-gray-300">
                                    <span v-for="star in 5" :key="`bg-${star}`"
                                        >★</span
                                    >
                                </div>

                                <div
                                    class="absolute inset-0 overflow-hidden text-2xl whitespace-nowrap text-yellow-400"
                                    :style="{
                                        width: `${ratingPercentage}%`,
                                    }"
                                >
                                    <span v-for="star in 5" :key="`fg-${star}`"
                                        >★</span
                                    >
                                </div>
                            </div>

                            <span class="text-lg font-semibold text-gray-900">
                                {{ (ratingAverage ?? 0).toFixed(1) }}
                            </span>
                        </div>
                    </div>

                    <button
                        v-if="user && !hasUserReviewed"
                        @click="toggleReviewForm"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-red-500 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-red-200 transition-all hover:shadow-xl hover:shadow-red-300 active:scale-95"
                    >
                        {{ t('menu.dishDetail.reviewSection.writeReview') }}
                    </button>
                    <div
                        v-else-if="user && hasUserReviewed"
                        class="flex items-center gap-2 rounded-full bg-green-50 px-4 py-2 text-sm font-medium text-green-700"
                    >
                        {{ t('menu.dishDetail.reviewSection.alreadyReviewed') }}
                    </div>
                </div>

                <div
                    v-if="showReviewForm"
                    class="mb-8 overflow-hidden rounded-xl bg-white p-5 shadow-md ring-1 ring-gray-100"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-base font-semibold text-gray-900"
                        >
                            {{
                                t(
                                    'menu.dishDetail.reviewSection.writeYourReview',
                                )
                            }}
                        </h3>
                        <button
                            @click="toggleReviewForm"
                            class="rounded-full p-1 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-600"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <form @submit.prevent="submitReview" class="space-y-4">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-medium text-gray-700"
                            >
                                {{
                                    t(
                                        'menu.dishDetail.reviewSection.yourRating',
                                    )
                                }}
                            </label>
                            <div class="flex gap-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="reviewForm.rating = star"
                                    @mouseenter="hoveredStars = star"
                                    @mouseleave="hoveredStars = 0"
                                    class="text-2xl transition-all hover:scale-110 focus:outline-none"
                                    :class="
                                        star <=
                                        (hoveredStars || reviewForm.rating)
                                            ? 'text-yellow-400 drop-shadow-sm'
                                            : 'text-gray-300 hover:text-gray-400'
                                    "
                                >
                                    ★
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                for="review"
                                class="mb-1.5 block text-xs font-medium text-gray-700"
                            >
                                {{
                                    t(
                                        'menu.dishDetail.reviewSection.yourReview',
                                    )
                                }}
                            </label>
                            <textarea
                                id="review"
                                v-model="reviewForm.review"
                                rows="3"
                                class="w-full resize-none rounded-lg border border-gray-200 px-3 py-2 text-sm transition-all focus:border-red-400 focus:ring-2 focus:ring-red-100 focus:outline-none"
                                :placeholder="
                                    t(
                                        'menu.dishDetail.reviewSection.reviewPlaceholder',
                                    )
                                "
                            ></textarea>
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-red-700 hover:shadow-md active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span
                                    v-if="isSubmitting"
                                    class="flex items-center gap-2"
                                >
                                    {{
                                        t(
                                            'menu.dishDetail.reviewSection.submitting',
                                        )
                                    }}
                                </span>
                                <span v-else>
                                    {{
                                        t(
                                            'menu.dishDetail.reviewSection.submit',
                                        )
                                    }}
                                </span>
                            </button>
                            <button
                                type="button"
                                @click="toggleReviewForm"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all hover:bg-gray-100 hover:text-gray-800 active:scale-95"
                            >
                                {{ t('menu.dishDetail.reviewSection.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div
                    v-if="showEditForm && editingReview"
                    class="mb-8 overflow-hidden rounded-xl bg-white p-5 shadow-md ring-1 ring-blue-100"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-base font-semibold text-gray-900"
                        >
                            {{
                                t(
                                    'menu.dishDetail.reviewSection.editYourReview',
                                )
                            }}
                        </h3>
                        <button
                            @click="cancelEdit"
                            class="rounded-full p-1 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-600"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <form @submit.prevent="updateReview" class="space-y-4">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-medium text-gray-700"
                            >
                                {{
                                    t(
                                        'menu.dishDetail.reviewSection.yourRating',
                                    )
                                }}
                            </label>
                            <div class="flex gap-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="editForm.rating = star"
                                    @mouseenter="hoveredStars = star"
                                    @mouseleave="hoveredStars = 0"
                                    class="text-2xl transition-all hover:scale-110 focus:outline-none"
                                    :class="
                                        star <=
                                        (hoveredStars || editForm.rating)
                                            ? 'text-yellow-400 drop-shadow-sm'
                                            : 'text-gray-300 hover:text-gray-400'
                                    "
                                >
                                    ★
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                for="edit-review"
                                class="mb-1.5 block text-xs font-medium text-gray-700"
                            >
                                {{
                                    t(
                                        'menu.dishDetail.reviewSection.yourReview',
                                    )
                                }}
                            </label>
                            <textarea
                                id="edit-review"
                                v-model="editForm.review"
                                rows="3"
                                class="w-full resize-none rounded-lg border border-gray-200 px-3 py-2 text-sm transition-all focus:border-red-400 focus:ring-2 focus:ring-red-100 focus:outline-none"
                                :placeholder="
                                    t(
                                        'menu.dishDetail.reviewSection.editReviewPlaceholder',
                                    )
                                "
                            ></textarea>
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-red-700 hover:shadow-md active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span
                                    v-if="isSubmitting"
                                    class="flex items-center gap-2"
                                >
                                    {{
                                        t(
                                            'menu.dishDetail.reviewSection.updating',
                                        )
                                    }}
                                </span>
                                <span v-else>
                                    {{
                                        t(
                                            'menu.dishDetail.reviewSection.update',
                                        )
                                    }}
                                </span>
                            </button>
                            <button
                                type="button"
                                @click="cancelEdit"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all hover:bg-gray-100 hover:text-gray-800 active:scale-95"
                            >
                                {{ t('menu.dishDetail.reviewSection.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div
                        v-for="review in reviews"
                        :key="review.id"
                        class="overflow-hidden rounded-2xl border border-gray-100 bg-white px-6 py-4 shadow-sm transition-all hover:shadow-lg"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    v-if="review.user.avatar"
                                    class="h-12 w-12 overflow-hidden rounded-full ring-2 ring-gray-100"
                                >
                                    <img
                                        :src="review.user.avatar"
                                        :alt="review.user.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-12 w-12 items-center justify-center rounded-full text-sm font-semibold"
                                    :class="getUserAvatarColor(review.user.id)"
                                >
                                    {{ getInitials(review.user.name) }}
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <div>
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ review.user.name }}
                                            </p>
                                            <span
                                                v-if="
                                                    review.user.id === user?.id
                                                "
                                                class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600"
                                            >
                                                {{
                                                    t(
                                                        'menu.dishDetail.reviewSection.you',
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="mt-0.5 flex items-center gap-3"
                                        >
                                            <span
                                                class="text-lg text-yellow-400"
                                            >
                                                {{ getStars(review.rating) }}
                                            </span>
                                            <span class="text-xs text-gray-400">
                                                {{
                                                    formatDateForHumans(
                                                        review.created_at,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        v-if="review.user.id === user?.id"
                                        class="relative flex-shrink-0"
                                    >
                                        <button
                                            @click.stop="
                                                toggleDropdown(
                                                    $event,
                                                    review.id,
                                                )
                                            "
                                            class="rounded-lg p-1.5 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-600"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                                                />
                                            </svg>
                                        </button>

                                        <Teleport to="body">
                                            <div
                                                v-if="
                                                    dropdownOpen === review.id
                                                "
                                                @click.stop
                                                class="fixed z-50 mt-2 w-40 origin-top-right rounded-xl bg-white py-1 shadow-xl ring-1 ring-gray-200 transition-all"
                                                :style="{
                                                    left: `${dropdownX}px`,
                                                    top: `${dropdownY}px`,
                                                }"
                                            >
                                                <button
                                                    @click="
                                                        toggleEditForm(review)
                                                    "
                                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50"
                                                >
                                                    {{
                                                        t(
                                                            'menu.dishDetail.reviewSection.edit',
                                                        )
                                                    }}
                                                </button>
                                                <button
                                                    @click="
                                                        deleteReview(review)
                                                    "
                                                    :disabled="isDeleting"
                                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50"
                                                >
                                                    {{
                                                        t(
                                                            'menu.dishDetail.reviewSection.delete',
                                                        )
                                                    }}
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    dropdownOpen === review.id
                                                "
                                                @click="closeDropdown"
                                                class="fixed inset-0 z-10"
                                            ></div>
                                        </Teleport>
                                    </div>
                                </div>

                                <p
                                    v-if="review.review"
                                    class="leading-relaxed text-gray-600"
                                >
                                    {{ review.review }}
                                </p>

                                <div
                                    class="mt-3 flex items-center justify-between text-xs text-gray-400"
                                >
                                    <span
                                        v-if="
                                            review.updated_at !==
                                            review.created_at
                                        "
                                    >
                                        {{
                                            t(
                                                'menu.dishDetail.reviewSection.edited',
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!reviews.length"
                        class="col-span-full rounded-2xl border-2 border-dashed border-gray-200 py-16 text-center"
                    >
                        <div class="mb-4 text-7xl">🍽️</div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{
                                t('menu.dishDetail.reviewSection.noReviewsYet')
                            }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ t('menu.dishDetail.reviewSection.firstReview') }}
                        </p>
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

<style scoped>
.origin-top-right {
    transform-origin: top right;
}
</style>
