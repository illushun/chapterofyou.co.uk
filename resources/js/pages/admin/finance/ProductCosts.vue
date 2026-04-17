<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

declare const route: any;

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
    status: string;
    total_cost: number;
    margin: number | null;
}

interface Paginated {
    data: Product[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    products: Paginated;
    filters: { search?: string; filter?: string };
}>();

const { paginate, fmtCurrency } = useAdmin();
const page  = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const search = ref(props.filters.search ?? '');
const filter = ref(props.filters.filter ?? '');

const applyFilters = () => {
    router.get(route('admin.finance.products'), {
        search: search.value || undefined,
        filter: filter.value || undefined,
    }, { preserveState: true, replace: true });
};

const marginBadgeClass = (m: number | null): string => {
    if (m === null) return 'adm-badge adm-badge--off';
    if (m >= 60)   return 'adm-badge pc-margin--great';
    if (m >= 40)   return 'adm-badge pc-margin--good';
    if (m >= 20)   return 'adm-badge pc-margin--ok';
    return 'adm-badge pc-margin--low';
};
</script>

<template>
    <AdminLayout>
        <Head title="Product Costs — Finance" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.finance.index')">Finance</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>Product Costs</span>
                </div>
                <h1 class="adm-title">Product Costs</h1>
                <p class="adm-sub">{{ products.total }} products &mdash; see cost of goods and profit margin per product.</p>
            </div>
            <Link :href="route('admin.finance.index')" class="adm-btn adm-btn--ghost adm-btn--sm">
                Cost Items
            </Link>
        </div>

        <!-- Flash -->
        <div v-if="flash.success" class="adm-flash adm-flash--success">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            {{ flash.success }}
        </div>

        <!-- Filters -->
        <div class="pc-filters">
            <input v-model="search" type="text" placeholder="Search name or MPN…"
                class="adm-input adm-input--sm pc-search"
                @keydown.enter="applyFilters" />
            <select v-model="filter" @change="applyFilters" class="adm-select adm-select--sm">
                <option value="">All products</option>
                <option value="costed">Has cost breakdown</option>
                <option value="uncosted">No breakdown yet</option>
            </select>
            <button @click="applyFilters" class="adm-btn adm-btn--ghost adm-btn--sm">Filter</button>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom:1.5rem">

            <!-- Desktop table -->
            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead class="adm-thead">
                        <tr>
                            <th class="adm-th">Product</th>
                            <th class="adm-th adm-th--right">Selling Price</th>
                            <th class="adm-th adm-th--right">COGS</th>
                            <th class="adm-th adm-th--right">Gross Profit</th>
                            <th class="adm-th adm-th--right">Margin</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in products.data" :key="product.id" class="adm-row">
                            <td class="adm-td">
                                <p class="pc-name">{{ product.name }}</p>
                                <p class="adm-td--mono" style="font-size:.72rem">{{ product.mpn }}</p>
                            </td>
                            <td class="adm-td adm-td--right adm-td--price">{{ fmtCurrency(product.cost) }}</td>
                            <td class="adm-td adm-td--right">
                                <template v-if="product.total_cost > 0">
                                    <span class="pc-cogs">{{ fmtCurrency(product.total_cost) }}</span>
                                </template>
                                <span v-else style="color:var(--bb-muted)">—</span>
                            </td>
                            <td class="adm-td adm-td--right adm-td--price"
                                :style="product.total_cost > 0 ? (product.cost - product.total_cost >= 0 ? 'color:var(--bb-sage-d)' : 'color:var(--bb-red)') : ''">
                                <template v-if="product.total_cost > 0">{{ fmtCurrency(product.cost - product.total_cost) }}</template>
                                <span v-else style="color:var(--bb-muted)">—</span>
                            </td>
                            <td class="adm-td adm-td--right">
                                <span :class="marginBadgeClass(product.margin)">
                                    {{ product.margin !== null ? `${product.margin}%` : '—' }}
                                </span>
                            </td>
                            <td class="adm-td adm-td--actions">
                                <Link :href="route('admin.finance.products.detail', product.id)"
                                    class="adm-action adm-action--edit">
                                    Build →
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="adm-mob-list">
                <div v-for="product in products.data" :key="product.id" class="adm-mob-card">
                    <div class="pc-mob-head">
                        <div style="flex:1;min-width:0">
                            <p class="pc-name">{{ product.name }}</p>
                            <p class="adm-td--mono" style="font-size:.7rem">{{ product.mpn }}</p>
                        </div>
                        <span :class="marginBadgeClass(product.margin)">
                            {{ product.margin !== null ? `${product.margin}%` : '—' }}
                        </span>
                    </div>
                    <div class="pc-mob-meta">
                        <div>
                            <p class="pc-mob-label">Selling Price</p>
                            <p class="pc-mob-val">{{ fmtCurrency(product.cost) }}</p>
                        </div>
                        <div>
                            <p class="pc-mob-label">COGS</p>
                            <p class="pc-mob-val pc-cogs">{{ product.total_cost > 0 ? fmtCurrency(product.total_cost) : '—' }}</p>
                        </div>
                        <div>
                            <p class="pc-mob-label">Profit</p>
                            <p class="pc-mob-val" :style="product.total_cost > 0 ? (product.cost - product.total_cost >= 0 ? 'color:var(--bb-sage-d)' : 'color:var(--bb-red)') : ''">
                                {{ product.total_cost > 0 ? fmtCurrency(product.cost - product.total_cost) : '—' }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <Link :href="route('admin.finance.products.detail', product.id)"
                            class="adm-btn adm-btn--ghost adm-btn--sm">
                            Build breakdown →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="!products.data.length" class="adm-empty">
                <div class="adm-empty-icon">📊</div>
                <p class="adm-empty-title">No products found</p>
                <p class="adm-empty-sub">Try adjusting your filters.</p>
            </div>

        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="adm-pagination">
            <p class="adm-page-info">
                Page <strong>{{ products.current_page }}</strong> of <strong>{{ products.last_page }}</strong>
            </p>
            <div class="adm-page-btns">
                <button v-for="link in products.links" :key="link.label"
                    @click.prevent="paginate(link.url)"
                    :disabled="!link.url"
                    class="adm-page-btn"
                    :class="{ 'adm-page-btn--active': link.active }"
                    v-html="link.label.replace('&laquo; Previous','←').replace('Next &raquo;','→')">
                </button>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.pc-filters {
    display: flex;
    gap: .6rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.pc-search { flex: 1; min-width: 180px; }

.pc-name { font-size: .875rem; font-weight: 600; color: var(--bb-text); }
.pc-cogs { color: var(--bb-red); font-weight: 600; }

/* Mobile */
.pc-mob-head { display: flex; align-items: flex-start; gap: .75rem; }
.pc-mob-meta { display: flex; gap: 1.5rem; padding-top: .5rem; border-top: 1px solid var(--bb-border); }
.pc-mob-label { font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--bb-muted); }
.pc-mob-val   { font-size: .875rem; font-weight: 600; color: var(--bb-text); margin-top: .1rem; }

/* Margin badge colours (extend adm-badge) */
.pc-margin--great { background: var(--bb-green-bg); color: #1a6b35; }
.pc-margin--good  { background: #d6ead8; color: var(--bb-sage-d); }
.pc-margin--ok    { background: var(--bb-warn-bg); color: var(--bb-warn-text); }
.pc-margin--low   { background: var(--bb-red-bg); color: var(--bb-red); }
</style>
