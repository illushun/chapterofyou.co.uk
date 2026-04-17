<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

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
    current_page: number;
    last_page: number;
    total: number;
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

const { paginate, fmtCurrency } = useAdmin();
const page = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const search   = ref(props.filters.search ?? '');
const status   = ref(props.filters.status ?? '');
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

const formatDate = (iso: string) =>
    new Date(iso).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' });

const statusBadgeClass = (s: string) => {
    switch (s) {
        case 'successful': return 'adm-badge adm-badge--on';
        case 'processing': return 'adm-badge adm-badge--lav';
        case 'cancelled':
        case 'failed':     return 'adm-badge adm-badge--red';
        case 'refunded':   return 'adm-badge adm-badge--warn';
        default:           return 'adm-badge adm-badge--off';
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Etsy Orders — Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.marketplace.etsy.index')">Marketplaces</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>Etsy Orders</span>
                </div>
                <h1 class="adm-title">Etsy Orders</h1>
                <p class="adm-sub">
                    <template v-if="connection?.shop_name">
                        Shop: <strong>{{ connection.shop_name }}</strong> &nbsp;·&nbsp;
                    </template>
                    {{ orders.total }} orders imported
                </p>
            </div>
            <div class="eo-header-actions">
                <Link :href="route('admin.marketplace.etsy.products')" class="adm-btn adm-btn--ghost adm-btn--sm">
                    Products
                </Link>
                <button @click="triggerImport" :disabled="importing" class="adm-btn adm-btn--primary">
                    <svg v-if="importing" class="adm-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    {{ importing ? 'Importing…' : 'Import Now' }}
                </button>
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

        <!-- Filters -->
        <div class="eo-filters">
            <input v-model="search" type="text" placeholder="Search email, name or receipt ID…"
                class="adm-input adm-input--sm eo-search-input" @keydown.enter="applyFilters" />
            <select v-model="status" @change="applyFilters" class="adm-select adm-select--sm eo-status-select">
                <option value="">All statuses</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
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
                            <th class="adm-th">Order</th>
                            <th class="adm-th">Customer</th>
                            <th class="adm-th">Date</th>
                            <th class="adm-th">Total</th>
                            <th class="adm-th">Status</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="o in orders.data" :key="o.id" class="adm-row">
                            <td class="adm-td">
                                <div class="eo-order-id">COY-{{ o.id }}</div>
                                <div class="adm-td--mono" style="font-size:0.7rem; margin-top:1px">
                                    Etsy #{{ o.marketplace_order_id }}
                                </div>
                            </td>
                            <td class="adm-td">
                                <div class="eo-customer-name">{{ o.first_name }} {{ o.last_name }}</div>
                                <div class="adm-td--mono" style="font-size:0.75rem; margin-top:1px">{{ o.email }}</div>
                            </td>
                            <td class="adm-td adm-td--mono" style="font-size:0.78rem">{{ formatDate(o.created_at) }}</td>
                            <td class="adm-td adm-td--price">{{ fmtCurrency(o.grand_total) }}</td>
                            <td class="adm-td">
                                <span :class="statusBadgeClass(o.status)">
                                    {{ statuses[o.status] ?? o.status }}
                                </span>
                            </td>
                            <td class="adm-td adm-td--actions">
                                <Link :href="route('admin.orders.show', o.id)" class="adm-action adm-action--edit">
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="adm-mob-list">
                <div v-for="o in orders.data" :key="o.id" class="adm-mob-card">
                    <div class="eo-mob-head">
                        <div>
                            <div class="eo-order-id">COY-{{ o.id }}</div>
                            <div class="adm-td--mono" style="font-size:0.7rem">Etsy #{{ o.marketplace_order_id }}</div>
                        </div>
                        <span :class="statusBadgeClass(o.status)">{{ statuses[o.status] ?? o.status }}</span>
                    </div>
                    <div class="eo-mob-meta">
                        <div>
                            <p class="eo-stat-label">Customer</p>
                            <p class="eo-stat-val">{{ o.first_name }} {{ o.last_name }}</p>
                            <p class="adm-td--mono" style="font-size:0.72rem">{{ o.email }}</p>
                        </div>
                        <div>
                            <p class="eo-stat-label">Total</p>
                            <p class="eo-stat-val">{{ fmtCurrency(o.grand_total) }}</p>
                        </div>
                    </div>
                    <div class="eo-mob-foot">
                        <span class="adm-td--mono" style="font-size:0.72rem; color:var(--bb-muted)">
                            {{ formatDate(o.created_at) }}
                        </span>
                        <Link :href="route('admin.orders.show', o.id)" class="adm-btn adm-btn--ghost adm-btn--sm">
                            View Order
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="!orders.data.length" class="adm-empty">
                <div class="adm-empty-icon">📦</div>
                <p class="adm-empty-title">No Etsy orders imported yet</p>
                <p class="adm-empty-sub">Click "Import Now" to pull in your latest Etsy orders.</p>
                <button @click="triggerImport" :disabled="importing" class="adm-btn adm-btn--primary">
                    {{ importing ? 'Importing…' : 'Import Now' }}
                </button>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="adm-pagination">
            <p class="adm-page-info">Page <strong>{{ orders.current_page }}</strong> of <strong>{{ orders.last_page }}</strong></p>
            <div class="adm-page-btns">
                <button v-for="link in orders.links" :key="link.label"
                    @click.prevent="paginate(link.url)" :disabled="!link.url"
                    class="adm-page-btn" :class="{ 'adm-page-btn--active': link.active }"
                    v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                </button>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.eo-header-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.eo-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
    align-items: center;
}
.eo-search-input { width: 260px; }
.eo-status-select { width: 160px; }

.eo-order-id   { font-size: 0.88rem; font-weight: 700; color: var(--bb-text); }
.eo-customer-name { font-size: 0.88rem; font-weight: 500; color: var(--bb-text); }

/* Mobile */
.eo-mob-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}
.eo-mob-meta {
    display: flex;
    gap: 2rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--bb-border);
    flex-wrap: wrap;
}
.eo-mob-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.eo-stat-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
}
.eo-stat-val {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--bb-text);
}
</style>
