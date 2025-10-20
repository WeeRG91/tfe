<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import dish from '@/routes/dish';
import { DishType } from '@/types/dish';
import { SquarePenIcon, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    onClose?: () => void;
    selectedDish: DishType | null;
}>();

const currentIndex = ref(0);

const nextImage = () => {
    if (!props.selectedDish?.images?.length) return;
    currentIndex.value =
        (currentIndex.value + 1) % props.selectedDish.images.length;
};

const prevImage = () => {
    if (!props.selectedDish?.images?.length) return;
    currentIndex.value =
        (currentIndex.value - 1 + props.selectedDish.images.length) %
        props.selectedDish.images.length;
};

watch(
    () => props.selectedDish,
    () => (currentIndex.value = 0),
);
</script>

<template>
    <Dialog :open="open" @update:open="props.onClose">
        <DialogContent class="max-w-2xl">
            <div class="flex flex-col gap-4 md:flex-row">
                <!-- Image Slider -->
                <div
                    class="relative w-full overflow-hidden rounded-lg md:w-1/2"
                >
                    <div
                        v-if="props.selectedDish?.images?.length"
                        class="relative"
                    >
                        <img
                            v-for="(img, index) in props.selectedDish.images"
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
                            class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/70 p-1"
                            @click="prevImage"
                        >
                            ‹
                        </button>
                        <button
                            class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/70 p-1"
                            @click="nextImage"
                        >
                            ›
                        </button>
                    </div>

                    <div v-else>
                        <img
                            :src="props.selectedDish?.image"
                            alt=""
                            class="h-full w-full rounded-lg object-cover"
                        />
                    </div>
                </div>

                <!-- Dish Details -->
                <div class="flex w-full flex-col gap-2 md:w-1/2">
                    <DialogHeader>
                        <DialogTitle class="flex flex-col gap-3">
                            <span>{{ props.selectedDish?.name }}</span>
                            <Badge :class="props.selectedDish?.category.color">
                                {{ props.selectedDish?.category.label }}
                            </Badge>
                        </DialogTitle>
                        <DialogDescription>
                            {{ props.selectedDish?.description }}
                        </DialogDescription>
                    </DialogHeader>
                    <div>
                        <span class="font-semibold">Ingredients:</span>
                        <ul class="list-inside list-disc">
                            <li
                                v-for="(ingredient, index) in props.selectedDish
                                    ?.ingredients"
                                :key="index"
                            >
                                {{ ingredient.name }}
                            </li>
                        </ul>
                    </div>

                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-lg font-semibold"
                            >{{ props.selectedDish?.price }} €</span
                        >
                        <Badge
                            :class="
                                props.selectedDish?.is_available === 'Available'
                                    ? 'bg-green-500 text-white'
                                    : 'bg-red-500 text-white'
                            "
                        >
                            {{ props.selectedDish?.is_available }}
                        </Badge>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <Button variant="outline" @click="props.onClose"
                            ><X /> Close</Button
                        >
                        <Button
                            ><a
                                :href="dish.edit(props.selectedDish?.id!).url"
                                class="flex gap-2"
                            >
                                <SquarePenIcon /> Edit
                            </a></Button
                        >
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
