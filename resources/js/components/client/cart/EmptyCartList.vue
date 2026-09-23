<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="text-center">
        <div class="mb-4 text-6xl">🛒</div>
        <h3 class="mb-2 text-xl font-semibold">Your cart is empty</h3>
        <p class="text-muted-foreground">
            Looks like you haven't added any items yet
        </p>
        <Link
            href="/menu/dishes"
            class="mt-4 inline-block rounded-md bg-primary px-6 py-2 text-primary-foreground transition hover:bg-primary/90"
        >
            Browse Menu
        </Link>
    </div>
</template>

<style scoped></style>
