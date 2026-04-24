<script setup lang="ts">
import CartDrawer from '@/components/client/cart/CartDrawer.vue';
import ClientGlobalSearchModal from '@/components/client/ClientGlobalSearchModal.vue';
import menu from '@/routes/menu';
import { useCartStore } from '@/stores/cart';
import {
    HandPlatter,
    Info,
    LogIn,
    Menu,
    NotepadText,
    Phone,
    Search,
    ShoppingCart,
    Wine,
    X,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const cartStore = useCartStore();
const { cartItemCount } = storeToRefs(cartStore);

const openSearch = ref<boolean>(false);
const mobileMenuOpen = ref<boolean>(false);
const currentPath = ref<string>('');
const isCartOpen = ref<boolean>(false);

const openCart = async () => {
    isCartOpen.value = true;
};

const closeCart = () => {
    isCartOpen.value = false;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const openModal = () => {
    openSearch.value = true;
};

const closeModal = () => {
    openSearch.value = false;
};

const handleSearchShortcut = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        openModal();
    }
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(async () => {
    window.addEventListener('keydown', handleSearchShortcut);
    currentPath.value = window.location.pathname;
    await cartStore.getCart();
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
    <div
        class="flex min-h-screen flex-col overflow-x-hidden bg-white text-gray-900"
    >
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

                    <nav class="hidden items-center gap-1 md:flex lg:gap-2">
                        <a
                            :href="menu.dish().url"
                            :class="[
                                'relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200',
                                currentPath.startsWith(menu.dish().url)
                                    ? 'bg-red-50 text-red-600'
                                    : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                            ]"
                        >
                            Menu
                            <span
                                v-if="currentPath.startsWith(menu.dish().url)"
                                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 transform rounded-full bg-red-500"
                            ></span>
                        </a>
                        <a
                            :href="menu.drink().url"
                            :class="[
                                'relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200',
                                currentPath.startsWith(menu.drink().url)
                                    ? 'bg-red-50 text-red-600'
                                    : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                            ]"
                        >
                            Drinks
                            <span
                                v-if="currentPath.startsWith(menu.drink().url)"
                                class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 transform rounded-full bg-red-500"
                            ></span>
                        </a>
                        <a
                            href="#"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            Orders
                        </a>
                        <a
                            href="#"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            About
                        </a>
                        <a
                            href="#"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            Contact
                        </a>
                    </nav>

                    <div class="flex items-center gap-3">
                        <button
                            @click="openCart()"
                            class="group relative flex items-center gap-2 rounded-lg px-3 py-2 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <ShoppingCart class="h-5 w-5" />
                            <span
                                v-if="cartItemCount > 0"
                                class="absolute -top-1 -right-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white shadow-sm"
                            >
                                {{ cartItemCount > 9 ? '9+' : cartItemCount }}
                            </span>
                        </button>

                        <button
                            @click="openModal()"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-600 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 md:border"
                        >
                            <Search class="h-5 w-5 md:h-4 md:w-4" />
                            <span class="hidden lg:inline">Search</span>
                            <kbd
                                class="ml-2 hidden rounded border bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600 lg:inline-block"
                            >
                                ⌘K
                            </kbd>
                        </button>

                        <button
                            class="hidden items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:from-red-600 hover:to-red-700 hover:shadow-md md:flex"
                        >
                            <LogIn class="h-4 w-4" />
                            Sign in
                        </button>

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

        <Transition name="fade">
            <div
                v-if="mobileMenuOpen"
                @click="closeMobileMenu"
                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
            ></div>
        </Transition>

        <Transition name="slide">
            <div
                v-if="mobileMenuOpen"
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
                            @click="closeMobileMenu"
                            class="rounded-full p-2 text-gray-400 transition-all duration-200 hover:bg-gray-100 hover:text-gray-600"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                        <a
                            @click="closeMobileMenu"
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
                                        : 'bg-gray-100 group-hover:bg-red-100',
                                ]"
                            >
                                <HandPlatter class="h-5 w-5" />
                            </div>
                            <span class="font-medium">Menu</span>
                        </a>

                        <a
                            @click="closeMobileMenu"
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
                                        : 'bg-gray-100 group-hover:bg-red-100',
                                ]"
                            >
                                <Wine class="h-5 w-5" />
                            </div>
                            <span class="font-medium">Drinks</span>
                        </a>

                        <a
                            @click="closeMobileMenu"
                            href="#"
                            class="flex items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <div
                                class="rounded-lg bg-gray-100 p-2 group-hover:bg-red-100"
                            >
                                <NotepadText class="h-5 w-5" />
                            </div>
                            <span class="font-medium">Orders</span>
                        </a>

                        <a
                            @click="closeMobileMenu"
                            href="#"
                            class="flex items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <div
                                class="rounded-lg bg-gray-100 p-2 group-hover:bg-red-100"
                            >
                                <Info class="h-5 w-5" />
                            </div>
                            <span class="font-medium">About</span>
                        </a>

                        <a
                            @click="closeMobileMenu"
                            href="#"
                            class="flex items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <div
                                class="rounded-lg bg-gray-100 p-2 group-hover:bg-red-100"
                            >
                                <Phone class="h-5 w-5" />
                            </div>
                            <span class="font-medium">Contact</span>
                        </a>

                        <div class="mt-4 border-t border-gray-100 pt-4">
                            <a
                                @click="closeMobileMenu"
                                href="#"
                                class="flex items-center gap-4 rounded-xl px-4 py-3 text-gray-700 transition-all duration-200 hover:bg-red-50 hover:text-red-600"
                            >
                                <div
                                    class="relative rounded-lg bg-gray-100 p-2 group-hover:bg-red-100"
                                >
                                    <ShoppingCart class="h-5 w-5" />
                                    <span
                                        v-if="cartItemCount > 0"
                                        class="absolute -top-1 -right-1 flex h-[16px] min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                                    >
                                        {{
                                            cartItemCount > 9
                                                ? '9+'
                                                : cartItemCount
                                        }}
                                    </span>
                                </div>
                                <span class="font-medium">Cart</span>
                            </a>
                        </div>
                    </nav>

                    <div class="border-t border-gray-100 p-6">
                        <button
                            @click="closeMobileMenu"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-4 py-3 font-medium text-white shadow-sm transition-all duration-200 hover:from-red-600 hover:to-red-700"
                        >
                            <LogIn class="h-5 w-5" />
                            Sign in
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="relative flex-1 overflow-x-hidden">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div
                    class="absolute -top-24 -right-24 h-110 w-110 rounded-full bg-red-300/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-24 -left-24 h-110 w-110 rounded-full bg-yellow-200/20 blur-3xl"
                ></div>
            </div>

            <main class="relative">
                <slot />
            </main>
        </div>

        <CartDrawer :open="isCartOpen" :onClose="closeCart" />
        <ClientGlobalSearchModal :open="openSearch" :onClose="closeModal" />
    </div>
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
