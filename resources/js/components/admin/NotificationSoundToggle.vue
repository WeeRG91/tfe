<script setup lang="ts">
import { useNotificationSoundStore } from '@/stores/notificationSound';
import { Volume2, VolumeX } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';

const soundStore = useNotificationSoundStore();
const { enabled, ready } = storeToRefs(soundStore);

const handleClick = async () => {
    if (enabled.value && ready.value) {
        soundStore.disable();

        return;
    }

    await soundStore.activate();
};
</script>

<template>
    <button
        type="button"
        :aria-pressed="enabled && ready"
        @click="handleClick"
        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-border bg-card p-2 text-sm font-medium text-card-foreground transition-colors hover:bg-accent hover:text-accent-foreground sm:w-auto"
    >
        <Volume2 class="size-5" v-if="enabled && ready" />
        <VolumeX class="size-5" v-else />
    </button>
</template>

<style scoped></style>
