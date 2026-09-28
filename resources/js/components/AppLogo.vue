<script setup lang="ts">
import type { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<AppPageProps>();

const restaurantName = computed(() => page.props.restaurant.name);
</script>

<template>
    <img
        src="/images/branding/kin-dee-logo-emblem-thai-v2.png"
        alt=""
        aria-hidden="true"
        class="size-8 shrink-0 rounded-full object-contain"
        draggable="false"
    />

    <div class="ml-1 grid flex-1 text-left leading-tight">
        <span class="truncate text-sm font-semibold">
            {{ restaurantName }}
        </span>

        <span class="truncate text-xs text-muted-foreground">
            Administration
        </span>
    </div>
</template>
