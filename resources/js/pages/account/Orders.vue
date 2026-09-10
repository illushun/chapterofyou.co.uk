<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import CoyBreadcrumbs from '@/components/ui/coy/CoyBreadcrumbs.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Order {
    id: number;
    date: string;
    total: number;
    status: string;
}

const props = defineProps<{ orders: Order[] }>();
const seo = useSeoHead({ noIndex: true });
const hasOrders = computed(() => props.orders.length > 0);

const fmt = (value: number) =>
    new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(Number(value) || 0);

const fmtDate = (date: string) =>
    new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

function statusStyle(status: string): string {
    switch (status.toLowerCase()) {
        case 'successful':
        case 'delivered':
            return 'status--green';
        case 'shipped':
            return 'status--blue';
        case 'processing':
            return 'status--amber';
        case 'pending':
            return 'status--yellow';
        case 'cancelled':
        case 'failed':
            return 'status--red';
        default:
            return 'status--grey';
    }
}

function statusLabel(status: string): string {
    if (status.toLowerCase() === 'successful') return 'Confirmed';
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
}
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <main class="op coy-storefront">
        <header class="coy-page-header">
            <div class="coy-container coy-page-header__inner">
                <div>
                    <CoyBreadcrumbs
                        :items="[
                            { label: 'Home', href: '/' },
                            { label: 'My account', href: '/account' },
                            { label: 'Orders' },
                        ]"
                    />
                    <h1 class="coy-page-header__title">My orders</h1>
                    <p class="coy-page-header__meta">
                        {{
                            hasOrders
                                ? `${orders.length} order${orders.length === 1 ? '' : 's'}`
                                : 'Your order history will appear here.'
                        }}
                    </p>
                </div>
                <Link href="/account" class="coy-page-header__action">
                    Back to account
                </Link>
            </div>
        </header>

        <div class="coy-container op-content">
            <div class="op-heading">
                <div>
                    <p class="coy-eyebrow">Purchase history</p>
                    <h2>Your orders</h2>
                </div>
                <Link href="/products" class="op-shop-link">
                    Continue shopping <span aria-hidden="true">→</span>
                </Link>
            </div>

            <section v-if="!hasOrders" class="op-empty">
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M4 7h16v13H4zM7 7l2-4h6l2 4M8 11h8" />
                </svg>
                <h2>Your first chapter starts here</h2>
                <p>
                    Explore the collection and find a fragrance for your space.
                </p>
                <Link href="/products" class="coy-button coy-button--primary">
                    Browse fragrances
                </Link>
            </section>

            <div v-else class="order-list">
                <article
                    v-for="order in orders"
                    :key="order.id"
                    class="order-card"
                >
                    <div class="order-card-main">
                        <div>
                            <p class="order-label">Order number</p>
                            <h2>#COY-{{ order.id }}</h2>
                        </div>
                        <span
                            class="status-badge"
                            :class="statusStyle(order.status)"
                        >
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                    <dl class="order-details">
                        <div>
                            <dt>Order date</dt>
                            <dd>{{ fmtDate(order.date) }}</dd>
                        </div>
                        <div>
                            <dt>Total</dt>
                            <dd>{{ fmt(order.total) }}</dd>
                        </div>
                    </dl>
                    <Link
                        :href="`/account/orders/${order.id}`"
                        class="order-action"
                    >
                        View order <span aria-hidden="true">→</span>
                    </Link>
                </article>
            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.op {
    min-height: 100vh;
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
}
.op-content {
    padding-block: clamp(2.5rem, 5vw, 4rem) clamp(4rem, 7vw, 6rem);
}
.op-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: var(--coy-space-5);
    margin-bottom: var(--coy-space-6);
    padding-bottom: var(--coy-space-4);
    border-bottom: 1px solid var(--coy-color-border);
}
.op-heading h2 {
    margin: var(--coy-space-1) 0 0;
    color: var(--coy-color-heading);
    font: 500 clamp(2rem, 4vw, 3rem) / 1.08 var(--coy-font-display);
}
.op-shop-link,
.order-action {
    display: inline-flex;
    align-items: center;
    gap: var(--coy-space-2);
    color: var(--coy-color-accent);
    font-size: var(--coy-text-sm);
    font-weight: var(--coy-font-weight-semibold);
    text-decoration: none;
}
.op-shop-link:hover,
.order-action:hover {
    text-decoration: underline;
    text-underline-offset: 0.2rem;
}
.order-list {
    display: grid;
    gap: var(--coy-space-4);
}
.order-card {
    display: grid;
    grid-template-columns: minmax(13rem, 1fr) minmax(16rem, 1fr) auto;
    align-items: center;
    gap: clamp(1.5rem, 4vw, 3rem);
    padding: clamp(1.25rem, 3vw, 1.75rem);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-sm);
    transition:
        border-color var(--coy-duration-base) var(--coy-ease),
        transform var(--coy-duration-base) var(--coy-ease);
}
.order-card:hover {
    border-color: var(--coy-color-rose-gold);
    transform: translateY(-1px);
}
.order-card-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--coy-space-4);
}
.order-label {
    margin: 0;
    color: var(--coy-color-text);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: var(--coy-tracking-label);
    text-transform: uppercase;
}
.order-card h2 {
    margin: var(--coy-space-1) 0 0;
    color: var(--coy-color-heading);
    font: 500 1.5rem/1.1 var(--coy-font-display);
}
.status-badge {
    display: inline-flex;
    padding: 0.3rem 0.7rem;
    border: 1px solid transparent;
    border-radius: var(--coy-radius-pill);
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
}
.status--green {
    color: var(--coy-color-success);
    background: var(--coy-color-success-soft);
    border-color: rgb(39 100 55/25%);
}
.status--blue {
    color: #34558c;
    background: #eef4ff;
    border-color: #c6d5ed;
}
.status--amber,
.status--yellow {
    color: #805022;
    background: #fff7e8;
    border-color: #e7cda4;
}
.status--red {
    color: var(--coy-color-error);
    background: var(--coy-color-error-soft);
    border-color: rgb(155 48 48/25%);
}
.status--grey {
    color: var(--coy-color-text);
    background: var(--coy-color-page);
    border-color: var(--coy-color-border);
}
.order-details {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--coy-space-5);
    margin: 0;
}
.order-details div {
    min-width: 0;
}
.order-details dt {
    color: var(--coy-color-text);
    font-size: 0.8rem;
}
.order-details dd {
    margin: var(--coy-space-1) 0 0;
    color: var(--coy-color-heading);
    font-weight: 600;
}
.order-details div:last-child dd {
    font-size: 1.125rem;
}
.order-action {
    justify-self: end;
    white-space: nowrap;
}
.op-empty {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: clamp(3rem, 8vw, 6rem) var(--coy-space-5);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-lg);
    text-align: center;
}
.op-empty svg {
    width: 3rem;
    fill: none;
    stroke: var(--coy-color-rose-gold);
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.4;
}
.op-empty h2 {
    margin: var(--coy-space-4) 0 var(--coy-space-2);
    color: var(--coy-color-heading);
    font: 500 clamp(1.75rem, 4vw, 2.5rem) / 1.1 var(--coy-font-display);
}
.op-empty p {
    margin: 0 0 var(--coy-space-5);
}
@media (max-width: 800px) {
    .order-card {
        grid-template-columns: 1fr auto;
    }
    .order-details {
        grid-column: 1;
        grid-row: 2;
    }
    .order-action {
        grid-column: 2;
        grid-row: 1/3;
    }
}
@media (max-width: 560px) {
    .coy-page-header__action {
        display: none;
    }
    .op-heading {
        align-items: flex-start;
        flex-direction: column;
    }
    .order-card {
        grid-template-columns: 1fr;
    }
    .order-details,
    .order-action {
        grid-column: 1;
        grid-row: auto;
    }
    .order-action {
        justify-self: start;
    }
    .order-card-main {
        align-items: flex-start;
    }
}
@media (prefers-reduced-motion: reduce) {
    .order-card {
        transition: none;
    }
}
</style>
