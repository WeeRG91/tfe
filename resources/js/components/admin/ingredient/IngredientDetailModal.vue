<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import ingredient from '@/routes/ingredient';
import { IngredientType } from '@/types/ingredient';
import { SquarePenIcon, X } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    onClose?: () => void;
    selectedIngredient: IngredientType | null;
}>();

const currentIndex = ref(0);

const nextImage = () => {
    if (!props.selectedIngredient?.images?.length) return;
    currentIndex.value =
        (currentIndex.value + 1) % props.selectedIngredient.images.length;
};

const prevImage = () => {
    if (!props.selectedIngredient?.images?.length) return;
    currentIndex.value =
        (currentIndex.value - 1 + props.selectedIngredient.images.length) %
        props.selectedIngredient.images.length;
};

watch(
    () => props.selectedIngredient,
    () => {
        currentIndex.value = 0;
        startAutoSlide();
    },
);

let intervalId: number | null = null;

const startAutoSlide = () => {
    stopAutoSlide();
    if (
        props.selectedIngredient?.images?.length &&
        props.selectedIngredient.images.length > 1
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
                        v-if="props.selectedIngredient?.images?.length"
                        class="relative"
                    >
                        <img
                            v-for="(img, index) in props.selectedIngredient
                                .images"
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
                            v-if="props.selectedIngredient?.images?.length > 1"
                            class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/70 p-1"
                            @click="prevImage"
                        >
                            ‹
                        </button>
                        <button
                            v-if="props.selectedIngredient?.images?.length > 1"
                            class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/70 p-1"
                            @click="nextImage"
                        >
                            ›
                        </button>
                    </div>

                    <div v-else>
                        <img
                            :src="props.selectedIngredient?.image"
                            alt=""
                            class="h-full w-full rounded-lg object-cover"
                        />
                    </div>
                </div>

                <!-- Dish Details -->
                <div class="flex w-full flex-col gap-2 md:w-1/2">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2">
                            <span>{{ props.selectedIngredient?.name }}</span>
                            <div class="relative flex items-center">
                                <div class="group relative">
                                    <img
                                        v-if="
                                            props.selectedIngredient?.allergen
                                                ?.image
                                        "
                                        :src="
                                            props.selectedIngredient?.allergen
                                                ?.image
                                        "
                                        :alt="
                                            props.selectedIngredient?.allergen
                                                ?.name
                                        "
                                        class="h-6 w-6 cursor-pointer object-cover"
                                    />

                                    <div
                                        v-if="
                                            props.selectedIngredient?.allergen
                                                ?.name
                                        "
                                        class="pointer-events-none absolute top-full left-1/2 z-50 mb-1 -translate-x-1/2 translate-y-1 rounded bg-gray-800 px-2 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow-lg transition-opacity duration-300 group-hover:opacity-100"
                                    >
                                        {{
                                            props.selectedIngredient?.allergen
                                                ?.name
                                        }}
                                    </div>
                                </div>
                            </div>
                        </DialogTitle>
                        <DialogDescription>
                            {{ props.selectedIngredient?.description }}
                        </DialogDescription>
                    </DialogHeader>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <Button variant="outline" @click="props.onClose"
                    ><X /> Close</Button
                >
                <Button
                    ><a
                        :href="
                            ingredient.edit(props.selectedIngredient?.id!).url
                        "
                        class="flex gap-2"
                    >
                        <SquarePenIcon /> Edit
                    </a></Button
                >
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
