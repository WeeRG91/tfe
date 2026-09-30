<script setup lang="ts">
import AnimatedButton from '@/components/AnimatedButton.vue';
import DishCard from '@/components/client/menu/DishCard.vue';
import menu from '@/routes/menu';
import type { ClientDishType } from '@/types/dish';
import type { MobileAppAvailability } from '@/types/mobile-app';
import type { HomepageReviewType } from '@/types/rating';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { BadgeCheck, Quote, Star } from 'lucide-vue-next';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileAppSection from '@/components/client/home/MobileAppSection.vue';

const props = defineProps<{
    recommendedDishes: ClientDishType[];
    homepageReviews: HomepageReviewType[];
    mobileApp: MobileAppAvailability;
    animationsReady: boolean;
}>();

const { t } = useI18n();

const slides = [
    {
        image: '/images/home/slider/dish-1.webp',
        thumbnail: '/images/home/thumbnails/dish-1.webp',
        title: t('home.slides.mainCourses.title'),
        word: t('home.slides.mainCourses.word'),
        description: t('home.slides.mainCourses.description'),
    },
    {
        image: '/images/home/slider/dish-2.webp',
        thumbnail: '/images/home/thumbnails/dish-2.webp',
        title: t('home.slides.soups.title'),
        word: t('home.slides.soups.word'),
        description: t('home.slides.soups.description'),
    },
    {
        image: '/images/home/slider/dish-3.webp',
        thumbnail: '/images/home/thumbnails/dish-3.webp',
        title: t('home.slides.appetizers.title'),
        word: t('home.slides.appetizers.word'),
        description: t('home.slides.appetizers.description'),
    },
    {
        image: '/images/home/slider/dish-4.webp',
        thumbnail: '/images/home/thumbnails/dish-4.webp',
        title: t('home.slides.noodles.title'),
        word: t('home.slides.noodles.word'),
        description: t('home.slides.noodles.description'),
    },
    {
        image: '/images/home/slider/dish-5.webp',
        thumbnail: '/images/home/thumbnails/dish-5.webp',
        title: t('home.slides.vegetarians.title'),
        word: t('home.slides.vegetarians.word'),
        description: t('home.slides.vegetarians.description'),
    },
];

const currentIndex = ref(0);
let interval: number;
const desktopThumbnailContainer = ref<HTMLElement | null>(null);
const mobileThumbnailContainer = ref<HTMLElement | null>(null);
const homeContentRoot = ref<HTMLElement | null>(null);
let homeAnimationContext: gsap.Context | undefined;

const preloadedSlideImages: HTMLImageElement[] = [];

const preloadSliderImages = () => {
    slides.forEach((slide, index) => {
        if (index === currentIndex.value) return;

        const image = new Image();

        image.decoding = 'async';
        image.src = slide.image;

        preloadedSlideImages.push(image);
    });
};

const scrollToActive = () => {
    nextTick(() => {
        if (window.innerWidth >= 768) {
            const container = desktopThumbnailContainer.value;

            if (!container) return;

            const activeThumbnail = container.children[currentIndex.value] as
                | HTMLElement
                | undefined;

            if (!activeThumbnail) return;

            const scrollTop =
                activeThumbnail.offsetTop -
                container.clientHeight / 2 +
                activeThumbnail.offsetHeight / 2;

            container.scrollTo({
                top: scrollTop,
                behavior: 'smooth',
            });

            return;
        }

        const container = mobileThumbnailContainer.value;

        if (!container) return;

        const activeThumbnail = container.children[currentIndex.value] as
            | HTMLElement
            | undefined;

        if (!activeThumbnail) return;

        const scrollLeft =
            activeThumbnail.offsetLeft -
            container.clientWidth / 2 +
            activeThumbnail.offsetWidth / 2;

        container.scrollTo({
            left: scrollLeft,
            behavior: 'smooth',
        });
    });
};

const setupHomeAnimations = async () => {
    if (
        !props.animationsReady ||
        !homeContentRoot.value ||
        homeAnimationContext
    ) {
        return;
    }

    await nextTick();

    const root = homeContentRoot.value;
    const scroller = root.closest('.landing-wrapper') as HTMLElement | null;

    gsap.registerPlugin(ScrollTrigger);

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (prefersReducedMotion) {
        return;
    }

    homeAnimationContext = gsap.context(() => {
        const sections = Array.from(
            root.querySelectorAll<HTMLElement>('[data-home-section]'),
        );

        sections.forEach((section) => {
            const revealItems = Array.from(
                section.querySelectorAll<HTMLElement>('[data-home-reveal]'),
            );

            const timeline = gsap.timeline({
                scrollTrigger: {
                    trigger: section,
                    scroller: scroller ?? undefined,
                    start: 'top 82%',
                    once: true,
                    invalidateOnRefresh: true,
                },
            });

            if (revealItems.length) {
                timeline.from(revealItems, {
                    autoAlpha: 0,
                    y: 32,
                    duration: 0.7,
                    stagger: 0.1,
                    ease: 'power3.out',
                    clearProps: 'transform,opacity,visibility',
                });
            }
        });
    }, root);

    requestAnimationFrame(() => {
        ScrollTrigger.refresh();
    });
};

watch(
    () => props.animationsReady,
    (isReady) => {
        if (isReady) {
            setupHomeAnimations();
        }
    },
    {
        immediate: true,
    },
);

onMounted(() => {
    preloadSliderImages();

    interval = window.setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % slides.length;

        scrollToActive();
    }, 5000);

    scrollToActive();
});

onUnmounted(() => {
    clearInterval(interval);

    homeAnimationContext?.revert();
    homeAnimationContext = undefined;
});

const goToSlide = (index: number) => {
    currentIndex.value = index;
    scrollToActive();
};
</script>

<template>
    <div ref="homeContentRoot">
        <section
            data-home-section
            class="mx-auto max-w-5xl px-8 pt-10 pb-8 sm:pt-14"
        >
            <Transition name="fade-slide" mode="out-in">
                <p
                    data-home-reveal
                    :key="slides[currentIndex].word"
                    class="mb-4 text-sm tracking-widest text-primary uppercase"
                >
                    [ {{ slides[currentIndex].word }} ]
                </p>
            </Transition>

            <h1
                data-home-reveal
                class="mb-6 text-3xl font-semibold uppercase md:text-5xl"
            >
                {{ t('home.headingPrefix') }}
                <Transition name="fade-slide" mode="out-in">
                    <span
                        :key="slides[currentIndex].title"
                        class="inline-block text-primary"
                    >
                        {{ slides[currentIndex].title }}
                    </span>
                </Transition>
            </h1>

            <div data-home-reveal class="description-slot mb-6 max-w-2xl">
                <Transition name="fade-slide">
                    <p
                        :key="slides[currentIndex].description"
                        class="text-muted-foreground"
                    >
                        {{ slides[currentIndex].description }}
                    </p>
                </Transition>
            </div>

            <div data-home-reveal class="w-fit">
                <AnimatedButton
                    as="a"
                    :href="menu.dish().url"
                    :text="t('home.callToAction')"
                />
            </div>
        </section>

        <section data-home-section class="px-8 pb-8 sm:pb-16">
            <div data-home-reveal class="relative mx-auto max-w-6xl">
                <div
                    class="relative h-[300px] overflow-hidden rounded-md sm:h-[450px]"
                >
                    <Transition name="image-fade">
                        <img
                            :key="slides[currentIndex].image"
                            :src="slides[currentIndex].image"
                            :alt="slides[currentIndex].title"
                            decoding="async"
                            fetchpriority="high"
                            class="absolute inset-0 h-full w-full object-cover"
                        />
                    </Transition>

                    <div
                        class="absolute inset-0 z-10 flex flex-col justify-end bg-gradient-to-t from-black/70 via-black/10 to-transparent p-6"
                    >
                        <Transition name="fade-slide" mode="out-in">
                            <p
                                :key="slides[currentIndex].word"
                                class="mb-2 w-fit rounded-full bg-primary px-2.5 py-1 text-xs tracking-widest text-primary-foreground uppercase sm:text-sm"
                            >
                                [ {{ slides[currentIndex].word }} ]
                            </p>
                        </Transition>

                        <Transition name="fade-slide" mode="out-in">
                            <h2
                                :key="slides[currentIndex].title"
                                class="text-2xl font-bold text-white uppercase sm:text-5xl"
                            >
                                {{ slides[currentIndex].title }}
                            </h2>
                        </Transition>
                    </div>
                </div>

                <div
                    ref="desktopThumbnailContainer"
                    class="absolute top-1/2 right-0 z-20 mx-4 hidden max-h-[560px] -translate-y-1/2 flex-col gap-4 overflow-y-hidden p-2 md:flex"
                    style="scrollbar-width: none; -ms-overflow-style: none"
                >
                    <img
                        v-for="(slide, index) in slides"
                        :key="slide.image"
                        :src="slide.thumbnail"
                        :alt="slide.title"
                        @click="goToSlide(index)"
                        class="h-24 w-40 flex-shrink-0 cursor-pointer rounded-md object-cover transition-all duration-300 ease-in-out"
                        :class="[
                            index === currentIndex
                                ? 'scale-105 opacity-100 ring-2 ring-primary ring-offset-2 ring-offset-background'
                                : 'opacity-50 grayscale hover:opacity-70 hover:grayscale-0',
                        ]"
                    />
                </div>

                <div
                    ref="mobileThumbnailContainer"
                    class="flex w-full gap-4 overflow-x-auto px-2 py-4 md:hidden"
                    style="scrollbar-width: none; -ms-overflow-style: none"
                >
                    <img
                        v-for="(slide, index) in slides"
                        :key="slide.image"
                        :src="slide.image"
                        :alt="slide.title"
                        @click="goToSlide(index)"
                        class="h-20 w-32 flex-shrink-0 cursor-pointer rounded-md object-cover transition-all duration-300 ease-in-out"
                        :class="[
                            index === currentIndex
                                ? 'scale-105 opacity-100 ring-2 ring-white'
                                : 'opacity-50 grayscale hover:opacity-70 hover:grayscale-0',
                        ]"
                    />
                </div>
            </div>
        </section>

        <section
            data-home-section
            v-if="recommendedDishes.length"
            class="px-6 py-14 lg:px-8 lg:py-16"
        >
            <div class="mx-auto max-w-6xl">
                <div
                    data-home-reveal
                    class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-sm tracking-widest text-primary uppercase"
                        >
                            [ {{ t('home.recommended.eyebrow') }} ]
                        </p>

                        <h2
                            class="mt-2 text-3xl font-semibold uppercase md:text-4xl"
                        >
                            {{ t('home.recommended.title') }}
                        </h2>

                        <p class="mt-3 max-w-2xl text-muted-foreground">
                            {{ t('home.recommended.description') }}
                        </p>
                    </div>

                    <a
                        :href="menu.dish().url"
                        class="font-medium text-primary transition-colors hover:text-primary/80"
                    >
                        {{ t('home.recommended.viewMenu') }} →
                    </a>
                </div>

                <div
                    data-home-reveal
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <DishCard
                        v-for="dish in recommendedDishes"
                        :key="dish.id"
                        :dish="dish"
                        :show-add-action="false"
                    />
                </div>
            </div>
        </section>

        <MobileAppSection :mobile-app="mobileApp" />

        <section
            data-home-section
            v-if="homepageReviews.length"
            class="px-6 py-10 lg:px-8 lg:py-16"
        >
            <div class="mx-auto max-w-6xl">
                <header data-home-reveal class="max-w-3xl">
                    <p
                        class="text-sm font-medium tracking-[0.22em] text-primary uppercase"
                    >
                        [ {{ t('home.reviews.eyebrow') }} ]
                    </p>

                    <h2
                        class="mt-4 text-3xl leading-tight font-semibold tracking-tight uppercase sm:text-4xl lg:text-5xl"
                    >
                        {{ t('home.reviews.title') }}
                    </h2>

                    <p
                        class="mt-5 max-w-2xl text-base leading-8 text-muted-foreground sm:text-lg"
                    >
                        {{ t('home.reviews.description') }}
                    </p>
                </header>

                <div
                    data-home-reveal
                    class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="review in homepageReviews"
                        :key="review.id"
                        class="flex h-full flex-col rounded-md border border-border bg-card p-6 text-card-foreground shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div
                                role="img"
                                :aria-label="
                                    t('home.reviews.ratingLabel', {
                                        rating: review.rating,
                                    })
                                "
                                class="flex gap-1"
                            >
                                <Star
                                    v-for="star in 5"
                                    :key="star"
                                    aria-hidden="true"
                                    class="size-4"
                                    :class="
                                        star <= review.rating
                                            ? 'fill-primary text-primary'
                                            : 'fill-transparent text-muted-foreground/30'
                                    "
                                />
                            </div>

                            <Quote
                                aria-hidden="true"
                                class="size-7 text-primary/30"
                            />
                        </div>

                        <blockquote class="mt-6 flex-1">
                            <p class="leading-7 text-muted-foreground">
                                “{{ review.review }}”
                            </p>
                        </blockquote>

                        <div class="mt-8 border-t border-border pt-5">
                            <div class="flex items-center gap-3">
                                <div
                                    aria-hidden="true"
                                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary font-semibold text-primary-foreground uppercase"
                                >
                                    {{ review.author.charAt(0) }}
                                </div>

                                <div>
                                    <p class="font-semibold">
                                        {{ review.author }}
                                    </p>

                                    <p
                                        class="mt-0.5 flex items-center gap-1.5 text-xs text-muted-foreground"
                                    >
                                        <BadgeCheck
                                            aria-hidden="true"
                                            class="size-3.5 text-primary"
                                        />

                                        {{ t('home.reviews.verified') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5">
                                <p
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    {{ t('home.reviews.dishLabel') }}
                                </p>

                                <a
                                    :href="menu.showDish(review.dish.id).url"
                                    class="mt-1 flex items-center justify-between gap-3 font-semibold text-primary transition-colors hover:text-primary/75"
                                >
                                    <span>{{ review.dish.name }}</span>

                                    <span class="shrink-0 text-sm">
                                        {{ t('home.reviews.viewDish') }} →
                                    </span>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.4s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.image-fade-enter-active,
.image-fade-leave-active {
    transition:
        opacity 700ms ease,
        transform 900ms cubic-bezier(0.22, 1, 0.36, 1);
    will-change: opacity, transform;
}

.image-fade-enter-from {
    opacity: 0;
    transform: scale(1.025);
}

.image-fade-enter-to,
.image-fade-leave-from {
    opacity: 1;
    transform: scale(1);
}

.image-fade-leave-to {
    opacity: 0;
    transform: scale(0.99);
}

.image-fade-leave-active {
    position: absolute;
    inset: 0;
}

.description-slot {
    display: grid;
    min-height: 10.5rem;
    align-items: start;
}

.description-slot > p {
    grid-area: 1 / 1;
}

@media (min-width: 640px) {
    .description-slot {
        min-height: 7.5rem;
    }
}

@media (min-width: 768px) {
    .description-slot {
        min-height: 6rem;
    }
}
</style>
