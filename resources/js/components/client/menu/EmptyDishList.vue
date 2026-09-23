<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div
        class="flex h-full w-full flex-col items-center justify-center p-10 text-center"
    >
        <div
            class="flex h-16 w-16 items-center justify-center rounded-full bg-muted text-3xl text-muted-foreground"
        >
            🍽️
        </div>

        <h3 class="mt-4 text-lg font-semibold">
            {{ t('menu.emptyList.title') }}
        </h3>

        <p class="mt-2 max-w-sm text-sm text-muted-foreground">
            {{ t('menu.emptyList.description') }}
        </p>
        <p class="mt-2 max-w-sm text-sm text-muted-foreground">
            {{ t('menu.emptyList.addition') }}
        </p>
    </div>
</template>

<style scoped></style>
