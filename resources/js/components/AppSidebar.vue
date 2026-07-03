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
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BeanOff,
    BookOpen,
    Beef,
    Wine,
    Carrot,
    Folder,
    LayoutGrid,
    Salad,
    SquareMenu,
    MessageCircle,
    UserRoundPen,
    UserRoundPlus
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { dashboard } from '@/routes/admin';
import dish from '@/routes/admin/dish';
import drink from '@/routes/admin/drink';
import ingredient from '@/routes/admin/ingredient';
import meat from '@/routes/admin/meat';
import allergen from '@/routes/admin/allergen';
import confirmedOrder from '@/routes/admin/confirmed-order';
import chat from '@/routes/admin/chat';
import role from '@/routes/admin/role';
import user from '@/routes/admin/user';

const page = usePage();
const currentUser = page.props.auth?.user;

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: user.index().url,
        icon: UserRoundPlus,
    },
    {
        title: 'Roles & Permissions',
        href: role.index().url,
        icon: UserRoundPen,
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
        title: 'Meats',
        href: meat.index(),
        icon: Beef,
    },
    {
        title: 'Allergens',
        href: allergen.index(),
        icon: BeanOff,
    },
    {
        title: 'Orders',
        href: confirmedOrder.index(),
        icon: SquareMenu,
    },
    {
        title: 'Messages',
        href: chat.chats().url,
        icon: MessageCircle,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'My restaurant',
        href: 'http://127.0.0.1:8000',
        icon: BookOpen,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: Folder,
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
            <NavUser v-if="currentUser" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
