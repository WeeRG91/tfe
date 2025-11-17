<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import Select from '@/components/Select.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AdminLayout from '@/layouts/AdminLayout.vue';
import drink from '@/routes/drink';
import { BreadcrumbItem } from '@/types';
import { CategoryEnumType, EditDrinkType, ErrorType } from '@/types/drink';
import { Head, useForm } from '@inertiajs/vue3';
import { SquarePenIcon, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    drinkToEdit: EditDrinkType;
    categories: CategoryEnumType[];
    errors: ErrorType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Drinks',
        href: drink.index().url,
    },
    {
        title: 'Edit',
        href: drink.edit(props.drinkToEdit.id).url,
    },
];

const selectedCategory = ref<number | null>(props.drinkToEdit.category ?? null);
const name = ref<string>(props.drinkToEdit.name ?? '');
const description = ref<string>(props.drinkToEdit.description ?? '');
const price = ref<number | null>(props.drinkToEdit.price ?? null);
const imageFile = ref<File[]>([]);

const drinkForm = useForm({
    name: '',
    description: '',
    price: null as number | null,
    category: null as number | null,
    images: [] as File[],
});

const submit = () => {
    drinkForm.name = name.value;
    drinkForm.description = description.value;
    drinkForm.price = price.value;
    drinkForm.category = selectedCategory.value;
    drinkForm.images = imageFile.value;

    drinkForm.post(drink.update(props.drinkToEdit.id).url, {
        forceFormData: true,
        onSuccess: () => {
            drinkForm.reset();
            toast.success('Drink successfully edited.');
        },
    });
};
</script>

<template>
    <Head title="Create" />
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
                            Dish Information
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

                        <FormNumberInput
                            id="price"
                            label="Price (€)"
                            v-model="price"
                            :error="props.errors.price"
                        />

                        <Select
                            v-model="selectedCategory"
                            :options="props.categories"
                            label="Category"
                            :error="props.errors.category"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            Photos
                        </h2>

                        <ImageUploader
                            v-model="imageFile"
                            :existingImages="props.drinkToEdit.images"
                        />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary">
                        <a :href="drink.index().url" class="flex items-center">
                            <X /> Cancel
                        </a>
                    </Button>
                    <Button type="submit" :disabled="drinkForm.processing"
                        ><SquarePenIcon v-if="!drinkForm.processing" /><Spinner
                            v-else
                        />
                        Edit</Button
                    >
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
