<script setup lang="ts">
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { login, register } from '@/routes';
import { RefreshCw, MoveLeft, MoveRight } from 'lucide-vue-next';

const resendForm = useForm({
    email: '',
});

const submit = () => {
    resendForm.post('/activate/resend/reactivation', {
        onSuccess: (response) => {
            console.log(response);

            toast.success(
                'Reactivation link sent successfully! Please check your email.',
            );

            resendForm.reset();
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.email) {
                toast.error(errors.email);
            } else if (errors.error) {
                toast.error(errors.error);
            } else {
                toast.error('Something went wrong. Please try again.');
            }
        },
    });
};
</script>

<template>
    <Head title="Reactivation Link Expired" />
    <CostumedAuthLayout title="Reactivation link expired" badge="Link Expired">
        <div class="space-y-6">
            <div class="text-center">
                <p class="text-sm text-gray-500">
                    The reactivation link is no longer valid. This usually happens
                    when:
                </p>
                <ul class="mt-2 space-y-1 text-sm text-gray-500">
                    <li>
                        • The link has expired (links are valid for 24 hours)
                    </li>
                    <li>• You've already reactivated your account</li>
                    <li>• The link was modified or corrupted</li>
                </ul>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-4 text-gray-500"
                    >Request new link</span
                    >
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label
                        for="email"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Email address
                    </label>
                    <div class="group">
                        <input
                            id="email"
                            v-model="resendForm.email"
                            type="email"
                            placeholder="Enter your email address"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 px-4 py-2.5 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            :class="{
                                'border-red-300 focus:border-red-400':
                                    resendForm.errors.email,
                            }"
                            required
                            autofocus
                        />
                    </div>
                    <p
                        v-if="resendForm.errors.email"
                        class="mt-1.5 text-sm text-red-500"
                    >
                        {{ resendForm.errors.email }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="resendForm.processing"
                    class="group w-full rounded-2xl bg-gray-900 px-4 py-3 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
                >
                    <span class="flex items-center justify-center gap-2">
                        <RefreshCw
                            class="h-5 w-5 transition-transform group-hover:rotate-12"
                        />
                        <span
                            v-if="resendForm.processing"
                            class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                        ></span>
                        <span v-else>Resend Reactivation Link</span>
                    </span>
                </button>
            </form>
        </div>

        <template #footer>
            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-100 bg-white/50 p-4 backdrop-blur-sm"
                >
                    <p class="text-center text-sm text-gray-500">
                        Need help?
                        <Link
                            href="/contact"
                            class="font-medium text-gray-900 transition-colors hover:text-gray-600"
                        >
                            Contact support
                        </Link>
                    </p>
                </div>

                <div class="flex items-center justify-center gap-8">
                    <Link
                        :href="login()"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 transition-all hover:text-gray-900"
                    >
                        <MoveLeft
                            class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                        />
                        Back to Sign In
                    </Link>

                    <div class="h-4 w-px bg-gray-200"></div>

                    <Link
                        :href="register()"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 transition-all hover:text-gray-900"
                    >
                        Create new account
                        <MoveRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>
                </div>
            </div>
        </template>
    </CostumedAuthLayout>
</template>
