<script setup lang="ts">
defineProps<{
    title: string;
    subtitle?: string;
    badge?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-red-50 via-white to-red-50 p-6"
    >
        <div
            class="w-full max-w-lg rounded-2xl border border-red-50/50 bg-white p-8 shadow-xl transition-all duration-300 sm:p-12"
        >
            <div class="mb-8 text-center">
                <p
                    class="text-sm font-semibold tracking-widest text-red-500 uppercase"
                >
                    [ {{ badge }} ]
                </p>
                <h1
                    class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 uppercase sm:text-3xl"
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
