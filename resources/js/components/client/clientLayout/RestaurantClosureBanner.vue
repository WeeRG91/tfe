<script setup lang="ts">
import { useRestaurantStore } from '@/stores/restaurant';
import { Clock3 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const restaurantStore = useRestaurantStore();
const { t } = useI18n();

const message = computed(() => {
    return (
        restaurantStore.current?.message?.trim() ||
        t('restaurant.closed')
    );
});
</script>

<template>
    <section
        v-if="restaurantStore.isClosed"
        class="closure-banner border-b border-amber-200 text-amber-950"
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
                                class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-600"
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
.closure-banner {
    background: linear-gradient(
        110deg,
        #fff7ed 0%,
        #fef3c7 50%,
        #fffbeb 100%
    );

    box-shadow: 0 3px 12px rgb(180 83 9 / 6%);
}

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
    min-width: 100cqw;
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
