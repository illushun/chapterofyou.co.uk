<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

interface Order {
    id: number;
    date: string;
    total: number;
    status: string;
}

const props = defineProps<{ orders: Order[] }>();

const seo = useSeoHead({ noIndex: true });

const hasOrders = computed(() => props.orders && props.orders.length > 0);

const fmt = (v: number) =>
    new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(Number(v) || 0);

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

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
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="op">
        <div class="op-wrap">

            <!-- Header -->
            <header class="op-header">
                <a href="/account" class="op-back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Back to Account
                </a>
                <h1 class="op-title">My Orders</h1>
                <p class="op-sub">
                    {{ hasOrders ? `${orders.length} order${orders.length !== 1 ? 's' : ''}` : 'No orders placed yet' }}
                </p>
            </header>

            <!-- Empty state -->
            <div v-if="!hasOrders" class="op-empty">
                <svg class="op-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 3H8l-2 4h12l-2-4z" />
                </svg>
                <h2>Nothing here yet</h2>
                <p>You haven't placed any orders with us yet.<br />Explore our collection and find your signature scent.
                </p>
                <a href="/products" class="btn-rose">Browse the collection</a>
            </div>

            <!-- Orders list -->
            <div v-else class="order-list">
                <a v-for="order in orders" :key="order.id" :href="`/account/orders/${order.id}`" class="order-card">

                    <div class="order-card-inner">

                        <!-- Order ref + date -->
                        <div class="order-ref">
                            <p class="order-label">Order</p>
                            <p class="order-number">#COY-{{ order.id }}</p>
                            <p class="order-date">{{ fmtDate(order.date) }}</p>
                        </div>

                        <!-- Status badge -->
                        <div class="order-status-wrap">
                            <span :class="['status-badge', statusStyle(order.status)]">
                                {{ statusLabel(order.status) }}
                            </span>
                        </div>

                        <!-- Total -->
                        <div class="order-total">
                            <p class="order-label">Total</p>
                            <p class="order-total-val">{{ fmt(order.total) }}</p>
                        </div>

                        <!-- Arrow -->
                        <div class="order-arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                    </div>
                </a>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.op {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.op-wrap {
    max-width: 760px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

/* ── Header ── */
.op-header {
    margin-bottom: 2.5rem;
}

.op-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    text-decoration: none;
    margin-bottom: 1rem;
    transition: color 0.2s;
}

.op-back:hover {
    color: #8c4a50;
}

.op-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.25rem;
}

.op-sub {
    font-size: 0.92rem;
    color: #6b4f4f;
    font-style: italic;
}

/* ── Empty state ── */
.op-empty {
    text-align: center;
    padding: 4rem 2rem;
    border: 1.5px dashed #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.9rem;
}

.op-empty-icon {
    width: 48px;
    height: 48px;
    color: #c9a4a4;
    margin-bottom: 0.25rem;
}

.op-empty h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
}

.op-empty p {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.6;
}

/* ── Order list ── */
.order-list {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

/* ── Order card ── */
.order-card {
    display: block;
    border: 1px solid #e5c9c7;
    border-radius: 18px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    text-decoration: none;
    color: inherit;
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.25s, transform 0.25s;
}

.order-card::before {
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

.order-card::after {
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

.order-card:hover {
    box-shadow: 0 6px 24px rgba(229, 201, 199, 0.55);
    transform: translateY(-2px);
}

.order-card-inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
    padding: 1.1rem 1.3rem;
}

/* Order ref */
.order-ref {
    flex: 1;
    min-width: 120px;
}

.order-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #6b4f4f;
    margin-bottom: 0.15rem;
}

.order-number {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-weight: 500;
    color: #2d1a1a;
    line-height: 1.2;
}

.order-date {
    font-size: 0.82rem;
    color: #6b4f4f;
    margin-top: 0.1rem;
    font-style: italic;
}

/* Status */
.order-status-wrap {
    flex-shrink: 0;
}

.status-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    border: 1px solid transparent;
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

/* Total */
.order-total {
    text-align: right;
    flex-shrink: 0;
}

.order-total-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.4rem;
    font-weight: 500;
    color: #8c4a50;
    line-height: 1.2;
}

/* Arrow */
.order-arrow {
    flex-shrink: 0;
    color: #c9a4a4;
    transition: color 0.2s, transform 0.2s;
    display: flex;
    align-items: center;
}

.order-card:hover .order-arrow {
    color: #8c4a50;
    transform: translateX(3px);
}

/* ── Button ── */
.btn-rose {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.4rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-rose:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

/* ── Mobile ── */
@media (max-width: 480px) {
    .order-card-inner {
        gap: 0.75rem;
    }

    .order-total {
        text-align: left;
    }
}
</style>
