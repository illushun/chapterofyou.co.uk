<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import NavBar from '@/components/NavBar.vue';

const IconCheckCircle = `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>`;
const IconArrowLeft = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>`;
const IconUser = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;

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

const props = defineProps<{
    order: Order;
}>();

const formatCurrency = (amount: number | string): string => {
    const n = Number(amount);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};
</script>

<template>
    <NavBar />

    <Head title="Order Confirmed" />

    <section class="py-20">
        <div class="min-h-[70vh] bg-background text-copy p-4 md:p-8 lg:p-12">
            <div class="max-w-3xl mx-auto">

                <!-- ── Success header ── -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl mb-6">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-8 text-center">

                        <div class="text-green-600 mx-auto mb-6 flex items-center justify-center"
                            v-html="IconCheckCircle"></div>

                        <h1 class="text-4xl sm:text-5xl font-black text-copy mb-3">
                            Order Confirmed!
                        </h1>
                        <p class="text-lg text-copy-light mb-2">
                            Thank you, {{ order.first_name }}. Your payment has been processed successfully.
                        </p>
                        <p class="text-sm text-copy-light">
                            A confirmation email has been sent to
                            <span class="font-semibold text-copy">{{ order.email }}</span>
                        </p>

                        <!-- Order ID + date -->
                        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4">
                            <div class="bg-background border-2 border-copy rounded-lg px-6 py-3">
                                <p class="text-xs text-copy-light uppercase tracking-wider mb-1">Order ID</p>
                                <p class="text-2xl font-black text-primary">#{{ order.id }}</p>
                            </div>
                            <div class="bg-background border-2 border-copy rounded-lg px-6 py-3">
                                <p class="text-xs text-copy-light uppercase tracking-wider mb-1">Placed</p>
                                <p class="text-base font-bold text-copy">{{ order.created_at }}</p>
                            </div>
                            <div class="bg-background border-2 border-copy rounded-lg px-6 py-3">
                                <p class="text-xs text-copy-light uppercase tracking-wider mb-1">Total paid</p>
                                <p class="text-2xl font-black text-primary">{{ formatCurrency(order.total) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Order breakdown ── -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] mb-6">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h2 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">
                            Order Summary
                        </h2>

                        <!-- Items -->
                        <table class="w-full text-sm mb-4">
                            <thead>
                                <tr
                                    class="border-b border-copy-light text-left text-xs uppercase tracking-wider text-copy-light">
                                    <th class="pb-2 font-medium">Item</th>
                                    <th class="pb-2 font-medium text-center">Qty</th>
                                    <th class="pb-2 font-medium text-right">Price</th>
                                    <th class="pb-2 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in order.items" :key="item.name"
                                    class="border-b border-copy-light last:border-b-0">
                                    <td class="py-2 font-medium text-copy">{{ item.name }}</td>
                                    <td class="py-2 text-center text-copy-light">{{ item.quantity }}</td>
                                    <td class="py-2 text-right text-copy-light">{{ formatCurrency(item.price) }}</td>
                                    <td class="py-2 text-right font-bold text-copy">{{ formatCurrency(item.total) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Totals -->
                        <div class="space-y-2 text-sm border-t border-copy-light pt-4">
                            <div class="flex justify-between text-copy-light">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(order.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-copy-light">
                                <span>Shipping</span>
                                <span>{{ Number(order.shipping) === 0 ? 'FREE' : formatCurrency(order.shipping)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-copy-light">
                                <span>VAT (20%)</span>
                                <span>{{ formatCurrency(order.tax) }}</span>
                            </div>
                            <div v-if="Number(order.voucher_discount) > 0"
                                class="flex justify-between text-green-700 font-medium">
                                <span>Discount applied</span>
                                <span>-{{ formatCurrency(order.voucher_discount) }}</span>
                            </div>
                            <div
                                class="flex justify-between text-copy font-black text-lg border-t-2 border-copy-light pt-3 mt-2">
                                <span>Order Total</span>
                                <span class="text-primary">{{ formatCurrency(order.total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Shipping address ── -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] mb-6">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h2 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">
                            Shipping Address
                        </h2>
                        <address class="not-italic text-copy-light text-sm leading-relaxed">
                            <p>{{ order.shipping_address.line_1 }}</p>
                            <p v-if="order.shipping_address.line_2">{{ order.shipping_address.line_2 }}</p>
                            <p>{{ order.shipping_address.city }}<span v-if="order.shipping_address.county">, {{
                                order.shipping_address.county }}</span></p>
                            <p>{{ order.shipping_address.postcode }}</p>
                            <p>{{ order.shipping_address.country }}</p>
                        </address>
                    </div>
                </div>

                <!-- ── Actions ── -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/products"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 border-2 border-copy rounded-lg text-base font-bold text-copy hover:bg-copy-light/10 transition">
                        <div v-html="IconArrowLeft"></div>
                        Continue Shopping
                    </a>
                    <a href="/account"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 border-2 border-copy rounded-lg text-base font-bold transition"
                        style="background-color: var(--primary); color: var(--primary-content);">
                        <div v-html="IconUser"></div>
                        My Account
                    </a>
                </div>

            </div>
        </div>
    </section>
</template>
