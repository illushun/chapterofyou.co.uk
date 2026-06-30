<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

interface CartItem {
    id: number;
    product_id: number;
    name: string;
    cost: number;
    quantity: number;
    image_url: string;
    subtotal: number;
    stock_qty: number;
}

const props = defineProps<{
    cartItems: CartItem[];
    cartTotal: number;
    giftVoucher: {
        amount: number;
        delivery_type: 'email' | 'physical';
        recipient_name: string;
        recipient_email: string | null;
    } | null;
}>();

const seo = useSeoHead({ noIndex: true });

const hasItems = computed(() => props.cartItems.length > 0 || !!props.giftVoucher);
const itemCount = computed(() => props.cartItems.reduce((s, i) => s + Number(i.quantity), 0));
const n = (v: unknown) => Number(v) || 0;
const fmt = (v: unknown) => `£${n(v).toFixed(2)}`;

const finalTotal = computed(() => n(props.cartTotal));
const remaining = computed(() => Math.max(0, 50 - n(props.cartTotal)));
const freeShip = computed(() => n(props.cartTotal) >= 50);
const progress = computed(() => Math.min(100, (n(props.cartTotal) / 50) * 100));

const pending = ref<Record<number, boolean>>({});
const removing = ref<Record<number, boolean>>({});

const updateQty = (productId: number, qty: number) => {
    const item = props.cartItems.find(i => i.product_id === productId);
    if (!item) return;
    if (qty < 1) { remove(productId); return; }
    qty = Math.min(qty, item.stock_qty);
    pending.value[productId] = true;
    router.put(`/cart/update/${productId}`, { quantity: qty }, {
        preserveScroll: true, preserveState: true, replace: true,
        onFinish: () => delete pending.value[productId],
    });
};

const remove = (productId: number) => {
    removing.value[productId] = true;
    router.delete(`/cart/remove/${productId}`, {
        preserveScroll: true,
        onFinish: () => delete removing.value[productId],
    });
};

const removingGiftVoucher = ref(false);
const removeGiftVoucher = () => {
    removingGiftVoucher.value = true;
    router.post(route('gift-vouchers.remove-from-cart'), {}, {
        preserveScroll: true,
        onFinish: () => { removingGiftVoucher.value = false; },
    });
};

const vatRegistered = computed(() => !!(usePage().props.vatRegistered));
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="cp">

        <div class="cp-wrap">

            <!-- ── Header ── -->
            <header class="cp-head">
                <div class="cp-head-left">

                    <div>
                        <h1 class="cp-title">
                            Your Basket
                            <span v-if="hasItems" class="item-badge">{{ itemCount }} {{ itemCount === 1 ? 'item' :
                                'items' }}</span>
                        </h1>
                        <a href="/products" class="cp-back">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m15 18-6-6 6-6" />
                            </svg>
                            Continue shopping
                        </a>
                    </div>
                </div>

                <div v-if="hasItems" class="ship-nudge">
                    <p class="ship-text">
                        <span v-if="freeShip">Free shipping unlocked!</span>
                        <span v-else>Add <em>{{ fmt(remaining) }}</em> more for free shipping</span>
                    </p>
                    <div class="ship-track">
                        <div class="ship-fill" :style="{ width: progress + '%' }">

                        </div>
                    </div>
                </div>
            </header>

            <!-- ── Grid ── -->
            <div class="cp-grid">

                <!-- Items column -->
                <section class="cp-items">

                    <!-- Empty state -->
                    <div v-if="!hasItems" class="cp-empty">

                        <h2>Your basket is empty</h2>
                        <p>Nothing here yet, let's find you something lovely.</p>
                        <a href="/products" class="btn-rose">Browse the collection</a>
                    </div>

                    <TransitionGroup v-else name="card" tag="div" class="card-list">
                        <div v-for="item in cartItems" :key="item.product_id" class="item-card"
                            :class="{ 'item-card--out': removing[item.product_id] }">



                            <div class="ci-img">
                                <img :src="item.image_url" :alt="item.name" />
                            </div>

                            <div class="ci-body">
                                <div class="ci-top">
                                    <div>
                                        <h2 class="ci-name">{{ item.name }}</h2>
                                        <p class="ci-unit">{{ fmt(item.cost) }} each</p>
                                    </div>
                                    <button @click="remove(item.product_id)" :disabled="removing[item.product_id]"
                                        class="ci-remove" :aria-label="`Remove ${item.name}`">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                            <path d="M18 6 6 18M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="ci-bottom">
                                    <div class="qty-wrap" :class="{ 'qty-wrap--busy': pending[item.product_id] }">
                                        <button class="qty-btn" @click="updateQty(item.product_id, item.quantity - 1)"
                                            :disabled="item.quantity <= 1 || !!pending[item.product_id]"
                                            aria-label="Decrease">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="3" stroke-linecap="round">
                                                <path d="M5 12h14" />
                                            </svg>
                                        </button>
                                        <span class="qty-num">{{ item.quantity }}</span>
                                        <button class="qty-btn" @click="updateQty(item.product_id, item.quantity + 1)"
                                            :disabled="item.quantity >= item.stock_qty || !!pending[item.product_id]"
                                            aria-label="Increase">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="3" stroke-linecap="round">
                                                <path d="M5 12h14" />
                                                <path d="M12 5v14" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="ci-line-total">{{ fmt(n(item.cost) * n(item.quantity)) }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="giftVoucher" key="gift-voucher" class="item-card gv-cart-card">
                            <div class="gv-cart-icon" aria-hidden="true">🎁</div>
                            <div class="ci-body">
                                <div class="ci-top">
                                    <div>
                                        <h2 class="ci-name">Gift Voucher</h2>
                                        <p class="ci-unit">
                                            {{ giftVoucher.delivery_type === 'email' ? 'E-Voucher' : 'Physical Voucher'
                                            }}
                                            · For: {{ giftVoucher.recipient_name }}
                                        </p>
                                    </div>
                                    <button @click="removeGiftVoucher" :disabled="removingGiftVoucher" class="ci-remove"
                                        aria-label="Remove gift voucher">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                            <path d="M18 6 6 18M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="ci-bottom">
                                    <span class="gv-cart-badge">Valid 1 year · All products</span>
                                    <p class="ci-line-total">{{ fmt(giftVoucher.amount) }}</p>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>
                </section>

                <!-- Summary sidebar -->
                <aside v-if="hasItems" class="cp-summary">
                    <div class="sum-card">

                        <div class="sum-floral" aria-hidden="true">
                            <svg viewBox="0 0 200 36" xmlns="http://www.w3.org/2000/svg" fill="none">
                                <circle cx="100" cy="18" r="5" fill="#a85058" opacity=".4" />
                                <circle cx="80" cy="18" r="3" fill="#a85058" opacity=".25" />
                                <circle cx="120" cy="18" r="3" fill="#a85058" opacity=".25" />
                                <circle cx="62" cy="18" r="2" fill="#a85058" opacity=".18" />
                                <circle cx="138" cy="18" r="2" fill="#a85058" opacity=".18" />
                                <line x1="0" y1="18" x2="56" y2="18" stroke="#a85058" stroke-width="0.8" opacity=".3" />
                                <line x1="144" y1="18" x2="200" y2="18" stroke="#a85058" stroke-width="0.8"
                                    opacity=".3" />
                            </svg>
                        </div>

                        <h2 class="sum-title">Order Summary</h2>

                        <div class="sum-rows">
                            <div class="sum-row">
                                <span>Subtotal</span>
                                <span>{{ fmt(cartTotal) }}</span>
                            </div>
                            <div v-if="vatRegistered" class="sum-row">
                                <span>VAT</span>
                                <span class="sum-vat-note">Included</span>
                            </div>
                            <div class="sum-row">
                                <span>Shipping</span>
                                <span class="sum-ship-note">
                                    {{ freeShip ? 'Free!' : 'At checkout' }}
                                </span>
                            </div>
                        </div>

                        <div class="sum-dash" aria-hidden="true"></div>

                        <div class="sum-total-row">
                            <span class="sum-total-label">Total</span>
                            <span class="sum-total-val">{{ fmt(finalTotal) }}</span>
                        </div>

                        <a href="/checkout" class="btn-checkout">
                            Checkout
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </a>

                        <p class="sum-secure">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            Secure checkout · Stripe
                        </p>
                    </div>
                </aside>

            </div>
        </div>

    </main>

    <Footer />
</template>

<style scoped>
.cp {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}


.cp-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 2.5rem 1.25rem 5rem;
}

/* Header */
.cp-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.cp-head-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.cp-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(1.9rem, 5vw, 2.8rem);
    font-weight: 400;
    font-style: italic;
    color: #2d1a1a;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    line-height: 1.1;
    margin-bottom: 0.3rem;
}

.item-badge {
    font-family: 'Nunito', sans-serif;
    font-style: normal;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #8c4a50;
    background: #faeaea;
    border: 1px solid #e8c0c0;
    border-radius: 999px;
    padding: 0.2rem 0.7rem;
    vertical-align: middle;
}

.cp-back {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.9rem;
    color: #6b4f4f;
    text-decoration: none;
    transition: color 0.2s;
}

.cp-back:hover {
    color: #8c4a50;
}

/* Shipping nudge */
.ship-nudge {
    min-width: 200px;
    max-width: 280px;
}

.ship-text {
    font-size: 0.9rem;
    color: #6b4f4f;
    margin-bottom: 0.45rem;
    line-height: 1.4;
}

.ship-text em {
    font-style: normal;
    font-weight: 600;
    color: #8c4a50;
}

.ship-track {
    height: 5px;
    background: #eedcda;
    border-radius: 999px;
    overflow: visible;
    position: relative;
}

.ship-fill {
    height: 100%;
    background: linear-gradient(90deg, #e8a4a8, #c9747a);
    border-radius: 999px;
    transition: width 0.6s cubic-bezier(.34, 1.56, .64, 1);
    position: relative;
}

/* Grid */
.cp-grid {
    display: grid;
    grid-template-columns: 1fr 316px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 820px) {
    .cp-grid {
        grid-template-columns: 1fr;
    }

    .ship-nudge {
        max-width: 100%;
        min-width: 0;
        width: 100%;
    }

    .cp-head {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Empty */
.cp-empty {
    text-align: center;
    padding: 5rem 2rem;
    border: 1.5px dashed #c9a8a4;
    border-radius: 24px;
    background: #fffafa;
}

.cp-empty h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.7rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.5rem;
}

.cp-empty p {
    font-size: 1rem;
    color: #6b4f4f;
    margin-bottom: 1.75rem;
    line-height: 1.6;
}

/* Card list */
.card-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Item card */
.item-card {
    display: flex;
    gap: 1.1rem;
    padding: 1.2rem 1.3rem;
    border-radius: 20px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    position: relative;
    transition: box-shadow 0.25s, transform 0.25s, opacity 0.3s;
    overflow: hidden;
}

.item-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.5rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.item-card::after {
    content: '✿';
    position: absolute;
    top: 6px;
    left: 10px;
    font-size: 1rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.item-card:hover {
    box-shadow: 0 6px 28px rgba(229, 201, 199, 0.5);
    transform: translateY(-2px);
}

.item-card--out {
    opacity: 0.35;
    pointer-events: none;
}


.ci-img {
    width: 82px;
    height: 82px;
    flex-shrink: 0;
    border-radius: 14px;
    border: 1px solid #e5c9c7;
    overflow: hidden;
    background: #fdf4f3;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ci-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 6px;
}

.ci-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 0.6rem;
}

.ci-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
}

.ci-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #2d1a1a;
    line-height: 1.3;
    margin-bottom: 0.15rem;
}

.ci-unit {
    font-size: 0.875rem;
    color: #6b4f4f;
    font-style: italic;
}

.ci-remove {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 1px solid #d4aaa8;
    background: transparent;
    color: #6b4f4f;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.ci-remove:hover {
    background: #faeaea;
    color: #8c4a50;
    border-color: #6b4f4f;
}

.ci-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Qty stepper */
.qty-wrap {
    display: inline-flex;
    align-items: center;
    border: 1px solid #d4aaa8;
    border-radius: 999px;
    background: #fdf4f3;
    overflow: hidden;
    transition: opacity 0.2s;
}

.qty-wrap--busy {
    opacity: 0.22;
    pointer-events: none;
}

.qty-btn {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    cursor: pointer;
    color: #8c4a50;
    transition: background 0.15s;
}

.qty-btn:hover:not(:disabled) {
    background: #faeaea;
}

.qty-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.qty-num {
    min-width: 28px;
    text-align: center;
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d1a1a;
    border-left: 1px solid #e5c9c7;
    border-right: 1px solid #e5c9c7;
    line-height: 30px;
    padding: 0 2px;
}

.ci-line-total {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.25rem;
    font-weight: 500;
    color: #8c4a50;
}

/* Summary card */
.cp-summary {
    position: sticky;
    top: 88px;
}

.sum-card {
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    background: #fffafa;
    box-shadow: 0 4px 24px rgba(229, 201, 199, 0.4);
    padding: 1.75rem 1.5rem;
    text-align: center;
}

.sum-floral {
    color: #8c4a50;
    margin-bottom: 0.75rem;
}

.sum-floral svg {
    width: 140px;
    height: auto;
    display: inline-block;
}

.sum-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.45rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1.25rem;
}

.sum-rows {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    text-align: left;
    margin-bottom: 1rem;
}

.sum-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.95rem;
    color: #6b4f4f;
}

.sum-row span:last-child {
    font-weight: 600;
    color: #2d1a1a;
}

.sum-ship-note {
    font-style: italic;
    font-weight: 400 !important;
    color: #8c4a50 !important;
    font-size: 0.9rem;
}

.sum-dash {
    border-top: 1px dashed #c9a8a4;
    margin: 1rem 0;
}

.sum-total-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 1.4rem;
}

.sum-total-label {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-style: italic;
    color: #2d1a1a;
}

.sum-total-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 500;
    color: #8c4a50;
}

.btn-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.85rem 1.5rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-decoration: none;
    box-shadow: 0 4px 18px rgba(168, 80, 88, 0.22);
    transition: box-shadow 0.25s, transform 0.25s;
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(168, 80, 88, 0.32);
}

.btn-rose {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.6rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 3px 14px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-rose:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(168, 80, 88, 0.28);
}

.sum-secure {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    font-size: 0.875rem;
    color: #6b4f4f;
    margin-top: 0.85rem;
    font-style: italic;
}

/* Transitions */
.card-enter-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}

.card-leave-active {
    transition: opacity 0.28s ease, transform 0.28s ease;
}

.card-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.card-leave-to {
    opacity: 0;
    transform: translateX(-12px);
}

/* Mobile */
@media (max-width: 480px) {
    .item-card {
        flex-direction: column;
    }

    .ci-img {
        width: 100%;
        height: 120px;
    }
}

.gv-cart-card {
    background: linear-gradient(135deg, #fffafa 0%, #fff8f0 100%);
    border-color: #c9a84c;
}

.gv-cart-icon {
    font-size: 2.5rem;
    width: 82px;
    height: 82px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 14px;
    border: 1px solid #e5c9c7;
    background: #fdf4f3;
}

.gv-cart-badge {
    font-size: 0.875rem;
    font-style: italic;
    color: #8a6a5a;
}

.sum-vat-note {
    font-weight: 400 !important;
    font-style: italic;
    color: #9a7070 !important;
    font-size: 0.9rem;
}
</style>
