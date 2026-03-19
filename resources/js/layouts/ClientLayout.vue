<script setup lang="ts">
import menu from '@/routes/menu';
</script>

<template>
    <div class="min-h-screen w-full bg-white text-gray-900">
        <header class="flex items-center justify-between border-b px-8 py-4">
            <div class="text-xl font-bold text-red-500">
                <a href="/">Restaurant</a>
            </div>

            <nav class="hidden gap-6 text-sm text-gray-600 md:flex">
                <a :href="menu.index().url">Menu</a>
                <a href="#">Orders</a>
                <a href="#">About</a>
                <a href="#">Contact</a>
            </nav>

            <div class="flex items-center gap-4">
                <button class="text-sm text-gray-600">Sign in</button>
                <input
                    type="text"
                    placeholder="Search docs"
                    class="hidden rounded border px-3 py-1 text-sm md:block"
                />
            </div>
        </header>

        <slot />
    </div>
</template>

<style scoped></style>
