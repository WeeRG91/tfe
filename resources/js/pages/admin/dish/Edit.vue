<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import MultipleSelect from '@/components/MultipleSelect.vue';
import Select from '@/components/Select.vue';
import CreateIngredientModal from '@/components/admin/ingredient/CreateIngredientModal.vue';
import CreateMeatModal from '@/components/admin/meat/CreateMeatModal.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import dish from '@/routes/dish';
import type { BreadcrumbItem, ErrorType } from '@/types';
import { AllergenOptionType } from '@/types/allergen';
import { CategoryOptionType } from '@/types/category';
import { EditDishType } from '@/types/dish';
import { IngredientOptionType } from '@/types/ingredient';
import { MeatOptionType } from '@/types/meat';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader, SquarePenIcon, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    dishToEdit: EditDishType;
    meats: MeatOptionType[];
    ingredients: IngredientOptionType[];
    categories: CategoryOptionType[];
    allergens: AllergenOptionType[];
    errors: ErrorType;
}>();

console.log(props.dishToEdit);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dishes',
        href: dish.index().url,
    },
    {
        title: 'Edit',
        href: dish.edit(props.dishToEdit.id).url,
    },
];

const showCreateMeatModal = ref<boolean>(false);
const showCreateIngredientModal = ref<boolean>(false);
const selectedMeats = ref<number[]>(props.dishToEdit.meats?.map((m) => m.id));
const selectedIngredients = ref<number[]>(
    props.dishToEdit.ingredients?.map((i) => i.id),
);
const selectedCategory = ref<number | null>(props.dishToEdit.category ?? null);
const name = ref(props.dishToEdit.name ?? '');
const description = ref(props.dishToEdit.description ?? '');
const price = ref(props.dishToEdit.price ?? 0);
const images = ref<File[]>([]);
const ingredients = ref<IngredientOptionType[]>(props.ingredients);
const meats = ref<MeatOptionType[]>(props.meats);
const isLoading = ref<boolean>(false);

const dishForm = useForm({
    name: '',
    description: '',
    price: null as number | null,
    category: null as number | null | undefined,
    meats: [] as number[],
    ingredients: [] as number[],
    images: [] as File[],
});

const submit = () => {
    isLoading.value = true;

    dishForm.name = name.value;
    dishForm.description = description.value;
    dishForm.price = price.value;
    dishForm.category = selectedCategory.value;
    dishForm.meats = selectedMeats.value;
    dishForm.ingredients = selectedIngredients.value;
    dishForm.images = images.value;

    dishForm.post(dish.update(props.dishToEdit.id).url, {
        forceFormData: true,
        errorBag: 'dishErrors',
        onSuccess: () => {
            dishForm.reset();
            toast.success('Dish successfully edited.');
        },
        onError: (error) => {
            if (error.message) toast.error(error.meessage);

            toast.error('Something went wrong. Please check the form.');
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

const handleMeatCreated = (meat: MeatOptionType) => {
    meats.value = [...meats.value, meat];
    selectedMeats.value = [...selectedMeats.value, meat.value];
    closeCreateMeatModal();
};

const closeCreateMeatModal = () => {
    showCreateMeatModal.value = false;
};
</script>

<template>
    <Head :title="`Edit the dish '${props.dishToEdit.name}'`" />

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
                            :error="props.errors?.dishErrors?.name"
                        />

                        <FormTextarea
                            id="description"
                            label="Description"
                            v-model="description"
                            :rows="5"
                            :error="props.errors?.dishErrors?.description"
                        />

                        <FormNumberInput
                            id="price"
                            label="Price (€)"
                            v-model="price"
                            :error="props.errors?.dishErrors?.price"
                        />

                        <MultipleSelect
                            v-model="selectedMeats"
                            :options="props.meats"
                            label="Meat Options"
                            :error="props.errors?.dishErrors?.meats"
                            @create="showCreateMeatModal = true"
                        />

                        <MultipleSelect
                            v-model="selectedIngredients"
                            :options="props.ingredients"
                            label="Ingredients"
                            :error="props.errors?.dishErrors?.ingredients"
                            @create="showCreateIngredientModal = true"
                        />

                        <Select
                            v-model="selectedCategory"
                            :options="props.categories"
                            label="Category"
                            :error="props.errors?.dishErrors?.category"
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
                            :existingImages="props.dishToEdit.images"
                            :mainImage="props.dishToEdit.main_image"
                        />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" class="w-24">
                        <a
                            :href="dish.index().url"
                            class="flex items-center justify-center gap-2"
                        >
                            <X /> Cancel
                        </a>
                    </Button>
                    <Button
                        type="submit"
                        class="w-24 cursor-pointer"
                        :disabled="dishForm.processing"
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

            <CreateMeatModal
                :open="showCreateMeatModal"
                :onClose="closeCreateMeatModal"
                :errors="props.errors.meatErrors"
                @create="handleMeatCreated"
            />

            <CreateIngredientModal
                :allergens="props.allergens"
                :open="showCreateIngredientModal"
                :onClose="closeCreateIngredientModal"
                :errors="props.errors.ingredientErrors"
                @create="handleIngredientCreated"
            />
        </div>
    </AdminLayout>
</template>
