<script setup lang="ts">
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import MultipleSelect from '@/components/MultipleSelect.vue';
import CreateIngredientModal from '@/components/admin/ingredient/CreateIngredientModal.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { BreadcrumbItem, ErrorType } from '@/types';
import { AllergenOptionType, EditAllergenType } from '@/types/allergen';
import { IngredientOptionType } from '@/types/ingredient';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader, SquarePenIcon, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import allergen from '@/routes/admin/allergen';

const props = defineProps<{
    allergenToEdit: EditAllergenType;
    ingredients: IngredientOptionType[];
    allergens: AllergenOptionType[];
    errors: ErrorType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Allergens',
        href: allergen.index().url,
    },
    {
        title: 'Edit',
        href: allergen.edit(props.allergenToEdit.id).url,
    },
];

const showCreateIngredientModal = ref<boolean>(false);
const selectedIngredients = ref<number[]>(
    props.allergenToEdit.ingredients?.map((i) => i.id) ?? [],
);
const name = ref<string>(props.allergenToEdit.name ?? '');
const description = ref<string>(props.allergenToEdit.description ?? '');
const images = ref<File[]>([]);
const ingredients = ref<IngredientOptionType[]>(props.ingredients);
const isLoading = ref<boolean>(false);

const allergenForm = useForm({
    name: '',
    description: '',
    ingredients: [] as number[],
    images: [] as File[],
});

const submit = () => {
    isLoading.value = true;

    allergenForm.name = name.value;
    allergenForm.description = description.value;
    allergenForm.ingredients = selectedIngredients.value;
    allergenForm.images = images.value;

    allergenForm.post(allergen.update(props.allergenToEdit.id).url, {
        forceFormData: true,
        errorBag: 'allergenErrors',
        onSuccess: () => {
            allergenForm.reset();
            toast.success('Allergen successfully edited.');
        },
        onError: (error) => {
            if (error.message) {
                toast.error(error.meessage);
            } else {
                toast.error('Something went wrong. Please check the form.');
            }
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const handleIngredientCreated = (ingredient: IngredientOptionType) => {
    ingredients.value = [...ingredients.value, ingredient];
    selectedIngredients.value = [
        ...selectedIngredients.value,
        ingredient.value,
    ];
    closeCreateIngredientModal();
};

const closeCreateIngredientModal = () => {
    showCreateIngredientModal.value = false;
};
</script>

<template>
    <Head :title="`Edit the allergen '${props.allergenToEdit.name}'`" />

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
                            :error="props.errors.allergenErrors?.name"
                        />

                        <FormTextarea
                            id="description"
                            label="Description"
                            v-model="description"
                            :rows="5"
                            :error="props.errors.allergenErrors?.description"
                        />

                        <MultipleSelect
                            v-model="selectedIngredients"
                            :options="ingredients"
                            label="Ingredients"
                            :error="props.errors.allergenErrors?.ingredients"
                            @create="showCreateIngredientModal = true"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            Photos
                        </h2>

                        <ImageUploader
                            v-model="images"
                            :existingImages="props.allergenToEdit.images"
                            :mainImage="props.allergenToEdit.main_image"
                        />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" class="w-24">
                        <a
                            :href="allergen.index().url"
                            class="flex items-center"
                        >
                            <X /> Cancel
                        </a>
                    </Button>
                    <Button
                        type="submit"
                        class="w-24 cursor-pointer"
                        :disabled="allergenForm.processing"
                    >
                        <span
                            v-if="isLoading"
                            class="flex items-center justify-center gap-2"
                        >
                            <Loader
                                class="animate-spin text-muted-foreground"
                            />
                        </span>
                        <span
                            v-else
                            class="flex items-center justify-center gap-2"
                        >
                            <SquarePenIcon /> Edit
                        </span>
                    </Button>
                </div>
            </form>

            <CreateIngredientModal
                :open="showCreateIngredientModal"
                :onClose="closeCreateIngredientModal"
                :allergens="props.allergens"
                :errors="props.errors.ingredientErrors"
                @create="handleIngredientCreated"
            />
        </div>
    </AdminLayout>
</template>
