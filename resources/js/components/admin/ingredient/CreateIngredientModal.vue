<script setup lang="ts">
import FormTextInput from '@/components/FormTextInput.vue';
import FormTextarea from '@/components/FormTextarea.vue';
import ImageUploader from '@/components/ImageUploader.vue';
import Select from '@/components/Select.vue';
import { Button } from '@/components/ui/button';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { toast } from 'vue-sonner';
import { AllergenOptionType } from '@/types/allergen';
import { IngredientErrorType, IngredientOptionType } from '@/types/ingredient';
import { Loader } from 'lucide-vue-next';
import ingredient from '@/routes/admin/ingredient';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
    allergens: AllergenOptionType[];
    errors?: IngredientErrorType;
}>();

const emit = defineEmits<{
    create: [ingredient: { value: number; label: string }];
}>();

const page = usePage();
const { t } = useI18n();

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

    ingredientForm.post(ingredient.quickCreate().url, {
        forceFormData: true,
        preserveScroll: true,
        errorBag: 'ingredientErrors',
        onSuccess: () => {
            ingredientForm.reset();
            const newIngredient = page.props.flash
                .createdIngredient as IngredientOptionType;
            emit('create', newIngredient);
        },
        onError: (error) => {
            if (error.message) toast.error(error.meessage);

            toast.error(t('ingredient.createModal.error'));
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
                <DialogTitle>{{
                    t('ingredient.createModal.title')
                }}</DialogTitle>
                <DialogDescription>
                    {{ t('ingredient.createModal.description') }}
                </DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-4 py-4">
                <FormTextInput
                    id="name"
                    :label="t('ingredient.createModal.fields.name')"
                    v-model="name"
                    :error="props.errors?.name"
                />

                <FormTextarea
                    id="description"
                    :label="t('ingredient.createModal.fields.description')"
                    v-model="description"
                    :rows="3"
                    :error="props.errors?.description"
                />

                <Select
                    v-model="selectedAllergen"
                    :options="props.allergens"
                    :label="t('ingredient.createModal.fields.allergen')"
                    :error="props.errors?.allergen_id"
                />

                <ImageUploader v-model="images" />
            </div>

            <DialogFooter>
                <Button
                    variant="secondary"
                    @click="props.onClose()"
                    class="sm:w-24"
                >
                    {{ t('admin.buttons.cancel') }}
                </Button>

                <Button
                    :disabled="ingredientForm.processing"
                    @click="submit"
                    class="sm:w-24"
                >
                    <span v-if="isLoading">
                        <Loader class="animate-spin text-muted-foreground" />
                    </span>
                    <span v-else>{{ t('admin.buttons.create') }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
