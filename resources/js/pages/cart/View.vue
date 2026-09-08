<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

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
const hasItems = computed(
    () => props.cartItems.length > 0 || !!props.giftVoucher,
);
const itemCount = computed(
    () =>
        props.cartItems.reduce(
            (total, item) => total + Number(item.quantity),
            0,
        ) + (props.giftVoucher ? 1 : 0),
);
const numberValue = (value: unknown) => Number(value) || 0;
const formatPrice = (value: unknown) => `£${numberValue(value).toFixed(2)}`;
const finalTotal = computed(() => numberValue(props.cartTotal));
const remaining = computed(() =>
    Math.max(0, 50 - numberValue(props.cartTotal)),
);
const freeShipping = computed(() => numberValue(props.cartTotal) >= 50);
const shippingLabel = computed(() => {
    if (props.giftVoucher?.delivery_type === 'physical') {
        return freeShipping.value
            ? 'Physical voucher postage £2.99'
            : 'Calculated at checkout';
    }
    return freeShipping.value ? 'Free' : 'Calculated at checkout';
});
const progress = computed(() =>
    Math.min(100, (numberValue(props.cartTotal) / 50) * 100),
);
const pending = ref<Record<number, boolean>>({});
const removing = ref<Record<number, boolean>>({});
const summaryCard = ref<HTMLElement | null>(null);
const summaryInView = ref(true);
let summaryObserver: IntersectionObserver | null = null;

watch(
    summaryCard,
    (element) => {
        summaryObserver?.disconnect();
        if (!element || !('IntersectionObserver' in window)) return;
        const bounds = element.getBoundingClientRect();
        summaryInView.value =
            bounds.top < window.innerHeight && bounds.bottom > 0;
        summaryObserver = new IntersectionObserver(
            ([entry]) => {
                summaryInView.value = entry.isIntersecting;
            },
            { threshold: 0.01 },
        );
        summaryObserver.observe(element);
    },
    { flush: 'post' },
);

onBeforeUnmount(() => summaryObserver?.disconnect());

function updateQuantity(productId: number, quantity: number) {
    const item = props.cartItems.find(
        (cartItem) => cartItem.product_id === productId,
    );
    if (!item) return;
    if (quantity < 1) {
        removeItem(productId);
        return;
    }
    pending.value[productId] = true;
    router.put(
        `/cart/update/${productId}`,
        { quantity: Math.min(quantity, item.stock_qty) },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => delete pending.value[productId],
        },
    );
}

function removeItem(productId: number) {
    removing.value[productId] = true;
    router.delete(`/cart/remove/${productId}`, {
        preserveScroll: true,
        onFinish: () => delete removing.value[productId],
    });
}

const removingGiftVoucher = ref(false);
function removeGiftVoucher() {
    removingGiftVoucher.value = true;
    router.post(
        route('gift-vouchers.remove-from-cart'),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                removingGiftVoucher.value = false;
            },
        },
    );
}

const vatRegistered = computed(() => !!usePage().props.vatRegistered);
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <main class="basket coy-storefront">
        <header class="basket-header coy-page-header">
            <div class="coy-container coy-page-header__inner header-inner">
                <div>
                    <h1 class="coy-page-header__title">Your basket</h1>
                    <p v-if="hasItems" class="coy-page-header__meta">
                        {{ itemCount }}
                        {{ itemCount === 1 ? 'item' : 'items' }}, ready when you
                        are.
                    </p>
                    <p v-else class="coy-page-header__meta">
                        A thoughtful choice can start here.
                    </p>
                </div>
                <Link
                    :href="route('products')"
                    class="continue-link coy-page-header__action"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Continue shopping
                </Link>
            </div>
        </header>

        <div class="coy-container basket-content">
            <section
                v-if="hasItems"
                class="delivery-progress"
                aria-labelledby="delivery-title"
            >
                <div class="delivery-copy">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path
                            d="M3 6h13v11H3zM16 10h3l2 3v4h-5zM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM18 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"
                        />
                    </svg>
                    <div>
                        <h2 id="delivery-title">
                            {{
                                freeShipping
                                    ? 'You have unlocked free UK delivery'
                                    : `${formatPrice(remaining)} away from free UK delivery`
                            }}
                        </h2>
                        <p>
                            {{
                                freeShipping
                                    ? 'That is one less thing to think about.'
                                    : 'Add another favourite and we will cover standard delivery.'
                            }}
                        </p>
                    </div>
                </div>
                <div
                    class="progress-track"
                    role="progressbar"
                    aria-label="Progress towards free delivery"
                    :aria-valuenow="Math.round(progress)"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <span :style="{ width: `${progress}%` }"></span>
                </div>
            </section>

            <div v-if="hasItems" class="basket-grid">
                <section class="basket-items" aria-labelledby="items-title">
                    <div class="section-heading">
                        <h2 id="items-title">Your selection</h2>
                        <span
                            >{{ itemCount }}
                            {{ itemCount === 1 ? 'item' : 'items' }}</span
                        >
                    </div>

                    <TransitionGroup name="item" tag="div" class="item-list">
                        <article
                            v-for="item in cartItems"
                            :key="item.product_id"
                            class="basket-item"
                            :class="{
                                'basket-item--removing':
                                    removing[item.product_id],
                            }"
                        >
                            <Link
                                :href="`/product/${item.product_id}`"
                                class="item-image"
                                :aria-label="`View ${item.name}`"
                            >
                                <img :src="item.image_url" :alt="item.name" />
                            </Link>
                            <div class="item-details">
                                <div class="item-topline">
                                    <div>
                                        <h3>
                                            <Link
                                                :href="`/product/${item.product_id}`"
                                                >{{ item.name }}</Link
                                            >
                                        </h3>
                                        <p class="unit-price">
                                            {{ formatPrice(item.cost) }} each
                                        </p>
                                    </div>
                                    <strong class="line-total">{{
                                        formatPrice(
                                            numberValue(item.cost) *
                                                numberValue(item.quantity),
                                        )
                                    }}</strong>
                                </div>
                                <div class="item-actions">
                                    <div
                                        class="quantity-field"
                                        :class="{
                                            'quantity-field--pending':
                                                pending[item.product_id],
                                        }"
                                    >
                                        <span class="quantity-label"
                                            >Quantity</span
                                        >
                                        <div class="quantity-control">
                                            <button
                                                type="button"
                                                :disabled="
                                                    item.quantity <= 1 ||
                                                    !!pending[item.product_id]
                                                "
                                                :aria-label="`Decrease quantity of ${item.name}`"
                                                @click="
                                                    updateQuantity(
                                                        item.product_id,
                                                        item.quantity - 1,
                                                    )
                                                "
                                            >
                                                −
                                            </button>
                                            <span aria-live="polite">{{
                                                item.quantity
                                            }}</span>
                                            <button
                                                type="button"
                                                :disabled="
                                                    item.quantity >=
                                                        item.stock_qty ||
                                                    !!pending[item.product_id]
                                                "
                                                :aria-label="`Increase quantity of ${item.name}`"
                                                @click="
                                                    updateQuantity(
                                                        item.product_id,
                                                        item.quantity + 1,
                                                    )
                                                "
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="remove-button"
                                        :disabled="removing[item.product_id]"
                                        @click="removeItem(item.product_id)"
                                    >
                                        <svg
                                            aria-hidden="true"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M4 7h16M9 7V4h6v3M8 7l1 13h6l1-13M11 11v5M13 11v5"
                                            />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </article>

                        <article
                            v-if="giftVoucher"
                            key="gift-voucher"
                            class="basket-item voucher-item"
                        >
                            <div
                                class="item-image voucher-image"
                                aria-hidden="true"
                            >
                                <svg viewBox="0 0 24 24">
                                    <path
                                        d="M3 9h18v12H3zM2 5h20v4H2zM12 5v16M12 5H8.5A2.5 2.5 0 1 1 11 2.5L12 5Zm0 0h3.5A2.5 2.5 0 1 0 13 2.5L12 5Z"
                                    />
                                </svg>
                            </div>
                            <div class="item-details">
                                <div class="item-topline">
                                    <div>
                                        <p class="item-label">
                                            A thoughtful gift
                                        </p>
                                        <h3>Gift voucher</h3>
                                        <p class="unit-price">
                                            {{
                                                giftVoucher.delivery_type ===
                                                'email'
                                                    ? 'Email delivery'
                                                    : 'Physical delivery'
                                            }}
                                            for {{ giftVoucher.recipient_name }}
                                        </p>
                                    </div>
                                    <strong class="line-total">{{
                                        formatPrice(giftVoucher.amount)
                                    }}</strong>
                                </div>
                                <div class="item-actions">
                                    <span class="voucher-note"
                                        >Valid for one year on all
                                        products</span
                                    >
                                    <button
                                        type="button"
                                        class="remove-button"
                                        :disabled="removingGiftVoucher"
                                        @click="removeGiftVoucher"
                                    >
                                        <svg
                                            aria-hidden="true"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M4 7h16M9 7V4h6v3M8 7l1 13h6l1-13M11 11v5M13 11v5"
                                            />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </article>
                    </TransitionGroup>
                </section>

                <aside class="order-summary" aria-labelledby="summary-title">
                    <div ref="summaryCard" class="summary-card">
                        <p class="coy-eyebrow">Almost yours</p>
                        <h2 id="summary-title">Order summary</h2>
                        <dl>
                            <div>
                                <dt>Subtotal</dt>
                                <dd>{{ formatPrice(cartTotal) }}</dd>
                            </div>
                            <div v-if="vatRegistered">
                                <dt>VAT</dt>
                                <dd>Included</dd>
                            </div>
                            <div>
                                <dt>Delivery</dt>
                                <dd>{{ shippingLabel }}</dd>
                            </div>
                        </dl>
                        <div class="summary-total">
                            <span>Total</span
                            ><strong>{{ formatPrice(finalTotal) }}</strong>
                        </div>
                        <Link href="/checkout" class="checkout-button">
                            Continue to checkout
                            <svg aria-hidden="true" viewBox="0 0 24 24">
                                <path d="M5 12h14m-7-7 7 7-7 7" />
                            </svg>
                        </Link>
                        <div class="checkout-reassurance">
                            <p>
                                <svg aria-hidden="true" viewBox="0 0 24 24">
                                    <rect
                                        x="4"
                                        y="10"
                                        width="16"
                                        height="11"
                                        rx="2"
                                    />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg
                                >Secure checkout
                            </p>
                            <p>
                                <svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="m4 12 5 5L20 6" /></svg
                                >30-day returns
                            </p>
                        </div>
                    </div>
                    <p class="help-copy">
                        Need help with your order?
                        <Link :href="route('contact')">Contact us</Link>
                    </p>
                </aside>
            </div>

            <section v-else class="empty-basket">
                <div class="empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4ZM3 6h18M16 10a4 4 0 0 1-8 0"
                        />
                    </svg>
                </div>
                <p class="coy-eyebrow">Room for something lovely</p>
                <h2 class="coy-heading">Your basket is waiting</h2>
                <p>
                    Explore thoughtful scents and gifts chosen to make everyday
                    moments feel more personal.
                </p>
                <Link
                    :href="route('products')"
                    class="coy-button coy-button--primary"
                    >Browse all products</Link
                >
                <Link
                    :href="route('scent-finder.index')"
                    class="empty-secondary"
                    >Or find your perfect scent</Link
                >
            </section>
        </div>
    </main>

    <Transition name="mobile-checkout">
        <div v-if="hasItems && !summaryInView" class="mobile-checkout-bar">
            <Link href="/checkout" class="mobile-checkout-button">
                <span>Checkout</span>
                <strong>{{ formatPrice(finalTotal) }}</strong>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M5 12h14m-7-7 7 7-7 7" />
                </svg>
            </Link>
        </div>
    </Transition>

    <Footer />
</template>

<style scoped>
.basket {
    min-height: 100vh;
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
}
.basket-header {
    background: var(--coy-color-blush);
}
.header-inner {
    min-height: 7rem;
}
.continue-link {
    flex-shrink: 0;
}
.continue-link:hover {
    background: var(--coy-color-surface);
}
.continue-link svg {
    width: 1rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.basket-content {
    padding-block: clamp(2rem, 5vw, 4rem) clamp(4rem, 7vw, 7rem);
}
.delivery-progress {
    margin-bottom: 2rem;
    padding: 1.25rem 1.5rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-sm);
}
.delivery-copy {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.delivery-copy > svg {
    width: 2rem;
    flex: 0 0 auto;
    fill: none;
    stroke: var(--coy-color-accent);
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.delivery-copy h2 {
    margin: 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 1.3rem;
    font-weight: 600;
    line-height: 1.25;
}
.delivery-copy p {
    margin: 0.2rem 0 0;
    font-size: 1rem;
}
.progress-track {
    height: 0.45rem;
    margin-top: 1rem;
    overflow: hidden;
    background: var(--coy-color-border-soft);
    border-radius: 999px;
}
.progress-track span {
    height: 100%;
    display: block;
    background: var(--coy-color-accent);
    border-radius: inherit;
    transition: width 0.4s var(--coy-ease);
}
.basket-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(19rem, 23rem);
    gap: clamp(2rem, 5vw, 4rem);
    align-items: start;
}
.section-heading {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}
.section-heading h2 {
    margin: 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(1.75rem, 3vw, 2.25rem);
    font-weight: 600;
}
.section-heading span {
    font-size: 1rem;
    font-weight: 600;
}
.item-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.basket-item {
    position: relative;
    display: grid;
    grid-template-columns: 8.5rem minmax(0, 1fr);
    gap: 1.25rem;
    padding: 1.25rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-sm);
    transition:
        opacity 0.25s,
        transform 0.25s;
}
.basket-item--removing {
    opacity: 0.35;
    pointer-events: none;
    transform: translateX(-0.75rem);
}
.item-image {
    aspect-ratio: 1;
    display: grid;
    place-items: center;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border-radius: var(--coy-radius-md);
}
.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.item-details {
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1.25rem;
}
.item-topline {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}
.item-label {
    margin: 0;
    color: var(--coy-color-accent);
    font-size: 0.875rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.item-topline h3 {
    margin: 0.2rem 0 0;
    font-family: var(--coy-font-display);
    font-size: 1.4rem;
    font-weight: 600;
    line-height: 1.2;
}
.item-topline h3 a {
    color: var(--coy-color-heading);
    text-decoration: none;
}
.item-topline h3 a:hover {
    color: var(--coy-color-accent);
    text-decoration: underline;
    text-underline-offset: 0.25rem;
}
.unit-price {
    margin: 0.35rem 0 0;
    font-size: 1rem;
}
.line-total {
    flex: 0 0 auto;
    color: var(--coy-color-heading);
    font-size: 1.2rem;
}
.item-actions {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--coy-color-border-soft);
}
.quantity-field {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: opacity 0.2s;
}
.quantity-field--pending {
    opacity: 0.45;
    pointer-events: none;
}
.quantity-label {
    font-size: 1rem;
    font-weight: 600;
}
.quantity-control {
    height: 2.5rem;
    display: grid;
    grid-template-columns: 2.5rem 2.5rem 2.5rem;
    overflow: hidden;
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: 999px;
}
.quantity-control button {
    display: grid;
    place-items: center;
    padding: 0;
    color: var(--coy-color-heading);
    background: transparent;
    border: 0;
    font: 700 1.1rem var(--coy-font-body);
    cursor: pointer;
}
.quantity-control button:hover:not(:disabled) {
    background: var(--coy-color-blush);
}
.quantity-control button:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
.quantity-control span {
    display: grid;
    place-items: center;
    border-inline: 1px solid var(--coy-color-border-soft);
    font-size: 1rem;
    font-weight: 700;
}
.remove-button {
    min-height: 2.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.65rem;
    color: var(--coy-color-accent);
    background: transparent;
    border: 0;
    border-radius: var(--coy-radius-sm);
    font: 600 1rem var(--coy-font-body);
    cursor: pointer;
}
.remove-button:hover {
    background: var(--coy-color-surface-soft);
}
.remove-button svg {
    width: 1rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.voucher-item {
    border-color: var(--coy-color-rose-gold);
}
.voucher-image svg {
    width: 3.5rem;
    fill: none;
    stroke: var(--coy-color-accent);
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.voucher-note {
    font-size: 1rem;
}
.order-summary {
    position: sticky;
    top: calc(var(--coy-nav-height) + 1.5rem);
}
.summary-card {
    padding: 1.75rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-md);
}
.summary-card h2 {
    margin: 0.25rem 0 1.5rem;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 2rem;
    font-weight: 600;
}
.summary-card dl {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin: 0;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--coy-color-border);
}
.summary-card dl div {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    font-size: 1rem;
}
.summary-card dt,
.summary-card dd {
    margin: 0;
}
.summary-card dd {
    color: var(--coy-color-heading);
    font-weight: 600;
    text-align: right;
}
.summary-total {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.35rem 0;
}
.summary-total span {
    font-size: 1rem;
    font-weight: 600;
}
.summary-total strong {
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 2rem;
    line-height: 1;
}
.checkout-button {
    min-height: 3.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border: 1px solid var(--coy-color-accent);
    border-radius: 999px;
    font-size: 1rem;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
    transition:
        background 0.2s,
        transform 0.2s;
}
.checkout-button:hover {
    background: var(--coy-color-accent-hover);
    transform: translateY(-1px);
}
.checkout-button svg {
    width: 1rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.checkout-reassurance {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
    margin-top: 1rem;
}
.checkout-reassurance p {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin: 0;
    font-size: 0.875rem;
}
.checkout-reassurance svg {
    width: 0.9rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.help-copy {
    margin: 1rem 0 0;
    font-size: 1rem;
    text-align: center;
}
.help-copy a {
    color: var(--coy-color-accent);
    font-weight: 700;
    text-underline-offset: 0.25rem;
}
.empty-basket {
    max-width: 46rem;
    margin: auto;
    padding: clamp(3rem, 8vw, 6rem) 1.5rem;
    text-align: center;
}
.empty-icon {
    width: 5.5rem;
    height: 5.5rem;
    display: grid;
    place-items: center;
    margin: 0 auto 1.5rem;
    color: var(--coy-color-accent);
    background: var(--coy-color-blush);
    border-radius: 50%;
}
.empty-icon svg {
    width: 2.25rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.6;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.empty-basket h2 {
    margin: 0.35rem 0 0.75rem;
    font-size: clamp(2.4rem, 6vw, 3.75rem);
    font-weight: 500;
}
.empty-basket > p:not(.coy-eyebrow) {
    max-width: 35rem;
    margin: 0 auto 1.75rem;
    font-size: 1.125rem;
}
.empty-secondary {
    display: block;
    margin-top: 1rem;
    color: var(--coy-color-accent);
    font-size: 1rem;
    font-weight: 700;
    text-underline-offset: 0.3rem;
}
.mobile-checkout-bar {
    display: none;
}
.item-enter-active,
.item-leave-active {
    transition:
        opacity 0.25s,
        transform 0.25s;
}
.item-enter-from {
    opacity: 0;
    transform: translateY(0.5rem);
}
.item-leave-to {
    opacity: 0;
    transform: translateX(-0.75rem);
}
@media (max-width: 850px) {
    .basket-grid {
        grid-template-columns: 1fr;
    }
    .order-summary {
        position: static;
    }
    .summary-card {
        max-width: none;
    }
}
@media (max-width: 620px) {
    .basket {
        padding-bottom: 5rem;
    }
    .header-inner {
        min-height: 5.75rem;
    }
    .continue-link {
        padding-inline: 0.75rem;
    }
    .basket-item {
        grid-template-columns: 6.5rem minmax(0, 1fr);
        gap: 1rem;
        padding: 1rem 1.35rem 1.25rem 1rem;
    }
    .item-actions {
        align-items: center;
    }
    .delivery-progress {
        padding: 1rem;
    }
    .delivery-copy {
        align-items: flex-start;
    }
    .summary-card {
        padding: 1.4rem;
    }
    .mobile-checkout-bar {
        position: fixed;
        z-index: 55;
        right: 0;
        bottom: 0;
        left: 0;
        display: block;
        padding: 0.75rem var(--coy-gutter)
            calc(0.75rem + env(safe-area-inset-bottom));
        background: rgb(255 253 251 / 96%);
        border-top: 1px solid var(--coy-color-border);
        box-shadow: 0 -10px 30px rgb(52 42 40 / 14%);
        backdrop-filter: blur(10px);
    }
    .mobile-checkout-button {
        min-height: 3.25rem;
        display: grid;
        grid-template-columns: 1fr auto auto;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 1rem;
        color: var(--coy-color-on-accent);
        background: var(--coy-color-accent);
        border-radius: 999px;
        font-size: 1rem;
        font-weight: 700;
        text-decoration: none;
    }
    .mobile-checkout-button strong {
        padding-left: 0.75rem;
        border-left: 1px solid rgb(255 255 255 / 35%);
        font-size: 1.125rem;
    }
    .mobile-checkout-button svg {
        width: 1rem;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .mobile-checkout-enter-active,
    .mobile-checkout-leave-active {
        transition:
            opacity 0.2s,
            transform 0.25s var(--coy-ease);
    }
    .mobile-checkout-enter-from,
    .mobile-checkout-leave-to {
        opacity: 0;
        transform: translateY(100%);
    }
}
@media (max-width: 430px) {
    .basket-item {
        grid-template-columns: 1fr;
        gap: 1.1rem;
        padding: 1rem 1.35rem 1.25rem 1rem;
    }
    .item-image {
        width: 100%;
        max-height: 13rem;
        aspect-ratio: 4 / 3;
    }
    .item-image img {
        object-fit: contain;
    }
    .item-topline {
        flex-direction: column;
        gap: 0.5rem;
    }
    .line-total {
        font-size: 1.25rem;
    }
    .quantity-field {
        width: auto;
    }
    .quantity-label {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .quantity-control {
        grid-template-columns: 2.35rem 2.35rem 2.35rem;
    }
    .item-actions {
        min-width: 0;
        flex-direction: row;
    }
    .remove-button {
        max-width: 100%;
        flex: 0 0 auto;
        align-self: center;
        margin: 0;
        padding-inline: 0.5rem;
    }
}
</style>
