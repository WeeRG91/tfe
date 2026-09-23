<script setup lang="ts">
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import Select from '@/components/Select.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ingredient from '@/routes/admin/ingredient';
import type { BreadcrumbItem } from '@/types';
import { AllergenOptionType } from '@/types/allergen';
import { IngredientErrorType } from '@/types/ingredient';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    allergens: AllergenOptionType[];
    errors: IngredientErrorType;
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('ingredient.form.breadcrumbs.ingredients'),
        href: ingredient.index().url,
    },
    {
        title: t('ingredient.form.breadcrumbs.create'),
        href: ingredient.create().url,
    },
]);

const selectedAllergen = ref<number | null>(null);
const name = ref<string>('');
const description = ref<string>('');
const images = ref<File[]>([]);
const isLoading = ref<boolean>(false);

const ingredientForm = useForm({
    name: '',
    description: '',
    allergen_id: null as number | null,
    images: [] as File[],
});

const submit = () => {
    isLoading.value = true;

    ingredientForm.name = name.value;
    ingredientForm.description = description.value;
    ingredientForm.allergen_id = selectedAllergen.value;
    ingredientForm.images = images.value;

    ingredientForm.post(ingredient.store().url, {
        forceFormData: true,
        onSuccess: () => {
            ingredientForm.reset();
            toast.success(t('ingredient.form.messages.created'));
        },
        onError: (error) => {
            if (error.message) {
                toast.error(error.meessage);
            } else {
                toast.error(t('ingredient.form.messages.error'));
            }
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <Head :title="t('ingredient.form.title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <form @submit.prevent="submit">
                <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            {{ t('ingredient.form.sections.information') }}
                        </h2>

                        <FormTextInput
                            id="name"
                            :label="t('ingredient.form.fields.name')"
                            v-model="name"
                            :error="props.errors.name"
                        />

                        <FormTextarea
                            id="description"
                            :label="t('ingredient.form.fields.description')"
                            v-model="description"
                            :rows="5"
                            :error="props.errors.description"
                        />

                        <Select
                            v-model="selectedAllergen"
                            :options="props.allergens"
                            :label="t('ingredient.form.fields.allergen')"
                            :error="props.errors.allergen_id"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            {{ t('ingredient.form.sections.photos') }}
                        </h2>

                        <ImageUploader v-model="images" />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" class="w-24">
                        <a
                            :href="ingredient.index().url"
                            class="flex items-center"
                        >
                            {{ t('admin.buttons.cancel') }}
                        </a>
                    </Button>
                    <Button
                        type="submit"
                        class="w-24 cursor-pointer"
                        :disabled="ingredientForm.processing"
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
        </div>
    </AdminLayout>
</template>
