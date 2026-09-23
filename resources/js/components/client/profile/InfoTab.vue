<script setup lang="ts">
import settings from '@/actions/App/Http/Controllers/Settings';
import ConfirmUserDeleteModal from '@/components/client/profile/ConfirmUserDeleteModal.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import verification from '@/routes/verification';
import type { User as UserType } from '@/types/index';
import { router, useForm } from '@inertiajs/vue3';
import { Camera, Mail, Save, Trash2, User } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    user: UserType;
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const { t } = useI18n();

const isEditing = ref<boolean>(false);
const photoPreview = ref<string | null>(props.user.avatar || null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const showDeleteModal = ref<boolean>(false);
const showRemovePhotoModal = ref<boolean>(false);
const isRemovingPhoto = ref<boolean>(false);

const verifyForm = useForm({});
const updateForm = useForm({
    name: props.user.name,
    email: props.user.email,
    avatar: null as File | null,
});

const updateProfile = () => {
    console.log(updateForm);
    updateForm.post(settings.ProfileController.update().url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (response) => {
            router.reload({
                only: ['auth'],
            });

            console.log(response);
            isEditing.value = false;
            toast.success(t('profile.infoTab.success.updated'));
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.name) {
                toast.error(errors.name);
            }
            if (errors.email) {
                toast.error(errors.email);
            }
            if (errors.profile_photo) {
                toast.error(errors.profile_photo);
            }
        },
    });
};

const removeProfilePhoto = () => {
    if (isRemovingPhoto.value) {
        return;
    }

    isRemovingPhoto.value = true;

    router.delete('/settings/profile/avatar', {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            updateForm.avatar = null;
            showRemovePhotoModal.value = false;

            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }

            router.reload({
                only: ['auth'],
            });

            toast.success(t('profile.infoTab.photo.removed'));
        },
        onError: () => {
            toast.error(t('profile.infoTab.photo.removeFailed'));
        },
        onFinish: () => {
            isRemovingPhoto.value = false;
        },
    });
};

const handlePhotoSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];

        if (file.size > 2 * 1024 * 1024) {
            toast.error(t('profile.infoTab.photo.tooLarge'));
            input.value = '';
            return;
        }

        const validTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];
        if (!validTypes.includes(file.type)) {
            toast.error(t('profile.infoTab.photo.invalidType'));
            input.value = '';
            return;
        }

        updateForm.avatar = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const cancelEdit = () => {
    isEditing.value = false;
    updateForm.reset();
    photoPreview.value = props.user.avatar || null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const openDeleteModal = () => {
    showDeleteModal.value = true;
};

const resendVerification = () => {
    verifyForm.post(verification.send().url, {
        onSuccess: () => {
            toast.success(t('profile.infoTab.verification.sentSuccess'));
        },
        onError: () => {
            toast.error(t('profile.infoTab.verification.sentFailed'));
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold md:text-2xl">
                {{ t('profile.infoTab.title') }}
            </h2>
            <button
                v-if="!isEditing"
                @click="isEditing = true"
                class="rounded-lg bg-primary/10 px-4 py-2 text-sm font-medium text-primary transition-colors hover:bg-primary/15"
            >
                {{ t('profile.infoTab.editProfile') }}
            </button>
        </div>

        <div v-if="mustVerifyEmail && !user.email_verified_at" class="mb-4">
            <div class="rounded-lg border border-warning/20 bg-warning/10 p-4">
                <p class="text-sm text-foreground">
                    {{ t('profile.infoTab.verification.unverified') }}
                    <button
                        @click="resendVerification"
                        class="font-medium text-warning underline decoration-warning/40 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-warning"
                    >
                        {{ t('profile.infoTab.verification.resend') }}
                    </button>
                </p>
                <div
                    v-if="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-success"
                >
                    {{ t('profile.infoTab.verification.sent') }}
                </div>
            </div>
        </div>

        <form @submit.prevent="updateProfile" class="space-y-5">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="md:col-span-1">
                    <div class="sticky top-6">
                        <div class="relative mx-auto h-32 w-32 flex-shrink-0">
                            <div
                                class="h-full w-full overflow-hidden rounded-full border-2 border-border bg-muted"
                            >
                                <img
                                    v-if="photoPreview"
                                    :src="photoPreview"
                                    :alt="t('profile.infoTab.photo.alt')"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-muted text-muted-foreground"
                                >
                                    <User class="h-20 w-20" />
                                </div>
                            </div>

                            <div
                                v-if="isEditing"
                                class="absolute -right-0 -bottom-0 flex gap-1"
                            >
                                <button
                                    type="button"
                                    @click="triggerFileInput"
                                    class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-colors hover:bg-primary/90"
                                    title="Change photo"
                                >
                                    <Camera class="h-5 w-5" />
                                </button>

                                <button
                                    v-if="photoPreview"
                                    type="button"
                                    :disabled="isRemovingPhoto"
                                    :title="t('profile.infoTab.photo.remove')"
                                    class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-destructive text-destructive-foreground shadow-lg transition-colors hover:bg-destructive/90 disabled:cursor-not-allowed disabled:opacity-50"
                                    @click="showRemovePhotoModal = true"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p
                                v-if="isEditing"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                {{ t('profile.infoTab.photo.requirements') }}
                            </p>
                            <p
                                v-if="updateForm.errors.avatar"
                                class="mt-1.5 text-xs text-destructive"
                            >
                                {{ updateForm.errors.avatar }}
                            </p>
                        </div>

                        <input
                            ref="fileInputRef"
                            type="file"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            class="hidden"
                            @change="handlePhotoSelect"
                        />
                    </div>
                </div>

                <div class="space-y-5 md:col-span-2">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-card-foreground"
                        >
                            {{ t('profile.infoTab.form.fullName') }}
                        </label>
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                            >
                                <User class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <input
                                v-model="updateForm.name"
                                type="text"
                                :disabled="!isEditing"
                                class="w-full rounded-lg border border-input bg-background py-2.5 pr-4 pl-10 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none disabled:cursor-not-allowed disabled:bg-muted/50 disabled:text-muted-foreground"
                                :class="{
                                    'border-destructive ring-2 ring-destructive/20':
                                        updateForm.errors.name,
                                }"
                                :placeholder="
                                    t(
                                        'profile.infoTab.form.fullNamePlaceholder',
                                    )
                                "
                            />
                        </div>
                        <p
                            v-if="updateForm.errors.name"
                            class="mt-1.5 text-xs text-destructive"
                        >
                            {{ updateForm.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-card-foreground"
                        >
                            {{ t('profile.infoTab.form.email') }}
                        </label>
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                            >
                                <Mail class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <input
                                v-model="updateForm.email"
                                type="email"
                                :disabled="!isEditing"
                                class="w-full rounded-lg border border-input bg-background py-2.5 pr-4 pl-10 text-sm text-foreground transition-colors placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/20 focus:outline-none disabled:cursor-not-allowed disabled:bg-muted/50 disabled:text-muted-foreground"
                                :class="{
                                    'border-destructive ring-2 ring-destructive/20':
                                        updateForm.errors.email,
                                }"
                                :placeholder="
                                    t('profile.infoTab.form.emailPlaceholder')
                                "
                            />
                        </div>
                        <p
                            v-if="updateForm.errors.email"
                            class="mt-1.5 text-xs text-destructive"
                        >
                            {{ updateForm.errors.email }}
                        </p>
                    </div>

                    <div v-if="isEditing" class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="
                                updateForm.processing || !updateForm.isDirty
                            "
                            class="flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save
                                v-if="!updateForm.processing"
                                class="h-4 w-4"
                            />
                            <span
                                v-if="updateForm.processing"
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground/20 border-t-primary-foreground"
                            ></span>
                            {{
                                updateForm.processing
                                    ? t('profile.infoTab.form.saving')
                                    : t('profile.infoTab.form.save')
                            }}
                        </button>
                        <button
                            type="button"
                            @click="cancelEdit"
                            class="rounded-lg border border-input bg-background px-6 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            {{ t('profile.infoTab.form.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="border-t border-border pt-6">
            <h2 class="text-xl font-semibold text-destructive md:text-2xl">
                {{ t('profile.infoTab.delete.title') }}
            </h2>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ t('profile.infoTab.delete.description') }}
            </p>
            <button
                @click="openDeleteModal"
                class="mt-4 flex items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-2.5 text-sm font-medium text-destructive transition-colors hover:bg-destructive/15"
            >
                <Trash2 class="h-4 w-4" />
                {{ t('profile.infoTab.delete.button') }}
            </button>
        </div>

        <ConfirmModal
            :open="showRemovePhotoModal"
            :on-close="() => (showRemovePhotoModal = false)"
            :message="t('profile.infoTab.photo.removeMessage')"
            type="destructive"
            :is-loading="isRemovingPhoto"
            @confirm="removeProfilePhoto"
        />

        <ConfirmUserDeleteModal v-model:open="showDeleteModal" />
    </div>
</template>
