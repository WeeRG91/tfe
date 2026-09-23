<script setup lang="ts"></script>

<template>
    <div
        class="group flex h-full overflow-hidden rounded-md border border-border bg-card"
    >
        <div class="relative w-32 overflow-hidden sm:w-40 md:w-44">
            <div class="h-68 w-full animate-pulse bg-muted"></div>

            <div class="absolute top-2 left-2">
                <div
                    class="h-6 w-16 animate-pulse rounded-full bg-muted-foreground/20"
                ></div>
            </div>
        </div>

        <div class="flex flex-1 flex-col px-4 py-3">
            <div class="flex items-start justify-between gap-2">
                <div class="h-6 flex-1 animate-pulse rounded bg-muted"></div>
                <div class="h-6 w-16 animate-pulse rounded bg-muted"></div>
            </div>

            <div class="mt-1 space-y-2">
                <div class="h-4 w-full animate-pulse rounded bg-muted"></div>
                <div class="h-4 w-3/4 animate-pulse rounded bg-muted"></div>
                <div class="h-4 w-5/6 animate-pulse rounded bg-muted"></div>
            </div>

            <div class="mt-auto">
                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <div class="flex gap-0.5">
                            <div
                                v-for="i in 5"
                                :key="i"
                                class="h-4 w-4 animate-pulse rounded bg-muted"
                            ></div>
                        </div>
                        <div
                            class="ml-1 h-3 w-8 animate-pulse rounded bg-muted"
                        ></div>
                    </div>

                    <div
                        class="h-4 w-6 animate-pulse rounded bg-muted"
                    ></div>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <div
                        class="h-3 w-20 animate-pulse rounded bg-muted"
                    ></div>
                    <div
                        class="h-5 w-12 animate-pulse rounded bg-muted"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
