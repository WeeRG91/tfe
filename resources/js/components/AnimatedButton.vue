<script setup lang="ts">
import { computed } from 'vue';

type Props = {
    as?: string;
    href?: string;
    text: string;
    bgColor?: string;
    beforeColor?: string;
    textColor?: string;
    hoverTextColor?: string;
};

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
    href: '',
    text: '',
    bgColor: 'transparent',
    beforeColor: '#fb2c36',
    textColor: '#fff',
    hoverTextColor: '#000',
});

const styleVars = computed(() => ({
    '--btn-bg': props.bgColor,
    '--btn-before': props.beforeColor,
    '--btn-text': props.textColor,
    '--btn-text-hover': props.hoverTextColor,
}));

const componentTag = computed(() => {
    if (props.as === 'a') return 'a';
    return 'button';
});

const componentProps = computed(() => {
    if (props.as === 'a') return { href: props.href };
    return { type: 'button' };
});
</script>

<template>
    <component
        :is="componentTag"
        v-bind="componentProps"
        :style="styleVars"
        class="animated-btn group relative inline-flex cursor-pointer items-center gap-2.5 overflow-hidden rounded-md border border-b-gray-200 bg-[var(--btn-bg)] px-5 py-2.5 text-sm no-underline"
    >
        <span
            class="relative z-10 text-[var(--btn-text)] transition-colors duration-300 group-hover:text-[var(--btn-text-hover)]"
        >
            {{ props.text }}
        </span>

        <span
            class="arrow relative z-10 text-[var(--btn-text)] transition-colors duration-300"
        >
            →
        </span>
    </component>
</template>

<style scoped>
.animated-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--btn-before);
    clip-path: circle(150% at calc(100% - 20px) 50%);
    transition: clip-path 0.5s ease;
    z-index: 0;
}

.animated-btn:hover::before {
    clip-path: circle(12px at calc(100% - 20px) 50%);
}

.animated-btn:hover .arrow {
    animation: arrow-move 1.2s ease-in-out infinite;
}
</style>
