<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { android, ios } from '@/routes/mobile-app';
import type { MobileAppAvailability } from '@/types/mobile-app';
import { Clock, Download } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

defineProps<{
    mobileApp: MobileAppAvailability;
}>();

const { t } = useI18n();
</script>

<template>
    <section
        id="mobile-app"
        data-home-section
        class="scroll-mt-24 bg-primary px-6 py-10 lg:px-8 lg:py-16"
    >
        <div
            data-home-reveal
            class="relative mx-auto max-w-6xl overflow-hidden rounded-md border border-border bg-gradient-to-br from-primary/10 via-card to-accent/20 px-6 py-10 shadow-sm sm:px-10 sm:py-12"
        >
            <div
                aria-hidden="true"
                class="pointer-events-none absolute -top-20 -right-20 size-56 rounded-full bg-primary/10 blur-3xl"
            ></div>

            <div
                aria-hidden="true"
                class="pointer-events-none absolute -bottom-24 -left-16 size-52 rounded-full bg-accent/30 blur-3xl"
            ></div>

            <div
                class="relative grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,0.8fr)]"
            >
                <div>
                    <p
                        class="text-sm font-medium tracking-[0.22em] text-primary-foreground uppercase"
                    >
                        [ {{ t('home.mobileApp.eyebrow') }} ]
                    </p>

                    <h2
                        class="mt-4 max-w-2xl text-xl leading-tight font-semibold tracking-tight uppercase sm:text-4xl"
                    >
                        {{ t('home.mobileApp.title') }}
                    </h2>

                    <p
                        class="mt-5 max-w-2xl text-base leading-8 text-muted-foreground sm:text-lg"
                    >
                        {{ t('home.mobileApp.description') }}
                    </p>
                </div>

                <div class="grid gap-4">
                    <Button
                        v-if="mobileApp.androidAvailable"
                        as="a"
                        :href="android().url"
                        variant="outline"
                        class="group h-auto w-full justify-start gap-4 border-border bg-background/80 px-5 py-4 text-left whitespace-normal shadow-sm hover:border-primary/40 hover:bg-accent"
                    >
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-md bg-white p-2 shadow-sm ring-1 ring-black/5"
                        >
                            <img
                                src="/images/mobile/google-play-logo.png"
                                alt=""
                                aria-hidden="true"
                                class="size-full object-contain"
                            />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-xs tracking-widest text-muted-foreground uppercase"
                            >
                                Android
                            </span>

                            <span class="mt-1 block font-semibold">
                                {{ t('home.mobileApp.downloadAndroid') }}
                            </span>
                        </span>

                        <Download
                            aria-hidden="true"
                            class="size-5 shrink-0 text-primary transition-transform group-hover:translate-y-0.5"
                        />
                    </Button>

                    <Button
                        v-else
                        type="button"
                        variant="outline"
                        disabled
                        class="h-auto w-full justify-start gap-4 border-border bg-background/60 px-5 py-4 text-left whitespace-normal disabled:opacity-70"
                    >
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-md bg-white p-2 shadow-sm ring-1 ring-black/5"
                        >
                            <img
                                src="/images/mobile/google-play-logo.png"
                                alt=""
                                aria-hidden="true"
                                class="size-full object-contain"
                            />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-xs tracking-widest text-muted-foreground uppercase"
                            >
                                Android
                            </span>

                            <span class="mt-1 block font-semibold">
                                {{ t('home.mobileApp.downloadAndroid') }}
                            </span>

                            <span
                                class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground"
                            >
                                <Clock aria-hidden="true" class="size-3.5" />

                                {{ t('home.mobileApp.comingSoon') }}
                            </span>
                        </span>
                    </Button>

                    <Button
                        v-if="mobileApp.iosAvailable"
                        as="a"
                        :href="ios().url"
                        variant="outline"
                        class="group h-auto w-full justify-start gap-4 border-border bg-background/80 px-5 py-4 text-left whitespace-normal shadow-sm hover:border-primary/40 hover:bg-accent"
                    >
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-md bg-white p-2 shadow-sm ring-1 ring-black/5"
                        >
                            <img
                                src="/images/mobile/app-store-logo.png"
                                alt=""
                                aria-hidden="true"
                                class="size-full object-contain"
                            />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-xs tracking-widest text-muted-foreground uppercase"
                            >
                                iOS
                            </span>

                            <span class="mt-1 block font-semibold">
                                {{ t('home.mobileApp.downloadIos') }}
                            </span>
                        </span>

                        <Download
                            aria-hidden="true"
                            class="size-5 shrink-0 text-primary transition-transform group-hover:translate-y-0.5"
                        />
                    </Button>

                    <Button
                        v-else
                        type="button"
                        variant="outline"
                        disabled
                        class="h-auto w-full justify-start gap-4 border-border bg-background/60 px-5 py-4 text-left whitespace-normal disabled:opacity-70"
                    >
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-md bg-white p-2 shadow-sm ring-1 ring-black/5"
                        >
                            <img
                                src="/images/mobile/app-store-logo.png"
                                alt=""
                                aria-hidden="true"
                                class="size-full object-contain"
                            />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-xs tracking-widest text-muted-foreground uppercase"
                            >
                                iOS
                            </span>

                            <span class="mt-1 block font-semibold">
                                {{ t('home.mobileApp.downloadIos') }}
                            </span>

                            <span
                                class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground"
                            >
                                <Clock aria-hidden="true" class="size-3.5" />

                                {{ t('home.mobileApp.comingSoon') }}
                            </span>
                        </span>
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>
