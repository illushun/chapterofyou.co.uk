<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Order {
    id: number;
    marketplace_order_id: string;
    first_name: string;
    last_name: string;
    email: string;
    grand_total: number;
    status: string;
    created_at: string;
}

interface Paginated {
    data: Order[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

interface Connection {
    shop_name: string | null;
    shop_id: string | null;
}

const props = defineProps<{
    orders: Paginated;
    filters: { status?: string; search?: string };
    statuses: Record<string, string>;
    connection: Connection | null;
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const importing = ref(false);

const applyFilters = () => {
    router.get(route('admin.marketplace.etsy.orders'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
};

const triggerImport = () => {
    importing.value = true;
    router.post(route('admin.marketplace.etsy.orders.import'), {}, {
        onFinish: () => { importing.value = false; },
    });
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const formatCurrency = (v: number) => `£${Number(v).toFixed(2)}`;

const formatDate = (iso: string) =>
    new Date(iso).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' });

const statusClasses = (s: string) => {
    switch (s) {
        case 'successful': return 'bg-green-500/20 text-green-700 border border-green-700';
        case 'processing': return 'bg-blue-500/20 text-blue-700 border border-blue-700';
        case 'cancelled':
        case 'failed':     return 'bg-red-500/20 text-red-700 border border-red-700';
        default:           return 'bg-gray-500/20 text-gray-600 border border-gray-400';
    }
};

const etsyReceiptUrl = (receiptId: string, shopId: string | null) =>
    shopId ? `https://www.etsy.com/your/shops/${shopId}/orders/${receiptId}` : null;
</script>

<template>
    <AdminLayout>
        <Head title="Etsy Orders" />

        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center mb-6 border-b-2 border-copy pb-2 gap-3">
            <div>
                <div class="flex items-center gap-2 text-xs text-copy-light mb-1">
                    <Link :href="route('admin.marketplace.etsy.index')" class="hover:underline">Marketplaces</Link>
                    <span>/</span>
                    <span>Etsy Orders</span>
                </div>
                <h2 class="text-3xl font-black">Etsy Orders</h2>
                <p v-if="connection?.shop_name" class="text-sm text-copy-light mt-0.5">
                    Shop: <span class="font-semibold text-copy">{{ connection.shop_name }}</span>
                </p>
            </div>
            <div class="flex gap-3">
                <Link :href="route('admin.marketplace.etsy.products')"
                    class="px-4 py-2 text-sm font-semibold border-2 border-copy bg-foreground hover:bg-secondary-light rounded-lg transition">
                    Products
                </Link>
                <button @click="triggerImport" :disabled="importing"
                    class="px-4 py-2 text-sm font-bold border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-sm transition disabled:opacity-50">
                    {{ importing ? 'Importing…' : 'Import Now' }}
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap gap-2">
            <input v-model="search" type="text" placeholder="Search by email, name or receipt ID…"
                class="border-2 border-copy rounded-lg px-3 py-2 text-sm bg-foreground w-72"
                @keydown.enter="applyFilters" />
            <select v-model="status" @change="applyFilters"
                class="border-2 border-copy rounded-lg px-3 py-2 text-sm bg-foreground">
                <option value="">All statuses</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
            </select>
            <button @click="applyFilters"
                class="px-4 py-2 text-sm font-semibold border-2 border-copy bg-primary text-primary-content rounded-lg hover:bg-primary-dark transition">
                Filter
            </button>
        </div>

        <!-- Table -->
        <div v-if="orders.data.length"
            class="rounded-lg border-2 border-copy bg-[var(--primary-content)] overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="bg-secondary-light text-left font-bold text-copy uppercase text-xs border-b-2 border-copy">
                            <th class="px-4 py-3">Receipt #</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-secondary-light/40 transition">
                            <td class="px-4 py-3">
                                <div class="font-semibold">COY-{{ order.id }}</div>
                                <div class="text-xs text-copy-light">Etsy #{{ order.marketplace_order_id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ order.first_name }} {{ order.last_name }}</div>
                                <div class="text-xs text-copy-light">{{ order.email }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ formatDate(order.created_at) }}</td>
                            <td class="px-4 py-3 font-bold">{{ formatCurrency(order.grand_total) }}</td>
                            <td class="px-4 py-3">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold uppercase', statusClasses(order.status)]">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.orders.show', order.id)"
                                        class="text-blue-500 hover:text-blue-700 font-semibold text-xs">
                                        View
                                    </Link>
                                    <a v-if="etsyReceiptUrl(order.marketplace_order_id, connection?.shop_id ?? null)"
                                        :href="etsyReceiptUrl(order.marketplace_order_id, connection?.shop_id ?? null)!"
                                        target="_blank" rel="noopener"
                                        class="text-xs text-copy-light hover:text-copy transition">
                                        Etsy ↗
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="md:hidden divide-y divide-copy-light/50">
                <div v-for="order in orders.data" :key="order.id" class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="font-bold">COY-{{ order.id }}</div>
                            <div class="text-xs text-copy-light">Etsy #{{ order.marketplace_order_id }}</div>
                        </div>
                        <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold uppercase', statusClasses(order.status)]">
                            {{ order.status }}
                        </span>
                    </div>
                    <div class="text-sm mb-1">{{ order.first_name }} {{ order.last_name }}</div>
                    <div class="text-xs text-copy-light mb-3">{{ order.email }}</div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">{{ formatCurrency(order.grand_total) }}</span>
                        <Link :href="route('admin.orders.show', order.id)"
                            class="px-3 py-1.5 text-xs font-semibold border-2 border-copy bg-foreground hover:bg-secondary-light rounded-lg transition">
                            View Order
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center p-12 border-4 border-dashed border-copy-light rounded-2xl bg-secondary-light/50">
            <p class="text-xl font-semibold text-copy mb-2">No Etsy orders imported yet.</p>
            <p class="text-sm text-copy-light mb-4">Click "Import Now" to pull in your latest Etsy orders.</p>
            <button @click="triggerImport" :disabled="importing"
                class="px-5 py-2.5 text-sm font-bold border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-sm transition disabled:opacity-50">
                {{ importing ? 'Importing…' : 'Import Now' }}
            </button>
        </div>

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="mt-6 flex justify-center">
            <ol class="flex gap-2 text-sm font-medium">
                <li v-for="link in orders.links" :key="link.label">
                    <button @click.prevent="paginate(link.url)" :disabled="!link.url"
                        :class="{ 'px-4 py-2 border-2 border-copy transition relative -m-0.5 font-bold': true, 'bg-primary text-primary-content shadow-md': link.active, 'bg-foreground hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed': !link.active }"
                        v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                    </button>
                </li>
            </ol>
        </div>
    </AdminLayout>
</template>
