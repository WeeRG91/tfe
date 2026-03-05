<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { BreadcrumbItem } from '@/types';
import { MeatErrorType } from '@/types/meat';
import { Head, useForm } from '@inertiajs/vue3';
import { CirclePlus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import meat from '@/routes/meat';

const props = defineProps<{
    errors: MeatErrorType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Meats',
        href: meat.index().url,
    },
    {
        title: 'Create',
        href: meat.create().url,
    },
];

const name = ref<string>('');
const description = ref<string>('');
const extra_price = ref<number | null>(null);
const images = ref<File[]>([]);

const meatForm = useForm({
    name: '',
    description: '',
    extra_price: null as number | null,
    images: [] as File[],
});

const submit = () => {
    meatForm.name = name.value;
    meatForm.description = description.value;
    meatForm.extra_price = extra_price.value;
    meatForm.images = images.value;

    meatForm.post(meat.store().url, {
        forceFormData: true,
        onSuccess: () => {
            meatForm.reset();
            toast.success('Meat successfully created.');
        },
        onError: (error) => {
            if (error.message) toast.error(error.meessage);

            toast.error('Something went wrong. Please check the form.');
        },
    });
};
</script>

<template>
    <Head title="Add meat" />

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
                            Meat Information
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
                            id="extra_price"
                            label="Extra price"
                            v-model="extra_price"
                            :error="props.errors.extra_price"
                        />
                    </div>
                    <div
                        class="relative flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h2 class="flex items-center border-b-1 py-2">
                            Photos
                        </h2>

                        <ImageUploader v-model="images" />
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary">
                        <a :href="meat.index().url" class="flex items-center">
                            <X /> Cancel
                        </a>
                    </Button>
                    <Button type="submit" :disabled="meatForm.processing"
                        ><CirclePlus /> Create</Button
                    >
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
