<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

interface EtsyListing {
    listing_id: string;
    status: 'draft' | 'active' | 'synced' | 'error';
    last_synced_at: string | null;
    sync_error: string | null;
}

interface EtsySetting {
    enabled: boolean;
    has_overrides: boolean;
    override_title: string | null;
    override_price: number | null;
}

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
    stock_qty: number;
    status: string;
    images: { image: string }[];
    etsy_listing: EtsyListing | null;
    etsy_setting: EtsySetting | null;
}

interface Paginated {
    data: Product[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Connection {
    shop_name: string | null;
    shop_id: string | null;
}

const props = defineProps<{
    products: Paginated;
    filters: { search?: string; filter?: string };
    connection: Connection | null;
}>();

const { paginate, fmtCurrency, stockLabel } = useAdmin();
const page = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const search     = ref(props.filters.search ?? '');
const filter     = ref(props.filters.filter ?? '');
const loadingId  = ref<number | null>(null);

const applyFilters = () => {
    router.get(route('admin.marketplace.etsy.products'), {
        search: search.value || undefined,
        filter: filter.value || undefined,
    }, { preserveState: true, replace: true });
};

const toggleProduct = (product: Product) => {
    loadingId.value = product.id;
    router.post(route('admin.marketplace.etsy.products.toggle', product.id), {}, {
        onFinish: () => { loadingId.value = null; },
    });
};

const exportProduct = (product: Product) => {
    loadingId.value = product.id;
    router.post(route('admin.marketplace.etsy.products.export', product.id), {}, {
        onFinish: () => { loadingId.value = null; },
    });
};

const syncProduct = (product: Product) => {
    loadingId.value = product.id;
    router.post(route('admin.marketplace.etsy.products.sync', product.id), {}, {
        onFinish: () => { loadingId.value = null; },
    });
};

const unlinkProduct = (product: Product) => {
    if (!confirm(`Unlink "${product.name}" from Etsy?\n\nThe listing will remain on Etsy but won't be tracked here.`)) return;
    loadingId.value = product.id;
    router.delete(route('admin.marketplace.etsy.products.unlink', product.id), {
        onFinish: () => { loadingId.value = null; },
    });
};

const isEnabled  = (p: Product) => p.etsy_setting?.enabled === true;
const isExported = (p: Product) => !!p.etsy_listing;

const formatDate = (iso: string | null) =>
    iso ? new Date(iso).toLocaleString('en-GB', { dateStyle: 'short', timeStyle: 'short' }) : '—';

const etsyListingUrl = (id: string) => `https://www.etsy.com/listing/${id}`;

const listingBadgeClass = (status: EtsyListing['status']) => ({
    draft:  'adm-badge adm-badge--warn',
    active: 'adm-badge adm-badge--on',
    synced: 'adm-badge adm-badge--lav',
    error:  'adm-badge adm-badge--red',
}[status] ?? 'adm-badge adm-badge--off');

const listingBadgeText = (status: EtsyListing['status']) => ({
    draft: 'Draft', active: 'Active', synced: 'Synced', error: 'Error',
}[status] ?? status);
</script>

<template>
    <AdminLayout>
        <Head title="Etsy Products — Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.marketplace.etsy.index')">Marketplaces</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>Etsy Products</span>
                </div>
                <h1 class="adm-title">Etsy Products</h1>
                <p class="adm-sub">
                    <template v-if="connection?.shop_name">
                        Shop: <strong>{{ connection.shop_name }}</strong> &nbsp;·&nbsp;
                    </template>
                    {{ products.total }} products
                </p>
            </div>
            <div class="ep-header-actions">
                <a :href="route('admin.marketplace.etsy.products.export-csv')"
                    class="adm-btn adm-btn--ghost adm-btn--sm"
                    title="Download a reference spreadsheet to copy listing data into Etsy manually">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Listing Reference CSV
                </a>
                <Link :href="route('admin.marketplace.etsy.orders')" class="adm-btn adm-btn--ghost adm-btn--sm">
                    View Orders
                </Link>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="flash.success" class="adm-flash adm-flash--success">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="adm-flash adm-flash--error">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            {{ flash.error }}
        </div>

        <!-- Notice -->
        <div class="adm-flash adm-flash--warn" style="margin-bottom:1.25rem">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span>
                <template v-if="connection">
                    Products export as <strong>draft listings</strong>. Add a shipping profile and activate them in your Etsy shop before they go live.
                </template>
                <template v-else>
                    <strong>No API connection yet.</strong>
                    The <strong>Download CSV</strong> button generates a reference sheet of your enabled products — open it alongside Etsy's listing form to copy titles, descriptions, prices, and tags across without re-typing. Once your API application is approved, you'll be able to push listings directly.
                </template>
            </span>
        </div>

        <!-- Filters -->
        <div class="ep-filters">
            <input v-model="search" type="text" placeholder="Search name or MPN…"
                class="adm-input adm-input--sm ep-search" @keydown.enter="applyFilters" />
            <select v-model="filter" @change="applyFilters" class="adm-select adm-select--sm ep-filter-select">
                <option value="">All products</option>
                <option value="enabled">Enabled for Etsy</option>
                <option value="not_exported">Enabled but not exported</option>
                <option value="exported">Exported</option>
            </select>
            <button @click="applyFilters" class="adm-btn adm-btn--ghost adm-btn--sm">Filter</button>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom:1.5rem">

            <!-- Desktop table -->
            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th" style="width:52px"></th>
                            <th class="adm-th">Product</th>
                            <th class="adm-th">Price</th>
                            <th class="adm-th">Stock</th>
                            <th class="adm-th ep-th-etsy">Etsy</th>
                            <th class="adm-th ep-th-sync">Last Synced</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in products.data" :key="p.id" class="adm-row"
                            :class="{ 'ep-row-disabled': !isEnabled(p) }">
                            <td class="adm-td">
                                <div class="adm-thumb">
                                    <img v-if="p.images[0]?.image" :src="p.images[0].image" :alt="p.name" />
                                    <span v-else class="adm-thumb-nil">—</span>
                                </div>
                            </td>
                            <td class="adm-td">
                                <div class="ep-product-name">{{ p.name }}</div>
                                <div class="adm-td--mono" style="font-size:0.72rem; margin-top:1px">{{ p.mpn }}</div>
                                <!-- Override indicator -->
                                <span v-if="p.etsy_setting?.has_overrides" class="adm-badge adm-badge--lav ep-override-badge">
                                    Overrides set
                                </span>
                            </td>
                            <td class="adm-td adm-td--price">
                                <template v-if="p.etsy_setting?.override_price">
                                    <span class="ep-override-price">{{ fmtCurrency(p.etsy_setting.override_price) }}</span>
                                    <span class="ep-base-price">{{ fmtCurrency(p.cost) }}</span>
                                </template>
                                <template v-else>{{ fmtCurrency(p.cost) }}</template>
                            </td>
                            <td class="adm-td">
                                <span class="adm-stock" :class="stockLabel(p.stock_qty).cls">
                                    {{ stockLabel(p.stock_qty).text }}
                                </span>
                            </td>
                            <td class="adm-td">
                                <template v-if="p.etsy_listing">
                                    <a :href="etsyListingUrl(p.etsy_listing.listing_id)" target="_blank"
                                        rel="noopener" :class="listingBadgeClass(p.etsy_listing.status)"
                                        class="ep-listing-link">
                                        {{ listingBadgeText(p.etsy_listing.status) }}
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                                    </a>
                                    <div v-if="p.etsy_listing.sync_error" class="ep-sync-error"
                                        :title="p.etsy_listing.sync_error">{{ p.etsy_listing.sync_error }}</div>
                                </template>
                                <span v-else-if="isEnabled(p)" class="adm-badge adm-badge--off">Not exported</span>
                                <span v-else class="adm-badge adm-badge--off">Disabled</span>
                            </td>
                            <td class="adm-td adm-td--mono" style="font-size:0.75rem">
                                {{ formatDate(p.etsy_listing?.last_synced_at ?? null) }}
                            </td>
                            <td class="adm-td adm-td--actions">
                                <!-- Enable toggle -->
                                <button @click="toggleProduct(p)" :disabled="loadingId === p.id"
                                    :class="['ep-toggle', isEnabled(p) ? 'ep-toggle--on' : 'ep-toggle--off']"
                                    :title="isEnabled(p) ? 'Disable for Etsy' : 'Enable for Etsy'">
                                    <span class="ep-toggle-dot"></span>
                                </button>
                                <!-- Settings -->
                                <Link :href="route('admin.marketplace.etsy.products.settings', p.id)"
                                    class="adm-action adm-action--edit">
                                    Settings
                                </Link>
                                <!-- Export / Sync -->
                                <template v-if="isEnabled(p)">
                                    <button v-if="!isExported(p)" @click="exportProduct(p)"
                                        :disabled="loadingId === p.id" class="adm-action adm-action--edit">
                                        {{ loadingId === p.id ? '…' : 'Export' }}
                                    </button>
                                    <button v-else @click="syncProduct(p)" :disabled="loadingId === p.id"
                                        class="adm-action adm-action--edit">
                                        {{ loadingId === p.id ? '…' : 'Sync' }}
                                    </button>
                                </template>
                                <!-- Unlink -->
                                <button v-if="isExported(p)" @click="unlinkProduct(p)"
                                    :disabled="loadingId === p.id" class="adm-action adm-action--del">
                                    Unlink
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="adm-mob-list">
                <div v-for="p in products.data" :key="p.id" class="adm-mob-card"
                    :class="{ 'ep-row-disabled': !isEnabled(p) }">
                    <div class="ep-mob-head">
                        <div class="adm-thumb adm-thumb--lg">
                            <img v-if="p.images[0]?.image" :src="p.images[0].image" :alt="p.name" />
                            <span v-else class="adm-thumb-nil">—</span>
                        </div>
                        <div class="ep-mob-info">
                            <p class="adm-td--mono" style="font-size:0.7rem">{{ p.mpn }}</p>
                            <p class="ep-product-name">{{ p.name }}</p>
                        </div>
                        <button @click="toggleProduct(p)" :disabled="loadingId === p.id"
                            :class="['ep-toggle', isEnabled(p) ? 'ep-toggle--on' : 'ep-toggle--off']">
                            <span class="ep-toggle-dot"></span>
                        </button>
                    </div>
                    <div class="ep-mob-meta">
                        <div>
                            <p class="ep-stat-label">Etsy price</p>
                            <p class="ep-stat-val">{{ fmtCurrency(p.etsy_setting?.override_price ?? p.cost) }}</p>
                        </div>
                        <div>
                            <p class="ep-stat-label">Stock</p>
                            <span class="adm-stock" :class="stockLabel(p.stock_qty).cls">{{ stockLabel(p.stock_qty).text }}</span>
                        </div>
                        <div>
                            <p class="ep-stat-label">Status</p>
                            <template v-if="p.etsy_listing">
                                <span :class="listingBadgeClass(p.etsy_listing.status)">{{ listingBadgeText(p.etsy_listing.status) }}</span>
                            </template>
                            <span v-else class="adm-badge adm-badge--off">Not exported</span>
                        </div>
                    </div>
                    <div class="ep-mob-foot">
                        <Link :href="route('admin.marketplace.etsy.products.settings', p.id)"
                            class="adm-btn adm-btn--ghost adm-btn--sm">Settings</Link>
                        <button v-if="isEnabled(p) && !isExported(p)" @click="exportProduct(p)"
                            :disabled="loadingId === p.id" class="adm-btn adm-btn--primary adm-btn--sm">
                            {{ loadingId === p.id ? '…' : 'Export' }}
                        </button>
                        <button v-if="isEnabled(p) && isExported(p)" @click="syncProduct(p)"
                            :disabled="loadingId === p.id" class="adm-btn adm-btn--ghost adm-btn--sm">Sync</button>
                        <button v-if="isExported(p)" @click="unlinkProduct(p)"
                            :disabled="loadingId === p.id" class="adm-btn adm-btn--danger adm-btn--sm">Unlink</button>
                    </div>
                </div>
            </div>

            <div v-if="!products.data.length" class="adm-empty">
                <div class="adm-empty-icon">🏪</div>
                <p class="adm-empty-title">No products found</p>
                <p class="adm-empty-sub">Try adjusting your search or filter.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="adm-pagination">
            <p class="adm-page-info">Page <strong>{{ products.current_page }}</strong> of <strong>{{ products.last_page }}</strong></p>
            <div class="adm-page-btns">
                <button v-for="link in products.links" :key="link.label"
                    @click.prevent="paginate(link.url)" :disabled="!link.url"
                    class="adm-page-btn" :class="{ 'adm-page-btn--active': link.active }"
                    v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                </button>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.ep-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.ep-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
    align-items: center;
}
.ep-search       { width: 220px; }
.ep-filter-select { width: 200px; }

.ep-th-etsy  { min-width: 110px; }
.ep-th-sync  { min-width: 120px; }

.ep-row-disabled { opacity: 0.55; }

.ep-product-name {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--bb-text);
}
.ep-override-badge { margin-top: 3px; }

.ep-override-price { font-weight: 700; color: var(--bb-text); }
.ep-base-price {
    font-size: 0.75rem;
    color: var(--bb-muted);
    text-decoration: line-through;
    margin-left: 4px;
}

.ep-listing-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    text-decoration: none;
    transition: opacity 0.12s;
}
.ep-listing-link:hover { opacity: 0.75; }
.ep-sync-error {
    font-size: 0.7rem;
    color: var(--bb-red);
    margin-top: 3px;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Toggle switch */
.ep-toggle {
    display: inline-flex;
    align-items: center;
    width: 36px;
    height: 20px;
    border-radius: 999px;
    border: none;
    padding: 2px;
    cursor: pointer;
    transition: background 0.2s;
    flex-shrink: 0;
    margin-right: 4px;
}
.ep-toggle:disabled { opacity: 0.5; cursor: not-allowed; }
.ep-toggle--on  { background: var(--bb-green); }
.ep-toggle--off { background: #ccc; }
.ep-toggle-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.2s;
    flex-shrink: 0;
}
.ep-toggle--on  .ep-toggle-dot { transform: translateX(16px); }
.ep-toggle--off .ep-toggle-dot { transform: translateX(0); }

/* Mobile */
.ep-mob-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.ep-mob-info  { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 0.15rem; }
.ep-mob-meta  { display: flex; gap: 1.5rem; padding-top: 0.5rem; border-top: 1px solid var(--bb-border); flex-wrap: wrap; }
.ep-mob-foot  { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.ep-stat-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--bb-muted); }
.ep-stat-val   { font-size: 0.9rem; font-weight: 600; color: var(--bb-text); }
</style>
