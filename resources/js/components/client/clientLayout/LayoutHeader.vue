<script setup lang="ts">
import DesktopNav from '@/components/client/clientLayout/DesktopNav.vue';
import { Menu } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import HeaderActions from './HeaderActions.vue';
import MobileMenu from './MobileMenu.vue';
import UserMenu from './UserMenu.vue';
import { User } from '@/types';

defineProps<{
    user: User | null;
    cartItemCount: number;
    unreadNotificationsCount: number;
}>();

const emit = defineEmits<{
    'open-cart': [];
    'open-search': [];
    'open-notifications': [];
}>();

const mobileMenuOpen = ref<boolean>(false);
const currentPath = ref<string>('');

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleSearchShortcut = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        emit('open-search');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleSearchShortcut);
    currentPath.value = window.location.pathname;
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleSearchShortcut);
});

watch(mobileMenuOpen, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <header
        class="sticky top-0 z-30 border-b bg-white/95 shadow-sm backdrop-blur-sm"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a
                        href="/"
                        @click="scrollToTop"
                        class="bg-gradient-to-r from-red-500 to-red-600 bg-clip-text text-2xl font-bold text-transparent transition-all hover:from-red-600 hover:to-red-700"
                    >
                        Restaurant
                    </a>
                </div>

                <DesktopNav :current-path="currentPath" />

                <div class="flex items-center gap-3">
                    <HeaderActions
                        :user="user"
                        :cart-item-count="cartItemCount"
                        :unread-notifications-count="unreadNotificationsCount"
                        @open-cart="emit('open-cart')"
                        @open-search="emit('open-search')"
                        @open-notifications="emit('open-notifications')"
                    />

                    <UserMenu :user="user" />

                    <button
                        @click="toggleMobileMenu"
                        class="flex items-center justify-center rounded-lg p-2 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600 md:hidden"
                    >
                        <Menu class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </header>

    <MobileMenu
        :open="mobileMenuOpen"
        :user="user"
        :current-path="currentPath"
        :cart-item-count="cartItemCount"
        :unread-notifications-count="unreadNotificationsCount"
        @close="closeMobileMenu"
    />
</template>
