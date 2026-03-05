<script setup lang="ts">
import FormNumberInput from '@/components/FormNumberInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import FormTextInput from '@/components/FormTextInput.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import meat from '@/routes/meat';
import { MeatErrorType, MeatOptionType } from '@/types/meat';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
    errors?: MeatErrorType;
}>();

const emit = defineEmits<{
    (e: 'create', meat: MeatOptionType): void;
}>();

const page = usePage();

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

    meatForm.post(meat.quickCreate().url, {
        forceFormData: true,
        preserveScroll: true,
        errorBag: 'meatErrors',
        onSuccess: () => {
            meatForm.reset();
            const newMeat = page.props.flash.createdMeat as MeatOptionType;
            emit('create', newMeat);
        },
        onError: (error) => {
            if (error.message) toast.error(error.meessage);

            toast.error('Something went wrong. Please check the form.');
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="props.onClose">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Create Meat Option</DialogTitle>
                <DialogDescription>
                    Fill in the information below to create a new meat option.
                </DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-4 py-4">
                <FormTextInput
                    id="name"
                    label="Name"
                    v-model="name"
                    :error="props.errors?.name"
                />

                <FormTextarea
                    id="description"
                    label="Description"
                    v-model="description"
                    :rows="3"
                    :error="props.errors?.description"
                />

                <FormNumberInput
                    id="extra_price"
                    label="Extra price"
                    v-model="extra_price"
                    :error="props.errors?.extra_price"
                />

                <ImageUploader v-model="images" />
            </div>

            <DialogFooter>
                <Button variant="secondary" @click="props.onClose()">
                    Cancel
                </Button>

                <Button :disabled="meatForm.processing" @click="submit">
                    Create
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
