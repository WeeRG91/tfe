<script setup lang="ts">
import { useClickOutside } from '@/composables/useClickOutside';
import { login, logout } from '@/routes';
import clientProfile from '@/routes/client-profile';
import loyaltyPointTransaction from '@/routes/loyalty-point-transaction';
import order from '@/routes/order';
import type { User as UserType } from '@/types/index';
import { Link, router } from '@inertiajs/vue3';
import { LogIn, NotepadText, Trophy, User } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
            class="flex items-center gap-2 rounded-lg px-2 py-1 transition duration-300 hover:scale-110 cursor-pointer"
        >
            <div
                class="relative h-9 w-9 overflow-hidden rounded-full bg-primary"
            >
                <img
                    v-if="user.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="h-full w-full object-cover"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center text-primary-foreground"
                >
                    <User class="h-4 w-4" />
                </div>
            </div>
        </button>

        <Link
            v-else
            :href="login()"
            class="hidden items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 sm:flex"
        >
            <LogIn class="h-4 w-4" />
            {{ t('navigation.userMenu.signIn') }}
        </Link>

        <Transition name="dropdown">
            <div
                v-if="isUserMenuOpen && user"
                class="absolute right-0 mt-2 w-56 rounded-xl border border-border bg-popover p-2 text-popover-foreground shadow-lg"
            >
                <div class="border-b border-border px-4 py-3">
                    <p class="text-sm font-semibold text-popover-foreground">
                        {{ user.name }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ user.email }}
                    </p>
                </div>
                <a
                    :href="clientProfile.edit().url"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                >
                    <User class="h-4 w-4" />
                    {{ t('navigation.userMenu.profile') }}
                </a>
                <a
                    :href="order.myOrders().url"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                >
                    <NotepadText class="h-4 w-4" />
                    {{ t('navigation.userMenu.orders') }}
                </a>
                <a
                    :href="loyaltyPointTransaction.myPoints().url"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                >
                    <Trophy class="h-4 w-4" />
                    {{ t('navigation.userMenu.points') }}
                </a>
                <button
                    @click="handleLogout"
                    class="flex w-full items-center gap-3 px-4 py-2 text-sm text-destructive transition-colors hover:bg-destructive/10"
                >
                    <LogIn class="h-4 w-4" />
                    {{ t('navigation.userMenu.signOut') }}
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
