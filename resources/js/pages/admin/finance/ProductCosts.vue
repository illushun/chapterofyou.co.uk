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

const marginClass = (m: number | null): string => {
    if (m === null) return 'fi-margin fi-margin--none';
    if (m >= 60) return 'fi-margin fi-margin--great';
    if (m >= 40) return 'fi-margin fi-margin--good';
    if (m >= 20) return 'fi-margin fi-margin--ok';
    return 'fi-margin fi-margin--low';
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
                <p class="adm-sub">See the cost of goods and profit margin for each product.</p>
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
        <div class="fi-filters">
            <input v-model="search" type="text" placeholder="Search name or MPN…"
                class="adm-input adm-input--sm" style="flex:1;min-width:180px"
                @keydown.enter="applyFilters" />
            <select v-model="filter" @change="applyFilters" class="adm-select adm-select--sm">
                <option value="">All products</option>
                <option value="costed">Has cost breakdown</option>
                <option value="uncosted">No breakdown yet</option>
            </select>
            <button @click="applyFilters" class="adm-btn adm-btn--ghost adm-btn--sm">Filter</button>
        </div>

        <!-- Table -->
        <div class="adm-card" style="padding:0">
            <table class="adm-table" v-if="products.data.length">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="adm-table-num">Selling Price</th>
                        <th class="adm-table-num">COGS</th>
                        <th class="adm-table-num">Gross Profit</th>
                        <th class="adm-table-num">Margin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products.data" :key="product.id">
                        <td>
                            <div class="fi-item-name">{{ product.name }}</div>
                            <div class="fi-item-note">{{ product.mpn }}</div>
                        </td>
                        <td class="adm-table-num">{{ fmtCurrency(product.cost) }}</td>
                        <td class="adm-table-num">
                            <template v-if="product.total_cost > 0">{{ fmtCurrency(product.total_cost) }}</template>
                            <span v-else class="adm-muted">—</span>
                        </td>
                        <td class="adm-table-num">
                            <template v-if="product.total_cost > 0">
                                {{ fmtCurrency(product.cost - product.total_cost) }}
                            </template>
                            <span v-else class="adm-muted">—</span>
                        </td>
                        <td class="adm-table-num">
                            <span :class="marginClass(product.margin)">
                                {{ product.margin !== null ? `${product.margin}%` : '—' }}
                            </span>
                        </td>
                        <td class="adm-table-actions">
                            <Link :href="route('admin.finance.products.detail', product.id)"
                                class="adm-btn adm-btn--ghost adm-btn--xs">
                                Build →
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="adm-empty">
                <p>No products found.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="adm-pagination">
            <template v-for="link in products.links" :key="link.label">
                <button v-if="link.url" @click="paginate(link.url)"
                    class="adm-page-btn" :class="{ 'adm-page-btn--active': link.active }"
                    v-html="link.label" />
                <span v-else class="adm-page-btn adm-page-btn--disabled" v-html="link.label" />
            </template>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fi-filters {
    display: flex;
    gap: .6rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.fi-item-name { font-weight: 600; font-size: .875rem; }
.fi-item-note { font-size: .75rem; color: #888; margin-top: .15rem; }

.fi-margin {
    display: inline-block;
    padding: .2rem .6rem;
    border-radius: 20px;
    font-size: .78rem;
    font-weight: 700;
}
.fi-margin--none    { background: #f0f0f0; color: #888; }
.fi-margin--great   { background: #d4edda; color: #1a6b35; }
.fi-margin--good    { background: #d6ead8; color: #2e7d52; }
.fi-margin--ok      { background: #fff3cd; color: #856404; }
.fi-margin--low     { background: #fde2e2; color: #c0392b; }
</style>
