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
import { usePermission } from '@/composables/usePermission';
import { dashboard } from '@/routes/admin';
import allergen from '@/routes/admin/allergen';
import chat from '@/routes/admin/chat';
import confirmedOrder from '@/routes/admin/confirmed-order';
import dish from '@/routes/admin/dish';
import drink from '@/routes/admin/drink';
import ingredient from '@/routes/admin/ingredient';
import meat from '@/routes/admin/meat';
import role from '@/routes/admin/role';
import user from '@/routes/admin/user';
import { type NavItem } from '@/types';
import {
    AdminPermissionEnum,
    AllergenPermissionEnum,
    ChatPermissionEnum,
    DishPermissionEnum,
    DrinkPermissionEnum,
    IngredientPermissionEnum,
    MeatPermissionEnum,
    OrderPermissionEnum,
    RolePermissionEnum,
    UserPermissionEnum,
} from '@/types/permission';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BeanOff,
    Beef,
    BookOpen,
    Carrot,
    Folder,
    LayoutGrid,
    MessageCircle,
    Salad,
    SquareMenu,
    UserRoundPen,
    UserRoundPlus,
    Wine,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const { can } = usePermission();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        permission: AdminPermissionEnum.DASHBOARD_VIEW,
    },
    {
        title: 'Users',
        href: user.index().url,
        icon: UserRoundPlus,
        permission: UserPermissionEnum.USER_VIEW,
    },
    {
        title: 'Roles & Permissions',
        href: role.index().url,
        icon: UserRoundPen,
        permission: RolePermissionEnum.ROLE_VIEW,
    },
    {
        title: 'Dishes',
        href: dish.index(),
        icon: Salad,
        permission: DishPermissionEnum.DISH_VIEW,
    },
    {
        title: 'Drinks',
        href: drink.index(),
        icon: Wine,
        permission: DrinkPermissionEnum.DRINK_VIEW,
    },
    {
        title: 'Ingredients',
        href: ingredient.index(),
        icon: Carrot,
        permission: IngredientPermissionEnum.INGREDIENT_VIEW,
    },
    {
        title: 'Meats',
        href: meat.index(),
        icon: Beef,
        permission: MeatPermissionEnum.MEAT_VIEW,
    },
    {
        title: 'Allergens',
        href: allergen.index(),
        icon: BeanOff,
        permission: AllergenPermissionEnum.ALLERGEN_VIEW,
    },
    {
        title: 'Orders',
        href: confirmedOrder.index(),
        icon: SquareMenu,
        permission: OrderPermissionEnum.ORDER_VIEW,
    },
    {
        title: 'Messages',
        href: chat.chats().url,
        icon: MessageCircle,
        permission: ChatPermissionEnum.CHAT_VIEW,
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

const visibleMainNavItems = computed(() =>
    mainNavItems.filter((item) => !item.permission || can(item.permission)),
);
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
            <NavMain :items="visibleMainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser v-if="currentUser" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
