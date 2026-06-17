<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader } from 'lucide-vue-next';

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
</script>

<template>
    <Dialog :open="props.open" @update:open="props.onClose">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Confirmation</DialogTitle>
                <DialogDescription>{{ props.message }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="secondary" @click="props.onClose()" class="w-24">
                    Cancel
                </Button>
                <Button
                    v-if="type === 'destructive'"
                    variant="destructive"
                    class="w-24"
                    @click="confirm"
                >
                    <span
                        v-if="isLoading"
                        class="flex items-center justify-center"
                    >
                        <Loader class="animate-spin text-muted-foreground" />
                    </span>
                    <span v-else>Confirm</span>
                </Button>
                <Button v-if="type === 'info'" @click="confirm" class="w-24">
                    <span
                        v-if="isLoading"
                        class="flex items-center justify-center"
                    >
                        <Loader class="animate-spin text-muted-foreground" />
                    </span>
                    <span v-else>Confirm</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
