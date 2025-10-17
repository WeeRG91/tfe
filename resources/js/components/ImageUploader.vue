<script setup lang="ts">
import { ref, watch } from 'vue';
import { ImagePlus } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: File[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', files: File[]): void;
}>();

const files = ref<File[]>(props.modelValue || []);
const previewUrls = ref<string[]>([]);
const isDragging = ref<boolean>(false);

watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) files.value = newValue;
    },
);

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
    const droppedFiles = Array.from(event.dataTransfer?.files || []);
    addFiles(droppedFiles);
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const selectedFiles = Array.from(target.files || []);
    addFiles(selectedFiles);
};

const addFiles = (newFiles: File[]) => {
    const updatedFiles = [...files.value, ...newFiles];
    files.value = updatedFiles;
    emit('update:modelValue', updatedFiles);
    updatePreviewImages();
};

const removeFile = (index: number) => {
    files.value.splice(index, 1);
    emit('update:modelValue', files.value);
    updatePreviewImages();
};

const updatePreviewImages = () => {
    previewUrls.value = files.value.map((file) => URL.createObjectURL(file));
};
</script>

<template>
    <div
        @dragover.prevent="isDragging = true"
        @dragleave="isDragging = false"
        @drop="handleDrop"
        class="relative flex flex-1 flex-col items-center bg-transparent justify-start rounded-2xl border-2 border-dashed p-6 text-gray-600 transition-all duration-200"
        :class="
            isDragging
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-300 bg-gray-50'
        "
    >
        <input
            type="file"
            multiple
            accept="image/*"
            class="absolute inset-0 cursor-pointer opacity-0 z-999"
            @change="handleFileChange"
        />

        <div
            v-if="!isDragging && !previewUrls.length"
            class="absolute inset-0 flex flex-col items-center justify-center space-y-2 text-center"
        >
            <ImagePlus />

            <p class="text-sm">Drag and drop images here, or click to browse</p>
        </div>

        <div v-else class="grid w-full grid-cols-2 gap-3 md:grid-cols-3">
            <div
                v-for="(url, index) in previewUrls"
                :key="index"
                class="group relative z-999"
            >
                <img
                    :src="url"
                    class="h-32 w-full rounded-xl object-cover shadow-md"
                    alt=""
                />
                <button
                    type="button"
                    @click.stop="removeFile(index)"
                    class="absolute top-2 right-2 rounded-full bg-red-500/50 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 z-999"
                >
                    ✕
                </button>
            </div>
        </div>

        <transition name="fade">
            <div
                v-if="isDragging"
                class="absolute inset-0 flex items-center justify-center rounded-2xl bg-blue-100/70 text-lg font-semibold text-blue-700"
            >
                Drop your images here
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
