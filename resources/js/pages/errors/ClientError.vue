<script setup lang="ts">
import type { SupportedLocale } from '@/i18n';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    HelpCircle,
    Home,
    RefreshCcw,
    SearchX,
} from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    status: 403 | 404 | 419 | 429 | 500 | 503;
    locale: SupportedLocale;
    fallbackLocale: SupportedLocale;
}>();

const {
    t,
    locale: i18nLocale,
    fallbackLocale: i18nFallbackLocale,
} = useI18n({ useScope: 'global' });

watch(
    () => props.locale,
    (value) => {
        i18nLocale.value = value;

        if (typeof document !== 'undefined') {
            document.documentElement.lang = value;
        }
    },
    { immediate: true },
);

watch(
    () => props.fallbackLocale,
    (value) => {
        i18nFallbackLocale.value = value;
    },
    { immediate: true },
);

const errorContent = computed(() => {
    const pages = {
        403: {
            title: t('errors.403.title'),
            eyebrow: t('errors.403.eyebrow'),
            description: t('errors.403.description'),
            icon: AlertTriangle,
        },
        404: {
            title: t('errors.404.title'),
            eyebrow: t('errors.404.eyebrow'),
            description: t('errors.404.description'),
            icon: SearchX,
        },
        419: {
            title: t('errors.419.title'),
            eyebrow: t('errors.419.eyebrow'),
            description: t('errors.419.description'),
            icon: RefreshCcw,
        },
        429: {
            title: t('errors.429.title'),
            eyebrow: t('errors.429.eyebrow'),
            description: t('errors.429.description'),
            icon: AlertTriangle,
        },
        500: {
            title: t('errors.500.title'),
            eyebrow: t('errors.500.eyebrow'),
            description: t('errors.500.description'),
            icon: AlertTriangle,
        },
        503: {
            title: t('errors.503.title'),
            eyebrow: t('errors.503.eyebrow'),
            description: t('errors.503.description'),
            icon: RefreshCcw,
        },
    };

    return pages[props.status] ?? pages[500];
});

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="`${status} - ${errorContent.title}`" />

    <section
        class="relative flex h-screen w-full items-center justify-center overflow-hidden px-6 py-16 md:px-8"
    >
        <div
            class="animate-pulse-grid absolute inset-0 bg-[linear-gradient(to_right,#ef44441a_1px,transparent_1px),linear-gradient(to_bottom,#ef44441a_1px,transparent_1px)] bg-[size:24px_24px]"
        ></div>

        <div
            class="z-10 grid w-full max-w-7xl items-center gap-3 lg:grid-cols-[1fr_28rem]"
        >
            <div class="order-2 text-center lg:order-1 lg:text-left">
                <div
                    class="mb-6 inline-flex items-center gap-2.5 rounded-full border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-700 lg:mb-8"
                >
                    <component :is="errorContent.icon" class="h-4 w-4" />
                    <span>
                        {{ errorContent.eyebrow }}
                    </span>
                </div>

                <p
                    class="text-8xl leading-none font-black text-red-600 sm:text-9xl md:text-[8rem]"
                >
                    {{ status }}
                </p>

                <h1
                    class="mx-auto mt-5 max-w-2xl text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl md:text-5xl lg:mx-0 lg:mt-6"
                >
                    {{ errorContent.title }}
                </h1>

                <p
                    class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-gray-600 sm:text-lg lg:mx-0 lg:mt-5"
                >
                    {{ errorContent.description }}
                </p>

                <div
                    class="mt-9 flex flex-wrap justify-center gap-4 lg:mt-12 lg:justify-start"
                >
                    <Link
                        href="/public"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:bg-red-700 hover:shadow-lg focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none sm:px-7 sm:py-3.5"
                    >
                        <Home
                            class="h-4 w-4 transition-transform group-hover:-translate-x-1"
                        />
                        <span>{{ t('errors.backToHome') }}</span>
                    </Link>
                    <button
                        type="button"
                        @click="goBack"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-300 hover:border-gray-400 hover:bg-gray-50 hover:text-gray-900 focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 focus:outline-none sm:px-7 sm:py-3.5"
                    >
                        <ArrowLeft
                            class="h-4 w-4 transition-transform group-hover:-translate-x-1.5"
                        />
                        <span>{{ t('errors.goBack') }}</span>
                    </button>
                </div>

                <div
                    class="mt-8 flex items-center justify-center gap-2 text-sm text-gray-400 lg:mt-10 lg:justify-start"
                >
                    <HelpCircle class="h-4 w-4" />
                    <span>
                        {{ t('errors.needAssistance') }}
                        <a
                            href="/support"
                            class="font-medium text-red-500 transition-colors hover:text-red-600"
                        >
                            {{ t('errors.contactSupport') }}
                        </a>
                    </span>
                </div>
            </div>

            <div class="order-1 hidden lg:order-2 lg:block">
                <div
                    class="relative mx-auto aspect-square max-w-sm lg:max-w-md"
                >
                    <div
                        class="absolute inset-0 rounded-full bg-gradient-to-r from-red-500/5 to-amber-500/5"
                    ></div>

                    <div
                        class="absolute inset-0 rounded-full border border-red-200"
                    ></div>

                    <div
                        class="absolute inset-8 rounded-full border border-red-100"
                    ></div>

                    <div
                        class="absolute inset-0 flex items-center justify-center rounded-full bg-white shadow-lg"
                    >
                        <span
                            class="text-8xl font-black text-red-600 md:text-9xl lg:text-[10rem]"
                        >
                            {{ status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
section {
    animation: fade-in 0.4s ease-out;
}

@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes pulse-grid {
    0% {
        opacity: 0.2;
    }
    40% {
        opacity: 0.4;
    }
    80% {
        opacity: 0.8;
    }
    100% {
        opacity: 1;
    }
}

.animate-pulse-grid {
    animation: pulse-grid 2s ease-in-out infinite;
}
</style>
