<script setup lang="ts">
import type { ThemeDefinition } from '@/types/theme';
import { Check } from 'lucide-vue-next';

defineProps<{
    theme: ThemeDefinition;
    selected: boolean;
    disabled?: boolean;
}>();

defineEmits<{
    select: [theme: ThemeDefinition];
}>();
</script>

<template>
    <button
        type="button"
        :disabled="disabled"
        :aria-pressed="selected"
        class="group w-full max-w-full min-w-0 overflow-hidden rounded-lg border bg-card text-left transition-all hover:-translate-y-0.5 hover:shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-60"
        :class="
            selected
                ? 'border-primary ring-2 ring-primary/30'
                : 'border-border hover:border-primary/50'
        "
        @click="$emit('select', theme)"
    >
        <div
            class="relative h-20 p-2"
            :style="{
                backgroundColor: theme.colors.background,
                color: theme.colors.foreground,
            }"
        >
            <div
                v-if="selected"
                class="absolute top-1.5 right-1.5 z-10 flex size-5 items-center justify-center rounded-full bg-primary text-primary-foreground"
            >
                <Check class="size-3.5" />
            </div>

            <div class="flex h-full gap-1.5">
                <div
                    class="w-1/4 border p-1.5"
                    :style="{
                        backgroundColor: theme.colors.sidebarBackground,
                        borderColor: theme.colors.sidebarBorder,
                        borderRadius: theme.radius,
                    }"
                >
                    <div
                        class="mb-1.5 h-1.5 w-3/4 rounded-full"
                        :style="{
                            backgroundColor: theme.colors.sidebarPrimary,
                        }"
                    />

                    <div
                        class="mb-1 h-1 w-full rounded-full opacity-50"
                        :style="{
                            backgroundColor: theme.colors.sidebarForeground,
                        }"
                    />

                    <div
                        class="h-1 w-2/3 rounded-full opacity-30"
                        :style="{
                            backgroundColor: theme.colors.sidebarForeground,
                        }"
                    />
                </div>

                <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                    <div
                        class="flex-1 border p-1.5"
                        :style="{
                            backgroundColor: theme.colors.card,
                            borderColor: theme.colors.border,
                            borderRadius: theme.radius,
                        }"
                    >
                        <div
                            class="mb-1.5 h-1.5 w-1/2 rounded-full"
                            :style="{
                                backgroundColor: theme.colors.cardForeground,
                            }"
                        />

                        <div class="flex gap-1">
                            <span
                                class="size-3.5 rounded-full"
                                :style="{
                                    backgroundColor: theme.colors.primary,
                                }"
                            />

                            <span
                                class="size-3.5 rounded-full"
                                :style="{
                                    backgroundColor: theme.colors.secondary,
                                }"
                            />

                            <span
                                class="size-3.5 rounded-full"
                                :style="{
                                    backgroundColor: theme.colors.accent,
                                }"
                            />
                        </div>
                    </div>

                    <div class="flex gap-1">
                        <span
                            class="h-2 flex-1 rounded-full"
                            :style="{
                                backgroundColor: theme.colors.success,
                            }"
                        />

                        <span
                            class="h-2 flex-1 rounded-full"
                            :style="{
                                backgroundColor: theme.colors.warning,
                            }"
                        />

                        <span
                            class="h-2 flex-1 rounded-full"
                            :style="{
                                backgroundColor: theme.colors.destructive,
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2 px-2.5 py-2">
            <span class="truncate text-sm font-medium text-card-foreground">
                {{ theme.name }}
            </span>

            <span
                class="shrink-0 rounded-full bg-muted px-1.5 py-0.5 text-[10px] leading-tight text-muted-foreground capitalize"
            >
                {{ theme.mode }}
            </span>
        </div>
    </button>
</template>
