<script setup lang="ts">
import { useClickOutside } from '@/composables/useClickOutside';
import order from '@/routes/order';
import { LogIn, NotepadText, User } from 'lucide-vue-next';
import { ref } from 'vue';
import loyaltyPointTransaction from '@/routes/loyalty-point-transaction';
import { Link, router } from '@inertiajs/vue3';
import { login, logout } from '@/routes';
import clientProfile from '@/routes/client-profile';
import type { User as UserType } from '@/types/index';

defineProps<{
    user: UserType | null;
}>();

const isUserMenuOpen = ref<boolean>(false);
const menuContainerRef = ref<HTMLElement | null>(null);

const toggleUserMenu = () => {
    isUserMenuOpen.value = !isUserMenuOpen.value;
};

const closeUserMenu = () => {
    isUserMenuOpen.value = false;
};

const handleLogout = () => {
    closeUserMenu();
    router.visit(logout());
};

useClickOutside(menuContainerRef, () => {
    if (isUserMenuOpen.value) {
        closeUserMenu();
    }
});
</script>

<template>
    <div
        ref="menuContainerRef"
        class="user-menu-container hidden md:relative md:block"
    >
        <button
            v-if="user"
            @click="toggleUserMenu"
            class="flex items-center gap-2 rounded-lg px-2 py-1 transition-all duration-200 hover:bg-gray-100"
        >
            <div
                class="relative h-9 w-9 overflow-hidden rounded-full bg-gradient-to-r from-red-500 to-red-600"
            >
                <img
                    v-if="user.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="h-full w-full object-cover"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center text-white"
                >
                    <User class="h-4 w-4" />
                </div>
            </div>
        </button>

        <Link
            v-else
            :href="login()"
            class="hidden items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:from-red-600 hover:to-red-700 hover:shadow-md sm:flex"
        >
            <LogIn class="h-4 w-4" />
            Sign in
        </Link>

        <Transition name="dropdown">
            <div
                v-if="isUserMenuOpen && user"
                class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-100 bg-white py-2 shadow-lg"
            >
                <div class="border-b border-gray-100 px-4 py-3">
                    <p class="text-sm font-semibold text-gray-900">
                        {{ user.name }}
                    </p>
                    <p class="text-xs text-gray-500">{{ user.email }}</p>
                </div>
                <a
                    :href="clientProfile.edit().url"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600"
                >
                    <User class="h-4 w-4" />
                    Profile
                </a>
                <a
                    :href="order.myOrders().url"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600"
                >
                    <NotepadText class="h-4 w-4" />
                    My Orders
                </a>
                <a
                    :href="loyaltyPointTransaction.myPoints().url"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600"
                >
                    <NotepadText class="h-4 w-4" />
                    My Points
                </a>
                <button
                    @click="handleLogout"
                    class="flex w-full items-center gap-3 px-4 py-2 text-sm text-red-600 transition-colors hover:bg-red-50"
                >
                    <LogIn class="h-4 w-4" />
                    Sign Out
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
