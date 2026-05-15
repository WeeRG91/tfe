<script setup lang="ts">
import { Clock, ShoppingBag } from 'lucide-vue-next';
</script>

<template>
    <div class="space-y-8">
        <div class="space-y-3">
            <div
                class="flex items-center gap-2 border-l-4 border-amber-500 pl-3"
            >
                <Clock class="h-5 w-5 text-amber-500" />
                <div class="h-6 w-32 animate-pulse rounded bg-gray-200"></div>
                <div class="h-4 w-6 animate-pulse rounded bg-gray-200"></div>
            </div>

            <div
                v-for="i in 4"
                :key="i"
                class="rounded-lg border bg-white shadow-sm"
            >
                <div class="p-4">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div class="flex flex-1 items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100"
                                >
                                    <ShoppingBag
                                        class="h-5 w-5 text-gray-400"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <div
                                            class="h-5 w-24 animate-pulse rounded bg-gray-200"
                                        ></div>
                                        <div
                                            class="h-5 w-20 animate-pulse rounded-full bg-gray-200"
                                        ></div>
                                    </div>
                                    <div
                                        class="mt-1 h-3 w-32 animate-pulse rounded bg-gray-200"
                                    ></div>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div
                                    class="h-3 w-10 animate-pulse rounded bg-gray-200"
                                ></div>
                                <div
                                    class="mt-1 h-6 w-16 animate-pulse rounded bg-gray-200"
                                ></div>
                            </div>
                        </div>

                        <!-- Progress Bar Skeleton -->
                        <div class="mt-2">
                            <div class="mb-1 flex justify-between">
                                <div
                                    class="h-3 w-24 animate-pulse rounded bg-gray-200"
                                ></div>
                                <div
                                    class="h-3 w-8 animate-pulse rounded bg-gray-200"
                                ></div>
                            </div>
                            <div
                                class="h-1.5 overflow-hidden rounded-full bg-gray-100"
                            >
                                <div
                                    class="h-full w-1/3 rounded-full bg-gradient-to-r from-amber-500 to-red-500"
                                ></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-3 w-3 animate-pulse rounded bg-gray-200"
                                ></div>
                                <div
                                    class="h-3 w-16 animate-pulse rounded bg-gray-200"
                                ></div>
                            </div>
                            <div
                                class="h-4 w-4 animate-pulse rounded bg-gray-200"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
