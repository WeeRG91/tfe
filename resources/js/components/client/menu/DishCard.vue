<script setup lang="ts">
import { formatPrice } from '@/lib/utils';
import menu from '@/routes/menu';
import { ClientDishType, DishAvailabilityEnum } from '@/types/dish';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Flame } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    dish: ClientDishType;
}>();

const emit = defineEmits<{
    add: [dish: ClientDishType];
}>();

const starAnimationTrigger = ref(0);
const ratingContainer = ref<HTMLElement | null>(null);
const floatingStars = ref<
    Array<{
        id: number;
        x: number;
        y: number;
        delay: number;
        size: number;
        angle: number;
        distance: number;
    }>
>([]);

const ratingPercentage = computed(() => {
    return ((props.dish.rating_average ?? 0) / 5) * 100;
});

const goToDetail = () => {
    if (props.dish.is_available === DishAvailabilityEnum.AVAILABLE) {
        router.visit(menu.showDish(props.dish.id).url);
    }
};

watch(
    () => [props.dish.rating_average, props.dish.rating_count],
    ([newAverage], [oldAverage]) => {
        if (newAverage !== oldAverage) {
            triggerStarAnimation();
        }
    },
);

const triggerStarAnimation = () => {
    starAnimationTrigger.value++;

    const stars = [];
    const numStars = 20 + Math.floor(Math.random() * 16);
    const container = ratingContainer.value;

    if (!container) return;

    const rect = container.getBoundingClientRect();
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    for (let i = 0; i < numStars; i++) {
        const angle = Math.random() * Math.PI * 2;
        const distance = 40 + Math.random() * 80;
        const x = centerX + Math.cos(angle) * distance;
        const y = centerY + Math.sin(angle) * distance;

        stars.push({
            id: Date.now() + i + Math.random() * 1000,
            x: x - 8,
            y: y - 8,
            delay: Math.random() * 0.1,
            size: 8 + Math.random() * 12,
            angle: angle,
            distance: distance,
        });
    }

    floatingStars.value = stars;

    setTimeout(() => {
        floatingStars.value = [];
    }, 800);
};
</script>

<template>
    <div
        @click="goToDetail"
        class="group flex h-full cursor-pointer flex-col rounded-md border bg-white transition-all duration-300"
        :class="{
            'opacity-60 grayscale hover:opacity-70':
                props.dish.is_available === DishAvailabilityEnum.UNAVAILABLE,
            'hover:scale-[1.02] hover:shadow-lg':
                props.dish.is_available === DishAvailabilityEnum.AVAILABLE,
            'cursor-not-allowed':
                props.dish.is_available === DishAvailabilityEnum.UNAVAILABLE,
        }"
    >
        <div class="relative overflow-hidden rounded-t-md">
            <img
                :src="props.dish.main_image"
                :alt="props.dish.name"
                class="h-48 w-full object-cover transition duration-500"
                :class="{
                    'group-hover:scale-105':
                        props.dish.is_available ===
                        DishAvailabilityEnum.AVAILABLE,
                    'group-hover:scale-100':
                        props.dish.is_available ===
                        DishAvailabilityEnum.UNAVAILABLE,
                }"
            />
        </div>

        <div class="flex flex-1 flex-col px-4 py-2">
            <div class="flex flex-row items-center justify-between">
                <h3
                    class="text-lg font-semibold uppercase transition-colors"
                    :class="{
                        'text-gray-400':
                            props.dish.is_available ===
                            DishAvailabilityEnum.UNAVAILABLE,
                    }"
                >
                    {{ props.dish.name }}
                </h3>

                <div class="flex items-center gap-1">
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

            <p
                class="mt-2 line-clamp-2 text-sm transition-colors"
                :class="{
                    'text-gray-400':
                        props.dish.is_available ===
                        DishAvailabilityEnum.UNAVAILABLE,
                    'text-gray-600':
                        props.dish.is_available ===
                        DishAvailabilityEnum.AVAILABLE,
                }"
            >
                {{ props.dish.description }}
            </p>

            <div class="mt-auto">
                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <div class="flex items-center gap-2">
                            <div class="relative overflow-visible">
                                <div
                                    ref="ratingContainer"
                                    class="rating-container relative inline-block"
                                >
                                    <div class="flex text-lg text-gray-300">
                                        <span
                                            v-for="star in 5"
                                            :key="`bg-${star}`"
                                            >★</span
                                        >
                                    </div>

                                    <div
                                        class="absolute top-0 left-0 overflow-hidden text-lg whitespace-nowrap text-yellow-400 transition-all duration-500"
                                        :style="{
                                            width: `${ratingPercentage}%`,
                                        }"
                                        :class="{
                                            'animate-pulse':
                                                starAnimationTrigger > 0,
                                        }"
                                    >
                                        <span
                                            v-for="star in 5"
                                            :key="`fg-${star}`"
                                            >★</span
                                        >
                                    </div>

                                    <div
                                        v-for="star in floatingStars"
                                        :key="star.id"
                                        class="firework-star pointer-events-none absolute text-yellow-400"
                                        :style="{
                                            left: `${star.x}px`,
                                            top: `${star.y}px`,
                                            fontSize: `${star.size}px`,
                                            animationDelay: `${star.delay}s`,
                                            '--angle': `${star.angle}rad`,
                                            '--distance': `${star.distance}px`,
                                        }"
                                    >
                                        ★
                                    </div>
                                </div>
                            </div>

                            <span class="text-xs font-semibold text-gray-900">
                                {{
                                    (props.dish.rating_average ?? 0).toFixed(1)
                                }}
                            </span>
                        </div>

                        <span
                            class="ml-1 text-xs transition-colors"
                            :class="{
                                'text-gray-400':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.UNAVAILABLE,
                            }"
                        >
                            ({{ props.dish.rating_count ?? 0 }})
                        </span>
                    </div>

                    <div
                        class="flex items-center gap-1 transition-colors"
                        :class="{
                            'text-gray-400':
                                props.dish.is_available ===
                                DishAvailabilityEnum.UNAVAILABLE,
                            'text-gray-500':
                                props.dish.is_available ===
                                DishAvailabilityEnum.AVAILABLE,
                        }"
                    >
                        <span
                            class="font-semibold transition-colors"
                            :class="{
                                'text-gray-400':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.UNAVAILABLE,
                                'text-red-500':
                                    props.dish.is_available ===
                                    DishAvailabilityEnum.AVAILABLE,
                            }"
                        >
                            €{{ formatPrice(props.dish.price) }}
                        </span>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3">
                    <span
                        class="text-xs tracking-widest uppercase transition-colors"
                        :class="{
                            'text-gray-400':
                                props.dish.is_available ===
                                DishAvailabilityEnum.UNAVAILABLE,
                        }"
                    >
                        {{ t(`menu.categories.${props.dish.category.key}`) }}
                    </span>

                    <button
                        v-if="
                            props.dish.is_available ===
                            DishAvailabilityEnum.AVAILABLE
                        "
                        @click.stop="emit('add', props.dish)"
                        class="cursor-pointer text-sm text-red-500 transition-all duration-300 hover:translate-x-1 hover:text-red-600"
                    >
                        {{ $t('menu.card.add') }}
                        <span
                            class="inline-block transition-transform group-hover:translate-x-1"
                            >→</span
                        >
                    </button>

                    <button
                        v-else
                        disabled
                        class="cursor-not-allowed text-sm text-gray-400"
                    >
                        {{ $t('menu.card.unavailable') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cursor-not-allowed {
    cursor: not-allowed;
}

.rating-container {
    position: relative;
    overflow: visible;
}

.firework-star {
    animation: fireworkExplode 0.8s ease-out forwards;
    opacity: 0;
}

@keyframes fireworkExplode {
    0% {
        opacity: 1;
        transform: translate(0, 0) scale(0) rotate(0deg);
    }
    30% {
        opacity: 1;
        transform: translate(
                calc(cos(var(--angle)) * var(--distance) * 0.3),
                calc(sin(var(--angle)) * var(--distance) * 0.3)
            )
            scale(1.2) rotate(60deg);
    }
    70% {
        opacity: 1;
        transform: translate(
                calc(cos(var(--angle)) * var(--distance) * 0.7),
                calc(sin(var(--angle)) * var(--distance) * 0.7)
            )
            scale(1) rotate(120deg);
    }
    100% {
        opacity: 0;
        transform: translate(
                calc(cos(var(--angle)) * var(--distance)),
                calc(sin(var(--angle)) * var(--distance))
            )
            scale(0.3) rotate(180deg);
    }
}

@keyframes ratingPulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}

.animate-pulse {
    animation: ratingPulse 0.6s ease-in-out 2;
}

.rating-container .text-yellow-400 {
    transition: text-shadow 0.3s ease;
}

.rating-container:hover .text-yellow-400 {
    text-shadow: 0 0 20px rgba(250, 204, 21, 0.3);
}
</style>
