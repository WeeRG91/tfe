<script setup lang="ts">
import menu from '@/routes/menu';
import order from '@/routes/order';
import {
    Bell,
    HandPlatter,
    Info,
    LogIn,
    LogOut,
    NotepadText,
    Phone,
    ShoppingCart,
    User,
    Wine,
    X,
} from 'lucide-vue-next';
import cart from '@/routes/cart';
import notification from '@/routes/notification';
import { router } from '@inertiajs/vue3';
import { login, logout } from '@/routes';
import clientProfile from '@/routes/client-profile';
import loyaltyPointTransaction from '@/routes/loyalty-point-transaction';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    open: boolean;
    user: any | null;
    currentPath: string;
    cartItemCount: number;
    unreadNotificationsCount: number;
}>();

const emit = defineEmits<{
    close: [];
}>();

const handleLogin = () => {
    emit('close');
    router.visit(login());
};

const handleLogout = () => {
    emit('close');
    router.visit(logout());
};
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                @click="emit('close')"
                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
            ></div>
        </Transition>

        <Transition name="slide">
            <div
                v-if="open"
                class="fixed top-0 right-0 z-50 h-full w-full max-w-xs bg-white shadow-2xl"
            >
                <div class="flex h-full flex-col">
                    <div
                        class="flex items-center justify-between border-b border-gray-100 p-6"
                    >
                        <div
                            class="bg-gradient-to-r from-red-500 to-red-600 bg-clip-text text-2xl font-bold text-transparent"
                        >
                            Restaurant
                        </div>
                        <button
                            @click="emit('close')"
                            class="rounded-full p-2 text-gray-400 transition-all duration-200 hover:bg-gray-100 hover:text-gray-600"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div v-if="user" class="border-b border-gray-100 p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="relative h-12 w-12 overflow-hidden rounded-full bg-gradient-to-r from-red-500 to-red-600"
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
                                    <User class="h-6 w-6" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ user.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                        <div v-if="user" class="mt-4 space-y-1">
                            <a
                                @click="emit('close')"
                                :href="clientProfile.edit().url"
                                class="flex items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                            >
                                <div class="rounded-lg bg-gray-100 p-2">
                                    <User class="h-5 w-5" />
                                </div>
                                <span class="font-medium">{{
                                    t('navigation.userMenu.profile')
                                }}</span>
                            </a>

                            <a
                                @click="emit('close')"
                                :href="order.myOrders().url"
                                :class="[
                                    'flex items-center gap-4 rounded-xl px-4 py-3 transition-all duration-200',
                                    currentPath.startsWith(order.myOrders().url)
                                        ? 'bg-red-50 text-red-600 shadow-sm'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                                ]"
                            >
                                <div class="rounded-lg bg-gray-100 p-2">
                                    <NotepadText class="h-5 w-5" />
                                </div>
                                <span class="font-medium">{{
                                    t('navigation.userMenu.orders')
                                }}</span>
                            </a>
                            <a
                                @click="emit('close')"
                                :href="loyaltyPointTransaction.myPoints().url"
                                :class="[
                                    'flex items-center gap-4 rounded-xl px-4 py-3 transition-all duration-200',
                                    currentPath.startsWith(order.myOrders().url)
                                        ? 'bg-red-50 text-red-600 shadow-sm'
                                        : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                                ]"
                            >
                                <div class="rounded-lg bg-gray-100 p-2">
                                    <NotepadText class="h-5 w-5" />
                                </div>
                                <span class="font-medium">{{
                                    t('navigation.userMenu.points')
                                }}</span>
                            </a>
                        </div>

                        <a
                            :href="cart.checkout().url"
                            class="flex cursor-pointer items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <div class="relative rounded-lg bg-gray-100 p-2">
                                <ShoppingCart class="h-5 w-5" />
                                <span
                                    v-if="cartItemCount > 0"
                                    class="absolute -top-1 -right-1 flex h-[16px] min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                                >
                                    {{
                                        cartItemCount > 9 ? '9+' : cartItemCount
                                    }}
                                </span>
                            </div>
                            <span class="font-medium">{{
                                t('navigation.cart')
                            }}</span>
                        </a>

                        <a
                            v-if="user"
                            :href="notification.myNotifications().url"
                            class="flex cursor-pointer items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <div class="relative rounded-lg bg-gray-100 p-2">
                                <Bell class="h-5 w-5" />
                                <span
                                    v-if="unreadNotificationsCount > 0"
                                    class="absolute -top-1 -right-1 flex h-[16px] min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                                >
                                    {{
                                        unreadNotificationsCount > 9
                                            ? '9+'
                                            : unreadNotificationsCount
                                    }}
                                </span>
                            </div>
                            <span class="font-medium">{{
                                t('navigation.userMenu.notifications')
                            }}</span>
                        </a>

                        <a
                            @click="emit('close')"
                            :href="menu.dish().url"
                            :class="[
                                'flex items-center gap-4 rounded-xl px-4 py-3 transition-all duration-200',
                                currentPath.startsWith(menu.dish().url)
                                    ? 'bg-red-50 text-red-600 shadow-sm'
                                    : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                            ]"
                        >
                            <div
                                :class="[
                                    'rounded-lg p-2 transition-all duration-200',
                                    currentPath.startsWith(menu.dish().url)
                                        ? 'bg-red-100'
                                        : 'bg-gray-100',
                                ]"
                            >
                                <HandPlatter class="h-5 w-5" />
                            </div>
                            <span class="font-medium">{{
                                t('navigation.menu')
                            }}</span>
                        </a>

                        <a
                            @click="emit('close')"
                            :href="menu.drink().url"
                            :class="[
                                'flex items-center gap-4 rounded-xl px-4 py-3 transition-all duration-200',
                                currentPath.startsWith(menu.drink().url)
                                    ? 'bg-red-50 text-red-600 shadow-sm'
                                    : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                            ]"
                        >
                            <div
                                :class="[
                                    'rounded-lg p-2 transition-all duration-200',
                                    currentPath.startsWith(menu.drink().url)
                                        ? 'bg-red-100'
                                        : 'bg-gray-100',
                                ]"
                            >
                                <Wine class="h-5 w-5" />
                            </div>
                            <span class="font-medium">{{
                                t('navigation.drinks')
                            }}</span>
                        </a>

                        <a
                            @click="emit('close')"
                            href="#"
                            class="flex items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <div class="rounded-lg bg-gray-100 p-2">
                                <Info class="h-5 w-5" />
                            </div>
                            <span class="font-medium">{{
                                t('navigation.about')
                            }}</span>
                        </a>

                        <a
                            @click="emit('close')"
                            href="#"
                            class="flex items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <div class="rounded-lg bg-gray-100 p-2">
                                <Phone class="h-5 w-5" />
                            </div>
                            <span class="font-medium">{{
                                t('navigation.contact')
                            }}</span>
                        </a>
                    </nav>

                    <div class="border-t border-gray-100 p-6">
                        <button
                            v-if="!user"
                            @click="handleLogin"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-4 py-3 font-medium text-white shadow-sm transition-all duration-200 hover:from-red-600 hover:to-red-700"
                        >
                            <LogIn class="h-5 w-5" />
                            {{ t('navigation.userMenu.signIn') }}
                        </button>
                        <button
                            v-else
                            @click="handleLogout"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-3 font-medium text-red-600 shadow-sm transition-all duration-200 hover:bg-red-50"
                        >
                            <LogOut class="h-5 w-5" />
                            {{ t('navigation.userMenu.signOut') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}
</style>
