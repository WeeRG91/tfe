<script setup lang="ts">
import image from '@/routes/admin/image';
import axios from 'axios';
import { ImagePlus, Star } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    modelValue: File[];
    existingImages?: { id: number; path: string }[];
    mainImage?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', images: File[]): void;
}>();

const { t } = useI18n();

const images = ref<File[]>(props.modelValue || []);
const existingMainImage = ref<string | null>(props.mainImage || null);
const existingImagesPreviewUrls = ref<string[]>(
    props.existingImages?.map((img) => img.path) || [],
);
const previewUrls = ref<string[]>([]);
const isDragging = ref<boolean>(false);
const error = ref<string>();

const MAX_IMAGE_SIZE = 5 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
const MAX_IMAGES = 5;
const MAX_WIDTH = 4000;
const MAX_HEIGHT = 4000;

const getImageDimensions = async (
    file: File,
): Promise<{ width: number; height: number }> => {
    const bitmap = await createImageBitmap(file);

    return {
        width: bitmap.width,
        height: bitmap.height,
    };
};

const validateImages = async (images: File[]) => {
    error.value = '';

    if (images.length > MAX_IMAGES) {
        error.value = t('admin.imageUploader.errors.maxImages', {
            max: MAX_IMAGES,
        });
        return false;
    }

    for (const image of images) {
        if (!ALLOWED_TYPES.includes(image.type)) {
            error.value = t('admin.imageUploader.errors.invalidFileType', {
                fileName: image.name,
            });
            return false;
        }

        if (image.size > MAX_IMAGE_SIZE) {
            error.value = t('admin.imageUploader.errors.maxFileSize', {
                fileName: image.name,
            });
            return false;
        }

        const { width, height } = await getImageDimensions(image);

        if (width > MAX_WIDTH || height > MAX_HEIGHT) {
            error.value = t('admin.imageUploader.errors.invalidDimensions', {
                fileName: image.name,
                maxWidth: MAX_WIDTH,
                maxHeight: MAX_HEIGHT,
                width,
                height,
            });

            return false;
        }
    }

    return true;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
    const droppedImages = Array.from(event.dataTransfer?.files || []);
    addImages(droppedImages);
};

const handleImageChange = (event: Event) => {
    event.preventDefault();
    const target = event.target as HTMLInputElement;
    const selectedImages = Array.from(target.files || []);
    addImages(selectedImages);
};

const addImages = (newFiles: File[]) => {
    error.value = '';
    validateImages(newFiles);

    if (error.value) {
        return;
    }

    const updatedImages = [...images.value, ...newFiles];
    images.value = updatedImages;
    emit('update:modelValue', updatedImages);
    updatePreviewImages();
};

const removeImage = (index: number) => {
    images.value.splice(index, 1);
    emit('update:modelValue', images.value);
    updatePreviewImages();
};

const removeExistingImage = async (index: number) => {
    const imageToRemove = props.existingImages?.[index];
    if (!imageToRemove) return;

    try {
        const response = await axios.delete(
            image.destroy(imageToRemove.id).url,
        );
        existingImagesPreviewUrls.value.splice(index, 1);
        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error(t('admin.imageUploader.errors.requestFailed'));
        }
    }
};

const setMainImage = async (index: number) => {
    const imageToSetMain = props.existingImages?.[index];
    if (!imageToSetMain) return;

    try {
        const response = await axios.post(
            image.setMainImage(imageToSetMain.id).url,
        );
        existingMainImage.value = imageToSetMain.path;
        toast.success(response.data.message);
    } catch (error) {
        if (error) {
            toast.error('Something went wrong. Please try again later.');
        }
    }
};

const updatePreviewImages = () => {
    previewUrls.value = images.value.map((image) => URL.createObjectURL(image));
};

watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) images.value = newValue;
    },
);
</script>

<template>
    <div
        @dragover.prevent="isDragging = true"
        @dragleave="isDragging = false"
        @drop="handleDrop"
        class="relative flex min-h-24 flex-1 flex-col items-center justify-start rounded-2xl border-2 border-dashed bg-background p-6 text-muted-foreground transition-all duration-200"
        :class="{
            'border-info bg-info/10': isDragging,
            'border-destructive bg-destructive/10': error && !isDragging,
            'border-border bg-muted/50': !isDragging && !error,
        }"
    >
        <input
            type="file"
            multiple
            accept="image/*"
            class="absolute inset-0 z-10 cursor-pointer opacity-0"
            @change="handleImageChange"
        />

        <div
            v-if="
                !isDragging &&
                !previewUrls.length &&
                !existingImagesPreviewUrls.length
            "
            class="absolute inset-0 flex flex-col items-center justify-center space-y-2 text-center"
        >
            <ImagePlus :class="error ? 'text-destructive' : ''" />
            <p class="text-sm" :class="error ? 'text-destructive' : ''">
                {{ t('admin.imageUploader.dragAndDrop') }}
            </p>
        </div>

        <div v-else class="grid w-full grid-cols-2 gap-3 md:grid-cols-3">
            <div
                v-for="(url, index) in existingImagesPreviewUrls"
                :key="index"
                class="group relative z-10"
            >
                <img
                    :src="url"
                    class="h-32 w-full rounded-xl object-cover shadow-md"
                    alt=""
                />
                <button
                    v-if="url !== existingMainImage"
                    type="button"
                    @click.stop="removeExistingImage(index)"
                    class="absolute top-2 right-2 z-20 cursor-pointer rounded-full bg-destructive px-2 py-1 text-xs text-destructive-foreground opacity-100 transition-opacity hover:bg-destructive/90 sm:opacity-0 sm:group-hover:opacity-100"
                >
                    ✕
                </button>
                <button
                    type="button"
                    @click.stop="setMainImage(index)"
                    :disabled="url === existingMainImage"
                    :class="url !== existingMainImage ? 'cursor-pointer' : ''"
                    class="absolute top-2 left-2 z-20 rounded-full bg-card/90 p-1 text-xs text-card-foreground opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100"
                >
                    <Star
                        class="h-5.5 w-5.5"
                        :class="
                            url === existingMainImage
                                ? 'fill-warning text-warning'
                                : ''
                        "
                    />
                </button>
            </div>
            <div
                v-for="(url, index) in previewUrls"
                :key="index"
                class="group relative z-10"
            >
                <img
                    :src="url"
                    class="h-32 w-full rounded-xl object-cover shadow-md"
                    alt=""
                />
                <button
                    type="button"
                    @click.stop="removeImage(index)"
                    class="absolute top-2 right-2 z-20 rounded-full bg-destructive px-2 py-1 text-xs text-destructive-foreground opacity-100 transition-opacity hover:bg-destructive/90 sm:opacity-0 sm:group-hover:opacity-100"
                >
                    ✕
                </button>
            </div>
        </div>

        <transition name="fade">
            <div
                v-if="isDragging"
                class="absolute inset-0 flex items-center justify-center rounded-2xl bg-info/20 text-lg font-semibold text-foreground"
            >
                {{ t('admin.imageUploader.dropImagesHere') }}
            </div>
        </transition>
    </div>
    <span v-if="error" class="text-sm text-destructive">{{ error }}</span>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
