<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/InputError.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePasswordStrength } from '@/composables/usePasswordStrength';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const password = ref<string>('');

const { passwordChecks, getPasswordStrengthColor } =
    usePasswordStrength(password);
const handlePasswordInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    password.value = target.value;
};
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Update password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <Form
                    v-bind="PasswordController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    @success="password = ''"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <Input
                            id="current_password"
                            ref="currentPasswordInput"
                            name="current_password"
                            type="password"
                            class="mt-1 block w-full"
                            placeholder="Current password"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <Input
                            id="password"
                            ref="passwordInput"
                            name="password"
                            type="password"
                            class="mt-1 block w-full"
                            placeholder="New password"
                            @input="handlePasswordInput"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div v-if="password.length > 0" class="space-y-2.5">
                        <div class="flex gap-1">
                            <div
                                v-for="i in 4"
                                :key="i"
                                class="h-1 flex-1 rounded-full transition-all duration-300"
                                :class="getPasswordStrengthColor(i)"
                            />
                        </div>
                        <div
                            class="grid grid-cols-2 gap-x-4 gap-y-0.5 text-xs text-gray-400"
                        >
                            <span
                                :class="{
                                    'text-emerald-500': passwordChecks.length,
                                }"
                                class="transition-colors duration-200"
                            >
                                {{ passwordChecks.length ? '✓' : '○' }} 8+
                                characters
                            </span>
                            <span
                                :class="{
                                    'text-emerald-500':
                                        passwordChecks.uppercase,
                                }"
                                class="transition-colors duration-200"
                            >
                                {{
                                    passwordChecks.uppercase ? '✓' : '○'
                                }}
                                Uppercase
                            </span>
                            <span
                                :class="{
                                    'text-emerald-500':
                                        passwordChecks.lowercase,
                                }"
                                class="transition-colors duration-200"
                            >
                                {{
                                    passwordChecks.lowercase ? '✓' : '○'
                                }}
                                Lowercase
                            </span>
                            <span
                                :class="{
                                    'text-emerald-500': passwordChecks.number,
                                }"
                                class="transition-colors duration-200"
                            >
                                {{ passwordChecks.number ? '✓' : '○' }} Number
                            </span>
                            <span
                                :class="{
                                    'text-emerald-500': passwordChecks.symbol,
                                }"
                                class="transition-colors duration-200"
                            >
                                {{ passwordChecks.symbol ? '✓' : '○' }} Symbol
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Confirm password</Label
                        >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            placeholder="Confirm password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                            >Save password</Button
                        >

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
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
