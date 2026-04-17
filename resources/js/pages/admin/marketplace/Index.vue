<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Connection {
    shop_name: string | null;
    shop_id: string | null;
    scopes: string | null;
    expires_at: string | null;
}

interface Stats {
    listings_count: number;
    orders_count: number;
    last_import: string | null;
}

const props = defineProps<{
    connection: Connection | null;
    stats: Stats | null;
}>();

const formatDate = (iso: string | null) =>
    iso ? new Date(iso).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }) : 'Never';

const disconnect = () => {
    if (confirm('Disconnect your Etsy shop? Existing imported orders will remain.')) {
        router.post(route('admin.marketplace.etsy.disconnect'));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Marketplaces" />

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Marketplaces</h2>
        </div>

        <!-- Etsy card -->
        <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] p-6 max-w-2xl">

            <div class="flex items-center gap-4 mb-5">
                <!-- Etsy logo mark -->
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-xl"
                    style="background: #F56400;">
                    e
                </div>
                <div>
                    <h3 class="text-xl font-bold">Etsy</h3>
                    <p class="text-sm text-copy-light">Export products &amp; import orders from your Etsy shop</p>
                </div>
                <span v-if="connection"
                    class="ml-auto px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-700 border border-green-700">
                    Connected
                </span>
                <span v-else
                    class="ml-auto px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-600 border border-gray-400">
                    Not connected
                </span>
            </div>

            <!-- Connected state -->
            <template v-if="connection">
                <div class="grid grid-cols-3 gap-4 mb-5">
                    <div class="bg-secondary-light rounded-lg p-3 text-center border border-copy-light/30">
                        <div class="text-2xl font-black">{{ stats?.listings_count ?? 0 }}</div>
                        <div class="text-xs text-copy-light mt-0.5">Listings</div>
                    </div>
                    <div class="bg-secondary-light rounded-lg p-3 text-center border border-copy-light/30">
                        <div class="text-2xl font-black">{{ stats?.orders_count ?? 0 }}</div>
                        <div class="text-xs text-copy-light mt-0.5">Orders imported</div>
                    </div>
                    <div class="bg-secondary-light rounded-lg p-3 text-center border border-copy-light/30">
                        <div class="text-xs font-semibold">Last import</div>
                        <div class="text-xs text-copy-light mt-0.5">{{ formatDate(stats?.last_import ?? null) }}</div>
                    </div>
                </div>

                <div class="text-sm text-copy-light mb-5">
                    <span class="font-semibold text-copy">Shop:</span>
                    {{ connection.shop_name ?? 'Unknown' }}
                    <span v-if="connection.shop_id" class="ml-2 text-xs">(#{{ connection.shop_id }})</span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link :href="route('admin.marketplace.etsy.products')"
                        class="px-4 py-2 text-sm font-semibold border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-sm transition">
                        Manage Products
                    </Link>
                    <Link :href="route('admin.marketplace.etsy.orders')"
                        class="px-4 py-2 text-sm font-semibold border-2 border-copy bg-foreground hover:bg-secondary-light rounded-lg shadow-sm transition">
                        View Orders
                    </Link>
                    <button @click="disconnect"
                        class="px-4 py-2 text-sm font-semibold border-2 border-red-400 text-red-600 hover:bg-red-50 rounded-lg transition ml-auto">
                        Disconnect
                    </button>
                </div>
            </template>

            <!-- Disconnected state -->
            <template v-else>
                <p class="text-sm text-copy-light mb-5">
                    Connect your Etsy shop to start exporting products and importing orders. You'll be redirected to
                    Etsy to authorise access.
                </p>
                <Link :href="route('admin.marketplace.etsy.connect')"
                    class="inline-block px-5 py-2.5 text-sm font-bold border-2 border-copy text-white rounded-lg shadow-sm transition"
                    style="background: #F56400;">
                    Connect Etsy Shop
                </Link>
            </template>
        </div>

    </AdminLayout>
</template>
