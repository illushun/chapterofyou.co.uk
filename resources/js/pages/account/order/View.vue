<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

interface Product { id: number; mpn: string; name: string; cost: number; }
interface OrderItem {
    id: number; product_id: number; product: Product;
    quantity: number; product_cost: number; product_total: number;
}
interface Order {
    id: number; payment_type: string;
    first_name: string; last_name: string; email: string; telephone: string | null;
    cost_total: number; shipping_total: number; voucher_discount?: number;
    tax_total: number; grand_total: number;
    billing_line_1: string; billing_line_2: string | null;
    billing_city: string; billing_county: string | null; billing_postcode: string;
    shipping_line_1: string; shipping_line_2: string | null;
    shipping_city: string; shipping_county: string | null; shipping_postcode: string;
    status: string; created_at: string; items: OrderItem[];
}

const props = defineProps<{ order: Order }>();

const seo = useSeoHead({ noIndex: true });

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
        case 'delivered': return 'status--green';
        case 'shipped': return 'status--blue';
        case 'processing': return 'status--amber';
        case 'pending': return 'status--yellow';
        case 'cancelled':
        case 'failed': return 'status--red';
        default: return 'status--grey';
    }
};

const statusLabel = (s: string): string => {
    if (s.toLowerCase() === 'successful') return 'Confirmed';
    return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
};

const statusMessage: Record<string, string> = {
    successful: 'Your order has been confirmed and is being prepared.',
    processing: 'Your order is being processed.',
    shipped: 'Your order is on its way to you.',
    delivered: 'Your order has been delivered. Enjoy!',
    pending: 'Your order is pending.',
    cancelled: 'This order has been cancelled.',
    failed: 'This order encountered a problem.',
};
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="ov">
        <div class="ov-wrap">

            <!-- Back -->
            <a href="/account/orders" class="ov-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back to Orders
            </a>

            <!-- Page header -->
            <header class="ov-header">
                <div>
                    <h1 class="ov-title">#COY-{{ String(order.id).padStart(5, '0') }}</h1>
                    <p class="ov-date">Placed {{ fmtDate(order.created_at) }}</p>
                </div>
                <span :class="['status-badge', statusStyle(order.status)]">
                    {{ statusLabel(order.status) }}
                </span>
            </header>

            <!-- Status banner -->
            <div v-if="statusMessage[order.status.toLowerCase()]" class="status-banner">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4M12 8h.01" />
                </svg>
                {{ statusMessage[order.status.toLowerCase()] }}
            </div>

            <!-- Cards -->
            <div class="ov-cards">

                <!-- Items + totals -->
                <section class="ov-card">
                    <h2 class="ov-card-title">
                        Items
                        <span class="ov-card-count">{{ order.items.length }}</span>
                    </h2>

                    <div class="item-list">
                        <div v-for="item in order.items" :key="item.id" class="item-row">
                            <div class="item-info">
                                <p class="item-name">{{ item.product.name }}</p>
                                <p class="item-mpn">{{ item.product.mpn }}</p>
                            </div>
                            <div class="item-pricing">
                                <p class="item-total">{{ fmt(item.product_total) }}</p>
                                <p class="item-unit">{{ item.quantity }} &times; {{ fmt(item.product_cost) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cost breakdown -->
                    <div class="totals">
                        <div class="totals-row">
                            <span>Subtotal</span>
                            <span>{{ fmt(order.cost_total) }}</span>
                        </div>
                        <div class="totals-row">
                            <span>Shipping</span>
                            <span>{{ Number(order.shipping_total) === 0 ? 'Free' : fmt(order.shipping_total) }}</span>
                        </div>
                        <div class="totals-row">
                            <span>VAT (20%)</span>
                            <span>{{ fmt(order.tax_total) }}</span>
                        </div>
                        <div v-if="Number(order.voucher_discount) > 0" class="totals-row totals-row--discount">
                            <span>Discount</span>
                            <span>-{{ fmt(order.voucher_discount!) }}</span>
                        </div>
                        <div class="totals-row totals-row--grand">
                            <span>Total</span>
                            <span>{{ fmt(order.grand_total) }}</span>
                        </div>
                    </div>
                </section>

                <!-- Delivery addresses -->
                <section class="ov-card">
                    <h2 class="ov-card-title">Delivery</h2>
                    <div class="address-grid">
                        <div>
                            <p class="address-label">Shipping Address</p>
                            <address class="address-block">
                                <span>{{ order.first_name }} {{ order.last_name }}</span>
                                <span>{{ order.shipping_line_1 }}</span>
                                <span v-if="order.shipping_line_2">{{ order.shipping_line_2 }}</span>
                                <span>{{ order.shipping_city }}<template v-if="order.shipping_county">, {{
                                    order.shipping_county }}</template></span>
                                <span>{{ order.shipping_postcode }}</span>
                            </address>
                        </div>
                        <div>
                            <p class="address-label">Billing Address</p>
                            <address class="address-block">
                                <span>{{ order.billing_line_1 }}</span>
                                <span v-if="order.billing_line_2">{{ order.billing_line_2 }}</span>
                                <span>{{ order.billing_city }}<template v-if="order.billing_county">, {{
                                    order.billing_county }}</template></span>
                                <span>{{ order.billing_postcode }}</span>
                            </address>
                        </div>
                    </div>
                </section>

                <!-- Help -->
                <section class="ov-card">
                    <h2 class="ov-card-title">Need help?</h2>
                    <p class="help-text">
                        If you have any questions about your order, please don't hesitate to get in touch.
                    </p>
                    <a href="mailto:contact@chapterofyou.co.uk" class="help-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        contact@chapterofyou.co.uk
                    </a>
                </section>

            </div>
        </div>
    </main>
</template>

<style scoped>
.ov {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.ov-wrap {
    max-width: 700px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

/* ── Back ── */
.ov-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    text-decoration: none;
    margin-bottom: 1.5rem;
    transition: color 0.2s;
}

.ov-back:hover {
    color: #8c4a50;
}

/* ── Header ── */
.ov-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.ov-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(1.8rem, 5vw, 2.4rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    line-height: 1.1;
    margin-bottom: 0.2rem;
}

.ov-date {
    font-size: 0.88rem;
    color: #6b4f4f;
    font-style: italic;
}

/* ── Status badge ── */
.status-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 0.28rem 0.85rem;
    border-radius: 999px;
    border: 1px solid transparent;
    white-space: nowrap;
}

.status--green {
    background: #f0faf0;
    color: #2d7a3a;
    border-color: #a8d8b0;
}

.status--blue {
    background: #f0f5ff;
    color: #2a4fa0;
    border-color: #a8bee8;
}

.status--amber {
    background: #fffbf0;
    color: #8a5a00;
    border-color: #e0c878;
}

.status--yellow {
    background: #fffff0;
    color: #7a6800;
    border-color: #d4c840;
}

.status--red {
    background: #fff5f5;
    color: #8c2a2a;
    border-color: #e8a8a8;
}

.status--grey {
    background: #f5f5f5;
    color: #555;
    border-color: #ccc;
}

/* ── Status banner ── */
.status-banner {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    color: #6b4f4f;
    font-style: italic;
    margin-bottom: 1.5rem;
}

/* ── Card stack ── */
.ov-cards {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ── Card ── */
.ov-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.ov-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.2rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.ov-card::after {
    content: '✿';
    position: absolute;
    top: 6px;
    left: 10px;
    font-size: 0.85rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.ov-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1.1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5c9c7;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ov-card-count {
    font-family: 'Nunito', sans-serif;
    font-style: normal;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: rgba(140, 74, 80, 0.1);
    color: #8c4a50;
    border: 1px solid rgba(140, 74, 80, 0.2);
    border-radius: 999px;
    padding: 0.15rem 0.55rem;
}

/* ── Items ── */
.item-list {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    margin-bottom: 1.25rem;
}

.item-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d1a1a;
    line-height: 1.3;
}

.item-mpn {
    font-size: 0.78rem;
    color: #6b4f4f;
    font-style: italic;
    margin-top: 0.1rem;
}

.item-pricing {
    text-align: right;
    flex-shrink: 0;
}

.item-total {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-weight: 500;
    color: #2d1a1a;
}

.item-unit {
    font-size: 0.78rem;
    color: #6b4f4f;
    margin-top: 0.1rem;
}

/* ── Totals ── */
.totals {
    border-top: 1px solid #e5c9c7;
    padding-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: #6b4f4f;
}

.totals-row span:last-child {
    font-weight: 500;
    color: #2d1a1a;
}

.totals-row--discount {
    color: #2d7a3a;
}

.totals-row--discount span:last-child {
    color: #2d7a3a;
}

.totals-row--grand {
    border-top: 1px solid #e5c9c7;
    padding-top: 0.6rem;
    margin-top: 0.2rem;
    font-size: 1rem;
    font-weight: 600;
    color: #2d1a1a;
}

.totals-row--grand span:last-child {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.4rem;
    font-weight: 500;
    color: #8c4a50;
}

/* ── Addresses ── */
.address-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 520px) {
    .address-grid {
        grid-template-columns: 1fr;
    }
}

.address-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8c4a50;
    margin-bottom: 0.5rem;
}

.address-block {
    font-style: normal;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    font-size: 0.9rem;
    color: #2d1a1a;
    line-height: 1.55;
}

/* ── Help ── */
.help-text {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.6;
    margin-bottom: 0.85rem;
}

.help-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.92rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
    transition: color 0.2s;
}

.help-link:hover {
    color: #6a3038;
}
</style>
