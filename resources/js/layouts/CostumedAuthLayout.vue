<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import RestaurantLogo from '@/components/RestaurantLogo.vue';

defineProps<{
    title: string;
    subtitle?: string;
    badge?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-screen items-center justify-center bg-gradient-to-br from-background via-muted/40 to-background p-6 text-foreground"
    >
        <div class="fixed top-1 right-4 z-50 sm:top-6 sm:right-32">
            <LanguageSwitcher />
        </div>

        <div
            class="fixed top-0 left-12 h-full w-0.5 bg-gradient-to-b from-primary/80 via-primary/30 to-primary/80 sm:left-24"
        ></div>

        <div
            class="fixed top-12 left-0 h-0.5 w-full bg-gradient-to-r from-primary/80 via-primary/30 to-primary/80 sm:top-24"
        ></div>

        <div class="fixed top-4 left-16 z-10 sm:top-14 sm:left-36">
            <RestaurantLogo size="xl" />
        </div>

        <div
            class="relative w-full max-w-lg rounded-2xl border border-border bg-card p-8 text-card-foreground shadow-xl transition-all duration-300 sm:p-12"
        >
            <div class="mb-8 text-center">
                <p
                    class="text-sm font-semibold tracking-widest text-primary uppercase"
                >
                    [ {{ badge }} ]
                </p>
                <h1
                    class="mt-2 text-xl font-semibold tracking-tight text-card-foreground uppercase sm:text-2xl"
                >
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="mt-1 text-sm text-muted-foreground">
                    {{ subtitle }}
                </p>
            </div>

            <div class="mb-6">
                <slot />
            </div>

            <div
                class="border-t border-border pt-4 text-center text-sm text-muted-foreground"
            >
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>
