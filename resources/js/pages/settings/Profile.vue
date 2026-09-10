<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { getInitials, getUserAvatarColor } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const { t } = useI18n();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('setting.profile.title'),
        href: edit().url,
    },
]);

const page = usePage();
const user = page.props.auth.user;

const photoPreview = ref<string | null>(user?.avatar || null);

const handlePhotoSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];

        if (file.size > 2 * 1024 * 1024) {
            toast.error(t('setting.profile.errors.photoTooLarge'));
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
            toast.error(t('setting.profile.errors.invalidPhoto'));
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('setting.profile.title')" />

        <SettingsLayout>
            <div v-if="user" class="flex flex-col space-y-6">
                <HeadingSmall
                    :title="t('setting.profile.heading.title')"
                    :description="t('setting.profile.heading.description')"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label>{{ t('setting.profile.fields.avatar') }}</Label>
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div
                                    class="h-20 w-20 overflow-hidden rounded-full bg-gray-200"
                                >
                                    <img
                                        v-if="photoPreview"
                                        :src="photoPreview"
                                        alt="Avatar"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-2xl font-semibold text-white"
                                        :class="getUserAvatarColor(user.id)"
                                    >
                                        {{ getInitials(user.name) }}
                                    </div>
                                </div>
                                <label
                                    for="avatar"
                                    class="absolute right-0 bottom-0 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-primary text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    <Input
                                        id="avatar"
                                        type="file"
                                        class="hidden"
                                        name="avatar"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        @change="handlePhotoSelect"
                                    />
                                </label>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-muted-foreground">
                                    {{ t('setting.profile.avatar.uploadHint') }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        t('setting.profile.avatar.requirements')
                                    }}
                                </p>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="errors.avatar" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">
                            {{ t('setting.profile.fields.name') }}
                        </Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            :name="t('setting.profile.placeholders.name')"
                            :default-value="user.name"
                            required
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">
                            {{ t('setting.profile.fields.email') }}
                        </Label>
                        <Input
                            id="email"
                            :type="t('setting.profile.placeholders.email')"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            {{ t('setting.profile.verification.unverified') }}
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                {{ t('setting.profile.verification.resend') }}
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            {{ t('setting.profile.verification.linkSent') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                        >
                            {{ t('admin.buttons.save') }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                {{ t('setting.profile.messages.saved') }}
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AdminLayout>
</template>
