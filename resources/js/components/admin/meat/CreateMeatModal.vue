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
import { MeatErrorType, MeatOptionType } from '@/types/meat';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Loader } from 'lucide-vue-next';
import meat from '@/routes/admin/meat';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
    errors?: MeatErrorType;
}>();

const emit = defineEmits<{
    create: [meat: MeatOptionType];
}>();

const page = usePage();

const name = ref<string>('');
const description = ref<string>('');
const extra_price = ref<number | null>(null);
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
        onFinish: () => {
            isLoading.value = false;
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
                <Button variant="secondary" @click="props.onClose()" class="sm:w-24">
                    Cancel
                </Button>

                <Button :disabled="meatForm.processing" @click="submit" class="sm:w-24">
                    <span v-if="isLoading">
                        <Loader class="animate-spin text-muted-foreground" />
                    </span>
                    <span v-else>Create</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
