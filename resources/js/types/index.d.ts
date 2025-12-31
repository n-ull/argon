import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { VNode } from 'vue';

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
    group?: string;
    badge?: VNode | string | null;
    extra?: VNode | null;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    flash: {
        message: {
            summary: string;
            detail: string;
            type: 'success' | 'error' | 'warning' | 'info';
        };
        data?: any;
        error?: any;
    };
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

export type EventStatus = 'draft' | 'published' | 'ended' | 'cancelled' | 'deleted' | 'archived';

export interface Event {
    id: number;
    title: string;
    description: string | null;
    start_date: string;
    end_date: string | null;
    location_info: Location;
    products: Product[] | null;
    slug: string;
    organizer: Organizer;
    status?: EventStatus | null;
    vertical_image_url: string | null;
    horizontal_image_url: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;
    organizer_id?: number;
    products_count?: number | null;
    orders_count?: number | null;
    taxes_and_fees: string[];
    widget_stats?: WidgetStats;
}

export interface WidgetStats {
    completed_orders_count: number;
    total_revenue: number;
    scanned_tickets_count: number;
    products_sold_count: number;
    courtesy_tickets_count: number;
    unique_visitors: number;
}

export interface Location {
    site: string | null;
    mapLink: string | null;
    city: string | null;
    country: string | null;
    address: string | null;
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
    start_sale_date?: string | null;
    end_sale_date?: string | null;
    is_hidden?: boolean;
    hide_when_sold_out?: boolean;
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;
    hide_before_sale_start_date?: boolean;
    hide_after_sale_end_date?: boolean;
    show_stock?: boolean;
}

export interface ProductPrice {
    id: number;
    label: string;
    price: number;
    sort_order: number;
    stock: number | null;
    sales_start_date: string | null;
    sales_end_date: string | null;
    sales_start_date_diff: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;
    is_sold_out?: boolean;
}

export interface Organizer {
    id: number;
    owner_id: number;
    name: string;
    email: string | null;
    phone: string | null;
    logo: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    events?: Event[];
    settings?: OrganizerSettings | null;
}

export interface OrganizerSettings {
    id: number;
    organizer_id: number;
    raise_money_method: string;
    raise_money_account: string | null;
    is_modo_active: boolean;
    is_mercadopago_active: boolean;
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;
}

export interface Order {
    id: number;
    reference_id: string;
    subtotal: number;
    created_at?: string | null;
    updated_at?: string | null;
    expires_at?: string | null;
    paid_at?: string | null;
    deleted_at?: string | null;
    items_snapshot: OrderItem[];
    fees_snapshot: any;
    fees_total: number;
    status: string;
    taxes_snapshot: any;
    taxes_total: number;
    event?: Event | null;
    settings?: OrganizerSettings | null;
    client: Client;
    used_payment_gateway_snapshot: string;
    total_gross: number;
    total: number;
}

export interface Client {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;
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

export type Cooperator = {
    id: number;
    name: string;
    email: string;
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;
}