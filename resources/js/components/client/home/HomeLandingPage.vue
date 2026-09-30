<script setup lang="ts">
import HomeContent from '@/components/client/home/HomeContent.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import type { ClientDishType } from '@/types/dish';
import type { MobileAppAvailability } from '@/types/mobile-app';
import type { HomepageReviewType } from '@/types/rating';
import { gsap } from 'gsap';
import { nextTick, onMounted, ref } from 'vue';

defineProps<{
    recommendedDishes: ClientDishType[];
    homepageReviews: HomepageReviewType[];
    mobileApp: MobileAppAvailability;
}>();

const INTRO_STORAGE_KEY = 'home-intro-seen';

const animationsReady = ref(false);

const scrollToMobileAppSection = async (): Promise<void> => {
    if (window.location.hash !== '#mobile-app') {
        return;
    }

    await nextTick();

    const mobileAppSection = document.getElementById('mobile-app');

    if (!mobileAppSection) {
        return;
    }

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    mobileAppSection.scrollIntoView({
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
        block: 'start',
    });
};

onMounted(() => {
    const hasSeenIntro = sessionStorage.getItem(INTRO_STORAGE_KEY) === 'true';

    if (hasSeenIntro) {
        gsap.set('.container-home', {
            display: 'none',
        });

        gsap.set('.content', {
            opacity: 1,
        });

        gsap.set('.landing-wrapper', {
            overflowY: 'auto',
            overflowX: 'hidden',
        });

        animationsReady.value = true;

        void scrollToMobileAppSection();

        return;
    }

    const timeline = gsap.timeline({
        delay: 0,
        onComplete: () => {
            sessionStorage.setItem(INTRO_STORAGE_KEY, 'true');
            animationsReady.value = true;

            void scrollToMobileAppSection();
        },
    });

    timeline.to('.col', {
        top: 0,
        duration: 3,
        ease: 'power4.inOut',
    });

    timeline.to(
        '.c-1 .item',
        {
            top: 0,
            stagger: 0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=2',
    );

    timeline.to(
        '.c-2 .item',
        {
            top: 0,
            stagger: -0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    timeline.to(
        '.c-3 .item',
        {
            top: 0,
            stagger: 0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    timeline.to(
        '.c-4 .item',
        {
            top: 0,
            stagger: -0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    timeline.to(
        '.c-5 .item',
        {
            top: 0,
            stagger: 0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    timeline.to(
        '.container-home',
        {
            scale: 6,
            duration: 4,
            ease: 'power4.inOut',
        },
        '-=2',
    );

    timeline.to(
        '.container-home',
        {
            opacity: 0,
            scale: 8,
            filter: 'blur(10px)',
            duration: 2,
            ease: 'power3.out',
        },
        '-=2',
    );

    timeline.to(
        '.content',
        {
            opacity: 1,
            duration: 1.5,
            ease: 'power3.out',
        },
        '>-0.3',
    );

    timeline.to('.container-home', {
        display: 'none',
    });

    timeline.to('.landing-wrapper', {
        overflowY: 'auto',
        overflowX: 'hidden',
    });
});
</script>

<template>
    <div class="landing-wrapper">
        <div class="container-home">
            <div class="col c-1">
                <div class="item" v-for="n in 5" :key="'c1' + n">
                    <img
                        :src="`/images/home/intro/dish-${n}.webp`"
                        :alt="`Dish ${n}`"
                    />
                </div>
            </div>

            <div class="col c-2">
                <div class="item" v-for="n in [6, 7, 8, 9, 10]" :key="'c2' + n">
                    <img
                        :src="`/images/home/intro/dish-${n}.webp`"
                        :alt="`Dish ${n}`"
                    />
                </div>
            </div>

            <div class="col c-3">
                <div
                    class="item"
                    v-for="n in [11, 12, 13, 14, 15]"
                    :key="'c3' + n"
                >
                    <img
                        :src="`/images/home/intro/dish-${n}.webp`"
                        :alt="`Dish ${n}`"
                    />
                </div>
            </div>

            <div class="col c-4">
                <div class="item" v-for="n in [6, 7, 8, 9, 10]" :key="'c4' + n">
                    <img
                        :src="`/images/home/intro/dish-${n}.webp`"
                        :alt="`Dish ${n}`"
                    />
                </div>
            </div>

            <div class="col c-5">
                <div class="item" v-for="n in 5" :key="'c5' + n">
                    <img
                        :src="`/images/home/intro/dish-${n}.webp`"
                        :alt="`Dish ${n}`"
                    />
                </div>
            </div>
        </div>

        <ClientLayout class="content opacity-0">
            <HomeContent
                :recommended-dishes="recommendedDishes"
                :homepage-reviews="homepageReviews"
                :mobile-app="mobileApp"
                :animations-ready="animationsReady"
            />
        </ClientLayout>
    </div>
</template>

<style scoped>
.landing-wrapper {
    width: 100vw;
    height: 100vh;
    overflow: hidden;
}

img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.title p {
    position: relative;
    top: 150px;
}

.preview img {
    position: relative;
    top: 100px;
    width: 80px;
    height: 50px;
}

.slide-num p {
    position: relative;
    top: 30px;
}

.container-home {
    position: fixed;
    width: 100%;
    height: 100%;
    display: flex;
    gap: 1em;
}

.container-home .col {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1em;
}

.col .item {
    flex: 1;
    overflow: hidden;
}

.c-1,
.c-3,
.c-5 {
    position: relative;
    top: 100%;
}

.c-1 .item,
.c-3 .item,
.c-5 .item {
    position: relative;
    top: 100%;
}

.c-2 .item,
.c-4 .item {
    position: relative;
    top: -100%;
}
</style>
