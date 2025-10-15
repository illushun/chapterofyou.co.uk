<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const IconArrowLeft = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>`;
const IconPlus = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`;
const IconMinus = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>`;
const IconTrash = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>`;

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
}>();

const hasItems = computed(() => props.cartItems.length > 0);
const taxRate = 0.20;
const shippingCost = computed(() => props.cartTotal > 100 ? 0.00 : 4.99);

const vat = computed(() => roundToTwo(props.cartTotal * taxRate));
const finalTotal = computed(() => roundToTwo(props.cartTotal + vat.value + shippingCost.value));

function roundToTwo(num: number): number {
    return Math.round(num * 100) / 100;
}

const updateQuantity = (productId: number, newQuantity: number) => {
    const item = props.cartItems.find(i => i.product_id === productId);
    if (!item) return;

    // Client-side validation
    if (newQuantity < 1) {
        removeProduct(productId);
        return;
    }
    if (newQuantity > item.stock_qty) {
        console.warn(`Cannot set quantity above stock limit (${item.stock_qty})`);
        newQuantity = item.stock_qty;
    }
    router.put(
        `/cart/update/${productId}`,
        { quantity: newQuantity },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
};

const removeProduct = (productId: number) => {
    if (confirm("Are you sure you want to remove this item?")) {
        router.delete(`/cart/remove/${productId}`);
    }
};

/**
 * Formats a number as currency (GBP).
 * FIX: Coerces input to a Number to ensure .toFixed() is always called on a number.
 */
const formatCurrency = (amount: number | string): string => {
    // Ensure the amount is treated as a number
    const numAmount = Number(amount);
    if (isNaN(numAmount)) {
        // Fallback for non-numeric values
        return '£0.00';
    }
    return `£${numAmount.toFixed(2)}`;
};

/**
 * Calculates the running total of all items as quantity changes are reflected
 * (Used for visual consistency before server response updates full total)
 */
const calculateItemSubtotal = (item: CartItem): string => {
    // Ensure cost and quantity are numbers before calculation, just in case
    const cost = Number(item.cost);
    const quantity = Number(item.quantity);

    return formatCurrency(roundToTwo(cost * quantity));
};
</script>

<template>
    <Head title="Shopping Cart" />

    <div class="min-h-screen bg-background text-copy p-4 md:p-8 lg:p-12">
        <div class="max-w-6xl mx-auto">

            <div class="mb-8">
                <h1 class="text-5xl font-black text-copy mb-2">Your Shopping Cart</h1>
                <a href="/products" class="inline-flex items-center text-primary hover:text-primary-dark transition font-semibold">
                    <div v-html="IconArrowLeft" class="size-5 mr-2"></div>
                    Continue Shopping
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2">
                    <div v-if="hasItems" class="flex flex-col gap-6">

                        <div v-for="item in props.cartItems" :key="item.id"
                             class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl"
                        >
                            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4 flex flex-col sm:flex-row items-start gap-4">

                                <!-- Product Image -->
                                <div class="w-24 h-24 flex-shrink-0 border border-border rounded-lg overflow-hidden flex items-center justify-center">
                                    <img :src="item.image_url" :alt="item.name" class="w-full h-full object-contain p-2" />
                                </div>

                                <!-- Product Details and Actions -->
                                <div class="flex-grow w-full">

                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-grow pr-4">
                                            <h2 class="text-xl font-bold text-copy">{{ item.name }}</h2>
                                            <p class="text-sm text-copy-lighter mt-1">Unit Price: {{ formatCurrency(item.cost) }}</p>
                                        </div>

                                        <button
                                            @click="removeProduct(item.product_id)"
                                            class="p-2 rounded-lg text-error hover:bg-error-light transition border-2 border-copy shadow-md flex-shrink-0 w-fit"
                                            aria-label="Remove item"
                                        >
                                            <div v-html="IconTrash"></div>
                                        </button>
                                     </div>

                                    <div class="flex justify-between items-center pt-3 mt-3 border-t border-copy-light/50">

                                        <!-- Quantity Selector -->
                                        <div class="flex items-center rounded-lg border-2 border-copy bg-background flex-shrink-0">
                                            <button
                                                @click="updateQuantity(item.product_id, item.quantity - 1)"
                                                :disabled="item.quantity <= 1"
                                                class="p-2 text-copy-light transition hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed"
                                                aria-label="Decrease quantity"
                                            >
                                                <div v-html="IconMinus"></div>
                                            </button>

                                            <span class="w-10 text-center text-lg font-bold text-copy">{{ item.quantity }}</span>

                                            <button
                                                @click="updateQuantity(item.product_id, item.quantity + 1)"
                                                :disabled="item.quantity >= item.stock_qty"
                                                class="p-2 text-copy-light transition hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed"
                                                aria-label="Increase quantity"
                                            >
                                                <div v-html="IconPlus"></div>
                                            </button>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-copy-lighter hidden sm:block">Item Subtotal:</p>
                                            <p class="text-2xl font-black text-primary">{{ calculateItemSubtotal(item) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Empty Cart State -->
                    <div v-else class="text-center p-12 border-4 border-dashed border-copy-light rounded-2xl bg-foreground/50">
                        <p class="text-2xl font-semibold text-copy mb-4">Your cart is currently empty.</p>
                        <a href="/products" class="text-primary hover:text-primary-dark transition font-bold underline">
                            Browse Products and Start Shopping
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-8 rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                         <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                            <h2 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy-light pb-3">Order Summary</h2>

                            <!-- Breakdown -->
                            <div class="space-y-3 text-copy text-lg">
                                <div class="flex justify-between">
                                    <span>Subtotal ({{ props.cartItems.length }} items)</span>
                                    <span class="font-bold">{{ formatCurrency(props.cartTotal) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>VAT ({{ taxRate * 100 }}%)</span>
                                    <span class="font-bold">{{ formatCurrency(vat) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Shipping</span>
                                    <span :class="['font-bold', shippingCost === 0 ? 'text-green-600' : 'text-copy']">
                                        {{ shippingCost === 0 ? 'FREE' : formatCurrency(shippingCost) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="mt-6 pt-4 border-t-2 border-copy-light flex justify-between items-center">
                                <span class="text-2xl font-extrabold text-copy">Order Total</span>
                                <span class="text-4xl font-black text-primary">{{ formatCurrency(finalTotal) }}</span>
                            </div>

                            <button
                                :disabled="!hasItems"
                                class="mt-6 w-full py-4 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 hover:bg-primary-dark rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background-color: var(--primary); color: var(--primary-content);"
                            >
                                Proceed to Checkout
                            </button>

                            <p v-if="shippingCost > 0" class="text-center text-sm text-copy-lighter mt-3">
                                Add {{ formatCurrency(100 - props.cartTotal) }} more for FREE shipping!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
