<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useAdmin } from '@/composables/useAdmin';

interface Product {
    id: number; name: string; mpn: string; cost: number;
    stock_qty: number; status: 'enabled' | 'disabled';
    parent_product_id: number | null; images: { image: string }[];
}
interface ProductsPaginated {
    data: Product[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number; last_page: number; total: number;
}

defineProps<{ products: ProductsPaginated }>();

const { paginate, confirmDelete, fmtCurrency, stockLabel } = useAdmin();
</script>

<template>
    <AdminLayout>

        <Head title="Products — Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Products</h1>
                <p class="adm-sub">{{ products.total }} products in catalogue</p>
            </div>
            <Link :href="route('admin.products.create')" class="adm-btn adm-btn--primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            Add Product
            </Link>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom:1.5rem">

            <!-- Desktop table -->
            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th" style="width:52px"></th>
                            <th class="adm-th">MPN</th>
                            <th class="adm-th">Name</th>
                            <th class="adm-th">Price</th>
                            <th class="adm-th">Stock</th>
                            <th class="adm-th">Status</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in products.data" :key="p.id" class="adm-row">
                            <td class="adm-td">
                                <div class="adm-thumb">
                                    <img v-if="p.images[0]?.image" :src="p.images[0].image" :alt="p.name" />
                                    <span v-else class="adm-thumb-nil">—</span>
                                </div>
                            </td>
                            <td class="adm-td adm-td--mono">{{ p.mpn }}</td>
                            <td class="adm-td">
                                <Link :href="route('admin.products.edit', p.id)" class="pi-name-link">{{ p.name }}
                                </Link>
                                <span v-if="p.parent_product_id"
                                    class="adm-badge adm-badge--lav pi-variant">Variant</span>
                            </td>
                            <td class="adm-td adm-td--price">{{ fmtCurrency(p.cost) }}</td>
                            <td class="adm-td">
                                <span class="adm-stock" :class="stockLabel(p.stock_qty).cls">
                                    {{ stockLabel(p.stock_qty).text }}
                                </span>
                            </td>
                            <td class="adm-td">
                                <span class="adm-badge"
                                    :class="p.status === 'enabled' ? 'adm-badge--on' : 'adm-badge--off'">
                                    {{ p.status === 'enabled' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="adm-td adm-td--actions">
                                <Link :href="route('admin.products.edit', p.id)" class="adm-action adm-action--edit">
                                Edit</Link>
                                <button @click="confirmDelete(p.name, 'admin.products.destroy', p.id)"
                                    class="adm-action adm-action--del">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="adm-mob-list">
                <div v-for="p in products.data" :key="p.id" class="adm-mob-card">
                    <div class="pi-mob-head">
                        <div class="adm-thumb adm-thumb--lg">
                            <img v-if="p.images[0]?.image" :src="p.images[0].image" :alt="p.name" />
                            <span v-else class="adm-thumb-nil">—</span>
                        </div>
                        <div class="pi-mob-info">
                            <p class="adm-td--mono" style="font-size:0.7rem">{{ p.mpn }}</p>
                            <Link :href="route('admin.products.edit', p.id)" class="pi-name-link">{{ p.name }}</Link>
                        </div>
                        <span class="adm-badge" :class="p.status === 'enabled' ? 'adm-badge--on' : 'adm-badge--off'">
                            {{ p.status === 'enabled' ? 'Active' : 'Off' }}
                        </span>
                    </div>
                    <div class="pi-mob-meta">
                        <div>
                            <p class="pi-stat-label">Price</p>
                            <p class="pi-stat-val">{{ fmtCurrency(p.cost) }}</p>
                        </div>
                        <div>
                            <p class="pi-stat-label">Stock</p>
                            <span class="adm-stock" :class="stockLabel(p.stock_qty).cls">{{ stockLabel(p.stock_qty).text
                            }}</span>
                        </div>
                    </div>
                    <div class="pi-mob-foot">
                        <Link :href="route('admin.products.edit', p.id)" class="adm-btn adm-btn--ghost adm-btn--sm">Edit
                        </Link>
                        <button @click="confirmDelete(p.name, 'admin.products.destroy', p.id)"
                            class="adm-btn adm-btn--danger adm-btn--sm">Delete</button>
                    </div>
                </div>
            </div>

            <div v-if="!products.data.length" class="adm-empty">
                <div class="adm-empty-icon">📦</div>
                <p class="adm-empty-title">No products yet</p>
                <p class="adm-empty-sub">Add your first product to get started.</p>
                <Link :href="route('admin.products.create')" class="adm-btn adm-btn--primary">Add Product</Link>
            </div>

        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="adm-pagination">
            <p class="adm-page-info">Page <strong>{{ products.current_page }}</strong> of <strong>{{ products.last_page
            }}</strong></p>
            <div class="adm-page-btns">
                <button v-for="link in products.links" :key="link.label" @click.prevent="paginate(link.url)"
                    :disabled="!link.url" class="adm-page-btn" :class="{ 'adm-page-btn--active': link.active }"
                    v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                </button>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* Only page-specific styles live here — everything else is in admin-design-system.css */

/* Name link */
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

/* Variant pill spacing */
.pi-variant {
    margin-left: 0.4rem;
    vertical-align: middle;
}

/* Mobile card layout */
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

.pi-mob-foot {
    display: flex;
    gap: 0.5rem;
}

.pi-stat-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
}

.pi-stat-val {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--bb-text);
}
</style>
