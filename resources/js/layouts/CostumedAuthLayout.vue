<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

defineProps<{
    title: string;
    subtitle?: string;
    badge?: string;
}>();

const goToHome = () => {
    router.visit('/');
};
</script>

<template>
    <div
        class="relative flex min-h-screen items-center justify-center bg-gradient-to-br from-red-50 via-white to-red-50 p-6"
    >
        <div class="fixed top-1 right-4 z-50 sm:top-6 sm:right-32">
            <LanguageSwitcher />
        </div>

        <div
            class="fixed top-0 left-12 h-full w-0.5 bg-gradient-to-b from-red-500/80 via-red-300/50 to-red-500/80 sm:left-24"
        ></div>

        <div
            class="fixed top-12 left-0 h-0.5 w-full bg-gradient-to-r from-red-500/80 via-red-300/50 to-red-500/80 sm:top-24"
        ></div>

        <div class="fixed top-8 left-20 z-10 sm:top-19 sm:left-36">
            <span
                @click="goToHome"
                class="inline-block cursor-pointer rounded-lg bg-red-500 px-4 py-1.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:scale-105 hover:bg-red-600 hover:shadow-lg active:scale-95 sm:text-lg"
            >
                Restaurant
            </span>
        </div>

        <div
            class="relative w-full max-w-lg rounded-2xl border border-red-50/50 bg-white p-8 shadow-xl transition-all duration-300 sm:p-12"
        >
            <div class="mb-8 text-center">
                <p
                    class="text-sm font-semibold tracking-widest text-red-500 uppercase"
                >
                    [ {{ badge }} ]
                </p>
                <h1
                    class="mt-2 text-xl font-semibold tracking-tight text-gray-900 uppercase sm:text-2xl"
                >
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-500">
                    {{ subtitle }}
                </p>
            </div>

            <div class="mb-6">
                <slot />
            </div>

            <div
                class="border-t border-gray-100 pt-4 text-center text-sm text-gray-500"
            >
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>
