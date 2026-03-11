<script setup lang="ts">
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

export type ModalType = 'destructive' | 'info';

const props = defineProps<{
    open: boolean;
    onClose: () => void;
    message: string;
    type: ModalType;
}>();

const emit = defineEmits<{(e: "confirm"): void}>();

const confirm = () => {
    emit('confirm');
}
</script>

<template>
    <Dialog :open="props.open" @update:open="props.onClose">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Confirmation</DialogTitle>
                <DialogDescription>{{props.message}}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="secondary" @click="props.onClose()">
                    Cancel
                </Button>
                <Button v-if="type === 'destructive'" variant="destructive" @click="confirm">
                    Confirm
                </Button>
                <Button v-if="type === 'info'" @click="confirm">
                    Confirm
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
