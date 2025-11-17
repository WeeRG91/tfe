<script setup lang="ts">
import { DrinkType } from '@/types/drink';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { SquarePenIcon, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import drink from '@/routes/drink';

const props = defineProps<{
    open: boolean;
    onClose?: () => void;
    selectedDrink: DrinkType | null;
}>();

const currentIndex = ref(0);

const nextImage = () => {
    if (!props.selectedDrink?.images?.length) return;
    currentIndex.value =
        (currentIndex.value + 1) % props.selectedDrink.images.length;
};

const prevImage = () => {
    if (!props.selectedDrink?.images?.length) return;
    currentIndex.value =
        (currentIndex.value - 1 + props.selectedDrink.images.length) %
        props.selectedDrink.images.length;
};

watch(
    () => props.selectedDrink,
    () => {
        currentIndex.value = 0;
        startAutoSlide();
    },
);

let intervalId: number | null = null;

const startAutoSlide = () => {
    stopAutoSlide();
    if (
        props.selectedDrink?.images?.length &&
        props.selectedDrink?.images.length > 1
    ) {
        intervalId = window.setInterval(nextImage, 3000);
    }
};

const stopAutoSlide = () => {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
};

onMounted(() => {
    if (props.open) startAutoSlide();
});

onBeforeUnmount(() => {
    stopAutoSlide();
});
</script>

<template>
    <Dialog :open="props.open" @update:open="props.onClose">
        <DialogContent class="max-w-2xl">
            <div class="flex flex-col gap-4 md:flex-row">
                <!-- Image Slider -->
                <div
                    class="relative w-full overflow-hidden rounded-lg md:w-1/2"
                >
                    <div
                        v-if="props.selectedDrink?.images?.length"
                        class="relative"
                    >
                        <img
                            v-for="(img, index) in props.selectedDrink.images"
                            :key="index"
                            :src="img.path"
                            alt=""
                            class="h-64 w-full rounded-lg object-cover transition-opacity duration-500"
                            :class="{
                                'opacity-100': currentIndex === index,
                                'absolute top-0 opacity-0':
                                    currentIndex !== index,
                            }"
                        />
                        <button
                            v-if="props.selectedDrink?.images?.length > 1"
                            class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/70 p-1"
                            @click="prevImage"
                        >
                            ‹
                        </button>
                        <button
                            v-if="props.selectedDrink?.images?.length > 1"
                            class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/70 p-1"
                            @click="nextImage"
                        >
                            ›
                        </button>
                    </div>

                    <div v-else>
                        <img
                            :src="props.selectedDrink?.image"
                            alt=""
                            class="h-full w-full rounded-lg object-cover"
                        />
                    </div>
                </div>

                <!-- Dish Details -->
                <div class="flex w-full flex-col gap-2 md:w-1/2">
                    <DialogHeader>
                        <DialogTitle class="flex flex-col gap-3">
                            <span>{{ props.selectedDrink?.name }}</span>
                            <Badge :class="props.selectedDrink?.category.color">
                                {{ props.selectedDrink?.category.label }}
                            </Badge>
                        </DialogTitle>
                        <DialogDescription>
                            {{ props.selectedDrink?.description }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-lg font-semibold"
                            >{{ props.selectedDrink?.price }} €</span
                        >
                        <Badge
                            :class="
                                props.selectedDrink?.is_available === 'Available'
                                    ? 'bg-green-500 text-white'
                                    : 'bg-red-500 text-white'
                            "
                        >
                            {{ props.selectedDrink?.is_available }}
                        </Badge>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <Button variant="outline" @click="props.onClose"
                    ><X /> Close</Button
                >
                <Button
                    ><a
                        :href="drink.edit(props.selectedDrink?.id!).url"
                        class="flex gap-2"
                    >
                        <SquarePenIcon /> Edit
                    </a></Button
                >
            </div>
        </DialogContent>
    </Dialog>
</template>
