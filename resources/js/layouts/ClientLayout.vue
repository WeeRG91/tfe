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
    User,
    Wine,
    X,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import order from '@/routes/order';
import { usePage } from '@inertiajs/vue3';

const cartStore = useCartStore();
const { cartItemCount } = storeToRefs(cartStore);

const openSearch = ref<boolean>(false);
const mobileMenuOpen = ref<boolean>(false);
const currentPath = ref<string>('');
const isCartOpen = ref<boolean>(false);
const isUserMenuOpen = ref<boolean>(false);

const page = usePage();
const user = page.props.auth?.user;

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

const toggleUserMenu = () => {
    isUserMenuOpen.value = !isUserMenuOpen.value;
};

const closeUserMenu = () => {
    isUserMenuOpen.value = false;
};

const handleLogout = () => {
    // Implement logout logic
    closeUserMenu();
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

const handleClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.user-menu-container')) {
        closeUserMenu();
    }
};

onMounted(async () => {
    window.addEventListener('keydown', handleSearchShortcut);
    document.addEventListener('click', handleClickOutside);
    currentPath.value = window.location.pathname;
    await cartStore.getCart();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleSearchShortcut);
    document.removeEventListener('click', handleClickOutside);
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

                        <!-- Avatar User Menu -->
                        <div class="user-menu-container relative">
                            <button
                                v-if="user"
                                @click="toggleUserMenu"
                                class="flex items-center gap-2 rounded-lg px-2 py-1 transition-all duration-200 hover:bg-gray-100"
                            >
                                <div
                                    class="relative h-8 w-8 overflow-hidden rounded-full bg-gradient-to-r from-red-500 to-red-600"
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
                                <span
                                    class="hidden text-sm font-medium text-gray-700 lg:inline"
                                >
                                    {{ user.name.split(' ')[0] }}
                                </span>
                            </button>

                            <button
                                v-else
                                class="hidden sm:flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:from-red-600 hover:to-red-700 hover:shadow-md"
                            >
                                <LogIn class="h-4 w-4" />
                                Sign in
                            </button>

                            <!-- Dropdown Menu -->
                            <Transition name="dropdown">
                                <div
                                    v-if="isUserMenuOpen && user"
                                    class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-100 bg-white py-2 shadow-lg"
                                >
                                    <div
                                        class="border-b border-gray-100 px-4 py-3"
                                    >
                                        <p
                                            class="text-sm font-semibold text-gray-900"
                                        >
                                            {{ user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ user.email }}
                                        </p>
                                    </div>
                                    <a
                                        href="#"
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

        <!-- Rest of your template remains the same -->
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
                        <!-- Mobile menu items remain the same -->
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
