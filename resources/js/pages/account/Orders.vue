<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import NavBar from '@/components/NavBar.vue';

interface Order {
    id: number;
    date: string;
    total: number;
    status: string;
}

const props = defineProps<{ orders: Order[] }>();

const hasOrders = computed(() => props.orders && props.orders.length > 0);

const fmt = (v: number) => new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(Number(v) || 0);

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

const statusStyle = (s: string): string => {
    switch (s.toLowerCase()) {
        case 'successful':
        case 'delivered': return 'bg-green-100 text-green-700 border-green-300';
        case 'shipped': return 'bg-blue-100 text-blue-700 border-blue-300';
        case 'processing': return 'bg-amber-100 text-amber-700 border-amber-300';
        case 'pending': return 'bg-yellow-100 text-yellow-700 border-yellow-300';
        case 'cancelled':
        case 'failed': return 'bg-red-100 text-red-700 border-red-300';
        default: return 'bg-gray-100 text-gray-600 border-gray-300';
    }
};

const statusLabel = (s: string): string => {
    if (s.toLowerCase() === 'successful') return 'Confirmed';
    return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
};
</script>

<template>
    <NavBar />

    <Head title="My Orders" />

    <section class="py-20">
        <div class="min-h-[70vh] text-copy p-4 md:p-8 lg:p-12">
            <div class="max-w-4xl mx-auto">

                <!-- Header -->
                <div class="mb-8">
                    <a href="/account"
                        class="inline-flex items-center gap-1.5 text-sm text-copy-light hover:text-copy transition mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M19 12H5" />
                        </svg>
                        Back to Account
                    </a>
                    <h1 class="text-4xl font-black text-copy">My Orders</h1>
                    <p class="text-copy-light mt-1">
                        {{ hasOrders ? `${orders.length} order${orders.length !== 1 ? 's' : ''}` : 'No orders yet' }}
                    </p>
                </div>

                <!-- Empty state -->
                <div v-if="!hasOrders" class="rounded-xl border-2 border-dashed border-copy text-center py-20 px-8">
                    <svg class="w-12 h-12 text-copy-light mx-auto mb-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 3H8l-2 4h12l-2-4z" />
                    </svg>
                    <h2 class="text-xl font-bold text-copy mb-2">No orders yet</h2>
                    <p class="text-copy-light mb-6 max-w-sm mx-auto">
                        You haven't placed any orders with us yet. Explore our collection and find your signature scent.
                    </p>
                    <a href="/products"
                        class="inline-block rounded-lg border-2 border-copy px-6 py-2.5 font-bold text-sm transition"
                        style="background-color: var(--primary); color: var(--primary-content);">
                        Browse Products
                    </a>
                </div>

                <!-- Orders list -->
                <div v-else class="space-y-3">
                    <a v-for="order in orders" :key="order.id" :href="`/account/orders/${order.id}`"
                        class="block rounded-xl border-2 border-copy bg-[var(--primary-content)] hover:shadow-md transition group">
                        <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                            <div class="flex flex-wrap items-center gap-4">

                                <!-- Order number -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-copy-light uppercase tracking-wider mb-0.5">Order</p>
                                    <p class="font-black text-copy text-lg leading-tight">
                                        #COY-{{ String(order.id).padStart(5, '0') }}
                                    </p>
                                    <p class="text-xs text-copy-light mt-0.5">{{ fmtDate(order.date) }}</p>
                                </div>

                                <!-- Status -->
                                <div class="flex-shrink-0">
                                    <span
                                        :class="['text-xs font-semibold px-3 py-1 rounded-full border', statusStyle(order.status)]">
                                        {{ statusLabel(order.status) }}
                                    </span>
                                </div>

                                <!-- Total -->
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs text-copy-light mb-0.5">Total</p>
                                    <p class="font-black text-xl text-copy">{{ fmt(order.total) }}</p>
                                </div>

                                <!-- Arrow -->
                                <div
                                    class="flex-shrink-0 text-copy-light group-hover:text-copy group-hover:translate-x-1 transition-transform">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>

                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </section>
</template>
