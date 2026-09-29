<script setup lang="ts">
import settings from '@/actions/App/Http/Controllers/Settings';
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, X } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
}>();

const deleteConfirmationText = ref('');
const deleteForm = useForm({
    password: '',
});

const resetForm = () => {
    deleteConfirmationText.value = '';
    deleteForm.password = '';
    deleteForm.clearErrors();
};

const closeModal = () => {
    emit('update:open', false);
    emit('close');
    resetForm();
};

const confirmDelete = () => {
    if (deleteConfirmationText.value !== 'DELETE') {
        toast.error('Please type "DELETE" to confirm');
        return;
    }

    deleteForm.delete(settings.ProfileController.destroy().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Account deleted successfully');
            closeModal();
        },
        onError: (errors) => {
            if (errors.password) {
                toast.error(errors.password);
            } else {
                toast.error('Failed to delete account. Please try again.');
            }
        },
    });
};

onMounted(() => {
    if (props.open) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

watch(
    () => props.open,
    (newValue) => {
        if (newValue) {
            resetForm();
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Teleport to="body">
        <Transition name="client-modal" appear>
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="closeModal"
            >
                <div
                    class="client-modal-panel w-full max-w-md rounded-xl border border-border bg-card p-6 text-card-foreground shadow-2xl"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-destructive/10"
                        >
                            <AlertTriangle class="h-6 w-6 text-destructive" />
                        </div>
                        <div class="flex-1">
                            <h3
                                class="text-lg font-semibold text-card-foreground"
                            >
                                Delete Account
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                This action cannot be undone. All your data will
                                be permanently removed.
                            </p>
                        </div>
                        <button
                            @click="closeModal"
                            class="rounded-lg p-1 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-card-foreground"
                            >
                                Current Password
                            </label>
                            <input
                                v-model="deleteForm.password"
                                type="password"
                                class="w-full rounded-lg border border-input bg-background px-4 py-2.5 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                                :class="{
                                    'border-destructive ring-2 ring-destructive/20':
                                        deleteForm.errors.password,
                                }"
                                placeholder="Enter your current password"
                                @keydown.enter="confirmDelete"
                            />
                            <p
                                v-if="deleteForm.errors.password"
                                class="mt-1.5 text-xs text-destructive"
                            >
                                {{ deleteForm.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-card-foreground"
                            >
                                Type
                                <span class="font-bold text-destructive"
                                    >DELETE</span
                                >
                                to confirm
                            </label>
                            <input
                                v-model="deleteConfirmationText"
                                type="text"
                                class="w-full rounded-lg border border-input bg-background px-4 py-2.5 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                                placeholder="Type DELETE"
                                @keydown.enter="confirmDelete"
                            />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                @click="closeModal"
                                :disabled="deleteForm.processing"
                                class="flex-1 rounded-lg border border-input bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                @click="confirmDelete"
                                :disabled="
                                    deleteForm.processing ||
                                    deleteConfirmationText !== 'DELETE'
                                "
                                class="flex-1 rounded-lg bg-destructive px-4 py-2.5 text-sm font-medium text-destructive-foreground transition-colors hover:bg-destructive/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span
                                    v-if="deleteForm.processing"
                                    class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-destructive-foreground/20 border-t-destructive-foreground"
                                ></span>
                                {{
                                    deleteForm.processing
                                        ? 'Deleting...'
                                        : 'Delete Account'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fixed {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.client-modal-enter-active,
.client-modal-leave-active {
    transition: opacity 200ms ease;
}

.client-modal-enter-active .client-modal-panel,
.client-modal-leave-active .client-modal-panel {
    transition:
        opacity 200ms ease,
        transform 200ms ease;
}

.client-modal-enter-from,
.client-modal-leave-to {
    opacity: 0;
}

.client-modal-enter-from .client-modal-panel,
.client-modal-leave-to .client-modal-panel {
    opacity: 0;
    transform: translateY(-1rem) scale(0.95);
}
</style>
