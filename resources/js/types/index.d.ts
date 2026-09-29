import { AllergenErrorType } from '@/types/allergen';
import { CategoryOptionType } from '@/types/category';
import { DishErrorType } from '@/types/dish';
import { IngredientErrorType, IngredientOptionType } from '@/types/ingredient';
import type { LocaleMetadata, LocaleType } from '@/types/locale';
import { MeatErrorType, MeatOptionType } from '@/types/meat';
import {
    AllergenPermissionEnum,
    DishPermissionEnum,
    DrinkPermissionEnum,
    IngredientPermissionEnum,
    MeatPermissionEnum,
    RolePermissionEnum,
    UserPermissionEnum,
} from '@/types/permission';
import { SearchResultEnum } from '@/types/search';
import {
    ThemeDefinition,
    ThemeKey,
    ThemeSelection,
    ThemeSurface,
} from '@/types/theme';
import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User | null;
    roles: string[];
    permissions: string[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    permission?: string;
}

export interface ThemePageProps {
    surface: ThemeSurface;
    selection: ThemeSelection;
    restaurantDefaultKey: ThemeKey;
    customThemes: ThemeDefinition[];
}

export interface RestaurantDetails {
    name: string;
    contactEmail: string;
    contactPhone: string;
    address: {
        street: string;
        postalCode: string;
        city: string;
        country: string;
    };
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    restaurant: RestaurantDetails;
    theme: ThemePageProps;
    features: {
        companyDelivery: boolean;
    };
    auth: Auth;
    locale: LocaleType;
    fallbackLocale: LocaleType;
    availableLocales: Record<LocaleType, LocaleMetadata>;
    sidebarOpen: boolean;
    flash: FlashProps;
    [key: string]: unknown;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type FlashProps = {
    createdIngredient?: IngredientOptionType;
    createdMeat?: MeatOptionType;
};

export type BreadcrumbItemType = BreadcrumbItem;

export type Paginated<T> = {
    data: T[];
    links: Links;
    meta: Meta;
};

export type Meta = {
    current_page: number;
    from: number;
    last_page: number;
    links: Link[];
    path: string;
    per_page: number;
    to: number;
    total: number;
};

export type Links = {
    first: string;
    last: string;
    next: string;
    prev: string;
};

export type Link = {
    active: boolean;
    label: string;
    page: number;
    url: string;
};

export type CursorPaginated<T> = {
    data: T[];
    path: string;
    per_page: number;
    next_cursor: string | null;
    next_page_url: string | null;
    prev_cursor: string | null;
    prev_page_url: string | null;
};

export type ErrorType = {
    dishErrors?: DishErrorType;
    meatErrors?: MeatErrorType;
    ingredientErrors?: IngredientErrorType;
    allergenErrors: AllergenErrorType;
};

export type FilterType = 'all' | 'available' | 'unavailable' | 'deleted';

export type GlobalSearchType = {
    dish: SearchType[];
    drink: SearchType[];
    ingredient: SearchType[];
    meat: SearchType[];
    allergen: SearchType[];
    user: SearchType[];
    role: SearchType[];
};

export type SearchType = {
    type: string;
    id: number;
    name: string;
    category: CategoryOptionType;
    main_image: string;
};

export type SearchResultType = {
    key: SearchResultEnum;
    label: string;
    permission?:
        | AllergenPermissionEnum
        | DishPermissionEnum
        | DrinkPermissionEnum
        | IngredientPermissionEnum
        | MeatPermissionEnum
        | RolePermissionEnum
        | UserPermissionEnum;
};
