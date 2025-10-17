declare module '@/routes' {
    // Public Routes
    export function home(parameters?: any): string;
    export function waitlist_store(parameters?: any): string;

    // Admin Routes
    export function admin_dashboard(parameters?: any): string;
    export function admin_products_index(parameters?: any): string;
    export function admin_products_create(parameters?: any): string;
    export function admin_products_edit(parameters?: any): string;
    export function admin_orders_index(parameters?: any): string;
    export function admin_users_index(parameters?: any): string;
    export function admin_carts_index(parameters?: any): string;
    // Add any other specific route functions used in your components
}
