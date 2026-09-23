<script setup lang="ts">
import { Gift } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-border bg-muted/40 p-12 text-center"
    >
        <div class="mb-4 rounded-full bg-primary/10 p-4">
            <Gift class="h-8 w-8 text-primary" />
        </div>
        <h3 class="mb-2 text-lg font-semibold text-foreground">
            {{ t('point.emptyPoint.title') }}
        </h3>
        <p class="max-w-md text-sm text-muted-foreground">
            {{ t('point.emptyPoint.description') }}
        </p>
    </div>
</template>

<style scoped></style>
