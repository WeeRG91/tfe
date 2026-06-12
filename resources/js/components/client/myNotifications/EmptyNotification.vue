<script setup lang="ts">
import { BellOff } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-16 text-center"
    >
        <BellOff class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-4 text-lg font-semibold text-gray-900">
            No notifications
        </h3>
        <p class="mt-1 text-sm text-gray-500">
            When you have notifications, they'll show up here.
        </p>
    </div>
</template>

<style scoped></style>
