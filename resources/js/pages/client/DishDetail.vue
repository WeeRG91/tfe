<script setup lang="ts">
import AnimatedButton from '@/components/AnimatedButton.vue';
import AddDishToCartModal from '@/components/client/cart/AddDishToCartModal.vue';
import { useDateFormatter } from '@/composables/useDateFormatter';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { getInitials } from '@/lib/utils';
import rating from '@/routes/rating';
import { useDishStore } from '@/stores/dish';
import { ClientDishType } from '@/types/dish';
import { ReviewResultType, ReviewType } from '@/types/rating';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { EllipsisVertical, Flame, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    dish: ClientDishType;
    reviewEligibility: {
        can_review: boolean;
        has_review: boolean;
    };
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

    return reviews.value.some((review) => review.user.id === user.value?.id);
});

const canWriteReview = computed(
    () =>
        Boolean(user.value) &&
        props.reviewEligibility.can_review &&
        !props.reviewEligibility.has_review &&
        !hasUserReviewed.value,
);

const hasExistingReview = computed(
    () => props.reviewEligibility.has_review || hasUserReviewed.value,
);

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

const openAddModal = async () => {
    if (!user.value) {
        router.visit('/login');

        return;
    }

    isAddModalOpen.value = true;
};

const toggleReviewForm = () => {
    if (!canWriteReview.value) {
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
    if (!canWriteReview.value) return;

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
                class="relative z-10 mb-6 flex items-center gap-2 text-sm text-muted-foreground"
            >
                <button
                    @click="$inertia.visit('/menu/dishes')"
                    class="cursor-pointer transition-colors hover:text-primary"
                >
                    {{ t('menu.dishDetail.menu') }}
                </button>
                <span>/</span>
                <span class="font-medium text-foreground">
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
                                      'text-success',
                                      'text-warning',
                                      'text-primary',
                                      'text-destructive',
                                  ][i]
                                : 'text-muted-foreground/30',
                        ]"
                    />
                </div>
            </div>

            <div
                class="relative z-10 grid flex-1 gap-10 lg:grid-cols-2 lg:gap-12"
            >
                <div class="overflow-hidden rounded-2xl bg-muted shadow-lg">
                    <img
                        :src="props.dish.main_image"
                        :alt="props.dish.name"
                        class="h-full w-full object-cover transition duration-700 hover:scale-105"
                    />
                </div>

                <div class="flex flex-col">
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-full bg-secondary px-4 py-1.5 text-sm font-semibold text-secondary-foreground shadow-sm"
                        >
                            {{ props.dish.category.label }}
                        </span>

                        <div class="ml-auto flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <div class="relative inline-block">
                                    <div
                                        class="flex text-lg text-muted-foreground/25"
                                    >
                                        <span
                                            v-for="star in 5"
                                            :key="`bg-${star}`"
                                            >★</span
                                        >
                                    </div>

                                    <div
                                        class="absolute top-0 left-0 overflow-hidden text-lg whitespace-nowrap text-warning"
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

                                <span class="font-semibold text-foreground">
                                    {{ (ratingAverage ?? 0).toFixed(1) }}
                                </span>
                            </div>

                            <span class="text-sm text-muted-foreground">
                                ({{ ratingCount ?? 0 }}
                                {{ t('menu.dishDetail.reviews') }})
                            </span>
                        </div>
                    </div>

                    <h1
                        class="mb-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl"
                    >
                        {{ props.dish.name }}
                    </h1>

                    <div class="mb-6 border-l-4 border-primary/30 pl-4">
                        <p class="leading-relaxed text-muted-foreground">
                            {{ props.dish.description }}
                        </p>
                    </div>

                    <div class="mb-8 flex items-baseline gap-2">
                        <span
                            class="text-3xl font-bold text-primary sm:text-4xl"
                            >€ {{ props.dish.price.toFixed(2) }}</span
                        >
                        <span class="text-sm text-muted-foreground">
                            {{ t('menu.dishDetail.includesVat') }}
                        </span>
                    </div>

                    <div v-if="props.dish.ingredients.length" class="mb-8">
                        <div class="mb-4 flex items-center gap-2">
                            <span
                                class="text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                {{ t('menu.dishDetail.ingredients') }}
                            </span>
                            <div
                                class="h-px flex-1 bg-gradient-to-r from-border to-transparent"
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
                                        class="h-14 w-14 rounded-full border-2 border-background object-cover shadow-md ring-1 ring-border transition-all duration-300 group-hover:shadow-lg group-hover:ring-primary/40"
                                    />

                                    <div
                                        v-if="ingredient.allergen"
                                        class="absolute -top-1 -right-1"
                                    >
                                        <div class="group/allergen relative">
                                            <div
                                                class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-warning text-xs font-bold text-warning-foreground shadow-md transition-transform hover:scale-110"
                                            >
                                                !
                                            </div>
                                            <div
                                                class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 flex w-max -translate-x-1/2 translate-y-2 scale-95 items-center gap-2 rounded-xl border border-border bg-popover px-3 py-1.5 text-xs font-medium text-popover-foreground opacity-0 shadow-xl transition-all duration-200 group-hover/allergen:translate-y-0 group-hover/allergen:scale-100 group-hover/allergen:opacity-100"
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
                                    class="text-center text-xs font-medium text-muted-foreground transition-colors group-hover:text-foreground"
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
                            class="inline-flex cursor-pointer items-center justify-center rounded-md border border-input bg-background px-6 py-3 text-sm font-medium text-foreground shadow-sm transition-all duration-200 hover:bg-accent hover:text-accent-foreground hover:shadow-md active:scale-95"
                            @click="$inertia.visit('/menu/dishes')"
                        >
                            {{ t('menu.dishDetail.backToMenu') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-16 border-t border-border pt-12">
                <div
                    class="mb-8 flex flex-wrap items-center justify-between gap-4"
                >
                    <div>
                        <h2
                            class="flex items-center gap-2 text-2xl font-bold text-foreground"
                        >
                            {{ t('menu.dishDetail.reviewSection.title') }}
                            <span
                                class="inline-flex items-center justify-center rounded-full bg-primary/10 px-2.5 py-0.5 text-sm font-semibold text-primary"
                            >
                                {{ ratingCount || 0 }}
                            </span>
                        </h2>
                        <div class="mt-1 flex items-center gap-3">
                            <div class="relative inline-block">
                                <div
                                    class="flex text-2xl text-muted-foreground/25"
                                >
                                    <span v-for="star in 5" :key="`bg-${star}`"
                                        >★</span
                                    >
                                </div>

                                <div
                                    class="absolute inset-0 overflow-hidden text-2xl whitespace-nowrap text-warning"
                                    :style="{
                                        width: `${ratingPercentage}%`,
                                    }"
                                >
                                    <span v-for="star in 5" :key="`fg-${star}`"
                                        >★</span
                                    >
                                </div>
                            </div>

                            <span class="text-lg font-semibold text-foreground">
                                {{ (ratingAverage ?? 0).toFixed(1) }}
                            </span>
                        </div>
                    </div>

                    <button
                        v-if="canWriteReview"
                        type="button"
                        @click="toggleReviewForm"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground shadow-lg transition-all hover:bg-primary/90 hover:shadow-xl active:scale-95"
                    >
                        {{ t('menu.dishDetail.reviewSection.writeReview') }}
                    </button>

                    <div
                        v-else-if="user && hasExistingReview"
                        class="flex items-center gap-2 rounded-full bg-success/10 px-4 py-2 text-sm font-medium text-success"
                    >
                        {{ t('menu.dishDetail.reviewSection.alreadyReviewed') }}
                    </div>

                    <div
                        v-else-if="user"
                        class="flex w-sm items-center justify-center rounded-xl border border-warning/30 bg-warning/10 px-5 py-3 text-[13px] text-foreground"
                    >
                        <span>{{
                            t('menu.dishDetail.reviewSection.notEligible')
                        }}</span>
                    </div>
                </div>

                <div
                    v-if="showReviewForm"
                    class="mb-8 overflow-hidden rounded-xl border border-border bg-card p-5 text-card-foreground shadow-md"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-base font-semibold text-card-foreground"
                        >
                            {{
                                t(
                                    'menu.dishDetail.reviewSection.writeYourReview',
                                )
                            }}
                        </h3>
                        <button
                            @click="toggleReviewForm"
                            class="rounded-full p-1 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <form @submit.prevent="submitReview" class="space-y-4">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-medium text-card-foreground"
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
                                            ? 'text-warning drop-shadow-sm'
                                            : 'text-muted-foreground/30 hover:text-muted-foreground/60'
                                    "
                                >
                                    ★
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                for="review"
                                class="mb-1.5 block text-xs font-medium text-card-foreground"
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
                                class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/30 focus:outline-none"
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
                                class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-md active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
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
                                class="rounded-lg px-4 py-2 text-sm font-medium text-muted-foreground transition-all hover:bg-accent hover:text-accent-foreground active:scale-95"
                            >
                                {{ t('menu.dishDetail.reviewSection.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div
                    v-if="showEditForm && editingReview"
                    class="mb-8 overflow-hidden rounded-xl border border-info/30 bg-card p-5 text-card-foreground shadow-md"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-2xl font-bold text-foreground"
                        >
                            {{
                                t(
                                    'menu.dishDetail.reviewSection.editYourReview',
                                )
                            }}
                        </h3>
                        <button
                            @click="cancelEdit"
                            class="rounded-full p-1 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <form @submit.prevent="updateReview" class="space-y-4">
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-medium text-card-foreground"
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
                                            ? 'text-warning drop-shadow-sm'
                                            : 'text-muted-foreground/30 hover:text-muted-foreground/60'
                                    "
                                >
                                    ★
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                for="edit-review"
                                class="mb-1.5 block text-xs font-medium text-card-foreground"
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
                                class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/30 focus:outline-none"
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
                                class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-md active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
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
                                class="rounded-lg px-4 py-2 text-sm font-medium text-muted-foreground transition-all hover:bg-accent hover:text-accent-foreground active:scale-95"
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
                        class="overflow-hidden rounded-2xl border border-border bg-card px-6 py-4 text-card-foreground shadow-sm transition-all hover:border-primary/30 hover:shadow-lg"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    v-if="review.user.avatar"
                                    class="h-12 w-12 overflow-hidden rounded-full ring-2 ring-border"
                                >
                                    <img
                                        :src="review.user.avatar"
                                        :alt="review.user.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground"
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
                                                class="font-semibold text-card-foreground"
                                            >
                                                {{ review.user.name }}
                                            </p>
                                            <span
                                                v-if="
                                                    review.user.id === user?.id
                                                "
                                                class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
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
                                            <span class="text-lg text-warning">
                                                {{ getStars(review.rating) }}
                                            </span>
                                            <span
                                                class="text-xs text-muted-foreground"
                                            >
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
                                            class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                        >
                                            <EllipsisVertical class="size-5" />
                                        </button>

                                        <Teleport to="body">
                                            <div
                                                v-if="
                                                    dropdownOpen === review.id
                                                "
                                                @click.stop
                                                class="fixed z-50 mt-2 w-40 origin-top-right rounded-xl border border-border bg-popover p-1 text-popover-foreground shadow-xl transition-all"
                                                :style="{
                                                    left: `${dropdownX}px`,
                                                    top: `${dropdownY}px`,
                                                }"
                                            >
                                                <button
                                                    @click="
                                                        toggleEditForm(review)
                                                    "
                                                    class="flex w-full items-center gap-3 rounded-sm px-4 py-2.5 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
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
                                                    class="flex w-full items-center gap-3 rounded-sm px-4 py-2.5 text-sm text-destructive transition-colors hover:bg-destructive/10 disabled:opacity-50"
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
                                    class="leading-relaxed text-muted-foreground"
                                >
                                    {{ review.review }}
                                </p>

                                <div
                                    class="mt-3 flex items-center justify-between text-xs text-muted-foreground"
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
                        class="col-span-full rounded-2xl border-2 border-dashed border-border bg-muted/20 py-16 text-center"
                    >
                        <div class="mb-4 text-7xl">🍽️</div>
                        <h3 class="text-lg font-semibold text-foreground">
                            {{
                                t('menu.dishDetail.reviewSection.noReviewsYet')
                            }}
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
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
