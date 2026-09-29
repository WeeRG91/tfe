<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        size?: 'sm' | 'md' | 'lg' | 'xl';
        alwaysShowName?: boolean;
    }>(),
    {
        size: 'md',
        alwaysShowName: false,
    },
);

const logoSizeClass = computed(() => {
    return {
        sm: 'h-9 w-9',
        md: 'h-12 w-12',
        lg: 'h-14 w-14',
        xl: 'h-20 w-20',
    }[props.size];
});

const scrollToTop = () => {
    if (window.location.pathname === '/') {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    }
};
</script>

<template>
    <Link
        href="/"
        aria-label="Kin Dee home"
        class="group inline-flex items-center"
        @click="scrollToTop"
    >
        <img
            src="/images/branding/kin-dee-logo-emblem-thai-v2.png"
            alt=""
            :class="[logoSizeClass, 'shrink-0 rounded-full object-contain']"
            draggable="false"
        />
    </Link>
</template>
