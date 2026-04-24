<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="text-center">
        <div class="mb-4 text-6xl">🛒</div>
        <h3 class="mb-2 text-xl font-semibold">Your cart is empty</h3>
        <p class="text-gray-600">Looks like you haven't added any items yet</p>
        <Link
            href="/menu/dishes"
            class="mt-4 inline-block rounded-md bg-red-500 px-6 py-2 text-white transition hover:bg-red-600"
        >
            Browse Menu
        </Link>
    </div>
</template>

<style scoped></style>
