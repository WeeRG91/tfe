<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import allergen from '@/routes/allergen';
import dish from '@/routes/dish';
import ingredient from '@/routes/ingredient';
import trashed from '@/routes/trashed';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BeanOff,
    BookOpen,
    Wine,
    Carrot,
    Folder,
    LayoutGrid,
    Salad,
    Trash,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import drink from '@/routes/drink';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Dishes',
        href: dish.index(),
        icon: Salad,
    },
    {
        title: 'Drinks',
        href: drink.index(),
        icon: Wine,
    },
    {
        title: 'Ingredients',
        href: ingredient.index(),
        icon: Carrot,
    },
    {
        title: 'Allergens',
        href: allergen.index(),
        icon: BeanOff,
    },
    {
        title: 'Trashed',
        href: trashed.index(),
        icon: Trash,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
