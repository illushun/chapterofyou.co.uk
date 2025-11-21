<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
}

interface OrderItem {
    id: number;
    product_id: number;
    product: Product;
    quantity: number;
    product_cost: number;
    product_total: number;
}

interface Order {
    id: number;
    user_id: number | null;
    user: User | null;
    payment_intent_id: string;
    payment_type: string;
    first_name: string;
    last_name: string;
    email: string;
    telephone: string | null;
    cost_total: number;
    shipping_total: number;
    tax_total: number;
    grand_total: number;
    billing_line_1: string;
    billing_line_2: string | null;
    billing_city: string;
    billing_postcode: string;
    shipping_line_1: string;
    shipping_line_2: string | null;
    shipping_city: string;
    shipping_postcode: string;
    status: 'pending' | 'successful' | 'failed' | 'processing' | 'shipped' | 'cancelled';
    created_at: string;
    items: OrderItem[];
}

const props = defineProps<{
    order: Order;
}>();

const formatCurrency = (amount: number | string | null | undefined): string => {
    const numericAmount = Number(amount) || 0;
    return `£${numericAmount.toFixed(2)}`;
};

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const getStatusClasses = (status: Order['status']) => {
    switch (status) {
        case 'successful':
        case 'shipped':
            return 'bg-green-500/20 text-green-700 border border-green-700';
        case 'processing':
            return 'bg-blue-500/20 text-blue-700 border border-blue-700';
        case 'pending':
            return 'bg-yellow-500/20 text-yellow-700 border border-yellow-700';
        case 'failed':
        case 'cancelled':
            return 'bg-red-500/20 text-error border border-error-dark';
        default:
            return 'bg-gray-500/20 text-gray-700 border border-gray-700';
    }
};

const addressToString = (line1: string, line2: string | null, city: string, postcode: string) => {
    return [line1, line2, city, postcode].filter(Boolean).join(', ');
};
</script>

<template>
    <AdminLayout>

        <Head :title="`Order #${order.id}`" />

        <div class="flex justify-between items-start mb-6 border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Order COY-0000{{ order.id }}</h2>
                <p class="text-copy-light">Placed on {{ formatDate(order.created_at) }}</p>
            </div>
            <span
                :class="['px-4 py-1.5 rounded-full text-lg font-bold uppercase border-2', getStatusClasses(order.status)]">
                {{ order.status }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Order Items ({{
                            order.items.length }})</h3>
                        <div v-for="item in order.items" :key="item.id"
                            class="flex items-center justify-between py-3 border-b border-copy-light/50 last:border-b-0">
                            <div class="flex flex-col">
                                <span class="font-semibold text-copy">{{ item.product.name }}</span>
                                <span class="text-xs text-copy-light">MPN: {{ item.product.mpn }} | Unit Price: {{
                                    formatCurrency(item.product_cost) }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-lg block">{{ formatCurrency(item.product_total) }}</span>
                                <span class="text-sm text-copy-light">Qty: {{ item.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Customer Details
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-copy">
                            <div><span class="font-semibold">Name:</span> {{ order.first_name }} {{ order.last_name }}
                            </div>
                            <div><span class="font-semibold">Email:</span> <a :href="`mailto:${order.email}`"
                                    class="text-primary hover:underline">{{ order.email }}</a></div>
                            <div><span class="font-semibold">Telephone:</span> {{ order.telephone || 'N/A' }}</div>
                            <div><span class="font-semibold">User Account:</span>
                                <Link v-if="order.user" :href="route('admin.users.show', order.user_id)"
                                    class="text-primary hover:underline">
                                {{ order.user.name }} (#{{ order.user_id }})
                                </Link>
                                <span v-else class="text-copy-light italic">Guest</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Shipping &
                            Billing</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-copy">
                            <div>
                                <p class="font-bold mb-2">Shipping Address</p>
                                <p>{{ order.shipping_line_1 }}</p>
                                <p v-if="order.shipping_line_2">{{ order.shipping_line_2 }}</p>
                                <p>{{ order.shipping_city }}</p>
                                <p>{{ order.shipping_postcode }}</p>
                            </div>
                            <div>
                                <p class="font-bold mb-2">Billing Address</p>
                                <p>{{ order.billing_line_1 }}</p>
                                <p v-if="order.billing_line_2">{{ order.billing_line_2 }}</p>
                                <p>{{ order.billing_city }}</p>
                                <p>{{ order.billing_postcode }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="sticky top-8 rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy-light pb-3">Financial
                            Summary</h3>

                        <div class="space-y-3 text-copy text-lg">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-bold">{{ formatCurrency(order.cost_total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Shipping</span>
                                <span class="font-bold">{{ formatCurrency(order.shipping_total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tax/VAT</span>
                                <span class="font-bold">{{ formatCurrency(order.tax_total) }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t-2 border-copy-light flex justify-between items-center">
                            <span class="text-2xl font-extrabold text-copy">Grand Total</span>
                            <span class="text-4xl font-black text-primary">{{ formatCurrency(order.grand_total)
                            }}</span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-copy-light text-sm">
                            <p><span class="font-semibold">Payment Method:</span> {{ order.payment_type }}</p>
                            <p><span class="font-semibold">Intent ID:</span> <span class="text-xs break-all">{{
                                order.payment_intent_id }}</span></p>
                        </div>

                        <button
                            class="mt-6 w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg bg-secondary-light hover:bg-secondary text-copy"
                            disabled>
                            Update Status (Future Feature)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
