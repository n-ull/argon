import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

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

export interface Event {
    id: number;
    title: string;
    description: string | null;
    start_date: string;
    end_date: string | null;
    location: string | null;
    products: Product[] | null;
}

export interface Product {
    id: number;
    name: string;
    description: string | null;
    product_price_type: string;
    product_type: string;
    max_per_order: number | null;
    min_per_order: number;
    product_prices: ProductPrice[];
}

export interface ProductPrice {
    id: number;
    label: string;
    price: number;
    sort_order: number;
}

export interface Organizer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    logo: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    events?: Event[];
    events_count?: number | null;
    settings?: OrganizerSettings | null;
}

export interface OrganizerSettings {
    id: number;
    organizer_id: number;
    raise_money_method: string;
    raise_money_account: string | null;
    is_modo_active: boolean;
    is_mercadopago_active: boolean;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

export type BreadcrumbItemType = BreadcrumbItem;
