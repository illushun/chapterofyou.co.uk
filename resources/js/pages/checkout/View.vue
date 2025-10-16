<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { onMounted, ref, computed, nextTick } from 'vue';

// --- Global Declarations ---
declare const Stripe: any;
declare const route: any;

// Helper to reliably access Ziggy's route function.
const getRoute = (name: string, params: any = {}, absolute: boolean = true) => {
    if (typeof window.route === 'function') {
        return window.route(name, params, absolute);
    }
    console.error(`Ziggy route function is not available for: ${name}`);
    return `/${name}`;
}

/**
 * Loads the Stripe.js script and returns a Promise that resolves when it's ready.
 */
const loadStripeScript = (): Promise<void> => {
    return new Promise((resolve) => {
        if (typeof Stripe !== 'undefined') {
            return resolve();
        }

        const scriptId = 'stripe-script';
        let script = document.getElementById(scriptId) as HTMLScriptElement;

        if (script) {
            script.onload = () => resolve();
        } else {
            script = document.createElement('script');
            script.src = 'https://js.stripe.com/v3/';
            script.id = scriptId;
            script.onload = () => resolve();
            document.head.appendChild(script);
        }
    });
};

// Utility to introduce a delay.
const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

// --- TYPES ---

interface CartItem {
    id: number;
    product_id: number;
    product: {
        name: string;
        cost: number;
        image_url: string;
    }
    quantity: number;
}

interface Summary {
    subtotal: number;
    tax: number;
    shipping: number;
    total: number;
}

// --- PROPS ---

const props = defineProps<{
    cartItems: CartItem[];
    summary: Summary;
}>();

// --- STATE ---

const isProcessing = ref(false);
const paymentError = ref<string | null>(null);
// New state for controlling when the container is rendered
const hasClientSecret = ref(false);
// State for the initial loading spinner (fetching secret and waiting for Stripe to load)
const isLoadingInitialData = ref(true);

// Template Ref for direct DOM access (Crucial)
const paymentContainer = ref<HTMLElement | null>(null);

// Stripe objects
const stripe = ref<any>(null);
const elements = ref<any>(null);
const paymentElement = ref<any>(null);
const clientSecret = ref('');
const paymentIntentId = ref('');

// Form for shipping and billing details
const addressForm = useForm({
    email: '',
    fullName: '',
    telephone: '',
    addressLine1: '',
    addressLine2: '', // Optional
    city: '',
    county: '', // Optional
    postcode: '',
    country: 'United Kingdom',
    saveInfo: false,
});

// --- COMPUTED & METHODS ---

const hasItems = computed(() => props.cartItems.length > 0);

/**
 * Formats a number as currency (GBP).
 */
const formatCurrency = (amount: number | string): string => {
    const numAmount = Number(amount);
    if (isNaN(numAmount)) {
        return '£0.00';
    }
    return `£${numAmount.toFixed(2)}`;
};


/**
 * Fetches a Payment Intent from the server to get the client secret.
 */
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

        if (!response.ok || data.error) {
            throw new Error(data.error || 'Failed to fetch payment intent.');
        }

        clientSecret.value = data.clientSecret;
        paymentIntentId.value = data.paymentIntentId;

    } catch (error: any) {
        console.error("Error fetching payment intent:", error.message);
        paymentError.value = "Could not initialize payment: " + error.message;
    }
};

/**
 * Initializes Stripe SDK and mounts the Payment Element.
 */
const initializeStripe = async () => {

    // 1. Get the client secret from the backend
    await fetchPaymentIntent();

    if (!clientSecret.value || paymentError.value) {
        isLoadingInitialData.value = false;
        return;
    }

    // 2. Client secret is ready. Set flag to true to render the container via v-if.
    hasClientSecret.value = true;

    // 3. Wait for the next DOM update cycle (guarantees v-if has run)
    await nextTick();

    // 4. Initialize Stripe and Elements
    stripe.value = Stripe(import.meta.env.VITE_STRIPE_KEY);
    elements.value = stripe.value.elements({ clientSecret: clientSecret.value });

    const appearance = {
        theme: 'stripe',
        variables: {
            colorPrimary: '#4f46e5',
            colorText: '#1f2937',
            colorBackground: '#ffffff',
        },
    };

    // 5. Create the Payment Element
    paymentElement.value = elements.value.create('payment', {
        layout: 'tabs',
        appearance: appearance,
    });

    // 6. CRITICAL FIX: Robust Mounting using Template Ref and Polling
    const maxRetries = 10;
    const retryDelayMs = 50;
    let attempts = 0;
    let mountedSuccessfully = false;

    // Use polling on the Template Ref's value which should now exist
    while (attempts < maxRetries && !mountedSuccessfully) {
        const container = paymentContainer.value;

        if (container) {
            try {
                // Call mount() only when the container ref is confirmed to exist
                paymentElement.value.mount(container);
                mountedSuccessfully = true;
            } catch (e) {
                // Catch internal Stripe mount errors and retry
                console.error(`Stripe mount failed internally on attempt ${attempts + 1}:`, e);
                await delay(retryDelayMs);
            }
        } else {
            // Should not happen often now, but we wait just in case of slow DOM updates
            await delay(retryDelayMs);
        }
        attempts++;
    }

    if (!mountedSuccessfully) {
         paymentError.value = "Payment system failed to load. The required element was not found in time.";
    }

    // 7. Hide the initial loading spinner
    isLoadingInitialData.value = false;
};

/**
 * Handles form submission and confirms payment using the Stripe Payment Element.
 */
const handleCardPayment = async () => {
    // Perform front-end validation
    if (addressForm.hasErrors || !addressForm.email || !addressForm.addressLine1 || !addressForm.postcode || !addressForm.fullName) {
        paymentError.value = "Please complete all required shipping and contact details.";
        addressForm.validate();
        return;
    }

    isProcessing.value = true;
    paymentError.value = null;

    if (!stripe.value || !paymentElement.value) {
        paymentError.value = "Payment system is not fully loaded. Please wait a moment.";
        isProcessing.value = false;
        return;
    }

    const paymentMethodType = 'card';

    // Confirm payment on the client side
    const { error: stripeError, paymentIntent } = await stripe.value.confirmPayment({
        elements: elements.value,
        confirmParams: {
            return_url: window.location.origin + getRoute('checkout.index', {}, false),
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
                        country: addressForm.country,
                    }
                }
            }
        },
        redirect: 'if_required',
    });


    if (stripeError) {
        paymentError.value = stripeError.message || "An error occurred with your payment details.";
        isProcessing.value = false;
        return;
    }

    if (paymentIntent && paymentIntent.status === 'succeeded') {
        finalizeOrder(paymentIntent.id, paymentMethodType);
    } else if (paymentIntent && paymentIntent.status === 'requires_action') {
        paymentError.value = "Payment requires external authentication. Please complete the redirect process.";
        isProcessing.value = false;
    } else {
        paymentError.value = "Payment pending or requires further review. Status: " + paymentIntent?.status;
        isProcessing.value = false;
    }
};

/**
 * Sends the final order confirmation to the server.
 */
const finalizeOrder = (piId: string, type: string) => {
    router.post(getRoute('checkout.process_payment'),
        {
            ...addressForm.data(),
            paymentIntentId: piId,
            paymentType: type,
        },
        {
            onSuccess: () => {
                // Controller handles the final redirect
            },
            onError: (errors) => {
                const errorKey = Object.keys(errors)[0];
                paymentError.value = errors[errorKey] || "Order finalization failed on the server. Please contact support.";
            },
            onFinish: () => {
                isProcessing.value = false;
            }
        }
    );
}

// --- LIFECYCLE HOOK ---

onMounted(async () => {
    if (hasItems.value) {
        // 1. AWAIT script loading first
        await loadStripeScript();
        // 2. Then, initialize Stripe elements
        await initializeStripe();
    } else {
        isLoadingInitialData.value = false;
    }
});
</script>

<template>
    <Head title="Checkout" />

    <!-- Utility styles for Tailwind variables -->
    <section class="py-20" :style="{'--primary': '#4f46e5', '--primary-dark': '#4338ca', '--primary-content': '#ffffff', '--background': '#f9fafb', '--foreground': '#ffffff', '--copy': '#1f2937', '--copy-lighter': '#4b5563', '--error': '#ef4444'}">

        <div class="min-h-screen bg-background text-copy p-4 md:p-8 lg:p-12">
            <div class="max-w-6xl mx-auto">

                <div class="mb-8">
                    <h1 class="text-5xl font-black text-copy mb-2">Secure Checkout</h1>
                    <a :href="getRoute('cart.view')" class="inline-flex items-center text-primary hover:text-primary-dark transition font-semibold">
                        <!-- Icon: Back Arrow -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 mr-2"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                        Return to Cart
                    </a>
                </div>

                <div v-if="!hasItems" class="text-center p-12 border-4 border-dashed border-copy-lighter rounded-2xl bg-foreground/50">
                    <p class="text-2xl font-semibold text-copy mb-4">You have no items in your cart to checkout.</p>
                    <a :href="getRoute('products.index')" class="text-primary hover:text-primary-dark transition font-bold underline">
                        Browse Products and Start Shopping
                    </a>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Left Column: Shipping Details & Payment -->
                    <div class="lg:col-span-2 space-y-8">

                        <!-- Shipping/Contact Details Form -->
                        <form @submit.prevent="handleCardPayment" class="p-6 border-2 border-copy/20 bg-foreground rounded-xl shadow-xl space-y-6">

                            <h2 class="text-3xl font-extrabold text-copy border-b-2 border-copy/10 pb-3">1. Shipping & Contact</h2>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-bold text-copy mb-1">Email Address</label>
                                <input id="email" type="email" v-model="addressForm.email" required
                                    :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.email}]"
                                    placeholder="your@email.com"
                                >
                                <p v-if="addressForm.errors.email" class="text-sm text-error mt-1">{{ addressForm.errors.email }}</p>
                            </div>

                            <!-- Full Name -->
                            <div>
                                <label for="fullName" class="block text-sm font-bold text-copy mb-1">Full Name</label>
                                <input id="fullName" type="text" v-model="addressForm.fullName" required
                                    :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.fullName}]"
                                    placeholder="Jane Doe"
                                >
                                <p v-if="addressForm.errors.fullName" class="text-sm text-error mt-1">{{ addressForm.errors.fullName }}</p>
                            </div>

                            <!-- Telephone -->
                            <div>
                                <label for="telephone" class="block text-sm font-bold text-copy mb-1">Telephone (Optional)</label>
                                <input id="telephone" type="tel" v-model="addressForm.telephone"
                                    :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.telephone}]"
                                    placeholder="07700 900000"
                                >
                                <p v-if="addressForm.errors.telephone" class="text-sm text-error mt-1">{{ addressForm.errors.telephone }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Address Line 1 -->
                                <div class="md:col-span-2">
                                    <label for="addressLine1" class="block text-sm font-bold text-copy mb-1">Address Line 1</label>
                                    <input id="addressLine1" type="text" v-model="addressForm.addressLine1" required
                                        :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.addressLine1}]"
                                        placeholder="123 Example Street"
                                    >
                                    <p v-if="addressForm.errors.addressLine1" class="text-sm text-error mt-1">{{ addressForm.errors.addressLine1 }}</p>
                                </div>

                                <!-- Address Line 2 (Optional) -->
                                <div class="md:col-span-2">
                                    <label for="addressLine2" class="block text-sm font-bold text-copy mb-1">Address Line 2 (Optional)</label>
                                    <input id="addressLine2" type="text" v-model="addressForm.addressLine2"
                                        :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.addressLine2}]"
                                        placeholder="Apartment, suite, etc."
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- City -->
                                <div>
                                    <label for="city" class="block text-sm font-bold text-copy mb-1">City</label>
                                    <input id="city" type="text" v-model="addressForm.city" required
                                        :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.city}]"
                                        placeholder="London"
                                    >
                                    <p v-if="addressForm.errors.city" class="text-sm text-error mt-1">{{ addressForm.errors.city }}</p>
                                </div>

                                <!-- County (Optional) -->
                                <div>
                                    <label for="county" class="block text-sm font-bold text-copy mb-1">County (Optional)</label>
                                    <input id="county" type="text" v-model="addressForm.county"
                                        :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.county}]"
                                        placeholder="Greater London"
                                    >
                                    <p v-if="addressForm.errors.county" class="text-sm text-error mt-1">{{ addressForm.errors.county }}</p>
                                </div>

                                <!-- Postcode -->
                                <div>
                                    <label for="postcode" class="block text-sm font-bold text-copy mb-1">Postcode</label>
                                    <input id="postcode" type="text" v-model="addressForm.postcode" required
                                        :class="['w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary', {'border-error': addressForm.errors.postcode}]"
                                        placeholder="SW1A 0AA"
                                    >
                                    <p v-if="addressForm.errors.postcode" class="text-sm text-error mt-1">{{ addressForm.errors.postcode }}</p>
                                </div>

                                <!-- Country -->
                                <div>
                                    <label for="country" class="block text-sm font-bold text-copy mb-1">Country</label>
                                    <input id="country" type="text" v-model="addressForm.country" readonly
                                        class="w-full p-3 rounded-lg border-2 border-copy/30 bg-background text-copy focus:ring-primary focus:border-primary opacity-70"
                                        placeholder="United Kingdom"
                                    >
                                </div>
                            </div>

                            <!-- Save Info Checkbox -->
                            <div class="flex items-center pt-4">
                                <input id="saveInfo" type="checkbox" v-model="addressForm.saveInfo"
                                    class="w-4 h-4 text-primary bg-background border-copy/30 rounded focus:ring-primary"
                                >
                                <label for="saveInfo" class="ml-2 text-sm text-copy">Save my information for a faster checkout</label>
                            </div>

                        </form>


                        <!-- Payment Section -->
                        <div class="p-6 border-2 border-copy/20 bg-foreground rounded-xl shadow-xl space-y-6">
                            <h2 class="text-3xl font-extrabold text-copy border-b-2 border-copy/10 pb-3">2. Payment</h2>

                            <!-- Initial Loading State (Fetching Client Secret/Loading Stripe) -->
                            <div
                                v-if="isLoadingInitialData"
                                class="p-3 relative min-h-24 bg-background/80 flex items-center justify-center rounded-lg z-10"
                            >
                                <div class="text-center p-8">
                                    <!-- Simple Spinner/Loader -->
                                    <svg class="animate-spin h-8 w-8 text-primary mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-lg font-semibold text-copy-lighter">
                                        Connecting to payment gateway...
                                    </p>
                                </div>
                            </div>

                            <!-- Payment Element Container (Only renders when we have the client secret) -->
                            <div
                                v-if="hasClientSecret"
                                ref="paymentContainer"
                                id="payment-element-container"
                                class="p-3 min-h-24"
                            >
                                <!-- Stripe iframe will be mounted here -->
                            </div>


                            <!-- Error Message Display -->
                            <div v-if="paymentError" class="mt-4 p-3 bg-red-100 border border-error rounded-lg text-error text-sm font-semibold">
                                **Payment Error:** {{ paymentError }}
                            </div>

                            <!-- Submit Button (disabled if loading, no client secret, or has error) -->
                            <button
                                @click.prevent="handleCardPayment"
                                :disabled="isProcessing || addressForm.processing || isLoadingInitialData || !hasClientSecret || !!paymentError"
                                class="mt-6 w-full py-4 border-2 border-copy/20 text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg"
                                :style="{
                                    'background-color': 'var(--primary)',
                                    'color': 'var(--primary-content)'
                                }"
                                :class="{
                                    'hover:bg-primary-dark': !isProcessing && hasClientSecret && !paymentError && !isLoadingInitialData,
                                    'opacity-50 cursor-wait': isProcessing,
                                    'cursor-not-allowed opacity-40': !hasClientSecret || !!paymentError || isLoadingInitialData
                                }"
                            >
                                <span v-if="isProcessing">Processing Payment...</span>
                                <span v-else>Pay {{ formatCurrency(summary.total) }} Now</span>
                            </button>

                            <p class="text-xs text-center text-copy-lighter pt-3">
                                All transactions are secured and encrypted.
                            </p>

                        </div>
                    </div>


                    <!-- Right Column: Order Summary & Review -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-8 rounded-xl border-2 border-copy/20 bg-primary-content/50">
                            <div class="relative rounded-xl -m-0.5 border-2 border-copy/20 bg-foreground p-6">
                                <h2 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy/10 pb-3">Order Summary</h2>

                                <!-- Breakdown -->
                                <div class="space-y-3 text-copy text-lg">
                                    <div class="flex justify-between">
                                        <span>Subtotal ({{ cartItems.length }} items)</span>
                                        <span class="font-bold">{{ formatCurrency(summary.subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>VAT (20%)</span>
                                        <span class="font-bold">{{ formatCurrency(summary.tax) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Shipping</span>
                                        <span :class="['font-bold', summary.shipping === 0 ? 'text-green-600' : 'text-copy']">
                                            {{ summary.shipping === 0 ? 'FREE' : formatCurrency(summary.shipping) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="mt-6 pt-4 border-t-2 border-copy/10 flex justify-between items-center">
                                    <span class="text-2xl font-extrabold text-copy">Total to Pay</span>
                                    <span class="text-4xl font-black text-primary">{{ formatCurrency(summary.total) }}</span>
                                </div>

                                <!-- Order Review -->
                                <h3 class="text-xl font-bold text-copy mt-6 pt-4 border-t-2 border-copy/10 pb-2">Items in Order</h3>
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    <div v-for="item in cartItems" :key="item.id" class="flex justify-between items-center text-sm border-b border-copy/10 last:border-b-0 py-1">
                                        <div class="truncate pr-2">
                                            {{ item.product.name }}
                                            <span class="text-copy-lighter font-medium"> (x{{ item.quantity }})</span>
                                        </div>
                                        <span class="font-bold text-copy flex-shrink-0">{{ formatCurrency(item.product.cost * item.quantity) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>

</template>
