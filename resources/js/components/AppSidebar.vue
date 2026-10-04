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
import deliveryCompanies from '@/routes/admin/delivery-companies';
import dish from '@/routes/admin/dish';
import drink from '@/routes/admin/drink';
import ingredient from '@/routes/admin/ingredient';
import meat from '@/routes/admin/meat';
import restaurantSchedule from '@/routes/admin/restaurant-schedule';
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
    Building2,
    CalendarClock,
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
import { useI18n } from 'vue-i18n';
import AppLogo from './AppLogo.vue';

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const { can } = usePermission();

const { t } = useI18n();

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: t('layout.navigation.dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
        permission: AdminPermissionEnum.DASHBOARD_VIEW,
    },
    {
        title: t('layout.navigation.users'),
        href: user.index().url,
        icon: UserRoundPlus,
        permission: UserPermissionEnum.USER_VIEW,
    },
    {
        title: t('layout.navigation.rolesPermissions'),
        href: role.index().url,
        icon: UserRoundPen,
        permission: RolePermissionEnum.ROLE_VIEW,
    },
    {
        title: t('layout.navigation.dishes'),
        href: dish.index(),
        icon: Salad,
        permission: DishPermissionEnum.DISH_VIEW,
    },
    {
        title: t('layout.navigation.drinks'),
        href: drink.index(),
        icon: Wine,
        permission: DrinkPermissionEnum.DRINK_VIEW,
    },
    {
        title: t('layout.navigation.ingredients'),
        href: ingredient.index(),
        icon: Carrot,
        permission: IngredientPermissionEnum.INGREDIENT_VIEW,
    },
    {
        title: t('layout.navigation.meats'),
        href: meat.index(),
        icon: Beef,
        permission: MeatPermissionEnum.MEAT_VIEW,
    },
    {
        title: t('layout.navigation.allergens'),
        href: allergen.index(),
        icon: BeanOff,
        permission: AllergenPermissionEnum.ALLERGEN_VIEW,
    },
    {
        title: t('layout.navigation.orders'),
        href: confirmedOrder.index(),
        icon: SquareMenu,
        permission: OrderPermissionEnum.ORDER_VIEW,
    },
    {
        title: t('layout.navigation.messages'),
        href: chat.chats().url,
        icon: MessageCircle,
        permission: ChatPermissionEnum.CHAT_VIEW,
    },
    {
        title: t('restaurantSchedule.title'),
        href: restaurantSchedule.index(),
        icon: CalendarClock,
        permission: AdminPermissionEnum.DASHBOARD_VIEW,
    },
    ...(page.props.features.companyDelivery
        ? [
              {
                  title: t('deliveryCompanies.title'),
                  href: deliveryCompanies.index(),
                  icon: Building2,
                  permission: AdminPermissionEnum.DASHBOARD_VIEW,
              },
          ]
        : []),
]);

const footerNavItems = computed<NavItem[]>(() => [
    {
        title: t('layout.navigation.myRestaurant'),
        href: '/',
        icon: BookOpen,
    },
    {
        title: t('layout.navigation.documentation'),
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: Folder,
    },
]);

const visibleMainNavItems = computed(() =>
    mainNavItems.value.filter(
        (item) => !item.permission || can(item.permission),
    ),
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
