<script setup lang="ts">
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import Select from '@/components/Select.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CirclePlus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import ingredient from '@/routes/ingredient';
import {
    AllergenOptionType,
    EditIngredientType,
    ErrorType,
} from '@/types/ingredient';

const props = defineProps<{
    ingredientToEdit: EditIngredientType;
    allergens: AllergenOptionType[];
    errors: ErrorType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ingredients',
        href: ingredient.index().url,
    },
    {
        title: 'Create',
        href: ingredient.create().url,
    },
];

const selectedAllergen = ref<number | null>(props.ingredientToEdit.allergen ?? null);
const name = ref<string>(props.ingredientToEdit.name ?? '');
const description = ref<string>(props.ingredientToEdit.description ?? '');
const imageFile = ref<File[]>([]);

const ingredientForm = useForm({
    name: '',
    description: '',
    allergen: null as number | null,
    images: [] as File[],
});

const submit = () => {
    ingredientForm.name = name.value;
    ingredientForm.description = description.value;
    ingredientForm.allergen = selectedAllergen.value;
    ingredientForm.images = imageFile.value;

    ingredientForm.post(ingredient.update(props.ingredientToEdit.id).url, {
        forceFormData: true,
        onSuccess: () => {
            ingredientForm.reset();
            toast.success('Ingredient successfully updated.');
        },
    });
};
</script>

<template>
    <Head title="Create a dish" />

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
                            Ingredient Information
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

                        <Select
                            v-model="selectedAllergen"
                            :options="props.allergens"
                            label="Category"
                            :error="props.errors.allergen"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            Photos
                        </h2>

                        <ImageUploader v-model="imageFile" :existingImages="props.ingredientToEdit.images" />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary">
                        <a
                            :href="ingredient.index().url"
                            class="flex items-center"
                        >
                            <X /> Cancel
                        </a>
                    </Button>
                    <Button type="submit" :disabled="ingredientForm.processing"
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
