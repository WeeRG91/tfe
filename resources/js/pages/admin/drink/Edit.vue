<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import Select from '@/components/Select.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { BreadcrumbItem } from '@/types';
import { CategoryOptionType } from '@/types/category';
import { DishErrorType } from '@/types/dish';
import { EditDrinkType } from '@/types/drink';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader, SquarePenIcon, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import drink from '@/routes/admin/drink';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    drinkToEdit: EditDrinkType;
    categories: CategoryOptionType[];
    errors: DishErrorType;
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('drink.form.breadcrumbs.drinks'),
        href: drink.index().url,
    },
    {
        title: t('drink.form.breadcrumbs.edit'),
        href: drink.edit(props.drinkToEdit.id).url,
    },
]);

const selectedCategory = ref<number | null>(props.drinkToEdit.category ?? null);
const name = ref<string>(props.drinkToEdit.name ?? '');
const description = ref<string>(props.drinkToEdit.description ?? '');
const price = ref<number | null>(props.drinkToEdit.price ?? null);
const images = ref<File[]>([]);
const isLoading = ref<boolean>(false);

const drinkForm = useForm({
    name: '',
    description: '',
    price: null as number | null,
    category: null as number | null,
    images: [] as File[],
});

const submit = () => {
    isLoading.value = true;

    drinkForm.name = name.value;
    drinkForm.description = description.value;
    drinkForm.price = price.value;
    drinkForm.category = selectedCategory.value;
    drinkForm.images = images.value;

    drinkForm.post(drink.update(props.drinkToEdit.id).url, {
        forceFormData: true,
        onSuccess: () => {
            drinkForm.reset();
            toast.success(t('drink.form.messages.edited'));
        },
        onError: (error) => {
            if (error.message) {
                toast.error(error.message);
            } else {
                toast.error(t('drink.form.messages.error'));
            }
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <Head
        :title="t('drink.form.editTitle', { name: props.drinkToEdit.name })"
    />
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
                            {{ t('drink.form.sections.information') }}
                        </h2>

                        <FormTextInput
                            id="name"
                            :label="t('drink.form.fields.name')"
                            v-model="name"
                            :error="props.errors.name"
                        />

                        <FormTextarea
                            id="description"
                            :label="t('drink.form.fields.description')"
                            v-model="description"
                            :rows="5"
                            :error="props.errors.description"
                        />

                        <FormNumberInput
                            id="price"
                            :label="t('drink.form.fields.price')"
                            v-model="price"
                            :error="props.errors.price"
                        />

                        <Select
                            v-model="selectedCategory"
                            :options="props.categories"
                            :label="t('drink.form.fields.category')"
                            :error="props.errors.category"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            {{ t('drink.form.sections.photos') }}
                        </h2>

                        <ImageUploader
                            v-model="images"
                            :existingImages="props.drinkToEdit.images"
                            :mainImage="props.drinkToEdit.main_image"
                        />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" class="w-24">
                        <a :href="drink.index().url" class="flex items-center">
                            <X /> {{ t('admin.buttons.cancel') }}
                        </a>
                    </Button>
                    <Button
                        type="submit"
                        class="w-24 cursor-pointer"
                        :disabled="drinkForm.processing"
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
                            <SquarePenIcon /> {{ t('admin.buttons.edit') }}
                        </span>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
