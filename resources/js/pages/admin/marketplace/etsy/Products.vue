<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface EtsyListing {
    listing_id: string;
    status: 'draft' | 'active' | 'synced' | 'error';
    last_synced_at: string | null;
    sync_error: string | null;
}

interface ProductImage {
    image: string;
    status: string;
}

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
    stock_qty: number;
    status: string;
    images: ProductImage[];
    etsy_listing: EtsyListing | null;
}

interface Paginated {
    data: Product[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

interface Connection {
    shop_name: string | null;
    shop_id: string | null;
}

const props = defineProps<{
    products: Paginated;
    filters: { search?: string };
    connection: Connection | null;
}>();

const search = ref(props.filters.search ?? '');
const loadingProduct = ref<number | null>(null);

const applySearch = () => {
    router.get(route('admin.marketplace.etsy.products'), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
};

const exportProduct = (product: Product) => {
    loadingProduct.value = product.id;
    router.post(route('admin.marketplace.etsy.products.export', product.id), {}, {
        onFinish: () => { loadingProduct.value = null; },
    });
};

const syncProduct = (product: Product) => {
    loadingProduct.value = product.id;
    router.post(route('admin.marketplace.etsy.products.sync', product.id), {}, {
        onFinish: () => { loadingProduct.value = null; },
    });
};

const unlinkProduct = (product: Product) => {
    if (!confirm(`Unlink "${product.name}" from Etsy? The listing will remain on Etsy but won't be tracked here.`)) return;
    loadingProduct.value = product.id;
    router.delete(route('admin.marketplace.etsy.products.unlink', product.id), {
        onFinish: () => { loadingProduct.value = null; },
    });
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const formatCurrency = (v: number) => `£${Number(v).toFixed(2)}`;

const formatDate = (iso: string | null) =>
    iso ? new Date(iso).toLocaleString('en-GB', { dateStyle: 'short', timeStyle: 'short' }) : '—';

const statusLabel: Record<string, { text: string; classes: string }> = {
    draft:  { text: 'Draft',   classes: 'bg-yellow-500/20 text-yellow-700 border-yellow-600' },
    active: { text: 'Active',  classes: 'bg-green-500/20 text-green-700 border-green-600' },
    synced: { text: 'Synced',  classes: 'bg-blue-500/20 text-blue-700 border-blue-600' },
    error:  { text: 'Error',   classes: 'bg-red-500/20 text-red-700 border-red-600' },
};

const etsyListingUrl = (listingId: string) =>
    `https://www.etsy.com/listing/${listingId}`;
</script>

<template>
    <AdminLayout>
        <Head title="Etsy Products" />

        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center mb-6 border-b-2 border-copy pb-2 gap-3">
            <div>
                <div class="flex items-center gap-2 text-xs text-copy-light mb-1">
                    <Link :href="route('admin.marketplace.etsy.index')" class="hover:underline">Marketplaces</Link>
                    <span>/</span>
                    <span>Etsy Products</span>
                </div>
                <h2 class="text-3xl font-black">Etsy Products</h2>
                <p v-if="connection?.shop_name" class="text-sm text-copy-light mt-0.5">
                    Shop: <span class="font-semibold text-copy">{{ connection.shop_name }}</span>
                </p>
            </div>
            <Link :href="route('admin.marketplace.etsy.orders')"
                class="px-4 py-2 text-sm font-semibold border-2 border-copy bg-foreground hover:bg-secondary-light rounded-lg transition">
                View Orders
            </Link>
        </div>

        <!-- Search -->
        <div class="mb-4 flex gap-2 max-w-sm">
            <input v-model="search" type="text" placeholder="Search by name or MPN…"
                class="flex-1 border-2 border-copy rounded-lg px-3 py-2 text-sm bg-foreground"
                @keydown.enter="applySearch" />
            <button @click="applySearch"
                class="px-4 py-2 text-sm font-semibold border-2 border-copy bg-primary text-primary-content rounded-lg hover:bg-primary-dark transition">
                Search
            </button>
        </div>

        <!-- Info callout -->
        <div class="mb-5 p-3 rounded-lg bg-yellow-50 border border-yellow-300 text-sm text-yellow-800">
            Products are exported as <strong>draft listings</strong> on Etsy. You'll need to add a shipping profile
            and activate them in your Etsy shop before they go live.
        </div>

        <!-- Table -->
        <div v-if="products.data.length"
            class="rounded-lg border-2 border-copy bg-[var(--primary-content)] overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="bg-secondary-light text-left font-bold text-copy uppercase text-xs border-b-2 border-copy">
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Etsy Status</th>
                            <th class="px-4 py-3">Last Synced</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="product in products.data" :key="product.id"
                            class="hover:bg-secondary-light/40 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img v-if="product.images[0]" :src="product.images[0].image"
                                        :alt="product.name"
                                        class="w-10 h-10 rounded-lg object-cover border border-copy-light/30 flex-shrink-0" />
                                    <div v-else
                                        class="w-10 h-10 rounded-lg bg-secondary-light border border-copy-light/30 flex-shrink-0" />
                                    <div>
                                        <div class="font-semibold">{{ product.name }}</div>
                                        <div class="text-xs text-copy-light">{{ product.mpn }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ formatCurrency(product.cost) }}</td>
                            <td class="px-4 py-3">{{ product.stock_qty }}</td>
                            <td class="px-4 py-3">
                                <template v-if="product.etsy_listing">
                                    <a :href="etsyListingUrl(product.etsy_listing.listing_id)" target="_blank"
                                        rel="noopener"
                                        :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition hover:opacity-80',
                                                 statusLabel[product.etsy_listing.status]?.classes ?? '']">
                                        {{ statusLabel[product.etsy_listing.status]?.text ?? product.etsy_listing.status }}
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3" />
                                        </svg>
                                    </a>
                                    <div v-if="product.etsy_listing.sync_error"
                                        class="mt-1 text-xs text-red-600 max-w-[200px] truncate"
                                        :title="product.etsy_listing.sync_error">
                                        {{ product.etsy_listing.sync_error }}
                                    </div>
                                </template>
                                <span v-else class="text-xs text-copy-light italic">Not exported</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-copy-light">
                                {{ formatDate(product.etsy_listing?.last_synced_at ?? null) }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <template v-if="!product.etsy_listing">
                                        <button @click="exportProduct(product)"
                                            :disabled="loadingProduct === product.id"
                                            class="px-3 py-1.5 text-xs font-semibold border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg transition disabled:opacity-50">
                                            {{ loadingProduct === product.id ? 'Exporting…' : 'Export to Etsy' }}
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button @click="syncProduct(product)"
                                            :disabled="loadingProduct === product.id"
                                            class="px-3 py-1.5 text-xs font-semibold border-2 border-copy bg-foreground hover:bg-secondary-light rounded-lg transition disabled:opacity-50">
                                            {{ loadingProduct === product.id ? 'Syncing…' : 'Sync' }}
                                        </button>
                                        <button @click="unlinkProduct(product)"
                                            :disabled="loadingProduct === product.id"
                                            class="px-3 py-1.5 text-xs font-semibold border border-red-400 text-red-600 hover:bg-red-50 rounded-lg transition disabled:opacity-50">
                                            Unlink
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="md:hidden divide-y divide-copy-light/50">
                <div v-for="product in products.data" :key="product.id" class="p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <img v-if="product.images[0]" :src="product.images[0].image" :alt="product.name"
                            class="w-12 h-12 rounded-lg object-cover border border-copy-light/30 flex-shrink-0" />
                        <div>
                            <div class="font-semibold">{{ product.name }}</div>
                            <div class="text-xs text-copy-light">{{ product.mpn }} · {{ formatCurrency(product.cost) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <template v-if="product.etsy_listing">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold border',
                                               statusLabel[product.etsy_listing.status]?.classes ?? '']">
                                    {{ statusLabel[product.etsy_listing.status]?.text }}
                                </span>
                            </template>
                            <span v-else class="text-xs text-copy-light italic">Not exported</span>
                        </div>
                        <div class="flex gap-2">
                            <button v-if="!product.etsy_listing" @click="exportProduct(product)"
                                :disabled="loadingProduct === product.id"
                                class="px-3 py-1.5 text-xs font-semibold border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg transition disabled:opacity-50">
                                {{ loadingProduct === product.id ? '…' : 'Export' }}
                            </button>
                            <template v-else>
                                <button @click="syncProduct(product)" :disabled="loadingProduct === product.id"
                                    class="px-3 py-1.5 text-xs font-semibold border-2 border-copy bg-foreground hover:bg-secondary-light rounded-lg transition disabled:opacity-50">
                                    Sync
                                </button>
                                <button @click="unlinkProduct(product)" :disabled="loadingProduct === product.id"
                                    class="px-3 py-1.5 text-xs font-semibold border border-red-400 text-red-600 hover:bg-red-50 rounded-lg transition disabled:opacity-50">
                                    Unlink
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center p-12 border-4 border-dashed border-copy-light rounded-2xl bg-secondary-light/50">
            <p class="text-xl font-semibold text-copy mb-2">No products found.</p>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="mt-6 flex justify-center">
            <ol class="flex gap-2 text-sm font-medium">
                <li v-for="link in products.links" :key="link.label">
                    <button @click.prevent="paginate(link.url)" :disabled="!link.url"
                        :class="{ 'px-4 py-2 border-2 border-copy transition relative -m-0.5 font-bold': true, 'bg-primary text-primary-content shadow-md': link.active, 'bg-foreground hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed': !link.active }"
                        v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                    </button>
                </li>
            </ol>
        </div>
    </AdminLayout>
</template>
