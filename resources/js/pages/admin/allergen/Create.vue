<script setup lang="ts">
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CirclePlus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { ErrorType, IngredientOptionType } from '@/types/allergen';
import allergen from '@/routes/allergen';
import MultipleSelect from '@/components/MultipleSelect.vue';


const props = defineProps<{
    ingredients: IngredientOptionType[];
    errors: ErrorType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Allergens',
        href: allergen.index().url,
    },
    {
        title: 'Create',
        href: allergen.create().url,
    },
];

const selectedIngredients = ref<number[]>([]);
const name = ref<string>('');
const description = ref<string>('');
const imageFile = ref<File[]>([]);

const allergenForm = useForm({
    name: '',
    description: '',
    ingredients: [] as number[],
    images: [] as File[],
});

const submit = () => {
    allergenForm.name = name.value;
    allergenForm.description = description.value;
    allergenForm.ingredients = selectedIngredients.value;
    allergenForm.images = imageFile.value;

    allergenForm.post(allergen.store().url, {
        forceFormData: true,
        onSuccess: () => {
            allergenForm.reset();
            toast.success('Allergen successfully created.');
        },
    });
};
</script>

<template>
    <Head title="Create an allergen" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <form @submit.prevent="submit">
                <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            Allergen Information
                        </h2>

                        <FormTextInput
                            id="name"
                            label="Name"
                            v-model="name"
                            :error="props.errors.name"
                        />

                        <FormTextarea
                            id="description"
                            label="Description"
                            v-model="description"
                            :rows="5"
                            :error="props.errors.description"
                        />

                        <MultipleSelect
                            v-model="selectedIngredients"
                            :options="props.ingredients"
                            label="Ingredients"
                            :error="props.errors.ingredients"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            Photos
                        </h2>

                        <ImageUploader v-model="imageFile" />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary">
                        <a
                            :href="allergen.index().url"
                            class="flex items-center"
                        >
                            <X /> Cancel
                        </a>
                    </Button>
                    <Button type="submit" :disabled="allergenForm.processing"
                        ><CirclePlus /> Create</Button
                    >
                </div>
            </form>
        </div>
    </AdminLayout>
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
