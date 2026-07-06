<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import CostumedAuthLayout from '@/layouts/CostumedAuthLayout.vue';
import { login, register } from '@/routes';
import { request } from '@/routes/password';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, MoveLeft, MoveRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref<boolean>(false);

const submit = () => {
    loginForm.post(login().url, {
        onSuccess: () => {
            toast.success('Logged in successfully!');
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.email) {
                toast.error(errors.email);
            } else if (errors.password) {
                toast.error(errors.password);
            } else {
                toast.error('Login failed. Please try again.');
            }
        },
    });
};
</script>

<template>
    <Head title="Log in" />

    <CostumedAuthLayout
        title="Log in to your account"
        subtitle="Enter your email and password below to log in"
        badge="Welcome Back"
    >
        <div
            v-if="status"
            class="mb-4 rounded-2xl border border-green-100 bg-green-50/50 p-4 text-center text-sm font-medium text-green-700 backdrop-blur-sm"
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
                    v-model="loginForm.email"
                    type="email"
                    placeholder="email@example.com"
                    class="w-full rounded-xl border border-gray-200 bg-white/50 px-4 py-2.5 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :class="{
                        'border-red-300 focus:border-red-400':
                            loginForm.errors.email,
                    }"
                    required
                />
                <p
                    v-if="loginForm.errors.email"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ loginForm.errors.email }}
                </p>
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    Password
                </label>
                <div class="group relative">
                    <input
                        id="password"
                        v-model="loginForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter your password"
                        class="w-full rounded-xl border border-gray-200 bg-white/50 py-2.5 pr-12 pl-4 text-sm text-gray-900 backdrop-blur-sm transition-all duration-200 placeholder:text-gray-400 focus:border-gray-400 focus:bg-white focus:ring-0 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :class="{
                            'border-red-300 focus:border-red-400':
                                loginForm.errors.password,
                        }"
                        required
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-gray-400 transition-colors hover:text-gray-600"
                        aria-label="Toggle password visibility"
                    >
                        <Eye v-if="showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p
                    v-if="loginForm.errors.password"
                    class="mt-1.5 text-sm text-red-500"
                >
                    {{ loginForm.errors.password }}
                </p>
            </div>

            <div class="flex items-center justify-between">
                <Label
                    for="remember"
                    class="flex cursor-pointer items-center space-x-3"
                >
                    <Checkbox
                        id="remember"
                        v-model="loginForm.remember"
                        :tabindex="3"
                        class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-0 focus:ring-offset-0"
                    />
                    <span class="text-sm text-gray-600">Remember me</span>
                </Label>
            </div>

            <button
                type="submit"
                :disabled="loginForm.processing"
                class="group w-full rounded-2xl bg-gray-900 px-4 py-3.5 text-sm font-medium text-white transition-all duration-200 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-900/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-gray-900 disabled:hover:shadow-none"
            >
                <span class="flex items-center justify-center gap-2">
                    <span
                        v-if="loginForm.processing"
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>
                    <span v-else>Log in</span>
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
                        :href="request()"
                        v-if="canResetPassword"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 transition-all hover:text-gray-900"
                    >
                        <MoveLeft
                            class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                        />
                        Forgot password?
                    </Link>

                    <div
                        v-if="canResetPassword"
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
