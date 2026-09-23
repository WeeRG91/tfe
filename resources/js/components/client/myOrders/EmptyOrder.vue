<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { BookOpen, ShoppingBag } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div
        class="flex min-h-[500px] flex-col items-center justify-center rounded-lg border-2 border-dashed border-border bg-muted/20 p-8 text-center"
    >
        <div class="relative mb-4">
            <ShoppingBag class="h-16 w-16 text-muted-foreground" />
        </div>
        <h3 class="mb-2 text-xl font-semibold text-foreground">
            {{ t('order.emptyOrder.title') }}
        </h3>
        <p class="mb-6 text-sm text-muted-foreground">
            {{ t('order.emptyOrder.description') }}
        </p>
        <button
            @click="router.visit('/menu/dishes')"
            class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground transition-all hover:bg-primary/90"
        >
            <BookOpen class="h-4 w-4" />
            {{ t('order.emptyOrder.browseMenu') }}
        </button>
    </div>
</template>

<style scoped></style>
