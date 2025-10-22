<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import MultipleSelect from '@/components/MultipleSelect.vue';
import Select from '@/components/Select.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import dish from '@/routes/dish';
import type { BreadcrumbItem } from '@/types';
import { CategoryType, IngredientType } from '@/types/dish';
import { Head, useForm } from '@inertiajs/vue3';
import { CirclePlus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    ingredients: IngredientType[];
    categories: CategoryType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dishes',
        href: dish.index().url,
    },
    {
        title: 'Create',
        href: dish.create().url,
    },
];

const selectedIngredients = ref<number[]>([]);
const selectedCategory = ref<number | null>(null);
const name = ref('');
const description = ref('');
const price = ref(null);
const imageFile = ref<File[]>([]);

const dishForm = useForm({
    name: '',
    description: '',
    price: null as number | null,
    category: null as number | null | undefined,
    ingredients: [] as number[],
    images: [] as File[],
});

const submit = () => {
    dishForm.name = name.value;
    dishForm.description = description.value;
    dishForm.price = price.value;
    dishForm.category = selectedCategory.value;
    dishForm.ingredients = selectedIngredients.value;
    dishForm.images = imageFile.value;

    dishForm.post(dish.store().url, {
        forceFormData: true,
        onSuccess: () => {
            dishForm.reset();
            toast.success('Dish successfully created.');
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
                            Dish Information
                        </h2>

                        <FormTextInput id="name" label="Name" v-model="name" />

                        <FormTextarea
                            id="description"
                            label="Description"
                            v-model="description"
                            :rows="5"
                        />

                        <FormNumberInput
                            id="price"
                            label="Price (€)"
                            v-model="price"
                        />

                        <MultipleSelect
                            v-model="selectedIngredients"
                            :options="props.ingredients"
                            label="Ingredients"
                        />

                        <Select
                            v-model="selectedCategory"
                            :options="props.categories"
                            label="Category"
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
                <div class="flex items-center justify-end gap-4 mt-4">
                    <Button type="button" variant="secondary">
                        <a :href="dish.index().url" class="flex items-center">
                            <X /> Cancel
                        </a>
                    </Button>
                    <Button type="submit" :disabled="dishForm.processing"
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
