<script setup lang="ts">
import HomeContent from '@/components/client/home/HomeContent.vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { gsap } from 'gsap';
import { onMounted } from 'vue';

onMounted(() => {
    const tl = gsap.timeline({ delay: 0 });

    tl.to('.col', {
        top: 0,
        duration: 3,
        ease: 'power4.inOut',
    });

    tl.to(
        '.c-1 .item',
        {
            top: 0,
            stagger: 0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=2',
    );

    tl.to(
        '.c-2 .item',
        {
            top: 0,
            stagger: -0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    tl.to(
        '.c-3 .item',
        {
            top: 0,
            stagger: 0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    tl.to(
        '.c-4 .item',
        {
            top: 0,
            stagger: -0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    tl.to(
        '.c-5 .item',
        {
            top: 0,
            stagger: 0.25,
            duration: 3,
            ease: 'power4.inOut',
        },
        '-=4',
    );

    tl.to(
        '.container-home',
        {
            scale: 6,
            duration: 4,
            ease: 'power4.inOut',
        },
        '-=2',
    );

    tl.to(
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

    tl.to(
        '.content',
        {
            opacity: 1,
            duration: 1.5,
            ease: 'power3.out',
        },
        '>-0.3',
    );

    tl.to('.container-home', {
        display: 'none',
    });

    tl.to('.landing-wrapper', {
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
                        :src="`/images/dish-${n}.jpg`"
                        :alt="`dish-${n}.jpg`"
                    />
                </div>
            </div>

            <div class="col c-2">
                <div class="item" v-for="n in [6, 7, 8, 9, 10]" :key="'c2' + n">
                    <img
                        :src="`/images/dish-${n}.jpg`"
                        :alt="`dish-${n}.jpg`"
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
                        :src="`/images/dish-${n}.jpg`"
                        :alt="`dish-${n}.jpg`"
                    />
                </div>
            </div>

            <div class="col c-4">
                <div class="item" v-for="n in [6, 7, 8, 9, 10]" :key="'c4' + n">
                    <img
                        :src="`/images/dish-${n}.jpg`"
                        :alt="`dish-${n}.jpg`"
                    />
                </div>
            </div>

            <div class="col c-5">
                <div class="item" v-for="n in 5" :key="'c5' + n">
                    <img
                        :src="`/images/dish-${n}.jpg`"
                        :alt="`dish-${n}.jpg`"
                    />
                </div>
            </div>
        </div>

        <ClientLayout class="content opacity-0">
            <HomeContent />
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

footer {
    position: absolute;
    bottom: 0;
    width: 100%;
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
    top: 100%;
    position: relative;
}

.c-1 .item,
.c-3 .item,
.c-5 .item {
    top: 100%;
    position: relative;
}

.c-2 .item,
.c-4 .item {
    top: -100%;
    position: relative;
}
</style>
