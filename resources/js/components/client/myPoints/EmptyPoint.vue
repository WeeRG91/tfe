<script setup lang="ts">
import { Gift } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl bg-gray-50 p-12 text-center"
    >
        <div class="mb-4 rounded-full bg-gray-200 p-4">
            <Gift class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="mb-2 text-lg font-semibold text-gray-900">No points yet</h3>
        <p class="max-w-md text-sm text-gray-500">
            Start earning loyalty points by placing orders and participating in
            promotions.
        </p>
    </div>
</template>

<style scoped></style>
