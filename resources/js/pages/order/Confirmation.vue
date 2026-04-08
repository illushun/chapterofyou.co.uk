<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

interface OrderItem {
    name: string;
    quantity: number;
    price: number;
    total: number;
}

interface ShippingAddress {
    line_1: string;
    line_2: string | null;
    city: string;
    county: string | null;
    postcode: string;
    country: string;
}

interface Order {
    id: number;
    status: string;
    email: string;
    first_name: string;
    subtotal: number;
    shipping: number;
    tax: number;
    voucher_discount: number;
    total: number;
    items: OrderItem[];
    shipping_address: ShippingAddress;
    created_at: string;
}

const props = defineProps<{ order: Order }>();

const seo = useSeoHead({ noIndex: true });

const fmt = (v: number | string): string => {
    const n = Number(v);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="cf">
        <div class="cf-wrap">

            <!-- Hero -->
            <header class="cf-hero">
                <div class="cf-petals" aria-hidden="true">
                    <span class="cf-petal cf-petal--1">✿</span>
                    <span class="cf-petal cf-petal--2">✦</span>
                    <span class="cf-petal cf-petal--3">✿</span>
                    <span class="cf-petal cf-petal--4">✦</span>
                    <span class="cf-petal cf-petal--5">✿</span>
                </div>

                <div class="cf-check-ring">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                </div>

                <h1 class="cf-hero-title">Order Confirmed!</h1>
                <p class="cf-hero-sub">
                    Thank you, <em>{{ order.first_name }}</em>. Your payment has been processed
                    and your order is on its way.
                </p>
                <p class="cf-hero-email">
                    A confirmation has been sent to
                    <strong>{{ order.email }}</strong>
                </p>

                <div class="cf-stats">
                    <div class="cf-stat">
                        <span class="cf-stat-label">Order</span>
                        <span class="cf-stat-val">#COY-{{ order.id }}</span>
                    </div>
                    <div class="cf-stat-sep" aria-hidden="true">✿</div>
                    <div class="cf-stat">
                        <span class="cf-stat-label">Placed</span>
                        <span class="cf-stat-val">{{ order.created_at }}</span>
                    </div>
                    <div class="cf-stat-sep" aria-hidden="true">✿</div>
                    <div class="cf-stat">
                        <span class="cf-stat-label">Total paid</span>
                        <span class="cf-stat-val cf-stat-val--accent">{{ fmt(order.total) }}</span>
                    </div>
                </div>
            </header>

            <!-- Content grid -->
            <div class="cf-grid">

                <div class="cf-card">
                    <h2 class="cf-card-title">Order Summary</h2>
                    <div class="cf-items">
                        <div v-for="item in order.items" :key="item.name" class="cf-item">
                            <div class="cf-item-info">
                                <p class="cf-item-name">{{ item.name }}</p>
                                <p class="cf-item-qty">× {{ item.quantity }}</p>
                            </div>
                            <p class="cf-item-total">{{ fmt(item.total) }}</p>
                        </div>
                    </div>
                    <div class="cf-totals">
                        <div class="cf-total-row">
                            <span class="cf-total-lbl">Subtotal</span>
                            <span>{{ fmt(order.subtotal) }}</span>
                        </div>
                        <div class="cf-total-row">
                            <span class="cf-total-lbl">Shipping</span>
                            <span>{{ Number(order.shipping) === 0 ? 'FREE' : fmt(order.shipping) }}</span>
                        </div>
                        <div class="cf-total-row">
                            <span class="cf-total-lbl">VAT</span>
                            <span
                                style="font-style:italic; color:#9a7070; font-weight:400; font-size:0.82rem;">Included</span>
                        </div>
                        <div v-if="Number(order.voucher_discount) > 0" class="cf-total-row cf-total-row--discount">
                            <span>Discount applied</span>
                            <span>−{{ fmt(order.voucher_discount) }}</span>
                        </div>
                        <div class="cf-total-grand">
                            <span>Order Total</span>
                            <span class="cf-grand-val">{{ fmt(order.total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="cf-card">
                    <h2 class="cf-card-title">Shipping Address</h2>
                    <address class="cf-address">
                        <span>{{ order.shipping_address.line_1 }}</span>
                        <span v-if="order.shipping_address.line_2">{{ order.shipping_address.line_2 }}</span>
                        <span>{{ order.shipping_address.city }}<template v-if="order.shipping_address.county">, {{
                            order.shipping_address.county }}</template></span>
                        <span>{{ order.shipping_address.postcode }}</span>
                        <span>{{ order.shipping_address.country }}</span>
                    </address>
                </div>

            </div>

            <!-- What happens next -->
            <div class="cf-next">
                <h2 class="cf-next-title">What happens next?</h2>
                <div class="cf-next-steps">
                    <div class="cf-step">
                        <div class="cf-step-num">1</div>
                        <div>
                            <p class="cf-step-head">We prepare your order</p>
                            <p class="cf-step-body">Your order is carefully handcrafted and packaged within 2–3 working
                                days.</p>
                        </div>
                    </div>
                    <div class="cf-step-arrow" aria-hidden="true">→</div>
                    <div class="cf-step">
                        <div class="cf-step-num">2</div>
                        <div>
                            <p class="cf-step-head">Dispatched with care</p>
                            <p class="cf-step-body">You'll receive a dispatch email with tracking details as soon as
                                it's on its
                                way.</p>
                        </div>
                    </div>
                    <div class="cf-step-arrow" aria-hidden="true">→</div>
                    <div class="cf-step">
                        <div class="cf-step-num">3</div>
                        <div>
                            <p class="cf-step-head">Enjoy your order</p>
                            <p class="cf-step-body">Your Chapter of You order arrives, ready to bring calm and beauty to
                                your space.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="cf-actions">
                <a href="/products" class="cf-btn cf-btn--ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7M19 12H5" />
                    </svg>
                    Continue Shopping
                </a>
                <a href="/account" class="cf-btn cf-btn--primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    My Account
                </a>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.cf {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.cf-wrap {
    max-width: 820px;
    margin: 0 auto;
    padding: 3.5rem 1.25rem 6rem;
}

/* Hero */
.cf-hero {
    text-align: center;
    padding: 3rem 1.5rem 2.5rem;
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    animation: cf-fade-in 0.6s ease both;
}

.cf-petals {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.cf-petal {
    position: absolute;
    color: #e5c9c7;
    font-size: 1.2rem;
    opacity: 0;
    animation: cf-float 4s ease-in-out infinite;
}

.cf-petal--1 {
    top: 12%;
    left: 8%;
    animation-delay: 0s;
    font-size: 1rem;
}

.cf-petal--2 {
    top: 20%;
    right: 10%;
    animation-delay: 0.6s;
    font-size: 0.8rem;
}

.cf-petal--3 {
    bottom: 18%;
    left: 12%;
    animation-delay: 1.2s;
}

.cf-petal--4 {
    bottom: 12%;
    right: 8%;
    animation-delay: 1.8s;
    font-size: 0.9rem;
}

.cf-petal--5 {
    top: 50%;
    left: 4%;
    animation-delay: 0.9s;
    font-size: 0.7rem;
}

@keyframes cf-float {
    0% {
        opacity: 0;
        transform: translateY(0) rotate(0deg);
    }

    20% {
        opacity: 0.6;
    }

    80% {
        opacity: 0.4;
    }

    100% {
        opacity: 0;
        transform: translateY(-20px) rotate(15deg);
    }
}

@keyframes cf-fade-in {
    from {
        opacity: 0;
        transform: translateY(16px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

.cf-check-ring {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
    box-shadow: 0 6px 20px rgba(168, 80, 88, 0.3);
    animation: cf-pop 0.5s cubic-bezier(.34, 1.56, .64, 1) 0.2s both;
    position: relative;
    z-index: 1;
}

@keyframes cf-pop {
    from {
        transform: scale(0.5);
        opacity: 0;
    }

    to {
        transform: scale(1);
        opacity: 1;
    }
}

.cf-hero-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 6vw, 3rem);
    font-weight: 400;
    font-style: italic;
    color: #2d1a1a;
    margin-bottom: 0.75rem;
    position: relative;
    z-index: 1;
}

.cf-hero-sub {
    font-size: 1rem;
    color: #6b4f4f;
    line-height: 1.65;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.cf-hero-sub em {
    font-style: normal;
    color: #8c4a50;
    font-weight: 600;
}

.cf-hero-email {
    font-size: 0.85rem;
    color: #9a7070;
    margin-bottom: 1.75rem;
    position: relative;
    z-index: 1;
}

.cf-hero-email strong {
    color: #2d1a1a;
}

.cf-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.75rem 1.5rem;
    padding: 1.25rem 1rem;
    background: #fdf4f3;
    border: 1px solid #e5c9c7;
    border-radius: 14px;
    position: relative;
    z-index: 1;
}

.cf-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
}

.cf-stat-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #c9a4a4;
}

.cf-stat-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #2d1a1a;
}

.cf-stat-val--accent {
    color: #8c4a50;
    font-size: 1.25rem;
    font-weight: 600;
}

.cf-stat-sep {
    font-size: 0.6rem;
    color: #e5c9c7;
}

/* Grid */
.cf-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
    animation: cf-fade-in 0.6s 0.15s ease both;
}

@media (max-width: 620px) {
    .cf-grid {
        grid-template-columns: 1fr;
    }
}

/* Cards */
.cf-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.cf-card::after {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.5rem;
    color: #e5c9c7;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.cf-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #2d1a1a;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f0dcd8;
    margin-bottom: 1rem;
}

/* Items */
.cf-items {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-bottom: 1rem;
}

.cf-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.cf-item-info {
    flex: 1;
    min-width: 0;
}

.cf-item-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2d1a1a;
    line-height: 1.3;
}

.cf-item-qty {
    font-size: 0.75rem;
    color: #9a7070;
    font-style: italic;
}

.cf-item-total {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1rem;
    font-weight: 500;
    color: #8c4a50;
    flex-shrink: 0;
}

/* Totals */
.cf-totals {
    border-top: 1px solid #f0dcd8;
    padding-top: 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.cf-total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #6b4f4f;
}

.cf-total-lbl {
    color: #9a7070;
}

.cf-total-row--discount {
    color: #2d7a3a;
    font-weight: 600;
}

.cf-total-grand {
    display: flex;
    justify-content: space-between;
    font-size: 0.95rem;
    font-weight: 700;
    color: #2d1a1a;
    padding-top: 0.65rem;
    border-top: 1px solid #e5c9c7;
    margin-top: 0.35rem;
}

.cf-grand-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.4rem;
    font-weight: 500;
    color: #8c4a50;
}

/* Address */
.cf-address {
    font-style: normal;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.6;
}

/* What next */
.cf-next {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    padding: 1.75rem 1.5rem;
    margin-bottom: 1.5rem;
    animation: cf-fade-in 0.6s 0.3s ease both;
}

.cf-next-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-weight: 500;
    color: #2d1a1a;
    text-align: center;
    margin-bottom: 1.5rem;
}

.cf-next-steps {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: center;
}

.cf-step {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    flex: 1;
    min-width: 160px;
    max-width: 200px;
}

.cf-step-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    flex-shrink: 0;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
}

.cf-step-head {
    font-size: 0.88rem;
    font-weight: 700;
    color: #2d1a1a;
    margin-bottom: 0.2rem;
}

.cf-step-body {
    font-size: 0.78rem;
    color: #6b4f4f;
    line-height: 1.55;
}

.cf-step-arrow {
    font-size: 1rem;
    color: #e5c9c7;
    margin-top: 0.5rem;
    flex-shrink: 0;
    padding-top: 4px;
}

@media (max-width: 580px) {
    .cf-step-arrow {
        display: none;
    }

    .cf-step {
        max-width: 100%;
    }
}

/* Actions */
.cf-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    flex-wrap: wrap;
    animation: cf-fade-in 0.6s 0.45s ease both;
}

.cf-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.6rem;
    border-radius: 999px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}

.cf-btn--ghost {
    border: 1px solid #e5c9c7;
    background: #fffafa;
    color: #6b4f4f;
}

.cf-btn--ghost:hover {
    border-color: #8c4a50;
    color: #8c4a50;
    transform: translateY(-1px);
}

.cf-btn--primary {
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    box-shadow: 0 4px 14px rgba(168, 80, 88, 0.22);
}

.cf-btn--primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(168, 80, 88, 0.3);
}
</style>
