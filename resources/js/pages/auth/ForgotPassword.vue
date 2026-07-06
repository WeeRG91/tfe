<script setup lang="ts">
import { login, register } from '@/routes';
import { Head, useForm, Link } from '@inertiajs/vue3';
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { toast } from 'vue-sonner';
import { request } from '@/routes/password';
import { MoveLeft, MoveRight } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const forgotForm = useForm({
    email: '',
});

const submit = () => {
    forgotForm.post(request().url, {
        onSuccess: () => {
            toast.success('Password reset link sent successfully');
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.email) {
                toast.error(errors.email);
            } else {
                toast.error('Something went wrong. Please try again.');
            }
        },
    });
};
</script>

<template>
    <Head title="Forgot password" />

    <CostumedAuthLayout
        title="Forgot password"
        subtitle="Enter your email to receive a password reset link"
        badge="Password"
    >
        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    Email address
                </label>
                <input
                    id="email"
                    v-model="forgotForm.email"
                    type="email"
                    placeholder="email@example.com"
                    class="w-full rounded-xl border border-gray-200 bg-white/50 px-4 py-2.5 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :class="{
                        'border-red-300 focus:border-red-400':
                            forgotForm.errors.email,
                    }"
                    required
                />
                <p
                    v-if="forgotForm.errors.email"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ forgotForm.errors.email }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="forgotForm.processing"
                class="group w-full rounded-2xl bg-gray-900 px-4 py-3.5 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="forgotForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>
                    <span v-else>Password reset link send</span>
                </span>
            </button>
        </form>

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

                    <div
                        class="h-4 w-px bg-gray-200"
                    ></div>

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
