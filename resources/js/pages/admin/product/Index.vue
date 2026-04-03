<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Product {
    id: number;
    name: string;
    mpn: string;
    cost: number;
    stock_qty: number;
    status: 'enabled' | 'disabled';
    parent_product_id: number | null;
    images: { image: string }[];
}
interface ProductsPaginated {
    data: Product[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{ products: ProductsPaginated }>();

const fmt = (v: number | string | null | undefined) =>
    `£${(Number(v) || 0).toFixed(2)}`;

const confirmDelete = (product: Product) => {
    if (confirm(`Delete "${product.name}"?\n\nThis cannot be undone.`)) {
        router.delete(route('admin.products.destroy', product.id), { preserveScroll: true });
    }
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const stockLabel = (qty: number) => {
    if (qty === 0) return { text: 'Out of stock', cls: 'stock--nil' };
    if (qty < 10) return { text: `Low — ${qty}`, cls: 'stock--low' };
    return { text: qty.toString(), cls: 'stock--ok' };
};
</script>

<template>
    <AdminLayout>

        <Head title="Products — Admin" />

        <!-- Header row -->
        <div class="pi-header">
            <div>
                <h1 class="pi-title">Products</h1>
                <p class="pi-sub">{{ products.total }} products in catalogue</p>
            </div>
            <Link :href="route('admin.products.create')" class="pi-btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            Add Product
            </Link>
        </div>

        <!-- Table card -->
        <div class="pi-card">

            <!-- Desktop table -->
            <div class="pi-table-wrap">
                <table class="pi-table">
                    <thead>
                        <tr class="pi-thead">
                            <th class="pi-th" style="width:52px"></th>
                            <th class="pi-th">MPN</th>
                            <th class="pi-th">Name</th>
                            <th class="pi-th">Price</th>
                            <th class="pi-th">Stock</th>
                            <th class="pi-th">Status</th>
                            <th class="pi-th pi-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in products.data" :key="p.id" class="pi-row">

                            <!-- Image -->
                            <td class="pi-td">
                                <div class="pi-thumb">
                                    <img v-if="p.images[0]?.image" :src="p.images[0].image" :alt="p.name"
                                        class="pi-thumb-img" />
                                    <span v-else class="pi-thumb-nil">—</span>
                                </div>
                            </td>

                            <!-- MPN -->
                            <td class="pi-td">
                                <span class="pi-mpn">{{ p.mpn }}</span>
                            </td>

                            <!-- Name -->
                            <td class="pi-td">
                                <Link :href="route('admin.products.edit', p.id)" class="pi-name-link">
                                {{ p.name }}
                                </Link>
                                <span v-if="p.parent_product_id" class="pi-variant-pill">Variant</span>
                            </td>

                            <!-- Price -->
                            <td class="pi-td pi-td--price">{{ fmt(p.cost) }}</td>

                            <!-- Stock -->
                            <td class="pi-td">
                                <span class="pi-stock-badge" :class="stockLabel(p.stock_qty).cls">
                                    {{ stockLabel(p.stock_qty).text }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="pi-td">
                                <span class="pi-status-badge"
                                    :class="p.status === 'enabled' ? 'status--on' : 'status--off'">
                                    {{ p.status === 'enabled' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="pi-td pi-td--actions">
                                <Link :href="route('admin.products.edit', p.id)"
                                    class="pi-action-btn pi-action-btn--edit">
                                Edit
                                </Link>
                                <button @click="confirmDelete(p)" class="pi-action-btn pi-action-btn--del">
                                    Delete
                                </button>
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="pi-mob-list">
                <div v-for="p in products.data" :key="p.id" class="pi-mob-card">
                    <div class="pi-mob-head">
                        <div class="pi-thumb pi-thumb--sm">
                            <img v-if="p.images[0]?.image" :src="p.images[0].image" :alt="p.name"
                                class="pi-thumb-img" />
                            <span v-else class="pi-thumb-nil">—</span>
                        </div>
                        <div class="pi-mob-info">
                            <p class="pi-mpn">{{ p.mpn }}</p>
                            <Link :href="route('admin.products.edit', p.id)" class="pi-name-link pi-name-link--lg">
                            {{ p.name }}
                            </Link>
                        </div>
                        <span class="pi-status-badge" :class="p.status === 'enabled' ? 'status--on' : 'status--off'">
                            {{ p.status === 'enabled' ? 'Active' : 'Off' }}
                        </span>
                    </div>
                    <div class="pi-mob-meta">
                        <div class="pi-mob-stat">
                            <span class="pi-mob-stat-label">Price</span>
                            <span class="pi-mob-stat-val">{{ fmt(p.cost) }}</span>
                        </div>
                        <div class="pi-mob-stat">
                            <span class="pi-mob-stat-label">Stock</span>
                            <span class="pi-stock-badge" :class="stockLabel(p.stock_qty).cls">
                                {{ stockLabel(p.stock_qty).text }}
                            </span>
                        </div>
                    </div>
                    <div class="pi-mob-foot">
                        <Link :href="route('admin.products.edit', p.id)" class="pi-btn-sm pi-btn-sm--edit">Edit</Link>
                        <button @click="confirmDelete(p)" class="pi-btn-sm pi-btn-sm--del">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="!products.data.length" class="pi-empty">
                <div class="pi-empty-icon">📦</div>
                <p class="pi-empty-title">No products yet</p>
                <p class="pi-empty-sub">Add your first product to get started.</p>
                <Link :href="route('admin.products.create')" class="pi-btn-primary">
                Add Product
                </Link>
            </div>

        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="pi-pagination">
            <p class="pi-page-info">
                Page <strong>{{ products.current_page }}</strong> of <strong>{{ products.last_page }}</strong>
            </p>
            <div class="pi-page-btns">
                <button v-for="link in products.links" :key="link.label" @click.prevent="paginate(link.url)"
                    :disabled="!link.url" class="pi-page-btn" :class="{ 'pi-page-btn--active': link.active }"
                    v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                </button>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── Tokens (inherit from layout's .al-root, define locally for safety) ── */
:root,
.pi-card {
    --bb-blush: #f2c4ce;
    --bb-blush-d: #d4899a;
    --bb-lavender: #c9b8f0;
    --bb-sage: #b8d9b8;
    --bb-sage-d: #4caf7d;
    --bb-peach: #f5d5b8;
    --bb-navy: #1a1a2e;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;
    --bb-red-bg: #fdeef0;
}

/* ── Page header ── */
.pi-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.pi-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--bb-text);
    margin-bottom: 0.2rem;
}

.pi-sub {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-weight: 400;
}

/* Primary button */
.pi-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.1rem;
    border-radius: 8px;
    background: var(--bb-navy);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
    white-space: nowrap;
}

.pi-btn-primary:hover {
    opacity: 0.88;
    transform: translateY(-1px);
}

/* ── Table card ── */
.pi-card {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 8px rgba(26, 26, 46, 0.05);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

/* ── Desktop table ── */
.pi-table-wrap {
    overflow-x: auto;
    display: none;
}

@media (min-width: 768px) {
    .pi-table-wrap {
        display: block;
    }
}

.pi-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.pi-thead {
    background: var(--bb-cream);
    border-bottom: 1px solid var(--bb-border);
}

.pi-th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
    white-space: nowrap;
}

.pi-th--right {
    text-align: right;
}

.pi-row {
    border-bottom: 1px solid var(--bb-border);
    transition: background 0.12s;
}

.pi-row:last-child {
    border-bottom: none;
}

.pi-row:hover {
    background: #fdfcfb;
}

.pi-td {
    padding: 0.85rem 1rem;
    vertical-align: middle;
    color: var(--bb-text);
}

.pi-td--price {
    font-weight: 600;
    white-space: nowrap;
}

.pi-td--actions {
    text-align: right;
    white-space: nowrap;
}

/* ── Thumbnail ── */
.pi-thumb {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    overflow: hidden;
    background: var(--bb-cream);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pi-thumb--sm {
    width: 44px;
    height: 44px;
}

.pi-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pi-thumb-nil {
    font-size: 0.7rem;
    color: var(--bb-muted);
}

/* ── MPN ── */
.pi-mpn {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--bb-muted);
    letter-spacing: 0.04em;
    font-family: monospace;
}

/* ── Name link ── */
.pi-name-link {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--bb-text);
    text-decoration: none;
    transition: color 0.15s;
}

.pi-name-link:hover {
    color: var(--bb-blush-d);
    text-decoration: underline;
}

.pi-name-link--lg {
    font-size: 0.95rem;
}

/* Variant pill */
.pi-variant-pill {
    display: inline-block;
    margin-left: 0.4rem;
    font-size: 0.62rem;
    font-weight: 600;
    padding: 0.12rem 0.45rem;
    border-radius: 999px;
    background: var(--bb-lavender);
    color: #3d2e8a;
    vertical-align: middle;
}

/* ── Stock badge ── */
.pi-stock-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.22rem 0.6rem;
    border-radius: 6px;
    white-space: nowrap;
}

.stock--ok {
    background: #e8f5ee;
    color: #2a7a50;
}

.stock--low {
    background: #fff8e6;
    color: #8a6000;
}

.stock--nil {
    background: var(--bb-red-bg);
    color: var(--bb-red);
}

/* ── Status badge ── */
.pi-status-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.22rem 0.65rem;
    border-radius: 999px;
}

.status--on {
    background: #e8f5ee;
    color: #2a7a50;
}

.status--off {
    background: #f3f3f8;
    color: var(--bb-muted);
}

/* ── Table action buttons ── */
.pi-action-btn {
    display: inline-flex;
    align-items: center;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    border: 1px solid transparent;
    text-decoration: none;
    cursor: pointer;
    background: none;
    margin-left: 0.35rem;
    transition: background 0.12s, color 0.12s, border-color 0.12s;
    font-family: 'DM Sans', sans-serif;
}

.pi-action-btn--edit {
    color: var(--bb-muted);
}

.pi-action-btn--edit:hover {
    background: var(--bb-cream);
    border-color: var(--bb-border);
    color: var(--bb-text);
}

.pi-action-btn--del {
    color: var(--bb-red);
}

.pi-action-btn--del:hover {
    background: var(--bb-red-bg);
    border-color: var(--bb-red);
}

/* ── Mobile cards ── */
.pi-mob-list {
    display: flex;
    flex-direction: column;
}

@media (min-width: 768px) {
    .pi-mob-list {
        display: none;
    }
}

.pi-mob-card {
    padding: 1rem;
    border-bottom: 1px solid var(--bb-border);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: background 0.12s;
}

.pi-mob-card:last-child {
    border-bottom: none;
}

.pi-mob-card:hover {
    background: #fdfcfb;
}

.pi-mob-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.pi-mob-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.pi-mob-meta {
    display: flex;
    gap: 2rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--bb-border);
}

.pi-mob-stat {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.pi-mob-stat-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
}

.pi-mob-stat-val {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--bb-text);
}

.pi-mob-foot {
    display: flex;
    gap: 0.5rem;
}

/* Small buttons for mobile */
.pi-btn-sm {
    display: inline-flex;
    align-items: center;
    padding: 0.42rem 0.85rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid var(--bb-border);
    cursor: pointer;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
    transition: background 0.12s;
}

.pi-btn-sm--edit {
    background: var(--bb-cream);
    color: var(--bb-text);
}

.pi-btn-sm--edit:hover {
    background: var(--bb-border);
}

.pi-btn-sm--del {
    background: var(--bb-red-bg);
    color: var(--bb-red);
    border-color: var(--bb-red);
}

.pi-btn-sm--del:hover {
    background: var(--bb-red);
    color: #fff;
}

/* ── Empty state ── */
.pi-empty {
    padding: 4rem 2rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.pi-empty-icon {
    font-size: 2.5rem;
    opacity: 0.5;
}

.pi-empty-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--bb-text);
}

.pi-empty-sub {
    font-size: 0.85rem;
    color: var(--bb-muted);
}

/* ── Pagination ── */
.pi-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.pi-page-info {
    font-size: 0.82rem;
    color: var(--bb-muted);
}

.pi-page-info strong {
    color: var(--bb-text);
}

.pi-page-btns {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.pi-page-btn {
    min-width: 36px;
    height: 34px;
    padding: 0 0.6rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    color: var(--bb-muted);
    font-size: 0.82rem;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.pi-page-btn:hover:not(:disabled) {
    background: var(--bb-cream);
    color: var(--bb-text);
}

.pi-page-btn--active {
    background: var(--bb-navy);
    color: #fff;
    border-color: var(--bb-navy);
    font-weight: 600;
}

.pi-page-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>
