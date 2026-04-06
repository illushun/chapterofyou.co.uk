<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

interface CartItem {
    id: number; product_id: number;
    product: { id: number; name: string; cost: number; stock_qty: number; images: { image: string }[]; };
    quantity: number;
}
interface Address {
    id: number; line_1: string; line_2: string | null;
    city: string; county: string | null; postcode: string; country: string; is_default: boolean;
}
interface Summary {
    subtotal: number; shipping: number; tax: number; voucher_discount: number; total: number;
}
interface AppliedVoucher { code: string; discount: number; type: string; value: number; }

const props = defineProps<{
    summary: Summary;
    cartItems: CartItem[];
    addresses: Address[];
    appliedVoucher: AppliedVoucher | null;
    isGuest: boolean;
}>();

const page = usePage();
const auth = computed(() => (page.props.auth as any) ?? {});

// ── Form fields ────────────────────────────────────────────────────────────
const email = ref(auth.value.user?.email ?? '');
const fullName = ref(auth.value.user?.name ?? '');
const addressLine1 = ref('');
const addressLine2 = ref('');
const city = ref('');
const county = ref('');
const postcode = ref('');
const country = ref('United Kingdom');
const telephone = ref('');
const selectedAddressId = ref<number | null>(null);

// Pre-fill saved address for logged-in users
if (props.addresses.length) {
    const def = props.addresses.find(a => a.is_default) ?? props.addresses[0];
    selectedAddressId.value = def.id;
    addressLine1.value = def.line_1;
    addressLine2.value = def.line_2 ?? '';
    city.value = def.city;
    county.value = def.county ?? '';
    postcode.value = def.postcode;
    country.value = def.country;
}

function selectSavedAddress(addr: Address) {
    selectedAddressId.value = addr.id;
    addressLine1.value = addr.line_1;
    addressLine2.value = addr.line_2 ?? '';
    city.value = addr.city;
    county.value = addr.county ?? '';
    postcode.value = addr.postcode;
    country.value = addr.country;
}

// ── Voucher ────────────────────────────────────────────────────────────────
const voucherCode = ref('');
const voucherLoading = ref(false);
const voucherError = ref('');
const voucherSuccess = ref('');
const appliedVoucher = ref(props.appliedVoucher);

async function applyVoucher() {
    if (!voucherCode.value.trim()) return;
    voucherLoading.value = true;
    voucherError.value = '';
    try {
        const res = await axios.post(route('voucher.apply'), { code: voucherCode.value.trim() });
        appliedVoucher.value = res.data;
        voucherSuccess.value = res.data.message;
        voucherCode.value = '';
        router.reload({ only: ['summary'] });
    } catch (e: any) {
        voucherError.value = e?.response?.data?.error ?? 'Invalid voucher code.';
    } finally {
        voucherLoading.value = false;
    }
}
async function removeVoucher() {
    await axios.post(route('voucher.remove'));
    appliedVoucher.value = null;
    voucherSuccess.value = '';
    router.reload({ only: ['summary'] });
}

// ── Stripe ─────────────────────────────────────────────────────────────────
const stripeLoaded = ref(false);
const cardElementMounted = ref(false);
const paymentProcessing = ref(false);
const paymentError = ref('');
const prButtonAvailable = ref(false);

let stripe: any = null;
let elements: any = null;
let cardElement: any = null;
let paymentRequest: any = null;

const fmt = (v: number) => `£${Number(v).toFixed(2)}`;

onMounted(() => {
    if ((window as any).Stripe) {
        initStripe();
    } else {
        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.onload = initStripe;
        document.head.appendChild(script);
    }
});

function initStripe() {
    stripe = (window as any).Stripe(import.meta.env.VITE_STRIPE_KEY);
    elements = stripe.elements();

    // ── Standard card element ────────────────────────────────────────────
    cardElement = elements.create('card', {
        style: {
            base: {
                fontFamily: "'Nunito', sans-serif",
                fontSize: '16px',
                color: '#2d1a1a',
                '::placeholder': { color: '#c9a4a4' },
            },
            invalid: { color: '#c84040' },
        },
        hidePostalCode: true,
    });
    cardElement.mount('#stripe-card-element');
    cardElement.on('ready', () => { cardElementMounted.value = true; });
    cardElement.on('change', (e: any) => { paymentError.value = e.error?.message ?? ''; });

    // ── Payment Request Button — Apple Pay / Google Pay ──────────────────
    paymentRequest = stripe.paymentRequest({
        country: 'GB',
        currency: 'gbp',
        total: { label: 'Chapter of You', amount: Math.round(props.summary.total * 100) },
        requestPayerName: true,
        requestPayerEmail: true,
    });

    paymentRequest.canMakePayment().then((result: any) => {
        if (!result) return;
        prButtonAvailable.value = true;
        const prButton = elements.create('paymentRequestButton', {
            paymentRequest,
            style: {
                paymentRequestButton: { type: 'buy', theme: 'dark', height: '48px' },
            },
        });
        prButton.mount('#stripe-pr-button');
    });

    // Handle Apple Pay / Google Pay confirmation
    paymentRequest.on('paymentmethod', async (ev: any) => {
        if (!validateForm(true)) {
            ev.complete('fail');
            return;
        }
        paymentProcessing.value = true;
        paymentError.value = '';
        try {
            const { data } = await axios.get(route('checkout.payment-intent'));
            const { paymentIntent, error } = await stripe.confirmCardPayment(
                data.clientSecret,
                { payment_method: ev.paymentMethod.id },
                { handleActions: false }
            );
            if (error) {
                ev.complete('fail');
                paymentError.value = error.message;
                paymentProcessing.value = false;
                return;
            }
            ev.complete('success');
            // Fill name/email from wallet if guest hasn't typed them
            if (!fullName.value && ev.payerName) fullName.value = ev.payerName;
            if (!email.value && ev.payerEmail) email.value = ev.payerEmail;
            await submitOrder(paymentIntent.id, ev.paymentMethod.type);
        } catch {
            ev.complete('fail');
            paymentError.value = 'Payment failed. Please try again.';
            paymentProcessing.value = false;
        }
    });

    stripeLoaded.value = true;
}

// ── Card payment ───────────────────────────────────────────────────────────
async function payWithCard() {
    if (paymentProcessing.value) return;
    if (!validateForm()) return;
    paymentProcessing.value = true;
    paymentError.value = '';
    try {
        const { data } = await axios.get(route('checkout.payment-intent'));
        const { paymentIntent, error } = await stripe.confirmCardPayment(data.clientSecret, {
            payment_method: { card: cardElement, billing_details: { name: fullName.value, email: email.value } },
        });
        if (error) {
            paymentError.value = error.message;
            paymentProcessing.value = false;
            return;
        }
        await submitOrder(paymentIntent.id, 'card');
    } catch {
        paymentError.value = 'Payment failed. Please try again.';
        paymentProcessing.value = false;
    }
}

async function submitOrder(paymentIntentId: string, paymentType: string) {
    try {
        const res = await axios.post(route('checkout.process'), {
            paymentIntentId,
            paymentType,
            email: email.value,
            fullName: fullName.value,
            addressLine1: addressLine1.value,
            addressLine2: addressLine2.value,
            city: city.value,
            postcode: postcode.value,
            county: county.value,
            country: country.value,
            telephone: telephone.value,
        });
        // Follow the Inertia redirect from the server
        if (res.data?.redirect) {
            window.location.href = res.data.redirect;
        } else {
            router.visit(route('home'));
        }
    } catch (err: any) {
        paymentError.value = err?.response?.data?.message ?? 'Order submission failed. Please try again.';
        paymentProcessing.value = false;
    }
}

// ── Validation ─────────────────────────────────────────────────────────────
const formErrors = ref<Record<string, string>>({});

function validateForm(walletPay = false): boolean {
    formErrors.value = {};
    if (!fullName.value.trim()) formErrors.value.fullName = 'Name is required.';
    if (!email.value.trim()) formErrors.value.email = 'Email is required.';
    // Address not required for wallet pay — it's collected by Apple/Google Pay
    if (!walletPay) {
        if (!addressLine1.value.trim()) formErrors.value.addressLine1 = 'Address is required.';
        if (!city.value.trim()) formErrors.value.city = 'City is required.';
        if (!postcode.value.trim()) formErrors.value.postcode = 'Postcode is required.';
    }
    return Object.keys(formErrors.value).length === 0;
}

const isFormValid = computed(() =>
    fullName.value.trim() && email.value.trim() &&
    addressLine1.value.trim() && city.value.trim() && postcode.value.trim()
);
</script>

<template>
    <NavBar />

    <Head title="Checkout | Chapter of You" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="ck">
        <div class="ck-wrap">

            <!-- Header -->
            <header class="ck-header">
                <h1 class="ck-title">Checkout</h1>
                <p v-if="isGuest" class="ck-guest-note">
                    Checking out as a guest.
                    <a :href="route('login')" class="ck-guest-link">Sign in</a>
                    to save your details for next time.
                </p>
            </header>

            <div class="ck-grid">

                <!-- ── Left: form ── -->
                <div class="ck-form-col">

                    <!-- ── Express checkout ── -->
                    <div v-if="prButtonAvailable" class="ck-card ck-express">
                        <p class="ck-express-label">Express Checkout</p>
                        <div id="stripe-pr-button"></div>
                        <div class="ck-or-divider">
                            <span class="ck-or-text">or pay with card</span>
                        </div>
                    </div>

                    <!-- Contact details -->
                    <div class="ck-card">
                        <h2 class="ck-card-title">Contact Details</h2>
                        <div class="ck-field-row">
                            <div class="ck-field">
                                <label class="ck-label">Full Name</label>
                                <input v-model="fullName" type="text" autocomplete="name" class="ck-input"
                                    :class="{ 'ck-input--err': formErrors.fullName }" placeholder="Jane Smith" />
                                <p v-if="formErrors.fullName" class="ck-err">{{ formErrors.fullName }}</p>
                            </div>
                            <div class="ck-field">
                                <label class="ck-label">Email Address</label>
                                <input v-model="email" type="email" autocomplete="email" class="ck-input"
                                    :class="{ 'ck-input--err': formErrors.email }" placeholder="jane@example.com" />
                                <p v-if="formErrors.email" class="ck-err">{{ formErrors.email }}</p>
                            </div>
                        </div>
                        <div class="ck-field">
                            <label class="ck-label">Phone <span class="ck-label-note">(optional)</span></label>
                            <input v-model="telephone" type="tel" autocomplete="tel" class="ck-input"
                                placeholder="+44 7700 900000" />
                        </div>
                    </div>

                    <!-- Saved addresses (logged-in users only) -->
                    <div v-if="!isGuest && addresses.length" class="ck-card">
                        <h2 class="ck-card-title">Saved Addresses</h2>
                        <div class="ck-address-list">
                            <button v-for="addr in addresses" :key="addr.id" type="button"
                                @click="selectSavedAddress(addr)" class="ck-address-option"
                                :class="{ 'ck-address-option--active': selectedAddressId === addr.id }">
                                <span class="ck-addr-radio">
                                    <span v-if="selectedAddressId === addr.id" class="ck-addr-dot"></span>
                                </span>
                                <span>{{ addr.line_1 }}, {{ addr.city }}, {{ addr.postcode }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Delivery address -->
                    <div class="ck-card">
                        <h2 class="ck-card-title">Delivery Address</h2>
                        <div class="ck-field">
                            <label class="ck-label">Address Line 1</label>
                            <input v-model="addressLine1" type="text" autocomplete="address-line1" class="ck-input"
                                :class="{ 'ck-input--err': formErrors.addressLine1 }" placeholder="12 Rose Lane" />
                            <p v-if="formErrors.addressLine1" class="ck-err">{{ formErrors.addressLine1 }}</p>
                        </div>
                        <div class="ck-field">
                            <label class="ck-label">Address Line 2 <span class="ck-label-note">(optional)</span></label>
                            <input v-model="addressLine2" type="text" autocomplete="address-line2" class="ck-input"
                                placeholder="Apartment, suite, etc." />
                        </div>
                        <div class="ck-field-row">
                            <div class="ck-field">
                                <label class="ck-label">Town / City</label>
                                <input v-model="city" type="text" autocomplete="address-level2" class="ck-input"
                                    :class="{ 'ck-input--err': formErrors.city }" placeholder="London" />
                                <p v-if="formErrors.city" class="ck-err">{{ formErrors.city }}</p>
                            </div>
                            <div class="ck-field">
                                <label class="ck-label">County <span class="ck-label-note">(optional)</span></label>
                                <input v-model="county" type="text" autocomplete="address-level1" class="ck-input"
                                    placeholder="Essex" />
                            </div>
                        </div>
                        <div class="ck-field-row">
                            <div class="ck-field">
                                <label class="ck-label">Postcode</label>
                                <input v-model="postcode" type="text" autocomplete="postal-code" class="ck-input"
                                    :class="{ 'ck-input--err': formErrors.postcode }" placeholder="SW1A 1AA"
                                    style="text-transform:uppercase" />
                                <p v-if="formErrors.postcode" class="ck-err">{{ formErrors.postcode }}</p>
                            </div>
                            <div class="ck-field">
                                <label class="ck-label">Country</label>
                                <input v-model="country" type="text" autocomplete="country-name" class="ck-input" />
                            </div>
                        </div>
                    </div>

                    <!-- Card payment -->
                    <div class="ck-card">
                        <h2 class="ck-card-title">Pay by Card</h2>

                        <div class="ck-stripe-wrap">
                            <div id="stripe-card-element" class="ck-stripe-el">
                                <div v-if="!cardElementMounted" class="ck-stripe-loading">
                                    <span class="ck-pulse"></span>
                                    Loading secure payment…
                                </div>
                            </div>
                        </div>

                        <p v-if="paymentError" class="ck-pay-error">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4M12 16h.01" />
                            </svg>
                            {{ paymentError }}
                        </p>

                        <button @click="payWithCard" :disabled="paymentProcessing || !isFormValid || !stripeLoaded"
                            class="ck-pay-btn">
                            <svg v-if="paymentProcessing" class="ck-spinner" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3"
                                    stroke-linecap="round" />
                            </svg>
                            <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="4" width="22" height="16" rx="2" />
                                <line x1="1" y1="10" x2="23" y2="10" />
                            </svg>
                            {{ paymentProcessing ? 'Processing…' : `Pay ${fmt(summary.total)}` }}
                        </button>

                        <div class="ck-trust">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            Secured by Stripe — your card details are never stored.
                        </div>
                    </div>

                </div>

                <!-- ── Right: order summary ── -->
                <aside class="ck-summary-col">
                    <div class="ck-card ck-summary">

                        <h2 class="ck-card-title">Your Order</h2>

                        <!-- Items -->
                        <div class="ck-items">
                            <div v-for="item in cartItems" :key="item.id" class="ck-item">
                                <div class="ck-item-img">
                                    <img :src="item.product.images?.[0]?.image || '/images/placeholder.jpg'"
                                        :alt="item.product.name" />
                                    <span class="ck-qty-badge">{{ item.quantity }}</span>
                                </div>
                                <p class="ck-item-name">{{ item.product.name }}</p>
                                <p class="ck-item-price">{{ fmt(item.product.cost * item.quantity) }}</p>
                            </div>
                        </div>

                        <!-- Voucher -->
                        <div class="ck-voucher-wrap">
                            <div v-if="!appliedVoucher" class="ck-voucher-row">
                                <input v-model="voucherCode" type="text" class="ck-voucher-input"
                                    placeholder="Discount code" :disabled="voucherLoading"
                                    @keyup.enter="applyVoucher" />
                                <button @click="applyVoucher" :disabled="voucherLoading || !voucherCode.trim()"
                                    class="ck-voucher-btn">Apply</button>
                            </div>
                            <p v-if="voucherError" class="ck-voucher-err">{{ voucherError }}</p>
                            <div v-if="appliedVoucher" class="ck-voucher-applied">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                {{ appliedVoucher.code }} — saved {{ fmt(appliedVoucher.discount) }}
                                <button @click="removeVoucher" class="ck-voucher-rm">✕</button>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="ck-totals">
                            <div class="ck-total-row">
                                <span class="ck-total-lbl">Subtotal</span>
                                <span>{{ fmt(summary.subtotal) }}</span>
                            </div>
                            <div class="ck-total-row">
                                <span class="ck-total-lbl">Shipping</span>
                                <span>{{ summary.shipping === 0 ? 'FREE' : fmt(summary.shipping) }}</span>
                            </div>
                            <div v-if="summary.voucher_discount > 0" class="ck-total-row ck-total-discount">
                                <span>Discount</span>
                                <span>−{{ fmt(summary.voucher_discount) }}</span>
                            </div>
                            <div class="ck-total-row">
                                <span class="ck-total-lbl">VAT (20%)</span>
                                <span>{{ fmt(summary.tax) }}</span>
                            </div>
                            <div class="ck-total-grand">
                                <span>Total</span>
                                <span class="ck-grand-val">{{ fmt(summary.total) }}</span>
                            </div>
                        </div>

                        <!-- Free delivery nudge -->
                        <p v-if="summary.subtotal < 50" class="ck-free-nudge">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13" rx="1" />
                                <path d="M16 8h4l3 3v5h-7V8z" />
                                <circle cx="5.5" cy="18.5" r="2.5" />
                                <circle cx="18.5" cy="18.5" r="2.5" />
                            </svg>
                            Spend {{ fmt(50 - summary.subtotal) }} more for free delivery
                        </p>

                        <!-- Policy links -->
                        <div class="ck-policy-links">
                            <a :href="route('returns')" class="ck-policy-link">Returns</a>
                            <span>·</span>
                            <a :href="route('privacy')" class="ck-policy-link">Privacy</a>
                            <span>·</span>
                            <a :href="route('terms')" class="ck-policy-link">Terms</a>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.ck {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.ck-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 3rem 1.25rem 5rem;
}

.ck-header {
    margin-bottom: 2rem;
}

.ck-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
}

.ck-guest-note {
    font-size: 0.85rem;
    color: #6b4f4f;
    margin-top: 0.4rem;
}

.ck-guest-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
}

.ck-guest-link:hover {
    text-decoration: underline;
}

/* Grid */
.ck-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 900px) {
    .ck-grid {
        grid-template-columns: 1fr;
    }
}

.ck-form-col {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ck-summary-col {
    display: flex;
    flex-direction: column;
}

@media (min-width: 900px) {
    .ck-summary-col {
        position: sticky;
        top: 84px;
    }
}

/* Cards */
.ck-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    overflow: hidden;
}

.ck-card::after {
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

.ck-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #2d1a1a;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f0dcd8;
}

/* Express checkout */
.ck-express {
    gap: 0.85rem;
}

.ck-express-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #c9a4a4;
    text-align: center;
}

.ck-or-divider {
    position: relative;
    text-align: center;
    border-top: 1px solid #f0dcd8;
    margin-top: 0.25rem;
}

.ck-or-text {
    position: relative;
    top: -0.55rem;
    background: #fffafa;
    padding: 0 0.75rem;
    font-size: 0.72rem;
    color: #c9a4a4;
    font-style: italic;
}

/* Fields */
.ck-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 560px) {
    .ck-field-row {
        grid-template-columns: 1fr;
    }
}

.ck-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.ck-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b4f4f;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.ck-label-note {
    font-weight: 400;
    text-transform: none;
    font-style: italic;
    color: #9a7070;
}

.ck-input {
    padding: 0.68rem 0.9rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    outline: none;
    width: 100%;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.ck-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.ck-input--err {
    border-color: #c84040;
}

.ck-err {
    font-size: 0.75rem;
    color: #c84040;
}

/* Saved addresses */
.ck-address-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.ck-address-option {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.65rem 0.9rem;
    border-radius: 10px;
    border: 1px solid #e5c9c7;
    background: #fdf4f3;
    cursor: pointer;
    text-align: left;
    width: 100%;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    color: #6b4f4f;
    transition: border-color 0.15s, background 0.15s;
}

.ck-address-option--active {
    border-color: #8c4a50;
    background: #fff5f5;
    color: #2d1a1a;
}

.ck-addr-radio {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #e5c9c7;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.15s;
}

.ck-address-option--active .ck-addr-radio {
    border-color: #8c4a50;
}

.ck-addr-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #8c4a50;
}

/* Stripe element */
.ck-stripe-wrap {
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    padding: 0.75rem 0.9rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.ck-stripe-wrap:focus-within {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.ck-stripe-el {
    min-height: 24px;
}

.ck-stripe-loading {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: #c9a4a4;
    font-style: italic;
}

.ck-pulse {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #c9a4a4;
    animation: ck-pulse 1s ease-in-out infinite;
}

@keyframes ck-pulse {

    0%,
    100% {
        opacity: 0.3;
    }

    50% {
        opacity: 1;
    }
}

.ck-pay-error {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    color: #c84040;
    font-weight: 500;
}

/* Pay button */
.ck-pay-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.9rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(168, 80, 88, 0.25);
    transition: transform 0.2s, box-shadow 0.2s;
}

.ck-pay-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(168, 80, 88, 0.32);
}

.ck-pay-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.ck-trust {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.72rem;
    color: #9a7070;
    justify-content: center;
}

.ck-spinner {
    width: 16px;
    height: 16px;
    animation: ck-spin 0.8s linear infinite;
}

@keyframes ck-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* Summary */
.ck-summary {
    gap: 0;
}

.ck-summary .ck-card-title {
    margin-bottom: 1rem;
}

.ck-items {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0dcd8;
}

.ck-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ck-item-img {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    border: 1px solid #e5c9c7;
    overflow: hidden;
    flex-shrink: 0;
}

.ck-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ck-qty-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #8c4a50;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ck-item-name {
    flex: 1;
    font-size: 0.85rem;
    font-weight: 600;
    color: #2d1a1a;
    line-height: 1.3;
}

.ck-item-price {
    font-size: 0.88rem;
    font-weight: 700;
    color: #2d1a1a;
    flex-shrink: 0;
}

/* Voucher */
.ck-voucher-wrap {
    padding: 0.85rem 0;
    border-bottom: 1px solid #f0dcd8;
}

.ck-voucher-row {
    display: flex;
}

.ck-voucher-input {
    flex: 1;
    padding: 0.55rem 0.85rem;
    border: 1px solid #e5c9c7;
    border-right: none;
    border-radius: 8px 0 0 8px;
    background: #fdf4f3;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    color: #2d1a1a;
    outline: none;
    transition: border-color 0.2s;
}

.ck-voucher-input:focus {
    border-color: #8c4a50;
}

.ck-voucher-btn {
    padding: 0.55rem 1rem;
    border: 1px solid #e5c9c7;
    border-radius: 0 8px 8px 0;
    background: #2d1a1a;
    color: #fffafa;
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
    white-space: nowrap;
}

.ck-voucher-btn:hover:not(:disabled) {
    background: #4a2828;
}

.ck-voucher-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.ck-voucher-err {
    font-size: 0.75rem;
    color: #c84040;
    margin-top: 0.4rem;
}

.ck-voucher-applied {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: #2d7a3a;
    font-weight: 600;
    background: #f2faf2;
    border: 1px solid #a8d8b0;
    border-radius: 8px;
    padding: 0.45rem 0.75rem;
}

.ck-voucher-rm {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    color: #9a7070;
    font-size: 0.75rem;
    transition: color 0.15s;
}

.ck-voucher-rm:hover {
    color: #c84040;
}

/* Totals */
.ck-totals {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0.85rem 0;
    border-bottom: 1px solid #f0dcd8;
}

.ck-total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
}

.ck-total-lbl {
    color: #6b4f4f;
}

.ck-total-discount {
    color: #2d7a3a;
    font-weight: 600;
}

.ck-total-grand {
    display: flex;
    justify-content: space-between;
    font-size: 1rem;
    font-weight: 700;
    color: #2d1a1a;
    padding-top: 0.6rem;
    border-top: 1px solid #e5c9c7;
    margin-top: 0.35rem;
}

.ck-grand-val {
    font-size: 1.2rem;
    color: #8c4a50;
    font-weight: 800;
}

.ck-free-nudge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.72rem;
    color: #9a7070;
    font-style: italic;
    padding-top: 0.25rem;
}

.ck-policy-links {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.72rem;
    color: #c9a4a4;
    justify-content: center;
    padding-top: 0.25rem;
}

.ck-policy-link {
    color: #9a7070;
    text-decoration: none;
}

.ck-policy-link:hover {
    color: #8c4a50;
    text-decoration: underline;
}
</style>
