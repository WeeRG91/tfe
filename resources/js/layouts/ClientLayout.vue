<script setup lang="ts">
import ClientGlobalSearchModal from '@/components/client/ClientGlobalSearchModal.vue';
import menu from '@/routes/menu';
import {
    HandPlatter,
    Info,
    LogIn,
    Menu,
    NotepadText,
    Phone,
    Search,
    Wine,
    X,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const openSearch = ref<boolean>(false);
const mobileMenuOpen = ref<boolean>(false);
const currentPath = ref<string>('');

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

onMounted(() => {
    window.addEventListener('keydown', handleSearchShortcut);
    currentPath.value = window.location.pathname;
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleSearchShortcut);
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col overflow-x-hidden bg-white text-gray-900"
    >
        <header class="flex items-center justify-between border-b px-8 py-3">
            <div class="text-xl font-bold text-red-500">
                <a href="/">Restaurant</a>
            </div>

            <nav class="hidden gap-6 text-sm text-gray-600 md:flex">
                <a
                    :href="menu.dish().url"
                    :class="[
                        'text-sm',
                        currentPath.startsWith(menu.dish().url)
                            ? 'font-semibold text-red-500'
                            : 'text-gray-600',
                    ]"
                >
                    Menu
                </a>
                <a
                    :href="menu.drink().url"
                    :class="[
                        'text-sm',
                        currentPath.startsWith(menu.drink().url)
                            ? 'font-semibold text-red-500'
                            : 'text-gray-600',
                    ]"
                    >Drinks</a
                >
                <a href="#">Orders</a>
                <a href="#">About</a>
                <a href="#">Contact</a>
            </nav>

            <div class="flex items-center gap-4">
                <button class="hidden text-sm text-gray-600 md:block">
                    Sign in
                </button>
                <button
                    @click="openModal()"
                    class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-muted-foreground transition hover:bg-muted md:shadow-md"
                >
                    <Search class="h-4 w-4" />
                    <span class="hidden md:inline">Search</span>

                    <kbd
                        class="ml-4 hidden rounded border bg-muted px-1.5 py-0.5 text-xs md:inline-block"
                    >
                        Ctrl K
                    </kbd>
                </button>

                <button
                    @click="toggleMobileMenu"
                    class="flex items-center justify-center rounded-md p-2 transition-colors hover:bg-gray-100 md:hidden"
                >
                    <Menu class="h-5 w-5" />
                </button>
            </div>
        </header>

        <Transition name="fade">
            <div
                v-if="mobileMenuOpen"
                @click="closeMobileMenu"
                class="fixed inset-0 z-40 bg-black/25 backdrop-blur-sm"
            ></div>
        </Transition>

        <Transition name="slide">
            <div
                v-if="mobileMenuOpen"
                class="fixed top-0 right-0 z-50 h-full w-full max-w-xs bg-white shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 p-6"
                >
                    <div class="text-2xl font-bold text-red-500">
                        <a href="/" @click="closeMobileMenu">Restaurant</a>
                    </div>
                    <button
                        @click="closeMobileMenu"
                        class="rounded-full p-2 transition-colors hover:bg-gray-100"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <nav class="flex flex-col gap-1 p-4">
                    <a
                        @click="closeMobileMenu"
                        :href="menu.dish().url"
                        :class="[
                            'group flex items-center gap-4 rounded-lg px-4 py-3 transition-colors',
                            currentPath.startsWith(menu.dish().url)
                                ? 'bg-red-50 text-red-600'
                                : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                        ]"
                    >
                        <div
                            :class="[
                                'rounded-lg p-2',
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
                            'group flex items-center gap-4 rounded-lg px-4 py-3 transition-colors',
                            currentPath.startsWith(menu.drink().url)
                                ? 'bg-red-50 text-red-600'
                                : 'text-gray-700 hover:bg-red-50 hover:text-red-600',
                        ]"
                    >
                        <div
                            :class="[
                                'rounded-lg p-2',
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
                        class="group flex items-center gap-4 rounded-lg px-4 py-3 text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600"
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
                        class="group flex items-center gap-4 rounded-lg px-4 py-3 text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600"
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
                        class="group flex items-center gap-4 rounded-lg px-4 py-3 text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600"
                    >
                        <div
                            class="rounded-lg bg-gray-100 p-2 group-hover:bg-red-100"
                        >
                            <Phone class="h-5 w-5" />
                        </div>
                        <span class="font-medium">Contact</span>
                    </a>
                </nav>

                <div
                    class="absolute right-0 bottom-0 left-0 border-t border-gray-100 p-6"
                >
                    <button
                        @click="closeMobileMenu"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-500 px-4 py-3 font-medium text-white transition-colors hover:bg-red-600"
                    >
                        <LogIn class="h-5 w-5" />
                        Sign in
                    </button>
                </div>
            </div>
        </Transition>

        <div class="relative max-w-screen flex-1 overflow-x-hidden">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div
                    class="absolute -top-24 -right-24 h-110 w-110 rounded-full bg-red-300/25 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-24 -left-24 h-110 w-110 rounded-full bg-yellow-200/25 blur-3xl"
                ></div>
            </div>

            <slot />
        </div>

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
