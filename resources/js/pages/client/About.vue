<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import menu from '@/routes/menu';
import type { AppPageProps } from '@/types';
import type { AboutClosureType, AboutOpeningDayType } from '@/types/about';
import { Head, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const cuisinePrinciples = [
    'aromatic',
    'balanced',
    'prepared',
    'welcoming',
] as const;

const restaurantValues = [
    'ingredients',
    'preparation',
    'tradition',
    'hospitality',
] as const;

const restaurantServices = [
    'dining',
    'takeaway',
    'delivery',
    'vegetarian',
] as const;

const props = defineProps<{
    hours: AboutOpeningDayType[];
    closures: AboutClosureType[];
    timezone: string;
}>();

const page = usePage<AppPageProps>();
const restaurant = computed(() => page.props.restaurant);
const { t, locale } = useI18n();

const currentWeekdayKey = computed(() => {
    return new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        timeZone: props.timezone,
    })
        .format(new Date())
        .toLowerCase();
});

const fullAddress = computed(() => {
    const address = restaurant.value.address;

    return [
        address.street,
        `${address.postalCode} ${address.city}`,
        address.country,
    ]
        .filter(Boolean)
        .join(', ');
});

const directionsUrl = computed(() => {
    const query = encodeURIComponent(fullAddress.value);

    return `https://www.google.com/maps/search/?api=1&query=${query}`;
});

const formatClosureDate = (closure: AboutClosureType): string => {
    const startAt = new Date(closure.starts_at);
    const endsAt = new Date(closure.ends_at);

    const visibleEndsAt = closure.is_all_day
        ? new Date(endsAt.getTime() - 86_400_000)
        : endsAt;

    const formatter = new Intl.DateTimeFormat(locale.value, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: props.timezone,
    });

    const formattedStart = formatter.format(startAt);
    const formattedEnd = formatter.format(visibleEndsAt);

    return formattedStart === formattedEnd
        ? formattedStart
        : `${formattedStart} – ${formattedEnd}`;
};

const formatClosureTime = (closure: AboutClosureType): string => {
    if (closure.is_all_day) {
        return t('about.schedule.closures.fullDay');
    }

    const formatter = new Intl.DateTimeFormat(locale.value, {
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
        timeZone: props.timezone,
    });

    return `${formatter.format(new Date(closure.starts_at))}–${formatter.format(
        new Date(closure.ends_at),
    )}`;
};

const pageRoot = ref<HTMLElement | null>(null);
let animationContext: gsap.Context | undefined;

onMounted(() => {
    if (!pageRoot.value) {
        return;
    }

    gsap.registerPlugin(ScrollTrigger);

    animationContext = gsap.context(() => {
        const prefersReducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        if (prefersReducedMotion) {
            return;
        }

        const heroTimeline = gsap.timeline({
            defaults: {
                ease: 'power3.out',
            },
        });

        heroTimeline.from('[data-hero-reveal]', {
            autoAlpha: 0,
            y: 32,
            duration: 0.8,
            stagger: 0.12,
        });

        heroTimeline.from(
            '[data-hero-image]',
            {
                autoAlpha: 0,
                x: 48,
                scale: 0.96,
                duration: 1,
            },
            '-=0.65',
        );

        const revealItems =
            gsap.utils.toArray<HTMLElement>('[data-reveal-item]');

        gsap.set(revealItems, {
            autoAlpha: 0,
            y: 40,
        });

        ScrollTrigger.batch(revealItems, {
            start: 'top 88%',
            once: true,
            interval: 0.1,
            batchMax: 4,

            onEnter: (batch) => {
                gsap.to(batch, {
                    autoAlpha: 1,
                    y: 0,
                    duration: 0.75,
                    stagger: 0.1,
                    ease: 'power3.out',
                    overwrite: 'auto',
                    clearProps: 'transform,opacity,visibility',
                });
            },
        });

        ScrollTrigger.refresh();
    }, pageRoot.value);
});

onUnmounted(() => {
    animationContext?.revert();
    animationContext = undefined;
});
</script>

<template>
    <Head :title="t('about.pageTitle')" />

    <ClientLayout>
        <div ref="pageRoot">
            <section class="relative isolate overflow-hidden">
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute top-1/4 -left-40 -z-10 h-96 w-96 rounded-full bg-primary/10 blur-3xl"
                />

                <div
                    class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl items-center gap-12 px-6 py-16 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20 lg:px-8 lg:py-24"
                >
                    <div class="relative z-10">
                        <p
                            data-hero-reveal
                            class="text-sm font-medium tracking-[0.22em] text-primary uppercase"
                        >
                            [ {{ t('about.hero.eyebrow') }} ]
                        </p>

                        <h1
                            data-hero-reveal
                            class="mt-5 max-w-3xl text-4xl leading-tight font-semibold tracking-tight text-foreground uppercase sm:text-5xl lg:text-6xl"
                        >
                            {{ t('about.hero.title') }}
                        </h1>

                        <p
                            data-hero-reveal
                            class="mt-6 max-w-2xl text-base leading-8 text-muted-foreground sm:text-lg"
                        >
                            {{
                                t('about.hero.description', {
                                    city: restaurant.address.city,
                                })
                            }}
                        </p>

                        <div
                            data-hero-reveal
                            class="mt-9 flex flex-col gap-4 sm:flex-row"
                        >
                            <a
                                :href="menu.dish().url"
                                class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                {{ t('about.hero.menuAction') }}
                                <span aria-hidden="true" class="ml-2">→</span>
                            </a>

                            <a
                                href="#opening-hours"
                                class="inline-flex items-center justify-center rounded-md border border-border bg-background px-6 py-3 text-sm font-semibold text-foreground shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-primary/40 hover:text-primary hover:shadow-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                {{ t('about.hero.scheduleAction') }}
                            </a>
                        </div>
                    </div>

                    <div
                        data-hero-image
                        class="relative mx-auto w-full max-w-2xl lg:mx-0"
                    >
                        <div
                            aria-hidden="true"
                            class="absolute -top-5 -right-5 h-full w-full rounded-md border border-primary/25"
                        />

                        <div
                            class="relative aspect-[4/5] overflow-hidden rounded-md bg-muted shadow-2xl sm:aspect-[5/4] lg:aspect-[4/5]"
                        >
                            <img
                                src="/images/home/slider/dish-1.webp"
                                :alt="t('about.hero.imageAlt')"
                                fetchpriority="high"
                                class="h-full w-full object-cover"
                            />

                            <div
                                aria-hidden="true"
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section
                data-reveal-section
                class="border-y border-border bg-muted/20 px-6 py-20 lg:px-8 lg:py-28"
            >
                <div
                    class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2 lg:gap-20"
                >
                    <div
                        data-reveal-item
                        class="relative isolate mx-auto w-full max-w-xl lg:mx-0"
                    >
                        <div
                            class="aspect-[4/3] overflow-hidden rounded-md bg-muted shadow-xl"
                        >
                            <img
                                src="/images/home/slider/dish-4.webp"
                                :alt="t('about.story.imageAlt')"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <div
                            aria-hidden="true"
                            class="absolute -right-4 -bottom-4 -z-10 h-full w-full rounded-md bg-primary/10"
                        />
                    </div>

                    <div data-reveal-item>
                        <p
                            class="text-sm font-medium tracking-[0.22em] text-primary uppercase"
                        >
                            [ {{ t('about.story.eyebrow') }} ]
                        </p>

                        <h2
                            class="mt-4 text-3xl leading-tight font-semibold tracking-tight text-foreground uppercase sm:text-4xl lg:text-5xl"
                        >
                            {{
                                t('about.story.title', {
                                    city: restaurant.address.city,
                                })
                            }}
                        </h2>

                        <div
                            class="mt-7 space-y-5 text-base leading-8 text-muted-foreground"
                        >
                            <p>
                                {{ t('about.story.firstParagraph') }}
                            </p>

                            <p>
                                {{ t('about.story.secondParagraph') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section data-reveal-section class="px-6 py-20 lg:px-8 lg:py-28">
                <div class="mx-auto max-w-7xl">
                    <div data-reveal-item class="max-w-3xl">
                        <p
                            class="text-sm font-medium tracking-[0.22em] text-primary uppercase"
                        >
                            [ {{ t('about.cuisine.eyebrow') }} ]
                        </p>

                        <h2
                            class="mt-4 text-3xl leading-tight font-semibold tracking-tight text-foreground uppercase sm:text-4xl lg:text-5xl"
                        >
                            {{ t('about.cuisine.title') }}
                        </h2>

                        <p
                            class="mt-6 text-base leading-8 text-muted-foreground sm:text-lg"
                        >
                            {{ t('about.cuisine.description') }}
                        </p>
                    </div>

                    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <article
                            v-for="(principle, index) in cuisinePrinciples"
                            :key="principle"
                            data-reveal-item
                            class="group rounded-md border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-lg"
                        >
                            <p
                                class="text-sm font-semibold tracking-[0.2em] text-primary"
                                aria-hidden="true"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </p>

                            <h3
                                class="mt-8 text-xl font-semibold text-card-foreground"
                            >
                                {{
                                    t(
                                        `about.cuisine.principles.${principle}.title`,
                                    )
                                }}
                            </h3>

                            <p
                                class="mt-3 text-sm leading-7 text-muted-foreground"
                            >
                                {{
                                    t(
                                        `about.cuisine.principles.${principle}.description`,
                                    )
                                }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section
                data-reveal-section
                class="bg-primary px-6 py-20 text-primary-foreground lg:px-8 lg:py-24"
            >
                <div class="mx-auto max-w-7xl">
                    <div data-reveal-item class="mx-auto max-w-3xl text-center">
                        <p
                            class="text-sm font-medium tracking-[0.22em] uppercase opacity-75"
                        >
                            [ {{ t('about.values.eyebrow') }} ]
                        </p>

                        <h2
                            class="mt-4 text-3xl leading-tight font-semibold tracking-tight uppercase sm:text-4xl lg:text-5xl"
                        >
                            {{ t('about.values.title') }}
                        </h2>

                        <p
                            class="mt-6 text-base leading-8 opacity-80 sm:text-lg"
                        >
                            {{ t('about.values.description') }}
                        </p>
                    </div>

                    <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <article
                            v-for="(value, index) in restaurantValues"
                            :key="value"
                            data-reveal-item
                            class="rounded-md border border-primary-foreground/20 bg-primary-foreground/5 p-6"
                        >
                            <div class="flex items-center gap-4">
                                <span
                                    aria-hidden="true"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-primary-foreground/30 text-xs font-semibold"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>

                                <h3 class="leading-6 font-semibold">
                                    {{ t(`about.values.items.${value}`) }}
                                </h3>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section
                id="opening-hours"
                data-reveal-section
                class="scroll-mt-20 px-6 py-20 lg:px-8 lg:py-28"
            >
                <div class="mx-auto max-w-5xl">
                    <div
                        data-reveal-item
                        class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
                    >
                        <div class="max-w-3xl">
                            <p
                                class="text-sm font-medium tracking-[0.22em] text-primary uppercase"
                            >
                                [ {{ t('about.schedule.eyebrow') }} ]
                            </p>

                            <h2
                                class="mt-4 text-3xl leading-tight font-semibold tracking-tight text-foreground uppercase sm:text-4xl lg:text-5xl"
                            >
                                {{ t('about.schedule.title') }}
                            </h2>

                            <p
                                class="mt-6 text-base leading-8 text-muted-foreground sm:text-lg"
                            >
                                {{ t('about.schedule.description') }}
                            </p>
                        </div>

                        <p
                            class="shrink-0 rounded-full border border-border bg-muted/50 px-4 py-2 text-xs font-medium text-muted-foreground"
                        >
                            {{ t('about.schedule.timezone', { timezone }) }}
                        </p>
                    </div>

                    <div class="mt-12 space-y-3">
                        <div
                            v-for="day in hours"
                            :key="day.id"
                            data-reveal-item
                            :aria-current="
                                day.weekday_key === currentWeekdayKey
                                    ? 'date'
                                    : undefined
                            "
                            :class="[
                                'grid gap-3 rounded-md border px-5 py-5 transition-colors sm:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] sm:items-center sm:px-6',
                                day.weekday_key === currentWeekdayKey
                                    ? 'border-primary bg-primary text-primary-foreground shadow-md'
                                    : 'border-border bg-card text-card-foreground shadow-sm',
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <p class="font-semibold">
                                    {{
                                        t(
                                            `about.schedule.weekdays.${day.weekday_key}`,
                                        )
                                    }}
                                </p>

                                <span
                                    v-if="day.weekday_key === currentWeekdayKey"
                                    class="rounded-full bg-primary-foreground/15 px-2.5 py-1 text-xs font-semibold"
                                >
                                    {{ t('about.schedule.today') }}
                                </span>
                            </div>

                            <div
                                v-if="day.is_open && day.periods.length"
                                class="flex flex-wrap gap-x-5 gap-y-2 sm:justify-end"
                            >
                                <span
                                    v-for="period in day.periods"
                                    :key="period.id"
                                    class="font-medium tabular-nums"
                                >
                                    {{ period.opens_at }}–{{ period.closes_at }}
                                </span>
                            </div>

                            <p
                                v-else
                                :class="[
                                    'font-medium sm:text-right',
                                    day.weekday_key === currentWeekdayKey
                                        ? 'text-primary-foreground/75'
                                        : 'text-muted-foreground',
                                ]"
                            >
                                {{ t('about.schedule.closed') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    data-reveal-item
                    class="mt-16 border-t border-border pt-12"
                >
                    <div class="max-w-3xl">
                        <h3
                            class="text-2xl font-semibold tracking-tight text-foreground uppercase sm:text-3xl"
                        >
                            {{ t('about.schedule.closures.title') }}
                        </h3>

                        <p class="mt-3 leading-7 text-muted-foreground">
                            {{ t('about.schedule.closures.description') }}
                        </p>
                    </div>

                    <div
                        v-if="closures.length"
                        class="mt-8 grid gap-4 md:grid-cols-2"
                    >
                        <article
                            v-for="closure in closures"
                            :key="closure.id"
                            data-reveal-item
                            class="rounded-md border border-border bg-card p-6 text-card-foreground shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <p class="leading-6 font-semibold">
                                    {{ formatClosureDate(closure) }}
                                </p>

                                <span
                                    class="w-fit shrink-0 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                                >
                                    {{ formatClosureTime(closure) }}
                                </span>
                            </div>

                            <p
                                class="mt-4 text-sm leading-7 text-muted-foreground"
                            >
                                {{
                                    closure.public_message ||
                                    t('about.schedule.closures.fallbackMessage')
                                }}
                            </p>
                        </article>
                    </div>

                    <div
                        v-else
                        data-reveal-item
                        class="mt-8 rounded-md border border-dashed border-border bg-muted/30 px-6 py-10 text-center"
                    >
                        <p class="text-sm text-muted-foreground">
                            {{ t('about.schedule.closures.empty') }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                data-reveal-section
                class="border-y border-border bg-muted/20 px-6 py-20 lg:px-8 lg:py-28"
            >
                <div class="mx-auto max-w-7xl">
                    <div data-reveal-item class="max-w-3xl">
                        <p
                            class="text-sm font-medium tracking-[0.22em] text-primary uppercase"
                        >
                            [ {{ t('about.location.eyebrow') }} ]
                        </p>

                        <h2
                            class="mt-4 text-3xl leading-tight font-semibold tracking-tight text-foreground uppercase sm:text-4xl lg:text-5xl"
                        >
                            {{ t('about.location.title') }}
                        </h2>

                        <p
                            class="mt-6 text-base leading-8 text-muted-foreground sm:text-lg"
                        >
                            {{
                                t('about.location.description', {
                                    city: restaurant.address.city,
                                })
                            }}
                        </p>
                    </div>

                    <div class="mt-12 grid gap-5 lg:grid-cols-3">
                        <article
                            data-reveal-item
                            class="rounded-md border border-border bg-card p-7 text-card-foreground shadow-sm"
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.2em] text-primary uppercase"
                            >
                                {{ t('about.location.addressLabel') }}
                            </p>

                            <address class="mt-5 not-italic">
                                <p class="text-lg font-semibold">
                                    {{ restaurant.name }}
                                </p>

                                <p class="mt-3 leading-7 text-muted-foreground">
                                    {{ restaurant.address.street }}<br />
                                    {{ restaurant.address.postalCode }}
                                    {{ restaurant.address.city }}<br />
                                    {{ restaurant.address.country }}
                                </p>
                            </address>

                            <a
                                :href="directionsUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-6 inline-flex items-center font-semibold text-primary transition-colors hover:text-primary/75"
                            >
                                {{ t('about.location.directionsAction') }}
                                <span aria-hidden="true" class="ml-2">↗</span>
                            </a>
                        </article>

                        <article
                            data-reveal-item
                            class="rounded-md border border-border bg-card p-7 text-card-foreground shadow-sm"
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.2em] text-primary uppercase"
                            >
                                {{ t('about.location.contactLabel') }}
                            </p>

                            <div class="mt-5 space-y-4">
                                <a
                                    :href="`tel:${restaurant.contactPhone}`"
                                    class="block font-semibold transition-colors hover:text-primary"
                                >
                                    {{ restaurant.contactPhone }}
                                </a>

                                <a
                                    :href="`mailto:${restaurant.contactEmail}`"
                                    class="block break-all text-muted-foreground transition-colors hover:text-primary"
                                >
                                    {{ restaurant.contactEmail }}
                                </a>
                            </div>
                        </article>

                        <article
                            data-reveal-item
                            class="rounded-md border border-border bg-card p-7 text-card-foreground shadow-sm"
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.2em] text-primary uppercase"
                            >
                                {{ t('about.location.serviceLabel') }}
                            </p>

                            <ul class="mt-5 space-y-3">
                                <li
                                    v-for="service in restaurantServices"
                                    :key="service"
                                    class="flex items-start gap-3 text-sm leading-6 text-muted-foreground"
                                >
                                    <span
                                        aria-hidden="true"
                                        class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"
                                    />

                                    {{
                                        t(`about.location.services.${service}`)
                                    }}
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section data-reveal-section class="px-6 py-20 lg:px-8 lg:py-28">
                <div
                    data-reveal-item
                    class="relative mx-auto max-w-6xl overflow-hidden rounded-md bg-primary px-6 py-16 text-center text-primary-foreground shadow-xl sm:px-12 lg:py-20"
                >
                    <div
                        aria-hidden="true"
                        class="absolute -top-24 -left-24 h-64 w-64 rounded-full bg-primary-foreground/10 blur-3xl"
                    />

                    <div
                        aria-hidden="true"
                        class="absolute -right-24 -bottom-24 h-64 w-64 rounded-full bg-primary-foreground/10 blur-3xl"
                    />

                    <div class="relative mx-auto max-w-3xl">
                        <p
                            class="text-sm font-medium tracking-[0.22em] uppercase opacity-75"
                        >
                            [ {{ t('about.callToAction.eyebrow') }} ]
                        </p>

                        <h2
                            class="mt-4 text-3xl leading-tight font-semibold tracking-tight uppercase sm:text-4xl lg:text-5xl"
                        >
                            {{ t('about.callToAction.title') }}
                        </h2>

                        <p class="mx-auto mt-6 max-w-2xl leading-8 opacity-80">
                            {{ t('about.callToAction.description') }}
                        </p>

                        <div
                            class="mt-9 flex flex-col justify-center gap-4 sm:flex-row"
                        >
                            <a
                                :href="menu.dish().url"
                                class="inline-flex items-center justify-center rounded-md bg-primary-foreground px-6 py-3 text-sm font-semibold text-primary shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:opacity-90 hover:shadow-md"
                            >
                                {{ t('about.callToAction.menuAction') }}
                                <span aria-hidden="true" class="ml-2">→</span>
                            </a>

                            <a
                                :href="`mailto:${restaurant.contactEmail}`"
                                class="inline-flex items-center justify-center rounded-md border border-primary-foreground/30 px-6 py-3 text-sm font-semibold transition-all duration-300 hover:-translate-y-0.5 hover:bg-primary-foreground/10"
                            >
                                {{ t('about.callToAction.contactAction') }}
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </ClientLayout>
</template>
