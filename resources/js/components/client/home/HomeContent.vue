<script setup lang="ts">
import AnimatedButton from '@/components/AnimatedButton.vue';
import DishCard from '@/components/client/menu/DishCard.vue';
import menu from '@/routes/menu';
import { ClientDishType } from '@/types/dish';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    recommendedDishes: ClientDishType[];
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
});

const goToSlide = (index: number) => {
    currentIndex.value = index;
    scrollToActive();
};
</script>

<template>
    <section class="mx-auto max-w-5xl p-8">
        <Transition name="fade-slide" mode="out-in">
            <p
                :key="slides[currentIndex].word"
                class="mb-4 text-sm tracking-widest text-primary uppercase"
            >
                [ {{ slides[currentIndex].word }} ]
            </p>
        </Transition>

        <h1 class="mb-6 text-3xl font-semibold uppercase md:text-5xl">
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

        <div class="description-slot mb-6 max-w-2xl">
            <Transition name="fade-slide">
                <p
                    :key="slides[currentIndex].description"
                    class="text-muted-foreground"
                >
                    {{ slides[currentIndex].description }}
                </p>
            </Transition>
        </div>

        <AnimatedButton
            as="a"
            :href="menu.dish().url"
            :text="t('home.callToAction')"
        />
    </section>

    <section class="px-8 pb-16">
        <div class="relative mx-auto max-w-6xl">
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
        v-if="recommendedDishes.length"
        class="border-t border-border bg-muted/30 px-6 py-16"
    >
        <div class="mx-auto max-w-6xl">
            <div
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p class="text-sm tracking-widest text-primary uppercase">
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

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <DishCard
                    v-for="dish in recommendedDishes"
                    :key="dish.id"
                    :dish="dish"
                    :show-add-action="false"
                />
            </div>
        </div>
    </section>
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
