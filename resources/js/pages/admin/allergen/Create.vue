<script setup lang="ts">
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import MultipleSelect from '@/components/MultipleSelect.vue';
import CreateIngredientModal from '@/components/admin/ingredient/CreateIngredientModal.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import allergen from '@/routes/admin/allergen';
import type { BreadcrumbItem, ErrorType } from '@/types';
import { AllergenOptionType } from '@/types/allergen';
import { IngredientOptionType } from '@/types/ingredient';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    ingredients: IngredientOptionType[];
    allergens: AllergenOptionType[];
    errors: ErrorType;
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('allergen.form.breadcrumbs.allergens'),
        href: allergen.index().url,
    },
    {
        title: t('allergen.form.breadcrumbs.create'),
        href: allergen.create().url,
    },
]);

const showCreateIngredientModal = ref<boolean>(false);
const selectedIngredients = ref<number[]>([]);
const name = ref<string>('');
const description = ref<string>('');
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

    allergenForm.post(allergen.store().url, {
        forceFormData: true,
        errorBag: 'allergenErrors',
        onSuccess: () => {
            allergenForm.reset();
            toast.success(t('allergen.form.messages.created'));
        },
        onError: (error) => {
            if (error.message) {
                toast.error(error.meessage);
            } else {
                toast.error(t('allergen.form.messages.error'));
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
    <Head :title="t('allergen.form.title')" />

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
                            {{ t('allergen.form.sections.information') }}
                        </h2>

                        <FormTextInput
                            id="name"
                            :label="t('allergen.form.fields.name')"
                            v-model="name"
                            :error="props.errors.allergenErrors?.name"
                        />

                        <FormTextarea
                            id="description"
                            :label="t('allergen.form.fields.description')"
                            v-model="description"
                            :rows="5"
                            :error="props.errors.allergenErrors?.description"
                        />

                        <MultipleSelect
                            v-model="selectedIngredients"
                            :options="ingredients"
                            :label="t('allergen.form.fields.ingredients')"
                            :error="props.errors.allergenErrors?.ingredients"
                            @create="showCreateIngredientModal = true"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            {{ t('allergen.form.sections.photos') }}
                        </h2>

                        <ImageUploader v-model="images" />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" class="w-24">
                        <a
                            :href="allergen.index().url"
                            class="flex items-center"
                        >
                            {{ t('admin.buttons.cancel') }}
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
                            >{{ t('admin.buttons.create') }}</span
                        >
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
