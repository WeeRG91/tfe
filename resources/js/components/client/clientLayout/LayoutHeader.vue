<script setup lang="ts">
import DesktopNav from '@/components/client/clientLayout/DesktopNav.vue';
import RestaurantLogo from '@/components/RestaurantLogo.vue';
import { User } from '@/types';
import { Menu } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import HeaderActions from './HeaderActions.vue';
import MobileMenu from './MobileMenu.vue';
import UserMenu from './UserMenu.vue';

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
        class="sticky top-0 z-30 border-b border-border bg-background/95 shadow-sm backdrop-blur-sm"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <RestaurantLogo size="lg" />
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
                        class="flex items-center justify-center rounded-lg p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:hidden"
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
