<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, nextTick, onMounted, ref } from 'vue';

declare const Stripe: any;
declare const route: any;

const getRoute = (name: string, params: any = {}, absolute: boolean = true) => {
    if (typeof window.route === 'function')
        return window.route(name, params, absolute);
    return `/${name}`;
};

const loadStripeScript = (): Promise<void> =>
    new Promise((resolve, reject) => {
        if (typeof Stripe !== 'undefined') return resolve();
        const scriptId = 'stripe-script';
        document.getElementById(scriptId)?.remove();
        const script = document.createElement('script');
        const timeout = window.setTimeout(
            () => finish(new Error('Payment loading timed out. Please retry.')),
            15000,
        );
        const finish = (error?: Error) => {
            window.clearTimeout(timeout);
            script.onload = null;
            script.onerror = null;
            if (error) {
                script.remove();
                reject(error);
            } else resolve();
        };
        script.src = 'https://js.stripe.com/v3/';
        script.id = scriptId;
        script.onload = () => finish();
        script.onerror = () =>
            finish(
                new Error(
                    'Payment could not load. Check your connection and retry.',
                ),
            );
        document.head.appendChild(script);
    });

const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));

interface CartItem {
    id: number;
    product_id: number;
    product: { name: string; cost: number; image_url: string | null };
    quantity: number;
}
interface Summary {
    subtotal: number;
    vat_component: number;
    shipping: number;
    total: number;
    voucher_discount: number;
}
interface Address {
    id: number;
    user_id: number;
    type: string;
    is_default: boolean;
    line_1: string;
    line_2: string;
    city: string;
    county: string;
    postcode: string;
    country: string;
}

const props = defineProps<{
    cartItems: CartItem[];
    summary: Summary;
    addresses: Address[];
    appliedVoucher: {
        code: string;
        discount: number;
        type: string;
        value: number;
    } | null;
    isGuest: boolean;
    giftVoucher: {
        amount: number;
        delivery_type: 'email' | 'physical';
        recipient_name: string;
        recipient_email: string | null;
        sender_name?: string;
        sender_email?: string;
    } | null;
}>();

const seo = useSeoHead({ noIndex: true });

const isProcessing = ref(false);
const paymentError = ref<string | null>(null);
const hasClientSecret = ref(false);
const isLoadingInitialData = ref(true);
const selectedAddressId = ref<number | null>(null);
const isManualAddressVisible = ref(true);
const summaryExpanded = ref(false);
const choosingAddress = ref(false);
const itemCount = computed(
    () =>
        props.cartItems.reduce(
            (count, item) => count + Number(item.quantity),
            0,
        ) + (props.giftVoucher ? 1 : 0),
);

const voucherCode = ref('');
const voucherLoading = ref(false);
const voucherError = ref<string | null>(null);
const voucherSuccess = ref<string | null>(null);
const activeVoucher = ref(props.appliedVoucher ?? null);

const voucherDiscount = computed(() => activeVoucher.value?.discount ?? 0);
const computedTotal = computed(
    () =>
        Math.max(0, Number(props.summary.subtotal) - voucherDiscount.value) +
        (Number(props.summary.shipping) || 0),
);

async function applyVoucher() {
    if (
        !voucherCode.value.trim() ||
        voucherLoading.value ||
        isProcessing.value ||
        confirmedPaymentId.value
    )
        return;
    voucherLoading.value = true;
    voucherError.value = null;
    voucherSuccess.value = null;
    try {
        const { data } = await axios.post(route('checkout.voucher.apply'), {
            code: voucherCode.value.trim(),
        });
        activeVoucher.value = data;
        voucherSuccess.value = data.message;
        voucherCode.value = '';
        await initializeStripe();
    } catch (err: any) {
        const r = err.response;
        voucherError.value =
            r?.data?.error ||
            r?.data?.errors?.code?.[0] ||
            r?.data?.message ||
            `Server error (${r?.status ?? 'unknown'}).`;
    } finally {
        voucherLoading.value = false;
    }
}

async function removeVoucher() {
    if (voucherLoading.value || isProcessing.value || confirmedPaymentId.value)
        return;
    voucherLoading.value = true;
    voucherError.value = null;
    try {
        await axios.post(route('checkout.voucher.remove'));
        activeVoucher.value = null;
        voucherSuccess.value = null;
        await initializeStripe();
    } catch {
        voucherError.value = 'We could not remove this code. Please try again.';
    } finally {
        voucherLoading.value = false;
    }
}

const paymentContainer = ref<HTMLElement | null>(null);
const stripe = ref<any>(null);
const elements = ref<any>(null);
const paymentElement = ref<any>(null);
const clientSecret = ref('');
const confirmedPaymentId = ref('');
const paymentDisabled = computed(
    () =>
        isProcessing.value ||
        voucherLoading.value ||
        isLoadingInitialData.value ||
        !hasClientSecret.value,
);
const isDigitalOnly = computed(
    () =>
        props.cartItems.length === 0 &&
        props.giftVoucher?.delivery_type === 'email',
);
const isPhysicalGiftOnly = computed(
    () =>
        props.cartItems.length === 0 &&
        props.giftVoucher?.delivery_type === 'physical',
);
const addressHeading = computed(() => {
    if (isDigitalOnly.value) return 'Billing address';
    if (isPhysicalGiftOnly.value) return 'Recipient delivery address';
    return 'Delivery address';
});

const addressForm = useForm({
    email: props.giftVoucher?.sender_email ?? '',
    fullName: props.giftVoucher?.sender_name ?? '',
    telephone: '',
    addressLine1: '',
    addressLine2: '',
    city: '',
    county: '',
    postcode: '',
    country: 'United Kingdom',
});

const hasItems = computed(
    () => props.cartItems.length > 0 || !!props.giftVoucher,
);
const isShippingAddressVisible = computed(() => isManualAddressVisible.value);

const fmt = (amount: number | string): string => {
    const n = Number(amount);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};

const selectAddress = (address: Address) => {
    selectedAddressId.value = address.id;
    addressForm.addressLine1 = address.line_1;
    addressForm.addressLine2 = address.line_2;
    addressForm.city = address.city;
    addressForm.county = address.county;
    addressForm.postcode = address.postcode;
    addressForm.country = address.country;
    isManualAddressVisible.value = false;
    choosingAddress.value = false;
};

const clearAddressSelection = () => {
    selectedAddressId.value = null;
    addressForm.addressLine1 = '';
    addressForm.addressLine2 = '';
    addressForm.city = '';
    addressForm.county = '';
    addressForm.postcode = '';
    addressForm.country = 'United Kingdom';
    isManualAddressVisible.value = true;
    choosingAddress.value = false;
};

const fetchPaymentIntent = async () => {
    try {
        const response = await fetch(getRoute('checkout.payment_intent'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') ?? '',
            },
        });
        const data = await response.json();
        if (!response.ok || data.error)
            throw new Error(data.error || 'Failed to fetch payment intent.');
        clientSecret.value = data.clientSecret;
    } catch (error: any) {
        paymentError.value = 'Could not initialise payment: ' + error.message;
    }
};

const initializeStripe = async () => {
    if (isProcessing.value || confirmedPaymentId.value) return;
    isLoadingInitialData.value = true;
    hasClientSecret.value = false;
    paymentError.value = null;
    clientSecret.value = '';
    paymentElement.value?.destroy();
    paymentElement.value = null;
    try {
        await loadStripeScript();
        await fetchPaymentIntent();
        if (!clientSecret.value || paymentError.value) {
            isLoadingInitialData.value = false;
            return;
        }
        await nextTick();
        stripe.value = Stripe(import.meta.env.VITE_STRIPE_KEY);
        elements.value = stripe.value.elements({
            clientSecret: clientSecret.value,
        });
        paymentElement.value = elements.value.create('payment', {
            layout: 'tabs',
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#794750',
                    colorText: '#342a28',
                    colorBackground: '#fffdfb',
                    colorDanger: '#9b3030',
                    fontFamily: 'Source Sans 3, Segoe UI, Arial, sans-serif',
                    borderRadius: '14px',
                },
            },
        });
        const maxRetries = 10;
        let attempts = 0;
        let mounted = false;
        while (attempts < maxRetries && !mounted) {
            const container = paymentContainer.value;
            if (container) {
                try {
                    paymentElement.value.mount(container);
                    mounted = true;
                } catch {
                    await delay(50);
                }
            } else {
                await delay(50);
            }
            attempts++;
        }
        if (!mounted)
            throw new Error('Payment system failed to load. Please retry.');
        hasClientSecret.value = true;
    } catch (error) {
        paymentError.value =
            error instanceof Error
                ? error.message
                : 'Payment could not load. Please retry.';
    } finally {
        isLoadingInitialData.value = false;
    }
};

async function focusFirstError() {
    isManualAddressVisible.value = true;
    await nextTick();
    document.getElementById(Object.keys(addressForm.errors)[0])?.focus();
}

function submitOrder() {
    router.post(
        getRoute('checkout.process_payment'),
        {
            ...addressForm.data(),
            paymentIntentId: confirmedPaymentId.value,
            paymentType: 'card',
        },
        {
            onError: (errors) => {
                addressForm.setError(errors);
                paymentError.value =
                    Object.values(errors)[0] ||
                    'Order confirmation failed. Please retry.';
                void focusFirstError();
            },
            onSuccess: (page) => {
                const flash = page.props.flash as
                    | { error?: string }
                    | undefined;
                if (flash?.error) paymentError.value = flash.error;
            },
            onFinish: () => {
                isProcessing.value = false;
            },
            preserveState: true,
        },
    );
}

const handleCardPayment = async () => {
    if (paymentDisabled.value) return;
    addressForm.clearErrors();
    const requiredFields = {
        email: 'Enter your email address.',
        fullName: 'Enter your full name.',
        addressLine1: 'Enter your address.',
        city: 'Enter your town or city.',
        postcode: 'Enter your postcode.',
    } as const;
    for (const field of Object.keys(
        requiredFields,
    ) as (keyof typeof requiredFields)[]) {
        if (!addressForm[field].trim())
            addressForm.setError(field, requiredFields[field]);
    }
    if (
        addressForm.email &&
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(addressForm.email)
    ) {
        addressForm.setError('email', 'Enter a valid email address.');
    }
    if (addressForm.hasErrors) {
        paymentError.value = 'Please check the highlighted details.';
        await focusFirstError();
        return;
    }
    isProcessing.value = true;
    paymentError.value = null;
    if (confirmedPaymentId.value) {
        submitOrder();
        return;
    }
    try {
        const { error: stripeError, paymentIntent } =
            await stripe.value.confirmPayment({
                elements: elements.value,
                confirmParams: {
                    return_url:
                        window.location.origin +
                        getRoute('checkout.index', {}, false),
                    payment_method_data: {
                        billing_details: {
                            name: addressForm.fullName,
                            email: addressForm.email,
                            phone: addressForm.telephone || undefined,
                            address: {
                                line1: addressForm.addressLine1,
                                line2: addressForm.addressLine2 || undefined,
                                city: addressForm.city,
                                state: addressForm.county || undefined,
                                postal_code: addressForm.postcode,
                                country: 'GB',
                            },
                        },
                    },
                },
                redirect: 'if_required',
            });
        if (stripeError) {
            paymentError.value = stripeError.message || 'An error occurred.';
            isProcessing.value = false;
            return;
        }
        if (paymentIntent?.status === 'succeeded') {
            confirmedPaymentId.value = paymentIntent.id;
            submitOrder();
        } else {
            paymentError.value =
                'Your payment has not completed. Please check your payment details and try again.';
            isProcessing.value = false;
        }
    } catch {
        paymentError.value =
            'Payment could not be confirmed. Please check your connection and retry.';
        isProcessing.value = false;
    }
};

onMounted(async () => {
    if (props.isGuest) {
        isManualAddressVisible.value = true;
    } else {
        const defaultAddress = props.addresses.find((a) => a.is_default);
        if (defaultAddress) {
            await nextTick();
            selectAddress(defaultAddress);
        } else if (props.addresses.length === 0) {
            isManualAddressVisible.value = true;
        }
    }
    if (hasItems.value) {
        await initializeStripe();
    } else {
        isLoadingInitialData.value = false;
    }
});

const vatRegistered = computed(() => !!usePage().props.vatRegistered);
</script>

<template>
    <SeoHead v-bind="seo" />

    <main class="co coy-storefront">
        <div class="co-header-band">
            <div class="co-wrap co-wrap--header">
                <header class="co-header">
                    <div class="co-identity">
                        <a
                            href="/"
                            class="co-brand"
                            aria-label="Chapter of You home"
                        >
                            <AppLogoIcon
                                class-name="co-brand-mark"
                                aria-hidden="true"
                            />
                        </a>
                        <h1 class="co-title coy-heading">Checkout</h1>
                        <span class="co-secure-label">
                            <svg aria-hidden="true" viewBox="0 0 24 24">
                                <rect
                                    x="4"
                                    y="10"
                                    width="16"
                                    height="11"
                                    rx="2"
                                />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>
                            Secure checkout
                        </span>
                    </div>
                    <div class="co-header-actions">
                        <a
                            :href="getRoute('cart.view')"
                            class="co-back coy-button coy-button--secondary"
                        >
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="m15 18-6-6 6-6" />
                            </svg>
                            Return to basket
                        </a>
                    </div>
                </header>
            </div>
        </div>

        <div class="co-wrap co-wrap--content">
            <div v-if="!hasItems" class="co-empty">
                <div class="co-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4ZM3 6h18M16 10a4 4 0 0 1-8 0"
                        />
                    </svg>
                </div>
                <p class="coy-eyebrow">Nothing to check out yet</p>
                <h2 class="coy-heading">Your basket is waiting</h2>
                <p>Choose something lovely and come back when you are ready.</p>
                <a
                    :href="getRoute('products')"
                    class="coy-button coy-button--primary"
                    >Browse products</a
                >
            </div>

            <div v-else class="co-grid">
                <form
                    class="co-left"
                    novalidate
                    @submit.prevent="handleCardPayment"
                >
                    <fieldset
                        :disabled="isProcessing"
                        class="co-checkout-fields"
                    >
                        <section class="co-card coy-card">
                            <div
                                class="co-section-heading co-section-heading--step"
                            >
                                <span class="co-step" aria-hidden="true"
                                    >1</span
                                >
                                <div>
                                    <h2 class="co-card-title coy-heading">
                                        Contact and delivery
                                    </h2>
                                </div>
                            </div>

                            <p v-if="isGuest" class="co-guest-note">
                                Checking out as a guest.
                                <a
                                    :href="getRoute('login')"
                                    class="co-guest-link"
                                    >Sign in</a
                                >
                                to use your saved details.
                            </p>

                            <div class="co-form">
                                <p class="co-hint">
                                    We will email your receipt and order updates
                                    here. Fields marked * are required.
                                </p>
                                <div class="co-field-row">
                                    <div class="field">
                                        <label for="email" class="field-label"
                                            >Email
                                            <span class="field-required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            id="email"
                                            :aria-invalid="
                                                !!addressForm.errors.email
                                            "
                                            :aria-describedby="
                                                addressForm.errors.email
                                                    ? 'email-error'
                                                    : undefined
                                            "
                                            type="email"
                                            v-model="addressForm.email"
                                            required
                                            class="field-input"
                                            :class="{
                                                'field-input--error':
                                                    addressForm.errors.email,
                                            }"
                                            placeholder="you@example.com"
                                            autocomplete="email"
                                        />
                                        <p
                                            v-if="addressForm.errors.email"
                                            id="email-error"
                                            class="field-error"
                                        >
                                            {{ addressForm.errors.email }}
                                        </p>
                                    </div>
                                    <div class="field">
                                        <label
                                            for="fullName"
                                            class="field-label"
                                            >Full Name
                                            <span class="field-required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            id="fullName"
                                            :aria-invalid="
                                                !!addressForm.errors.fullName
                                            "
                                            :aria-describedby="
                                                addressForm.errors.fullName
                                                    ? 'fullName-error'
                                                    : undefined
                                            "
                                            type="text"
                                            v-model="addressForm.fullName"
                                            required
                                            class="field-input"
                                            :class="{
                                                'field-input--error':
                                                    addressForm.errors.fullName,
                                            }"
                                            placeholder="Jane Smith"
                                            autocomplete="name"
                                        />
                                        <p
                                            v-if="addressForm.errors.fullName"
                                            id="fullName-error"
                                            class="field-error"
                                        >
                                            {{ addressForm.errors.fullName }}
                                        </p>
                                    </div>
                                </div>

                                <div class="field" style="max-width: 280px">
                                    <label for="telephone" class="field-label"
                                        >Phone
                                        <span class="field-optional"
                                            >(optional)</span
                                        ></label
                                    >
                                    <input
                                        id="telephone"
                                        type="tel"
                                        v-model="addressForm.telephone"
                                        class="field-input"
                                        placeholder="07700 900000"
                                        autocomplete="tel"
                                    />
                                </div>

                                <div class="co-delivery-heading">
                                    <h2 class="co-card-title coy-heading">
                                        {{ addressHeading }}
                                    </h2>
                                    <p class="co-hint">
                                        {{
                                            isDigitalOnly
                                                ? 'Your voucher arrives by email. We only need this address for billing.'
                                                : 'Check where you would like your order delivered.'
                                        }}
                                    </p>
                                </div>
                                <div
                                    v-if="
                                        !isGuest &&
                                        addresses.length > 0 &&
                                        (choosingAddress ||
                                            selectedAddressId === null)
                                    "
                                    class="co-saved-addresses"
                                >
                                    <h3>Choose a saved address</h3>
                                    <div class="co-address-grid">
                                        <button
                                            v-for="address in addresses"
                                            :key="address.id"
                                            type="button"
                                            @click="selectAddress(address)"
                                            class="co-address-card"
                                            :aria-pressed="
                                                selectedAddressId === address.id
                                            "
                                            :class="{
                                                'co-address-card--selected':
                                                    selectedAddressId ===
                                                    address.id,
                                            }"
                                        >
                                            <div class="co-address-card-head">
                                                <span class="co-address-type">{{
                                                    address.type
                                                }}</span>
                                                <span
                                                    v-if="address.is_default"
                                                    class="co-address-default"
                                                    >Default</span
                                                >
                                            </div>
                                            <span class="co-address-preview">
                                                {{ address.line_1 }},
                                                {{ address.postcode }}
                                            </span>
                                        </button>
                                    </div>
                                    <button
                                        @click="clearAddressSelection"
                                        type="button"
                                        class="co-clear-btn"
                                    >
                                        Use a different address
                                    </button>
                                </div>

                                <div
                                    v-if="
                                        selectedAddressId !== null &&
                                        !isManualAddressVisible &&
                                        !choosingAddress
                                    "
                                    class="co-selected-address"
                                >
                                    <p>
                                        {{ addressForm.addressLine1
                                        }}<br
                                            v-if="addressForm.addressLine2"
                                        />{{ addressForm.addressLine2 }}<br />{{
                                            addressForm.city
                                        }}, {{ addressForm.postcode }}<br />{{
                                            addressForm.country
                                        }}
                                    </p>
                                    <div class="co-address-actions">
                                        <button
                                            type="button"
                                            class="co-clear-btn"
                                            @click="choosingAddress = true"
                                        >
                                            Change address
                                        </button>
                                        <button
                                            type="button"
                                            class="co-clear-btn"
                                            @click="
                                                isManualAddressVisible = true
                                            "
                                        >
                                            Edit address
                                        </button>
                                    </div>
                                </div>
                                <div
                                    v-if="isShippingAddressVisible"
                                    class="co-address-fields"
                                >
                                    <div class="field">
                                        <label
                                            for="addressLine1"
                                            class="field-label"
                                            >Address Line 1
                                            <span class="field-required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            id="addressLine1"
                                            :aria-invalid="
                                                !!addressForm.errors
                                                    .addressLine1
                                            "
                                            :aria-describedby="
                                                addressForm.errors.addressLine1
                                                    ? 'addressLine1-error'
                                                    : undefined
                                            "
                                            type="text"
                                            v-model="addressForm.addressLine1"
                                            required
                                            class="field-input"
                                            :class="{
                                                'field-input--error':
                                                    addressForm.errors
                                                        .addressLine1,
                                            }"
                                            placeholder="123 Example Street"
                                            autocomplete="address-line1"
                                        />
                                        <p
                                            v-if="
                                                addressForm.errors.addressLine1
                                            "
                                            id="addressLine1-error"
                                            class="field-error"
                                        >
                                            {{
                                                addressForm.errors.addressLine1
                                            }}
                                        </p>
                                    </div>

                                    <div class="field">
                                        <label
                                            for="addressLine2"
                                            class="field-label"
                                            >Address Line 2
                                            <span class="field-optional"
                                                >(optional)</span
                                            ></label
                                        >
                                        <input
                                            id="addressLine2"
                                            type="text"
                                            v-model="addressForm.addressLine2"
                                            class="field-input"
                                            placeholder="Apartment, suite, etc."
                                            autocomplete="address-line2"
                                        />
                                    </div>

                                    <div class="co-field-row co-field-row--3">
                                        <div class="field">
                                            <label
                                                for="city"
                                                class="field-label"
                                                >City
                                                <span class="field-required"
                                                    >*</span
                                                ></label
                                            >
                                            <input
                                                id="city"
                                                :aria-invalid="
                                                    !!addressForm.errors.city
                                                "
                                                :aria-describedby="
                                                    addressForm.errors.city
                                                        ? 'city-error'
                                                        : undefined
                                                "
                                                type="text"
                                                v-model="addressForm.city"
                                                required
                                                class="field-input"
                                                :class="{
                                                    'field-input--error':
                                                        addressForm.errors.city,
                                                }"
                                                placeholder="London"
                                                autocomplete="address-level2"
                                            />
                                            <p
                                                v-if="addressForm.errors.city"
                                                id="city-error"
                                                class="field-error"
                                            >
                                                {{ addressForm.errors.city }}
                                            </p>
                                        </div>
                                        <div class="field">
                                            <label
                                                for="postcode"
                                                class="field-label"
                                                >Postcode
                                                <span class="field-required"
                                                    >*</span
                                                ></label
                                            >
                                            <input
                                                id="postcode"
                                                :aria-invalid="
                                                    !!addressForm.errors
                                                        .postcode
                                                "
                                                :aria-describedby="
                                                    addressForm.errors.postcode
                                                        ? 'postcode-error'
                                                        : undefined
                                                "
                                                type="text"
                                                v-model="addressForm.postcode"
                                                required
                                                class="field-input"
                                                :class="{
                                                    'field-input--error':
                                                        addressForm.errors
                                                            .postcode,
                                                }"
                                                placeholder="SW1A 0AA"
                                                autocomplete="postal-code"
                                            />
                                            <p
                                                v-if="
                                                    addressForm.errors.postcode
                                                "
                                                id="postcode-error"
                                                class="field-error"
                                            >
                                                {{
                                                    addressForm.errors.postcode
                                                }}
                                            </p>
                                        </div>
                                        <div class="field">
                                            <label
                                                for="county"
                                                class="field-label"
                                                >County
                                                <span class="field-optional"
                                                    >(optional)</span
                                                ></label
                                            >
                                            <input
                                                id="county"
                                                type="text"
                                                v-model="addressForm.county"
                                                class="field-input"
                                                placeholder="Greater London"
                                                autocomplete="address-level1"
                                            />
                                        </div>
                                    </div>

                                    <div class="field" style="max-width: 200px">
                                        <label for="country" class="field-label"
                                            >Country</label
                                        >
                                        <input
                                            id="country"
                                            type="text"
                                            v-model="addressForm.country"
                                            readonly
                                            class="field-input field-input--readonly"
                                            autocomplete="country-name"
                                        />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="co-card coy-card">
                            <div
                                class="co-section-heading co-section-heading--step"
                            >
                                <span class="co-step" aria-hidden="true"
                                    >2</span
                                >
                                <div>
                                    <h2 class="co-card-title coy-heading">
                                        Payment
                                    </h2>
                                </div>
                            </div>

                            <div
                                v-if="isLoadingInitialData"
                                class="co-payment-loading"
                            >
                                <svg
                                    class="co-spinner"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="#e5c9c7"
                                        stroke-width="3"
                                    />
                                    <path
                                        d="M12 2a10 10 0 0 1 10 10"
                                        stroke="#8c4a50"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                <p>Connecting to payment gateway...</p>
                            </div>

                            <div
                                v-if="clientSecret"
                                ref="paymentContainer"
                                class="co-stripe-container"
                            ></div>

                            <div
                                v-if="paymentError"
                                class="co-payment-error"
                                role="alert"
                            >
                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    style="flex-shrink: 0"
                                >
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 8v4M12 16h.01" />
                                </svg>
                                {{ paymentError }}
                            </div>

                            <button
                                v-if="!hasClientSecret && !isLoadingInitialData"
                                type="button"
                                class="coy-button coy-button--secondary co-full-button"
                                @click="initializeStripe"
                            >
                                Retry loading payment
                            </button>
                            <button
                                type="submit"
                                :disabled="paymentDisabled"
                                class="coy-button coy-button--primary co-pay-btn"
                                :class="{
                                    'co-pay-btn--disabled': paymentDisabled,
                                }"
                            >
                                <svg
                                    v-if="isProcessing"
                                    class="co-spinner co-spinner--sm"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="rgba(255,255,255,0.3)"
                                        stroke-width="3"
                                    />
                                    <path
                                        d="M12 2a10 10 0 0 1 10 10"
                                        stroke="#fff"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                {{
                                    isProcessing
                                        ? 'Processing...'
                                        : `Pay ${fmt(computedTotal)} securely`
                                }}
                            </button>

                            <p class="co-secure-note">
                                <svg
                                    width="11"
                                    height="11"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                >
                                    <rect
                                        x="3"
                                        y="11"
                                        width="18"
                                        height="11"
                                        rx="2"
                                    />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                All transactions are secured and encrypted via
                                Stripe
                            </p>
                        </section>
                    </fieldset>
                </form>

                <aside
                    class="co-summary"
                    :class="{ 'co-summary--expanded': summaryExpanded }"
                >
                    <button
                        type="button"
                        class="co-summary-toggle"
                        :aria-expanded="summaryExpanded"
                        aria-controls="checkout-summary"
                        @click="summaryExpanded = !summaryExpanded"
                    >
                        <span
                            >Order summary · {{ itemCount }}
                            {{ itemCount === 1 ? 'item' : 'items'
                            }}<small>{{
                                summaryExpanded
                                    ? 'Hide details'
                                    : 'Show details'
                            }}</small></span
                        ><strong>{{ fmt(computedTotal) }}</strong>
                    </button>
                    <div id="checkout-summary" class="co-summary-card coy-card">
                        <p class="coy-eyebrow">Your selection</p>
                        <h2 class="co-summary-title coy-heading">
                            Order summary
                        </h2>

                        <div class="co-items-section">
                            <h3 class="co-items-title">Items in your order</h3>
                            <div class="co-items-list">
                                <div
                                    v-for="item in cartItems"
                                    :key="item.id"
                                    class="co-item-row"
                                >
                                    <img
                                        v-if="item.product.image_url"
                                        :src="item.product.image_url"
                                        :alt="item.product.name"
                                        class="co-item-image"
                                    />
                                    <span
                                        v-else
                                        class="co-item-image co-item-image--empty"
                                        aria-hidden="true"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path
                                                d="M3 5h18v14H3zM3 15l5-5 4 4 3-3 6 6M16 9h.01"
                                            />
                                        </svg>
                                    </span>
                                    <span class="co-item-name">
                                        {{ item.product.name }}
                                        <span class="co-item-qty"
                                            >&times;{{ item.quantity }}</span
                                        >
                                    </span>
                                    <span class="co-item-price">{{
                                        fmt(item.product.cost * item.quantity)
                                    }}</span>
                                </div>

                                <div
                                    v-if="giftVoucher"
                                    class="co-item-row co-item-row--gv"
                                >
                                    <span class="co-item-name">
                                        Gift Voucher
                                        <span class="co-item-qty">
                                            ·
                                            {{
                                                giftVoucher.delivery_type ===
                                                'email'
                                                    ? 'E-Voucher'
                                                    : 'Physical'
                                            }}
                                        </span>
                                    </span>
                                    <span class="co-item-price">{{
                                        fmt(giftVoucher.amount)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <a :href="getRoute('cart.view')" class="co-edit-basket"
                            >Edit basket</a
                        >
                        <div class="co-summary-rows">
                            <div class="co-summary-row">
                                <span>Subtotal</span>
                                <span>{{ fmt(summary.subtotal) }}</span>
                            </div>
                            <div
                                v-if="vatRegistered"
                                class="co-summary-row co-summary-row--vat-note"
                            >
                                <span>VAT</span>
                                <span>Included in price</span>
                            </div>
                            <div class="co-summary-row">
                                <span>{{
                                    isDigitalOnly
                                        ? 'Email delivery'
                                        : isPhysicalGiftOnly
                                          ? 'Voucher postage'
                                          : 'Delivery'
                                }}</span>
                                <span
                                    :class="
                                        Number(summary.shipping) === 0
                                            ? 'co-free-shipping'
                                            : ''
                                    "
                                >
                                    {{
                                        summary.shipping === 0
                                            ? 'FREE'
                                            : fmt(summary.shipping)
                                    }}
                                </span>
                            </div>
                            <div
                                v-if="voucherDiscount > 0"
                                class="co-summary-row co-summary-row--discount"
                            >
                                <span
                                    >Discount ({{ activeVoucher?.code }})</span
                                >
                                <span>-{{ fmt(voucherDiscount) }}</span>
                            </div>
                        </div>

                        <div class="co-voucher-section">
                            <label for="voucher-code" class="co-voucher-label"
                                >Discount or gift voucher code</label
                            >

                            <div v-if="activeVoucher" class="co-voucher-active">
                                <div>
                                    <p class="co-voucher-code">
                                        {{ activeVoucher.code }}
                                    </p>
                                    <p class="co-voucher-saved">
                                        Saving {{ fmt(activeVoucher.discount) }}
                                    </p>
                                </div>
                                <button
                                    @click="removeVoucher"
                                    class="co-voucher-remove"
                                >
                                    Remove
                                </button>
                            </div>

                            <div v-else class="co-voucher-input">
                                <input
                                    type="text"
                                    id="voucher-code"
                                    v-model="voucherCode"
                                    :aria-invalid="!!voucherError"
                                    aria-describedby="voucher-feedback"
                                    placeholder="Enter code..."
                                    class="co-voucher-field"
                                    @keyup.enter="applyVoucher"
                                />
                                <button
                                    @click="applyVoucher"
                                    :disabled="
                                        voucherLoading || !voucherCode.trim()
                                    "
                                    class="coy-button coy-button--secondary co-voucher-btn"
                                >
                                    {{ voucherLoading ? '...' : 'Apply' }}
                                </button>
                            </div>

                            <p
                                v-if="voucherSuccess"
                                role="status"
                                class="co-voucher-msg co-voucher-msg--success"
                            >
                                {{ voucherSuccess }}
                            </p>
                            <p
                                v-if="voucherError"
                                id="voucher-feedback"
                                role="alert"
                                class="co-voucher-msg co-voucher-msg--error"
                            >
                                {{ voucherError }}
                            </p>
                        </div>

                        <div class="co-total-row" aria-live="polite">
                            <span class="co-total-label">Total</span>
                            <span class="co-total-val">{{
                                fmt(computedTotal)
                            }}</span>
                        </div>

                        <p class="co-hint">
                            Need help? <a href="/contact">Contact us</a>
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.co {
    min-height: 100vh;
}
.co-wrap {
    width: min(100% - 2 * var(--coy-gutter), var(--coy-container-lg));
    margin-inline: auto;
}
.co-header-band {
    background: var(--coy-color-blush);
    border-bottom: 1px solid var(--coy-color-border);
}
.co-wrap--header {
    padding-block: 1.2rem;
}
.co-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.co-identity {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.co-brand {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
}
.co-brand-mark {
    width: 3rem;
    height: 3rem;
}
.co-title {
    margin: 0;
    font-family: var(--coy-font-display);
    font-size: 1.75rem;
    font-weight: 500;
}
.co-secure-label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: var(--coy-color-text);
    font-size: var(--coy-text-xs);
}
.co-secure-label svg {
    width: 0.9rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
}
.co-back,
.co-clear-btn,
.co-edit-basket {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: var(--coy-control-height);
    color: var(--coy-color-accent);
    font: 600 1rem var(--coy-font-body);
    text-underline-offset: 0.2rem;
}
.co-back {
    text-decoration: none;
}
.co-clear-btn {
    background: transparent;
    border: 0;
    cursor: pointer;
    text-decoration: underline;
}
.co-wrap--content {
    padding-block: 2.5rem 5rem;
}
.co-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(19rem, 23rem);
    gap: clamp(2rem, 5vw, 4rem);
    align-items: start;
}
.co-left,
.co-checkout-fields {
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
}
.co-checkout-fields {
    display: flex;
    flex-direction: column;
    gap: var(--coy-space-5);
}
.co-card {
    padding: clamp(1.5rem, 4vw, 2.25rem);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-sm);
}
.co-card + .co-card {
    padding-top: clamp(1.5rem, 4vw, 2.25rem);
}
.co-section-heading {
    margin-bottom: 1.25rem;
}
.co-section-heading--step {
    display: flex;
    align-items: center;
    gap: var(--coy-space-3);
}
.co-step {
    width: 1.9rem;
    height: 1.9rem;
    display: grid;
    flex: none;
    place-items: center;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-radius: 50%;
    font-size: 0.8rem;
    font-weight: 700;
}
.co-card-title {
    margin: 0;
    font-size: 1.75rem;
}
.co-guest-note {
    margin: 0 0 1.5rem;
}
.co-guest-link,
.co-hint a {
    color: var(--coy-color-accent);
    text-underline-offset: 0.2rem;
}
.co-form,
.co-address-fields {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.co-hint {
    margin: 0;
    color: var(--coy-color-text);
    font-size: 1rem;
}
.co-field-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}
.co-field-row--3 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
.field {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.field-label {
    color: var(--coy-color-heading);
    font-weight: 600;
}
.field-optional {
    color: var(--coy-color-text);
    font-weight: 400;
}
.field-required,
.field-error {
    color: var(--coy-color-error);
}
.field-error {
    margin: 0;
    font-size: var(--coy-text-xs);
}
.field-input,
.co-voucher-field {
    width: 100%;
    min-width: 0;
    min-height: var(--coy-control-height);
    padding: 0.7rem 1rem;
    font: inherit;
    color: var(--coy-color-heading);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-md);
}
.field-input--error {
    border-color: var(--coy-color-error);
}
.field-input--readonly {
    background: var(--coy-color-surface-soft);
}
.co-delivery-heading {
    margin-top: 1rem;
    padding-top: 1.75rem;
    border-top: 1px solid var(--coy-color-border);
}
.co-delivery-heading .co-hint {
    margin-top: 0.5rem;
}
.co-saved-addresses h3 {
    margin: 0 0 0.75rem;
    font-size: 1rem;
    font-weight: 600;
}
.co-address-grid {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
.co-address-card {
    min-width: 0;
    padding: 1rem;
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    background: var(--coy-color-surface);
    color: var(--coy-color-heading);
    font: inherit;
    text-align: left;
    cursor: pointer;
}
.co-address-card--selected {
    border-color: var(--coy-color-accent);
    background: var(--coy-color-surface-soft);
}
.co-address-card-head {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}
.co-address-type {
    font-weight: 600;
}
.co-address-default {
    color: var(--coy-color-accent);
    font-size: var(--coy-text-xs);
}
.co-address-preview {
    display: block;
}
.co-selected-address {
    padding: 1rem 1.25rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
}
.co-selected-address p {
    margin: 0;
}
.co-address-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 0.5rem;
}
.co-payment-loading {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-block: 1.5rem;
}
.co-spinner {
    width: 2rem;
    height: 2rem;
    flex-shrink: 0;
    animation: co-spin 1s linear infinite;
}
.co-spinner--sm {
    width: 1rem;
    height: 1rem;
}
@keyframes co-spin {
    to {
        transform: rotate(360deg);
    }
}
.co-stripe-container {
    min-height: 6rem;
}
.co-payment-error {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-block: 1rem;
    padding: 1rem;
    color: var(--coy-color-error);
    background: var(--coy-color-error-soft);
    border: 1px solid var(--coy-color-error);
    border-radius: var(--coy-radius-sm);
}
.co-pay-btn,
.co-full-button {
    width: 100%;
    margin-top: 1.25rem;
    min-height: 3.25rem;
}
.co-pay-btn:disabled,
.co-voucher-btn:disabled {
    background: var(--coy-color-border-soft);
    border-color: var(--coy-color-border);
    color: var(--coy-color-text);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.co-secure-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin: 1rem 0 0;
    font-size: var(--coy-text-xs);
    text-align: center;
}
.co-summary {
    position: sticky;
    top: 1.5rem;
    min-width: 0;
}
.co-summary-card {
    padding: clamp(1.25rem, 3vw, 1.75rem);
    box-shadow: var(--coy-shadow-sm);
    border-color: var(--coy-color-border);
}
.co-summary-title {
    margin: 0.25rem 0 1.5rem;
    font-size: 1.75rem;
}
.co-summary-toggle {
    display: none;
}
.co-items-title {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip-path: inset(50%);
}
.co-items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.co-item-row {
    display: grid;
    grid-template-columns: 3.5rem minmax(0, 1fr) auto;
    gap: 0.75rem;
    align-items: center;
}
.co-item-image {
    width: 3.5rem;
    height: 3.5rem;
    object-fit: cover;
    border-radius: var(--coy-radius-sm);
    background: var(--coy-color-surface-soft);
}
.co-item-image--empty {
    display: grid;
    place-items: center;
    color: var(--coy-color-border);
}
.co-item-image--empty svg {
    width: 1.5rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.co-item-name {
    color: var(--coy-color-heading);
    overflow-wrap: anywhere;
}
.co-item-qty {
    display: block;
    color: var(--coy-color-text);
    font-size: var(--coy-text-xs);
}
.co-item-price {
    color: var(--coy-color-heading);
    font-weight: 600;
    white-space: nowrap;
}
.co-item-row--gv {
    grid-template-columns: minmax(0, 1fr) auto;
    padding: 1rem;
    background: var(--coy-color-surface-soft);
    border-radius: var(--coy-radius-sm);
}
.co-edit-basket {
    margin-block: 0.5rem;
}
.co-summary-rows {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding-block: 1.25rem;
    border-top: 1px solid var(--coy-color-border-soft);
}
.co-summary-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}
.co-summary-row span:last-child {
    text-align: right;
    color: var(--coy-color-heading);
}
.co-summary-row--discount span,
.co-summary-row .co-free-shipping {
    color: var(--coy-color-success);
}
.co-voucher-section {
    padding-block: 1.25rem;
    border-top: 1px solid var(--coy-color-border-soft);
}
.co-voucher-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--coy-color-heading);
}
.co-voucher-input {
    display: flex;
    gap: 0.5rem;
}
.co-voucher-btn {
    padding-inline: 1rem;
}
.co-voucher-active {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--coy-color-success-soft);
    border-radius: var(--coy-radius-sm);
}
.co-voucher-active p {
    margin: 0;
    color: var(--coy-color-success);
}
.co-voucher-remove {
    min-height: var(--coy-control-height);
    background: transparent;
    border: 0;
    color: var(--coy-color-accent);
    cursor: pointer;
    text-decoration: underline;
}
.co-voucher-msg {
    margin: 0.5rem 0 0;
}
.co-voucher-msg--error {
    color: var(--coy-color-error);
}
.co-voucher-msg--success {
    color: var(--coy-color-success);
}
.co-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding-block: 1.25rem;
    border-top: 1px solid var(--coy-color-border);
    color: var(--coy-color-heading);
    font-weight: 600;
}
.co-total-val {
    font-size: 1.5rem;
    font-variant-numeric: tabular-nums;
}
.co-empty {
    max-width: 40rem;
    margin: auto;
    padding-block: 3rem;
    text-align: center;
}
.co-empty h2 {
    font-size: 2.5rem;
}
.co-empty-icon {
    width: 5rem;
    margin: 0 auto 1.5rem;
}
.co-empty-icon svg {
    fill: none;
    stroke: var(--coy-color-accent);
    stroke-width: 1.5;
}
@media (max-width: 860px) {
    .co-grid {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    .co-left,
    .co-summary {
        width: 100%;
    }
    .co-summary {
        order: -1;
        position: static;
    }
    .co-summary-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--coy-color-border);
        border-radius: var(--coy-radius-sm);
        color: var(--coy-color-heading);
        background: var(--coy-color-surface);
        font: inherit;
        text-align: left;
        cursor: pointer;
    }
    .co-summary-toggle small {
        display: block;
        color: var(--coy-color-accent);
        font-size: var(--coy-text-xs);
        text-decoration: underline;
    }
    .co-summary-toggle strong {
        white-space: nowrap;
    }
    .co-summary-card {
        display: none;
        margin-top: 0.75rem;
    }
    .co-summary--expanded .co-summary-card {
        display: block;
    }
    .co-wrap--content {
        padding-top: 1.5rem;
    }
}
@media (max-width: 540px) {
    .co-secure-label {
        display: none;
    }
    .co-field-row,
    .co-field-row--3,
    .co-address-grid {
        grid-template-columns: 1fr;
    }
    .co-back {
        font-size: var(--coy-text-xs);
    }
    .co-back svg {
        display: none;
    }
    .co-summary-card {
        padding: 1rem;
    }
}
</style>
