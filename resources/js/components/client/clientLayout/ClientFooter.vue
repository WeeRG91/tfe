<script setup lang="ts">
import { Mail } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineProps<{
    restaurantName: string;
    contactEmail: string;
}>();

const { t } = useI18n();
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="relative overflow-hidden border-t border-border bg-background/95 text-foreground shadow-[0_-4px_10px_rgb(0_0_0/0.08)] backdrop-blur-sm"
    >
        <div
            aria-hidden="true"
            class="pointer-events-none absolute -top-24 -left-24 h-64 w-64 rounded-full bg-primary/5 blur-3xl"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-24 -bottom-24 h-64 w-64 rounded-full bg-accent/20 blur-3xl"
        />

        <div
            class="relative mx-auto flex max-w-7xl flex-col gap-8 px-6 py-12 md:flex-row md:items-center md:justify-between lg:px-8"
        >
            <div class="max-w-xl text-center md:text-left">
                <div
                    class="flex items-center justify-center gap-3 md:justify-start"
                >
                    <img
                        src="/images/branding/kin-dee-logo-emblem-thai-v2.png"
                        alt=""
                        aria-hidden="true"
                        class="h-12 w-12 shrink-0 rounded-full object-contain shadow-sm"
                    />

                    <p class="text-2xl font-bold tracking-tight text-primary">
                        {{ restaurantName }}
                    </p>
                </div>

                <p
                    class="mt-4 max-w-lg text-sm leading-6 text-muted-foreground"
                >
                    {{ t('footer.description') }}
                </p>
            </div>

            <div class="flex flex-col items-center md:items-end">
                <p
                    class="mb-3 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    {{ t('footer.contact') }}
                </p>

                <a
                    :href="`mailto:${contactEmail}`"
                    class="group inline-flex items-center gap-3 rounded-full border border-border bg-background px-5 py-3 text-sm font-medium shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/40 hover:text-primary hover:shadow-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                    >
                        <Mail class="h-4 w-4" aria-hidden="true" />
                    </span>

                    {{ contactEmail }}
                </a>
            </div>
        </div>

        <div class="relative border-t border-border bg-muted/20">
            <p
                class="mx-auto max-w-7xl px-6 py-4 text-center text-xs text-muted-foreground lg:px-8"
            >
                © {{ currentYear }} {{ restaurantName }}.
                {{ t('footer.copyright') }}
            </p>
        </div>
    </footer>
</template>
