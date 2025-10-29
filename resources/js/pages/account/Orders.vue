<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

import NavBar from '@/components/NavBar.vue';

const IconArrowLeft = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>`;
const IconBox = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2z"/><path d="M12 2v4"/><path d="M12 18v-4"/><path d="M3 10h18"/><path d="M3 14h18"/></svg>`;

interface Order {
    id: number;
    order_number: string;
    date: string;
    total: number;
    status: 'Delivered' | 'Shipped' | 'Processing' | 'Cancelled';
}

const props = defineProps<{
    orders: Order[];
}>();

/**
 * Formats a raw number into a GBP currency string.
 */
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(amount);
};

/**
 * Determines the Tailwind classes for the status badge based on the status string.
 */
const getStatusClasses = (status: Order['status']): string => {
    switch (status) {
        case 'Delivered':
            return 'bg-green-100 text-green-700 border-green-400';
        case 'Shipped':
            return 'bg-blue-100 text-blue-700 border-blue-400';
        case 'Processing':
            return 'bg-yellow-100 text-yellow-700 border-yellow-400';
        case 'Cancelled':
            return 'bg-red-100 text-red-700 border-red-400';
        default:
            return 'bg-gray-100 text-gray-700 border-gray-400';
    }
};

/**
 * Mock data for demonstration purposes if the actual props.orders is empty.
 */
const mockOrders: Order[] = [
    { id: 101, order_number: 'CPL-78432', date: '2024-07-20', total: 124.99, status: 'Delivered' },
    { id: 102, order_number: 'CPL-78433', date: '2024-08-01', total: 45.00, status: 'Shipped' },
    { id: 103, order_number: 'CPL-78434', date: '2024-08-15', total: 205.50, status: 'Processing' },
    { id: 104, order_number: 'CPL-78435', date: '2024-09-02', total: 60.00, status: 'Delivered' },
];

const currentOrders = computed(() => props.orders && props.orders.length > 0 ? props.orders : mockOrders);
const hasOrders = computed(() => currentOrders.value.length > 0);
</script>

<template>
    <NavBar />

    <Head title="My Orders" />

    <section class="py-20">

        <div class="min-h-[70vh] bg-background text-copy p-4 md:p-8 lg:p-12">
            <div class="max-w-6xl mx-auto">

                <!-- Header and Back Link -->
                <div class="mb-8">
                    <h1 class="text-5xl font-black text-copy mb-2">My Orders</h1>
                    <a href="/account" class="inline-flex items-center text-primary hover:text-primary-dark transition font-semibold">
                        <div v-html="IconArrowLeft" class="size-5 mr-2"></div>
                        Back to Account
                    </a>
                </div>

                <!-- Main Content Card -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl p-0.5">
                    <div class="rounded-xl border-2 border-copy bg-foreground p-6 sm:p-8">

                        <div v-if="!hasOrders" class="text-center py-12">
                            <div class="text-copy-light mx-auto mb-4" v-html="IconBox"></div>
                            <h2 class="text-2xl font-bold text-copy mb-2">No Orders Yet</h2>
                            <p class="text-copy-lighter mb-6">
                                It looks like you haven't placed any orders with us. Time to explore our products!
                            </p>
                            <a href="/products" class="inline-block px-6 py-3 border-2 border-copy rounded-lg text-lg font-bold text-secondary-content bg-secondary hover:bg-secondary-dark transition">
                                Start Shopping
                            </a>
                        </div>

                        <div v-else class="space-y-4">
                            <!-- Table Header (Visible on medium screens and up) -->
                            <div class="hidden md:grid md:grid-cols-5 gap-4 py-3 px-4 font-bold text-copy border-b-2 border-copy-light">
                                <div class="col-span-1">Order #</div>
                                <div class="col-span-1">Date</div>
                                <div class="col-span-1 text-center">Total</div>
                                <div class="col-span-1 text-center">Status</div>
                                <div class="col-span-1 text-right">Details</div>
                            </div>

                            <!-- Order List Items -->
                            <div v-for="order in currentOrders" :key="order.id" class="relative rounded-lg p-4 transition duration-200 ease-in-out hover:bg-background/50 border-2 border-transparent hover:border-copy-light cursor-pointer">

                                <!-- Responsive Grid for Order Data -->
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-y-3 md:gap-4 items-center">

                                    <!-- Order # -->
                                    <div class="col-span-2 md:col-span-1 font-bold text-lg md:text-copy">
                                        <span class="md:hidden text-copy-lighter text-sm block mb-0.5">Order Number</span>
                                        {{ order.id }}
                                    </div>

                                    <!-- Date -->
                                    <div class="col-span-1 md:col-span-1 text-copy-lighter text-sm">
                                        <span class="md:hidden text-copy-lighter text-sm block mb-0.5">Date</span>
                                        {{ order.date }}
                                    </div>

                                    <!-- Total -->
                                    <div class="col-span-1 md:col-span-1 text-right md:text-center font-extrabold text-primary">
                                        <span class="md:hidden text-copy-lighter text-sm block mb-0.5">Total</span>
                                        {{ formatCurrency(order.total) }}
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="col-span-2 md:col-span-1 flex justify-start md:justify-center">
                                        <!-- NOTE: Changed status classes to standard Tailwind colors for better contrast on light backgrounds -->
                                        <span :class="getStatusClasses(order.status)" class="text-xs font-semibold px-3 py-1 rounded-full border">
                                            {{ order.status }}
                                        </span>
                                    </div>

                                    <!-- Detail Link/Button -->
                                    <div class="col-span-2 md:col-span-1 text-right mt-3 md:mt-0">
                                        <a :href="`/account/orders/${order.id}`" class="text-primary text-sm font-semibold hover:text-primary-dark transition">
                                            View Details &rarr;
                                        </a>
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
