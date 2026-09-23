<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Loader } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

export type ModalType = 'destructive' | 'info';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
    message: string;
    type: ModalType;
    isLoading: boolean;
}>();

const emit = defineEmits<{ (e: 'confirm'): void }>();

const confirm = () => {
    emit('confirm');
};

const { t } = useI18n();
</script>

<template>
    <Dialog :open="props.open" @update:open="props.onClose">
        <DialogContent
            class="max-w-2xl border-border bg-card text-card-foreground"
        >
            <DialogHeader>
                <DialogTitle>
                    {{ t('confirmModal.title') }}
                </DialogTitle>
                <DialogDescription>{{ props.message }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="secondary"
                    @click="props.onClose()"
                    class="w-24"
                >
                    {{ t('confirmModal.cancel') }}
                </Button>
                <Button
                    v-if="type === 'destructive'"
                    variant="destructive"
                    class="w-24"
                    :disabled="isLoading"
                    @click="confirm"
                >
                    <span
                        v-if="isLoading"
                        class="flex items-center justify-center"
                    >
                        <Loader
                            class="animate-spin text-destructive-foreground"
                        />
                    </span>
                    <span v-else>
                        {{ t('confirmModal.confirm') }}
                    </span>
                </Button>
                <Button
                    v-if="type === 'info'"
                    class="w-24"
                    :disabled="isLoading"
                    @click="confirm"
                >
                    <span
                        v-if="isLoading"
                        class="flex items-center justify-center"
                    >
                        <Loader class="animate-spin text-primary-foreground" />
                    </span>
                    <span v-else>
                        {{ t('confirmModal.confirm') }}
                    </span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
