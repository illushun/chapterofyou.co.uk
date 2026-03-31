<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import NavBar from '@/components/NavBar.vue';

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
}
interface OrderItem {
    id: number;
    product_id: number;
    product: Product;
    quantity: number;
    product_cost: number;
    product_total: number;
}
interface Order {
    id: number;
    payment_type: string;
    first_name: string;
    last_name: string;
    email: string;
    telephone: string | null;
    cost_total: number;
    shipping_total: number;
    voucher_discount?: number;
    tax_total: number;
    grand_total: number;
    billing_line_1: string;
    billing_line_2: string | null;
    billing_city: string;
    billing_county: string | null;
    billing_postcode: string;
    shipping_line_1: string;
    shipping_line_2: string | null;
    shipping_city: string;
    shipping_county: string | null;
    shipping_postcode: string;
    status: string;
    created_at: string;
    items: OrderItem[];
}

const props = defineProps<{ order: Order }>();

const fmt = (v: number | string) =>
    new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(Number(v) || 0);

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });

const statusStyle = (s: string): string => {
    switch (s.toLowerCase()) {
        case 'successful':
        case 'delivered': return 'bg-green-100 text-green-700 border-green-300';
        case 'shipped': return 'bg-blue-100 text-blue-700 border-blue-300';
        case 'processing': return 'bg-amber-100 text-amber-700 border-amber-300';
        case 'pending': return 'bg-yellow-100 text-yellow-700 border-yellow-300';
        case 'cancelled':
        case 'failed': return 'bg-red-100 text-red-700 border-red-300';
        default: return 'bg-gray-100 text-gray-600 border-gray-300';
    }
};

const statusLabel = (s: string): string => {
    if (s.toLowerCase() === 'successful') return 'Confirmed';
    return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
};

const statusMessage: Record<string, string> = {
    successful: 'Your order has been confirmed and is being prepared.',
    processing: 'Your order is being processed.',
    shipped: 'Your order is on its way to you!',
    delivered: 'Your order has been delivered. Enjoy!',
    pending: 'Your order is pending.',
    cancelled: 'This order has been cancelled.',
    failed: 'This order encountered a problem.',
};
</script>

<template>
    <NavBar />

    <Head :title="`Order #COY-${String(order.id).padStart(5, '0')}`" />

    <section class="py-20">
        <div class="min-h-[70vh] text-copy p-4 md:p-8 lg:p-12">
            <div class="max-w-3xl mx-auto">

                <!-- Back link -->
                <a href="/account/orders"
                    class="inline-flex items-center gap-1.5 text-sm text-copy-light hover:text-copy transition mb-6">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M19 12H5" />
                    </svg>
                    Back to Orders
                </a>

                <!-- Header -->
                <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
                    <div>
                        <h1 class="text-3xl font-black text-copy">
                            Order #COY-{{ String(order.id).padStart(6, '0') }}
                        </h1>
                        <p class="text-copy-light text-sm mt-0.5">Placed {{ fmtDate(order.created_at) }}</p>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-sm font-semibold border', statusStyle(order.status)]">
                        {{ statusLabel(order.status) }}
                    </span>
                </div>

                <!-- Status message banner -->
                <div v-if="statusMessage[order.status.toLowerCase()]"
                    class="mb-5 rounded-lg border border-copy-light/40 bg-foreground px-4 py-3 text-sm text-copy-light">
                    {{ statusMessage[order.status.toLowerCase()] }}
                </div>

                <div class="space-y-5">

                    <!-- Items -->
                    <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                        <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                            <h2 class="font-bold text-copy mb-4 border-b border-copy-light pb-2">
                                Items ({{ order.items.length }})
                            </h2>
                            <div class="space-y-3">
                                <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-copy text-sm">{{ item.product.name }}</p>
                                        <p class="text-xs text-copy-light">{{ item.product.mpn }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold text-copy">{{ fmt(item.product_total) }}</p>
                                        <p class="text-xs text-copy-light">{{ item.quantity }} × {{
                                            fmt(item.product_cost) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals breakdown -->
                            <div class="mt-5 pt-4 border-t border-copy-light space-y-2 text-sm">
                                <div class="flex justify-between text-copy-light">
                                    <span>Subtotal</span>
                                    <span>{{ fmt(order.cost_total) }}</span>
                                </div>
                                <div class="flex justify-between text-copy-light">
                                    <span>Shipping</span>
                                    <span>{{ Number(order.shipping_total) === 0 ? 'FREE' : fmt(order.shipping_total)
                                        }}</span>
                                </div>
                                <div class="flex justify-between text-copy-light">
                                    <span>VAT (20%)</span>
                                    <span>{{ fmt(order.tax_total) }}</span>
                                </div>
                                <div v-if="Number(order.voucher_discount) > 0"
                                    class="flex justify-between text-green-700 font-medium">
                                    <span>Discount</span>
                                    <span>-{{ fmt(order.voucher_discount!) }}</span>
                                </div>
                                <div
                                    class="flex justify-between font-black text-copy text-base pt-2 border-t border-copy-light">
                                    <span>Total</span>
                                    <span class="text-primary" style="color: var(--primary);">{{ fmt(order.grand_total)
                                        }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping + Billing -->
                    <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                        <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                            <h2 class="font-bold text-copy mb-4 border-b border-copy-light pb-2">Delivery</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-xs text-copy-light uppercase tracking-wider font-medium mb-2">
                                        Shipping Address</p>
                                    <address class="not-italic text-copy leading-relaxed">
                                        <p>{{ order.first_name }} {{ order.last_name }}</p>
                                        <p>{{ order.shipping_line_1 }}</p>
                                        <p v-if="order.shipping_line_2">{{ order.shipping_line_2 }}</p>
                                        <p>{{ order.shipping_city }}<span v-if="order.shipping_county">, {{
                                            order.shipping_county }}</span></p>
                                        <p>{{ order.shipping_postcode }}</p>
                                    </address>
                                </div>
                                <div>
                                    <p class="text-xs text-copy-light uppercase tracking-wider font-medium mb-2">Billing
                                        Address</p>
                                    <address class="not-italic text-copy leading-relaxed">
                                        <p>{{ order.billing_line_1 }}</p>
                                        <p v-if="order.billing_line_2">{{ order.billing_line_2 }}</p>
                                        <p>{{ order.billing_city }}<span v-if="order.billing_county">, {{
                                            order.billing_county }}</span></p>
                                        <p>{{ order.billing_postcode }}</p>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help -->
                    <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                        <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                            <h2 class="font-bold text-copy mb-2">Need Help?</h2>
                            <p class="text-sm text-copy-light mb-3">
                                If you have any questions about your order, please don't hesitate to get in touch.
                            </p>
                            <a href="mailto:contact@chapterofyou.co.uk"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-copy hover:text-primary transition"
                                style="color: var(--primary);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                contact@chapterofyou.co.uk
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</template>
