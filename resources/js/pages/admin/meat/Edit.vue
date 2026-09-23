<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import meat from '@/routes/admin/meat';
import { BreadcrumbItem } from '@/types';
import { EditMeatType, MeatErrorType } from '@/types/meat';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    meatToEdit: EditMeatType;
    errors: MeatErrorType;
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('meat.form.breadcrumbs.meats'),
        href: meat.index().url,
    },
    {
        title: t('meat.form.breadcrumbs.edit'),
        href: meat.edit(props.meatToEdit.id).url,
    },
]);

const name = ref<string>(props.meatToEdit.name || '');
const description = ref<string>(props.meatToEdit.description || '');
const extra_price = ref<number | null>(props.meatToEdit.extra_price || null);
const images = ref<File[]>([]);
const isLoading = ref<boolean>(false);

const meatForm = useForm({
    name: '',
    description: '',
    extra_price: null as number | null,
    images: [] as File[],
});

const submit = () => {
    isLoading.value = true;

    meatForm.name = name.value;
    meatForm.description = description.value;
    meatForm.extra_price = extra_price.value;
    meatForm.images = images.value;

    meatForm.post(meat.update(props.meatToEdit.id).url, {
        forceFormData: true,
        onSuccess: () => {
            meatForm.reset();
            toast.success(t('meat.form.messages.edited'));
        },
        onError: (error) => {
            if (error.message) {
                toast.error(error.meessage);
            } else {
                toast.error(t('meat.form.messages.error'));
            }
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <Head :title="t('meat.form.editTitle', { name: props.meatToEdit.name })" />

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
                            {{ t('meat.form.sections.information') }}
                        </h2>

                        <FormTextInput
                            id="name"
                            :label="t('meat.form.fields.name')"
                            v-model="name"
                            :error="props.errors.name"
                        />

                        <FormTextarea
                            id="description"
                            :label="t('meat.form.fields.description')"
                            v-model="description"
                            :rows="5"
                            :error="props.errors.description"
                        />

                        <FormNumberInput
                            id="extra_price"
                            :label="t('meat.form.fields.extraPrice')"
                            v-model="extra_price"
                            :error="props.errors.extra_price"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            {{ t('meat.form.sections.photos') }}
                        </h2>

                        <ImageUploader
                            v-model="images"
                            :existing-images="props.meatToEdit.images"
                            :mainImage="props.meatToEdit.main_image"
                        />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" class="w-24">
                        <a :href="meat.index().url" class="flex items-center">
                            {{ t('admin.buttons.cancel') }}
                        </a>
                    </Button>
                    <Button
                        type="submit"
                        class="w-24 cursor-pointer"
                        :disabled="meatForm.processing"
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
                            {{ t('admin.buttons.edit') }}
                        </span>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
