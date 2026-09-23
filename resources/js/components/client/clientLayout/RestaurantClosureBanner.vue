<script setup lang="ts">
import { useRestaurantClosureMessage } from '@/composables/useRestaurantClosureMessage';
import { useRestaurantStore } from '@/stores/restaurant';

const restaurantStore = useRestaurantStore();

const { message } = useRestaurantClosureMessage();
</script>

<template>
    <section
        v-if="restaurantStore.isClosed"
        class="closure-banner border-b border-warning/30 bg-gradient-to-r from-warning/10 via-warning/15 to-warning/10 text-foreground shadow-sm shadow-warning/10"
        role="status"
        aria-live="polite"
        aria-atomic="true"
    >
        <p class="sr-only">{{ message }}</p>

        <div
            class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:gap-5 sm:px-6 lg:px-8"
            aria-hidden="true"
        >
            <div class="banner-window min-w-0 flex-1 overflow-hidden">
                <div class="banner-track flex w-max">
                    <div
                        v-for="copy in 2"
                        :key="copy"
                        class="banner-group flex shrink-0 items-center justify-around gap-10 px-5"
                    >
                        <span
                            v-for="item in 2"
                            :key="item"
                            class="banner-message flex shrink-0 items-center gap-3 text-sm font-medium"
                        >
                            <span
                                class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning"
                            />

                            {{ message }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.banner-window {
    width: 100%;
    container-type: inline-size;

    mask-image: linear-gradient(
        to right,
        transparent,
        black 24px,
        black calc(100% - 24px),
        transparent
    );
}

.banner-group {
    min-width: 100vw;
}

.banner-track {
    animation: closure-slide 35s linear infinite;
}

.closure-banner:hover .banner-track {
    animation-play-state: paused;
}

@keyframes closure-slide {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-50%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .banner-window {
        mask-image: none;
    }

    .banner-track {
        width: 100%;
        animation: none;
    }

    .banner-group {
        min-width: 0;
        width: 100%;
        padding: 0;
    }

    .banner-group:nth-child(2),
    .banner-message:not(:first-child) {
        display: none;
    }

    .banner-message {
        flex-shrink: 1;
        white-space: normal;
        overflow-wrap: anywhere;
    }
}
</style>
