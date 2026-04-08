<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref, computed, nextTick } from 'vue';
import axios from 'axios';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

declare const Stripe: any;
declare const route: any;

const getRoute = (name: string, params: any = {}, absolute: boolean = true) => {
    if (typeof window.route === 'function') return window.route(name, params, absolute);
    return `/${name}`;
};

const loadStripeScript = (): Promise<void> => new Promise((resolve) => {
    if (typeof Stripe !== 'undefined') return resolve();
    const scriptId = 'stripe-script';
    let script = document.getElementById(scriptId) as HTMLScriptElement;
    if (script) { script.onload = () => resolve(); }
    else {
        script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.id = scriptId;
        script.onload = () => resolve();
        document.head.appendChild(script);
    }
});

const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

interface CartItem {
    id: number; product_id: number;
    product: { name: string; cost: number; image_url: string; }
    quantity: number;
}
interface Summary { subtotal: number; vat_component: number; shipping: number; total: number; voucher_discount: number; }
interface Address {
    id: number; user_id: number; type: string; is_default: boolean;
    line_1: string; line_2: string; city: string; county: string; postcode: string; country: string;
}

const props = defineProps<{
    cartItems: CartItem[];
    summary: Summary;
    addresses: Address[];
    appliedVoucher: { code: string; discount: number; type: string; value: number } | null;
    isGuest: boolean;
    giftVoucher: {
        amount: number;
        delivery_type: 'email' | 'physical';
        recipient_name: string;
        recipient_email: string | null;
    } | null;
}>();

const seo = useSeoHead({ noIndex: true });

const isProcessing = ref(false);
const paymentError = ref<string | null>(null);
const hasClientSecret = ref(false);
const isLoadingInitialData = ref(true);
const selectedAddressId = ref<number | null>(null);
const isManualAddressVisible = ref(false);

const voucherCode = ref('');
const voucherLoading = ref(false);
const voucherError = ref<string | null>(null);
const voucherSuccess = ref<string | null>(null);
const activeVoucher = ref(props.appliedVoucher ?? null);

const voucherDiscount = computed(() => activeVoucher.value?.discount ?? 0);
const computedTotal = computed(() =>
    Math.max(0, Number(props.summary.subtotal) - voucherDiscount.value) + (Number(props.summary.shipping) || 0)
);

async function applyVoucher() {
    if (!voucherCode.value.trim()) return;
    voucherLoading.value = true; voucherError.value = null; voucherSuccess.value = null;
    try {
        const { data } = await axios.post(route('checkout.voucher.apply'), { code: voucherCode.value.trim() });
        activeVoucher.value = data; voucherSuccess.value = data.message; voucherCode.value = '';
        await initializeStripe();
    } catch (err: any) {
        const r = err.response;
        voucherError.value = r?.data?.error || r?.data?.errors?.code?.[0] || r?.data?.message || `Server error (${r?.status ?? 'unknown'}).`;
    } finally { voucherLoading.value = false; }
}

async function removeVoucher() {
    await axios.post(route('checkout.voucher.remove'));
    activeVoucher.value = null; voucherSuccess.value = null; voucherError.value = null;
    await initializeStripe();
}

const paymentContainer = ref<HTMLElement | null>(null);
const stripe = ref<any>(null);
const elements = ref<any>(null);
const paymentElement = ref<any>(null);
const clientSecret = ref('');
const paymentIntentId = ref('');

const addressForm = useForm({
    email: '', fullName: '', telephone: '',
    addressLine1: '', addressLine2: '',
    city: '', county: '', postcode: '', country: 'United Kingdom',
    saveInfo: false,
});

const formatAddress = (address: Address): string[] =>
    [address.line_1, address.line_2, address.city, address.county, address.postcode, address.country].filter(Boolean);

const hasItems = computed(() => props.cartItems.length > 0 || !!props.giftVoucher);
const isShippingAddressVisible = computed(() => selectedAddressId.value !== null || isManualAddressVisible.value);

const fmt = (amount: number | string): string => {
    const n = Number(amount);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};

const selectAddress = (address: Address) => {
    selectedAddressId.value = address.id;
    addressForm.addressLine1 = address.line_1; addressForm.addressLine2 = address.line_2;
    addressForm.city = address.city; addressForm.county = address.county;
    addressForm.postcode = address.postcode; addressForm.country = address.country;
    isManualAddressVisible.value = true;
};

const clearAddressSelection = () => {
    selectedAddressId.value = null;
    addressForm.addressLine1 = ''; addressForm.addressLine2 = '';
    addressForm.city = ''; addressForm.county = '';
    addressForm.postcode = ''; addressForm.country = 'United Kingdom';
    isManualAddressVisible.value = false;
};

const fetchPaymentIntent = async () => {
    try {
        const response = await fetch(getRoute('checkout.payment_intent'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
        });
        const data = await response.json();
        if (!response.ok || data.error) throw new Error(data.error || 'Failed to fetch payment intent.');
        clientSecret.value = data.clientSecret;
        paymentIntentId.value = data.paymentIntentId;
    } catch (error: any) {
        paymentError.value = 'Could not initialise payment: ' + error.message;
    }
};

const initializeStripe = async () => {
    await fetchPaymentIntent();
    if (!clientSecret.value || paymentError.value) { isLoadingInitialData.value = false; return; }
    hasClientSecret.value = true;
    await nextTick();
    stripe.value = Stripe(import.meta.env.VITE_STRIPE_KEY);
    elements.value = stripe.value.elements({ clientSecret: clientSecret.value });
    paymentElement.value = elements.value.create('payment', {
        layout: 'tabs',
        appearance: { theme: 'stripe', variables: { colorPrimary: '#8c4a50', colorText: '#2d1a1a', colorBackground: '#fffafa' } },
    });
    const maxRetries = 10; let attempts = 0; let mounted = false;
    while (attempts < maxRetries && !mounted) {
        const container = paymentContainer.value;
        if (container) {
            try { paymentElement.value.mount(container); mounted = true; }
            catch (e) { await delay(50); }
        } else { await delay(50); }
        attempts++;
    }
    if (!mounted) paymentError.value = 'Payment system failed to load.';
    isLoadingInitialData.value = false;
};

const handleCardPayment = async () => {
    if (!addressForm.email || !addressForm.addressLine1 || !addressForm.postcode || !addressForm.fullName) {
        paymentError.value = 'Please complete all required fields.'; return;
    }
    isProcessing.value = true; paymentError.value = null;
    if (!stripe.value || !paymentElement.value) {
        paymentError.value = 'Payment system not loaded.'; isProcessing.value = false; return;
    }
    const { error: stripeError, paymentIntent } = await stripe.value.confirmPayment({
        elements: elements.value,
        confirmParams: {
            return_url: window.location.origin + getRoute('checkout.index', {}, false),
            payment_method_data: {
                billing_details: {
                    name: addressForm.fullName, email: addressForm.email,
                    phone: addressForm.telephone || undefined,
                    address: { line1: addressForm.addressLine1, line2: addressForm.addressLine2 || undefined, city: addressForm.city, state: addressForm.county || undefined, postal_code: addressForm.postcode, country: 'GB' }
                }
            }
        },
        redirect: 'if_required',
    });
    if (stripeError) { paymentError.value = stripeError.message || 'An error occurred.'; isProcessing.value = false; return; }
    if (paymentIntent?.status === 'succeeded') {
        router.post(getRoute('checkout.process_payment'), { ...addressForm.data(), paymentIntentId: paymentIntent.id, paymentType: 'card' }, {
            onError: (errors) => { paymentError.value = Object.values(errors)[0] as string || 'Order failed.'; },
            onFinish: () => { isProcessing.value = false; }
        });
    } else {
        paymentError.value = 'Payment status: ' + paymentIntent?.status;
        isProcessing.value = false;
    }
};

onMounted(async () => {
    // Guests always see the manual address form — no saved addresses
    if (props.isGuest) {
        isManualAddressVisible.value = true;
    } else {
        const defaultAddress = props.addresses.find(a => a.is_default);
        if (defaultAddress) { await nextTick(); selectAddress(defaultAddress); }
        else if (props.addresses.length === 0) { isManualAddressVisible.value = true; }
    }
    if (hasItems.value) { await loadStripeScript(); await initializeStripe(); }
    else { isLoadingInitialData.value = false; }
});

const vatRegistered = computed(() => !!(usePage().props.vatRegistered));
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="co">
        <div class="co-wrap">

            <!-- Header -->
            <header class="co-header">
                <h1 class="co-title">Checkout</h1>
                <div class="co-header-sub">
                    <a :href="getRoute('cart.view')" class="co-back">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                        Back to cart
                    </a>
                    <!-- Guest notice -->
                    <p v-if="isGuest" class="co-guest-note">
                        Checking out as a guest.
                        <a :href="getRoute('login')" class="co-guest-link">Sign in</a>
                        to save your details for next time.
                    </p>
                </div>
            </header>

            <!-- Empty cart -->
            <div v-if="!hasItems" class="co-empty">
                <p>No items in your cart.</p>
                <a :href="getRoute('products')" class="btn-rose">Browse Products</a>
            </div>

            <div v-else class="co-grid">

                <!-- Left column -->
                <div class="co-left">

                    <!-- Saved addresses — logged-in users only -->
                    <section v-if="!isGuest && addresses.length > 0" class="co-card">
                        <h2 class="co-card-title">Saved Addresses</h2>
                        <div class="co-address-grid">
                            <div v-for="address in addresses" :key="address.id" @click="selectAddress(address)"
                                class="co-address-card"
                                :class="{ 'co-address-card--selected': selectedAddressId === address.id }">
                                <div class="co-address-card-head">
                                    <span class="co-address-type">{{ address.type }}</span>
                                    <span v-if="address.is_default" class="co-address-default">Default</span>
                                </div>
                                <div class="co-address-lines">
                                    <span v-for="line in formatAddress(address)" :key="line">{{ line }}</span>
                                </div>
                            </div>
                        </div>
                        <button v-if="selectedAddressId !== null" @click="clearAddressSelection" type="button"
                            class="co-clear-btn">
                            Clear &amp; enter manually
                        </button>
                    </section>

                    <!-- Contact & shipping form -->
                    <section class="co-card">
                        <h2 class="co-card-title">
                            <span class="co-step">1</span>
                            Contact &amp; Shipping
                        </h2>

                        <form @submit.prevent="handleCardPayment" class="co-form">

                            <div class="co-field-row">
                                <div class="field">
                                    <label for="email" class="field-label">Email <span
                                            class="field-required">*</span></label>
                                    <input id="email" type="email" v-model="addressForm.email" required
                                        class="field-input" :class="{ 'field-input--error': addressForm.errors.email }"
                                        placeholder="you@example.com" autocomplete="email" />
                                    <p v-if="addressForm.errors.email" class="field-error">{{ addressForm.errors.email
                                    }}</p>
                                </div>
                                <div class="field">
                                    <label for="fullName" class="field-label">Full Name <span
                                            class="field-required">*</span></label>
                                    <input id="fullName" type="text" v-model="addressForm.fullName" required
                                        class="field-input"
                                        :class="{ 'field-input--error': addressForm.errors.fullName }"
                                        placeholder="Jane Smith" autocomplete="name" />
                                    <p v-if="addressForm.errors.fullName" class="field-error">{{
                                        addressForm.errors.fullName }}</p>
                                </div>
                            </div>

                            <div class="field" style="max-width: 280px;">
                                <label for="telephone" class="field-label">Phone <span
                                        class="field-optional">(optional)</span></label>
                                <input id="telephone" type="tel" v-model="addressForm.telephone" class="field-input"
                                    placeholder="07700 900000" autocomplete="tel" />
                            </div>

                            <div v-if="!isShippingAddressVisible" class="co-add-address-btn-wrap">
                                <button type="button" @click="isManualAddressVisible = true" class="co-add-address-btn">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 5v14M5 12h14" />
                                    </svg>
                                    Add Shipping Address
                                </button>
                            </div>

                            <div v-if="isShippingAddressVisible" class="co-address-fields">
                                <h3 class="co-address-fields-title">Shipping Address</h3>

                                <div class="field">
                                    <label for="addressLine1" class="field-label">Address Line 1 <span
                                            class="field-required">*</span></label>
                                    <input id="addressLine1" type="text" v-model="addressForm.addressLine1" required
                                        class="field-input"
                                        :class="{ 'field-input--error': addressForm.errors.addressLine1 }"
                                        placeholder="123 Example Street" autocomplete="address-line1" />
                                    <p v-if="addressForm.errors.addressLine1" class="field-error">{{
                                        addressForm.errors.addressLine1 }}</p>
                                </div>

                                <div class="field">
                                    <label for="addressLine2" class="field-label">Address Line 2 <span
                                            class="field-optional">(optional)</span></label>
                                    <input id="addressLine2" type="text" v-model="addressForm.addressLine2"
                                        class="field-input" placeholder="Apartment, suite, etc."
                                        autocomplete="address-line2" />
                                </div>

                                <div class="co-field-row co-field-row--3">
                                    <div class="field">
                                        <label for="city" class="field-label">City <span
                                                class="field-required">*</span></label>
                                        <input id="city" type="text" v-model="addressForm.city" required
                                            class="field-input"
                                            :class="{ 'field-input--error': addressForm.errors.city }"
                                            placeholder="London" autocomplete="address-level2" />
                                        <p v-if="addressForm.errors.city" class="field-error">{{ addressForm.errors.city
                                        }}</p>
                                    </div>
                                    <div class="field">
                                        <label for="postcode" class="field-label">Postcode <span
                                                class="field-required">*</span></label>
                                        <input id="postcode" type="text" v-model="addressForm.postcode" required
                                            class="field-input"
                                            :class="{ 'field-input--error': addressForm.errors.postcode }"
                                            placeholder="SW1A 0AA" autocomplete="postal-code" />
                                        <p v-if="addressForm.errors.postcode" class="field-error">{{
                                            addressForm.errors.postcode }}</p>
                                    </div>
                                    <div class="field">
                                        <label for="county" class="field-label">County <span
                                                class="field-optional">(optional)</span></label>
                                        <input id="county" type="text" v-model="addressForm.county" class="field-input"
                                            placeholder="Greater London" autocomplete="address-level1" />
                                    </div>
                                </div>

                                <div class="field" style="max-width: 200px;">
                                    <label for="country" class="field-label">Country</label>
                                    <input id="country" type="text" v-model="addressForm.country" readonly
                                        class="field-input field-input--readonly" autocomplete="country-name" />
                                </div>
                            </div>

                            <!-- Save info — logged-in users only -->
                            <label v-if="!isGuest" class="co-save-label">
                                <input type="checkbox" v-model="addressForm.saveInfo" class="co-save-check" />
                                <span>Save my details for faster checkout next time</span>
                            </label>

                            <!-- Guest prompt -->
                            <div v-if="isGuest" class="co-guest-prompt">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                <span>
                                    Want to track your orders and save your details?
                                    <a :href="getRoute('register')" class="co-guest-link">Create a free account</a>
                                </span>
                            </div>

                        </form>
                    </section>

                    <!-- Payment section -->
                    <section class="co-card">
                        <h2 class="co-card-title">
                            <span class="co-step">2</span>
                            Payment
                        </h2>

                        <div v-if="isLoadingInitialData" class="co-payment-loading">
                            <svg class="co-spinner" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="#e5c9c7" stroke-width="3" />
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="#8c4a50" stroke-width="3"
                                    stroke-linecap="round" />
                            </svg>
                            <p>Connecting to payment gateway...</p>
                        </div>

                        <div v-if="hasClientSecret" ref="paymentContainer" class="co-stripe-container"></div>

                        <div v-if="paymentError" class="co-payment-error">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4M12 16h.01" />
                            </svg>
                            {{ paymentError }}
                        </div>

                        <button @click.prevent="handleCardPayment"
                            :disabled="isProcessing || isLoadingInitialData || !hasClientSecret || !!paymentError"
                            class="btn-rose btn-rose--full co-pay-btn"
                            :class="{ 'btn-rose--disabled': isProcessing || isLoadingInitialData || !hasClientSecret || !!paymentError }">
                            <svg v-if="isProcessing" class="co-spinner co-spinner--sm" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3"
                                    stroke-linecap="round" />
                            </svg>
                            {{ isProcessing ? 'Processing...' : `Pay ${fmt(computedTotal)}` }}
                        </button>

                        <p class="co-secure-note">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            All transactions are secured and encrypted via Stripe
                        </p>
                    </section>

                </div>

                <!-- Right column: order summary -->
                <aside class="co-summary">
                    <div class="co-summary-card">
                        <h2 class="co-summary-title">Order Summary</h2>

                        <div class="co-summary-rows">
                            <div class="co-summary-row">
                                <span>Subtotal</span>
                                <span>{{ fmt(summary.subtotal) }}</span>
                            </div>
                            <div v-if="vatRegistered" class="co-summary-row co-summary-row--vat-note">
                                <span>VAT</span>
                                <span>Included in price</span>
                            </div>
                            <div class="co-summary-row">
                                <span>Shipping (48 Tracked)</span>
                                <span :class="summary.shipping === 0 ? 'co-free-shipping' : ''">
                                    {{ summary.shipping === 0 ? 'FREE' : fmt(summary.shipping) }}
                                </span>
                            </div>
                            <div v-if="voucherDiscount > 0" class="co-summary-row co-summary-row--discount">
                                <span>Discount ({{ activeVoucher?.code }})</span>
                                <span>-{{ fmt(voucherDiscount) }}</span>
                            </div>
                        </div>

                        <div class="co-voucher-section">
                            <p class="co-voucher-label">Discount Code</p>

                            <div v-if="activeVoucher" class="co-voucher-active">
                                <div>
                                    <p class="co-voucher-code">{{ activeVoucher.code }}</p>
                                    <p class="co-voucher-saved">Saving {{ fmt(activeVoucher.discount) }}</p>
                                </div>
                                <button @click="removeVoucher" class="co-voucher-remove">Remove</button>
                            </div>

                            <div v-else class="co-voucher-input">
                                <input type="text" v-model="voucherCode" placeholder="Enter code..."
                                    class="co-voucher-field" @keyup.enter="applyVoucher" />
                                <button @click="applyVoucher" :disabled="voucherLoading || !voucherCode.trim()"
                                    class="btn-rose btn-rose--sm co-voucher-btn">
                                    {{ voucherLoading ? '...' : 'Apply' }}
                                </button>
                            </div>

                            <p v-if="voucherSuccess" class="co-voucher-msg co-voucher-msg--success">{{ voucherSuccess }}
                            </p>
                            <p v-if="voucherError" class="co-voucher-msg co-voucher-msg--error">{{ voucherError }}</p>
                        </div>

                        <div class="co-total-row">
                            <span class="co-total-label">Total</span>
                            <span class="co-total-val">{{ fmt(computedTotal) }}</span>
                        </div>

                        <div class="co-items-section">
                            <h3 class="co-items-title">Items in your order</h3>
                            <div class="co-items-list">
                                <div v-for="item in cartItems" :key="item.id" class="co-item-row">
                                    <span class="co-item-name">
                                        {{ item.product.name }}
                                        <span class="co-item-qty">&times;{{ item.quantity }}</span>
                                    </span>
                                    <span class="co-item-price">{{ fmt(item.product.cost * item.quantity) }}</span>
                                </div>

                                <div v-if="giftVoucher" class="co-item-row co-item-row--gv">
                                    <span class="co-item-name">
                                        Gift Voucher
                                        <span class="co-item-qty">
                                            · {{ giftVoucher.delivery_type === 'email' ? 'E-Voucher' : 'Physical' }}
                                        </span>
                                    </span>
                                    <span class="co-item-price">{{ fmt(giftVoucher.amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.co {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.co-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 3rem 1.25rem 5rem;
}

.co-header {
    margin-bottom: 2rem;
}

.co-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.4rem;
}

.co-header-sub {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.co-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    text-decoration: none;
    transition: color 0.2s;
}

.co-back:hover {
    color: #8c4a50;
}

.co-guest-note {
    font-size: 0.85rem;
    color: #6b4f4f;
}

.co-guest-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
}

.co-guest-link:hover {
    text-decoration: underline;
}

.co-empty {
    text-align: center;
    padding: 4rem 2rem;
    border: 1.5px dashed #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.co-empty p {
    font-size: 0.95rem;
    color: #6b4f4f;
    font-style: italic;
}

.co-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 860px) {
    .co-grid {
        grid-template-columns: 1fr;
    }
}

.co-left {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.co-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.co-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.5rem;
    color: #c9a4a4;
    opacity: 0.1;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.co-card::after {
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

.co-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #e5c9c7;
}

.co-step {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-style: normal;
    font-size: 0.78rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.co-address-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
    margin-bottom: 0.75rem;
}

@media (max-width: 540px) {
    .co-address-grid {
        grid-template-columns: 1fr;
    }
}

.co-address-card {
    border: 1px solid #e5c9c7;
    border-radius: 14px;
    padding: 0.9rem 1rem;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    background: #fdf4f3;
    position: relative;
    overflow: hidden;
}

.co-address-card:hover {
    border-color: #c9a4a4;
    background: #faeaea;
}

.co-address-card--selected {
    border-color: #8c4a50;
    background: #fff5f5;
    box-shadow: 0 0 0 2px rgba(140, 74, 80, 0.12);
}

.co-address-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.4rem;
}

.co-address-type {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8c4a50;
}

.co-address-default {
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: rgba(140, 74, 80, 0.1);
    color: #8c4a50;
    border: 1px solid rgba(140, 74, 80, 0.2);
    border-radius: 999px;
    padding: 0.1rem 0.45rem;
}

.co-address-lines {
    display: flex;
    flex-direction: column;
    gap: 0.05rem;
    font-size: 0.85rem;
    color: #2d1a1a;
    line-height: 1.45;
}

.co-clear-btn {
    font-size: 0.8rem;
    color: #8c4a50;
    background: none;
    border: none;
    cursor: pointer;
    font-family: 'Nunito', sans-serif;
    transition: color 0.2s;
    padding: 0;
    text-decoration: underline;
}

.co-clear-btn:hover {
    color: #6a3038;
}

.co-form {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.co-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.co-field-row--3 {
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 540px) {

    .co-field-row,
    .co-field-row--3 {
        grid-template-columns: 1fr;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b4f4f;
}

.field-required {
    color: #8c4a50;
}

.field-optional {
    font-weight: 400;
    text-transform: none;
    font-style: italic;
    color: #9a7070;
}

.field-input {
    padding: 0.65rem 0.9rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.field-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.field-input--error {
    border-color: #c84040;
}

.field-input--readonly {
    opacity: 0.6;
    cursor: not-allowed;
}

.field-error {
    font-size: 0.78rem;
    color: #b54040;
}

.co-add-address-btn-wrap {
    padding-top: 0.25rem;
}

.co-add-address-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.1rem;
    border-radius: 999px;
    border: 1px dashed #c9a4a4;
    background: transparent;
    color: #8c4a50;
    font-family: 'Nunito', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}

.co-add-address-btn:hover {
    background: #faeaea;
    border-color: #8c4a50;
}

.co-address-fields {
    border-top: 1px solid #e5c9c7;
    padding-top: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.co-address-fields-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #8c4a50;
    margin-bottom: 0.25rem;
}

.co-save-label {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    cursor: pointer;
    padding-top: 0.25rem;
}

.co-save-check {
    width: 15px;
    height: 15px;
    accent-color: #8c4a50;
    cursor: pointer;
}

.co-guest-prompt {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    background: rgba(140, 74, 80, 0.04);
    border: 1px solid #e5c9c7;
    font-size: 0.85rem;
    color: #6b4f4f;
    margin-top: 0.25rem;
}

.co-guest-prompt svg {
    flex-shrink: 0;
    color: #8c4a50;
    margin-top: 2px;
}

.co-payment-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 2.5rem 1rem;
    color: #6b4f4f;
    font-size: 0.9rem;
    font-style: italic;
}

.co-spinner {
    width: 36px;
    height: 36px;
    animation: co-spin 0.9s linear infinite;
}

.co-spinner--sm {
    width: 16px;
    height: 16px;
}

@keyframes co-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.co-stripe-container {
    padding: 0.25rem 0;
    min-height: 100px;
}

.co-payment-error {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    background: #fff5f5;
    border: 1px solid #e8a8a8;
    color: #8c2a2a;
    font-size: 0.88rem;
    margin-top: 0.75rem;
}

.co-pay-btn {
    margin-top: 1rem;
}

.co-secure-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: #9a7070;
    font-style: italic;
    margin-top: 0.75rem;
    text-align: center;
}

.co-summary {
    position: sticky;
    top: 88px;
}

.co-summary-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.co-summary-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.5rem;
    color: #c9a4a4;
    opacity: 0.1;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.co-summary-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1.1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5c9c7;
}

.co-summary-rows {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-bottom: 1rem;
}

.co-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.88rem;
    color: #6b4f4f;
}

.co-summary-row span:last-child {
    font-weight: 600;
    color: #2d1a1a;
}

.co-summary-row--discount {
    color: #2d7a3a;
}

.co-summary-row--discount span:last-child {
    color: #2d7a3a;
}

.co-free-shipping {
    color: #2d7a3a;
    font-weight: 600;
}

.co-voucher-section {
    border-top: 1px dashed #e5c9c7;
    padding-top: 1rem;
    margin-bottom: 1rem;
}

.co-voucher-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #8c4a50;
    margin-bottom: 0.6rem;
}

.co-voucher-active {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.6rem 0.85rem;
    border-radius: 10px;
    background: #f0faf0;
    border: 1px solid #a8d8b0;
}

.co-voucher-code {
    font-size: 0.85rem;
    font-weight: 700;
    color: #2d7a3a;
    font-family: monospace;
    letter-spacing: 0.05em;
}

.co-voucher-saved {
    font-size: 0.75rem;
    color: #2d7a3a;
    margin-top: 0.1rem;
}

.co-voucher-remove {
    font-size: 0.78rem;
    font-weight: 600;
    color: #8c2a2a;
    background: none;
    border: none;
    cursor: pointer;
    font-family: 'Nunito', sans-serif;
    transition: color 0.2s;
    text-decoration: underline;
}

.co-voucher-remove:hover {
    color: #6a1a1a;
}

.co-voucher-input {
    display: flex;
    gap: 0.5rem;
}

.co-voucher-field {
    flex: 1;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: monospace;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    outline: none;
    transition: border-color 0.2s;
}

.co-voucher-field:focus {
    border-color: #8c4a50;
}

.co-voucher-btn {
    flex-shrink: 0;
}

.co-voucher-msg {
    font-size: 0.78rem;
    margin-top: 0.4rem;
    font-style: italic;
}

.co-voucher-msg--success {
    color: #2d7a3a;
}

.co-voucher-msg--error {
    color: #b54040;
}

.co-total-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 0.85rem 0;
    border-top: 1px solid #e5c9c7;
    border-bottom: 1px solid #e5c9c7;
    margin-bottom: 1rem;
}

.co-total-label {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-style: italic;
    color: #2d1a1a;
}

.co-total-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.8rem;
    font-weight: 500;
    color: #8c4a50;
}

.co-items-title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #8c4a50;
    margin-bottom: 0.65rem;
}

.co-items-list {
    max-height: 200px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.co-items-list::-webkit-scrollbar {
    width: 3px;
}

.co-items-list::-webkit-scrollbar-thumb {
    background: #e5c9c7;
    border-radius: 999px;
}

.co-item-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.5rem;
    font-size: 0.85rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid #f0dcd8;
}

.co-item-row:last-child {
    border-bottom: none;
}

.co-item-name {
    color: #2d1a1a;
    flex: 1;
    min-width: 0;
}

.co-item-qty {
    color: #6b4f4f;
    font-size: 0.78rem;
    margin-left: 0.3rem;
}

.co-item-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1rem;
    font-weight: 500;
    color: #8c4a50;
    flex-shrink: 0;
}

.btn-rose {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.72rem 1.5rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
}

.btn-rose:hover:not(:disabled):not(.btn-rose--disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.btn-rose--disabled,
.btn-rose:disabled {
    background: #f0dcd8;
    border-color: #e5c9c7;
    color: #9a7070;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-rose--full {
    width: 100%;
}

.btn-rose--sm {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

.co-item-row--gv {
    background: rgba(201, 168, 76, 0.04);
    border-radius: 4px;
    padding: 4px 4px;
    margin-top: 2px;
}

.co-summary-row--vat-note span:last-child {
    font-weight: 400 !important;
    color: #9a7070 !important;
    font-style: italic;
    font-size: 0.82rem;
}
</style>
