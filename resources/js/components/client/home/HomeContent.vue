<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import AnimatedButton from '@/components/AnimatedButton.vue';
import menu from '@/routes/menu';

const slides = [
    {
        image: '/images/dish-1.jpg',
        title: 'Main Courses',
        word: 'Spicy',
        description:
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Architecto repellat optio nam at ipsa sequi accusamus, minima doloremque quod. Harum.',
    },
    {
        image: '/images/dish-2.jpg',
        title: 'Soups',
        word: 'Tangy',
        description:
            'Lorem ipsum dolor sit amet consectetur, adipisicing elit. Nulla amet debitis ea saepe deserunt veniam modi inventore adipisci vel illum! Consequuntur hic ut',
    },
    {
        image: '/images/dish-3.jpg',
        title: 'Appetizers',
        word: 'Aromatic',
        description:
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Architecto repellat optio nam at ipsa sequi accusamus, minima doloremque quod. Harum.',
    },
    {
        image: '/images/dish-4.jpg',
        title: 'Noodles',
        word: 'Savory',
        description:
            'Lorem ipsum dolor sit amet consectetur, adipisicing elit. Nulla amet debitis ea saepe deserunt veniam modi inventore adipisci vel illum! Consequuntur hic ut',
    },
    {
        image: '/images/dish-5.jpg',
        title: 'Vegetarians',
        word: 'Fresh',
        description:
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Architecto repellat optio nam at ipsa sequi accusamus, minima doloremque quod. Harum.',
    },
];

const currentIndex = ref(0);
let interval: number;
const desktopThumbnailContainer = ref<HTMLElement | null>(null);
const mobileThumbnailContainer = ref<HTMLElement | null>(null);
const virtualIndex = ref(slides.length);

const infiniteSlides = computed(() => {
    return [...slides, ...slides, ...slides, ...slides, ...slides];
});

const thumbnailOffset = computed(() => {
    if (!desktopThumbnailContainer.value) return 0;

    const containerHeight = desktopThumbnailContainer.value.clientHeight;
    const thumbnailHeight = 96;
    const gap = 16;

    return (
        virtualIndex.value * (thumbnailHeight + gap) -
        containerHeight / 2 +
        thumbnailHeight / 2
    );
});

const scrollToActive = () => {
    nextTick(() => {
        if (window.innerWidth >= 768) {
            if (!desktopThumbnailContainer.value) return;

            desktopThumbnailContainer.value.scrollTo({
                top: thumbnailOffset.value,
                behavior: 'smooth',
            });
        } else {
            if (!mobileThumbnailContainer.value) return;

            const activeThumb = mobileThumbnailContainer.value.children[
                virtualIndex.value
            ] as HTMLElement;
            if (!activeThumb) return;

            const container = mobileThumbnailContainer.value;
            const containerWidth = container.clientWidth;
            const thumbWidth = activeThumb.offsetWidth;

            const scrollLeft =
                activeThumb.offsetLeft - containerWidth / 2 + thumbWidth / 2;

            container.scrollTo({
                left: scrollLeft,
                behavior: 'smooth',
            });
        }
    });
};

onMounted(() => {
    interval = setInterval(() => {
        virtualIndex.value++;

        if (virtualIndex.value >= slides.length * 4) {
            virtualIndex.value =
                slides.length + (virtualIndex.value % slides.length);

            nextTick(() => {
                if (
                    desktopThumbnailContainer.value &&
                    window.innerWidth >= 768
                ) {
                    desktopThumbnailContainer.value.scrollTop =
                        thumbnailOffset.value;
                }
            });
        }

        currentIndex.value = virtualIndex.value % slides.length;

        scrollToActive();
    }, 5000);

    scrollToActive();
});

onUnmounted(() => {
    clearInterval(interval);
});

const goToSlide = (index: number) => {
    const base = Math.floor(virtualIndex.value / slides.length) * slides.length;
    virtualIndex.value = base + index;

    currentIndex.value = index;
    scrollToActive();
};
</script>

<template>
    <section class="mx-auto max-w-5xl px-8 py-14">
        <Transition name="fade-slide" mode="out-in">
            <p
                :key="slides[currentIndex].word"
                class="mb-4 text-sm tracking-widest text-red-500 uppercase"
            >
                [ {{ slides[currentIndex].word }} ]
            </p>
        </Transition>

        <h1 class="mb-6 text-4xl font-semibold uppercase md:text-5xl">
            Our
            <Transition name="fade-slide" mode="out-in">
                <span
                    :key="slides[currentIndex].title"
                    class="inline-block text-red-500"
                >
                    {{ slides[currentIndex].title }}
                </span>
            </Transition>
        </h1>

        <Transition name="fade-slide" mode="out-in">
            <p
                :key="slides[currentIndex].description"
                class="mb-6 max-w-2xl text-gray-600"
            >
                {{ slides[currentIndex].description }}
            </p>
        </Transition>

        <AnimatedButton
            as="a"
            :href="menu.dish().url"
            text="Let's get spicy"
        />
    </section>

    <section class="px-8 pb-16">
        <div class="relative mx-auto max-w-6xl">
            <div class="relative overflow-hidden rounded-md">
                <img
                    :src="slides[currentIndex].image"
                    :alt="slides[currentIndex].image"
                    class="h-[300px] w-full object-cover transition-all duration-700 sm:h-[450px]"
                />

                <div
                    class="absolute inset-0 flex flex-col justify-end bg-black/10 p-6"
                >
                    <Transition name="fade-slide" mode="out-in">
                        <p
                            :key="slides[currentIndex].word"
                            class="mb-2 text-xs tracking-widest text-red-500 uppercase sm:text-sm"
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
                class="absolute top-1/2 right-0 mx-4 hidden max-h-[544px] -translate-y-1/2 flex-col gap-4 overflow-y-hidden px-2 md:flex"
                style="scrollbar-width: none; -ms-overflow-style: none"
            >
                <img
                    v-for="(slide, index) in infiniteSlides"
                    :key="index"
                    :src="slide.image"
                    :alt="slide.title"
                    @click="goToSlide(index % slides.length)"
                    class="h-24 w-40 flex-shrink-0 cursor-pointer rounded-md object-cover transition-all duration-300 ease-in-out"
                    :class="[
                        index === virtualIndex
                            ? 'scale-105 opacity-100 ring-2 ring-white'
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
                    v-for="(slide, index) in infiniteSlides"
                    :key="index"
                    :src="slide.image"
                    :alt="slide.title"
                    @click="goToSlide(index % slides.length)"
                    class="h-20 w-32 flex-shrink-0 cursor-pointer rounded-md object-cover transition-all duration-300 ease-in-out"
                    :class="[
                        index === virtualIndex
                            ? 'scale-105 opacity-100 ring-2 ring-white'
                            : 'opacity-50 grayscale hover:opacity-70 hover:grayscale-0',
                    ]"
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
</style>
