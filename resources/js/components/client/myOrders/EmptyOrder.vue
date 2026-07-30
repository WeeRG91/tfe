<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { BookOpen, ShoppingBag } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div
        class="flex min-h-[500px] flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 p-8 text-center"
    >
        <div class="relative mb-4">
            <ShoppingBag class="h-16 w-16 text-gray-400" />
        </div>
        <h3 class="mb-2 text-xl font-semibold text-gray-800">
            {{ t('order.emptyOrder.title') }}
        </h3>
        <p class="mb-6 text-sm text-gray-600">
            {{ t('order.emptyOrder.description') }}
        </p>
        <button
            @click="router.visit('/menu/dishes')"
            class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-red-600"
        >
            <BookOpen class="h-4 w-4" />
            {{ t('order.emptyOrder.browseMenu') }}
        </button>
    </div>
</template>

<style scoped></style>
