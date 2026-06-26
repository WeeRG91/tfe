import { AllergenErrorType } from '@/types/allergen';
import { CategoryOptionType } from '@/types/category';
import { DishErrorType } from '@/types/dish';
import { IngredientErrorType, IngredientOptionType } from '@/types/ingredient';
import { MeatErrorType, MeatOptionType } from '@/types/meat';
import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { SearchResultEnum } from '@/types/search';

export interface Auth {
    user: User;
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
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    flash: FlashProps;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    loyalty_points: number;
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
};
