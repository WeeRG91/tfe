<script setup lang="ts">
import { AlertTriangle, X } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import settings from '@/actions/App/Http/Controllers/Settings';

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
        if (!newValue) {
            resetForm();
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeModal"
        >
            <div
                class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl transition-all"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100"
                    >
                        <AlertTriangle class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Delete Account
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            This action cannot be undone. All your data will be
                            permanently removed.
                        </p>
                    </div>
                    <button
                        @click="closeModal"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Current Password
                        </label>
                        <input
                            v-model="deleteForm.password"
                            type="password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                            :class="{
                                'border-red-500 ring-2 ring-red-500/20':
                                    deleteForm.errors.password,
                            }"
                            placeholder="Enter your current password"
                            @keydown.enter="confirmDelete"
                        />
                        <p
                            v-if="deleteForm.errors.password"
                            class="mt-1.5 text-xs text-red-500"
                        >
                            {{ deleteForm.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Type
                            <span class="font-bold text-red-600">DELETE</span>
                            to confirm
                        </label>
                        <input
                            v-model="deleteConfirmationText"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                            placeholder="Type DELETE"
                            @keydown.enter="confirmDelete"
                        />
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            @click="closeModal"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmDelete"
                            :disabled="
                                deleteForm.processing ||
                                deleteConfirmationText !== 'DELETE'
                            "
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span
                                v-if="deleteForm.processing"
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
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
</template>

<style scoped>
.fixed {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
</style>
