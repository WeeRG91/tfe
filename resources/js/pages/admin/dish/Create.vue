<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import MultipleSelect from '@/components/MultipleSelect.vue';
import Select from '@/components/Select.vue';
import SpicyLevelSelector from '@/components/admin/dish/SpicyLevelSelector.vue';
import CreateIngredientModal from '@/components/admin/ingredient/CreateIngredientModal.vue';
import CreateMeatModal from '@/components/admin/meat/CreateMeatModal.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import dish from '@/routes/admin/dish';
import type { BreadcrumbItem, ErrorType } from '@/types';
import { AllergenOptionType } from '@/types/allergen';
import { CategoryOptionType } from '@/types/category';
import { IngredientOptionType } from '@/types/ingredient';
import { MeatOptionType } from '@/types/meat';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    ingredients: IngredientOptionType[];
    meats: MeatOptionType[];
    categories: CategoryOptionType[];
    allergens: AllergenOptionType[];
    errors: ErrorType;
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('dish.form.breadcrumbs.dishes'),
        href: dish.index().url,
    },
    {
        title: t('dish.form.breadcrumbs.create'),
        href: dish.create().url,
    },
]);

const showCreateMeatModal = ref<boolean>(false);
const showCreateIngredientModal = ref<boolean>(false);
const selectedMeats = ref<number[]>([]);
const selectedIngredients = ref<number[]>([]);
const selectedCategory = ref<number | null>(null);
const name = ref<string>('');
const description = ref<string>('');
const price = ref<number>(0);
const defaultSpicyLevel = ref<number>(0);
const ingredients = ref<IngredientOptionType[]>(props.ingredients);
const meats = ref<MeatOptionType[]>(props.meats);
const imageFile = ref<File[]>([]);
const isLoading = ref<boolean>(false);

const dishForm = useForm({
    name: '',
    description: '',
    price: 0,
    default_spicy_level: 0,
    category: null as number | null,
    meats: [] as number[],
    ingredients: [] as number[],
    images: [] as File[],
});

const submit = () => {
    isLoading.value = true;

    dishForm.name = name.value;
    dishForm.description = description.value;
    dishForm.price = price.value;
    dishForm.default_spicy_level = defaultSpicyLevel.value;
    dishForm.category = selectedCategory.value;
    dishForm.meats = selectedMeats.value;
    dishForm.ingredients = selectedIngredients.value;
    dishForm.images = imageFile.value;

    dishForm.post(dish.store().url, {
        forceFormData: true,
        errorBag: 'dishErrors',
        onSuccess: () => {
            dishForm.reset();
            toast.success(t('dish.form.message.created'));
        },
        onError: (error) => {
            console.log(error);
            if (error) {
                toast.error(t('dish.form.message.error'));
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
    <Head :title="t('dish.form.title')" />

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
                            {{ t('dish.form.sections.information') }}
                        </h2>

                        <FormTextInput
                            id="name"
                            :label="t('dish.form.fields.name')"
                            v-model="name"
                            :error="props.errors?.dishErrors?.name"
                        />

                        <FormTextarea
                            id="description"
                            :label="t('dish.form.fields.description')"
                            v-model="description"
                            :rows="5"
                            :error="props.errors?.dishErrors?.description"
                        />

                        <FormNumberInput
                            id="price"
                            :label="t('dish.form.fields.price')"
                            v-model="price"
                            :error="props.errors?.dishErrors?.price"
                        />

                        <SpicyLevelSelector
                            v-model="defaultSpicyLevel"
                            :label="t('dish.form.fields.defaultSpicyLevel')"
                            :error="
                                props.errors?.dishErrors?.default_spicy_level
                            "
                        />

                        <MultipleSelect
                            v-model="selectedMeats"
                            :options="meats"
                            :label="t('dish.form.fields.meatOptions')"
                            :error="props.errors?.dishErrors?.meats"
                            @create="showCreateMeatModal = true"
                        />

                        <MultipleSelect
                            v-model="selectedIngredients"
                            :options="ingredients"
                            :label="t('dish.form.fields.ingredients')"
                            :error="props.errors?.dishErrors?.ingredients"
                            @create="showCreateIngredientModal = true"
                        />

                        <Select
                            v-model="selectedCategory"
                            :options="props.categories"
                            :label="t('dish.form.fields.category')"
                            :error="props.errors?.dishErrors?.category"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            {{ t('dish.form.sections.photos') }}
                        </h2>

                        <ImageUploader v-model="imageFile" />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" class="w-24">
                        <a
                            :href="dish.index().url"
                            class="flex items-center justify-center gap-2"
                        >
                            {{ t('admin.buttons.cancel') }}
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
                            {{ t('admin.buttons.create') }}</span
                        >
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
