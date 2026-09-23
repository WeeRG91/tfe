<script setup lang="ts">
import ClientThemeSelector from '@/components/client/clientLayout/ClientThemeSelector.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { User } from '@/types';
import { Bell, Search, ShoppingCart } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
</script>

<template>
    <LanguageSwitcher />

    <ClientThemeSelector />

    <button
        v-if="user"
        @click="emit('open-notifications')"
        class="group relative hidden size-10 items-center justify-center rounded-lg p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground sm:flex cursor-pointer"
    >
        <Bell class="size-5" />
        <span
            v-if="unreadNotificationsCount > 0"
            class="absolute -top-1 -right-1 flex h-[18px] min-w-[18px] animate-pulse items-center justify-center rounded-full bg-primary px-1 text-xs font-bold text-primary-foreground shadow-sm"
        >
            {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
        </span>
    </button>

    <button
        v-if="user"
        @click="emit('open-cart')"
        class="group relative hidden size-10 items-center justify-center rounded-lg p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground sm:flex cursor-pointer"
    >
        <ShoppingCart class="size-5" />
        <span
            v-if="cartItemCount > 0"
            class="absolute -top-1 -right-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-primary px-1 text-xs font-bold text-primary-foreground shadow-sm"
        >
            {{ cartItemCount > 9 ? '9+' : cartItemCount }}
        </span>
    </button>

    <button
        @click="emit('open-search')"
        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:border md:border-border"
    >
        <Search class="h-5 w-5 md:h-4 md:w-4" />
        <span class="hidden lg:inline">{{ t('navigation.search') }}</span>
        <kbd
            class="ml-2 hidden rounded border border-border bg-muted px-1.5 py-0.5 text-xs text-muted-foreground lg:inline-block"
        >
            ⌘K
        </kbd>
    </button>
</template>
