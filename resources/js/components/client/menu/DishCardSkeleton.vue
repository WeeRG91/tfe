<script setup lang="ts"></script>

<template>
    <div
        class="flex h-full animate-pulse flex-col overflow-hidden rounded-md border border-border bg-card"
    >
        <div class="h-48 w-full bg-muted"></div>

        <div class="flex flex-1 flex-col p-4">
            <div class="flex items-center justify-between">
                <div class="h-5 w-32 rounded bg-muted"></div>
                <div class="h-5 w-16 rounded bg-muted"></div>
            </div>

            <div class="mt-2 space-y-2">
                <div class="h-3 w-full rounded bg-muted"></div>
                <div class="h-3 w-5/6 rounded bg-muted"></div>
            </div>

            <div class="mt-auto">
                <div class="mt-3 flex items-center justify-between">
                    <div class="flex gap-1">
                        <div
                            v-for="i in 5"
                            :key="i"
                            class="h-4 w-4 rounded bg-muted"
                        ></div>
                    </div>

                    <div class="h-4 w-10 rounded bg-muted"></div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div class="h-3 w-20 rounded bg-muted"></div>
                    <div class="h-4 w-16 rounded bg-muted"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
