<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

declare const route: any;

interface AttachedItem {
    id: number;
    name: string;
    category: string;
    supplier_name: string | null;
    supplier_url: string | null;
    purchase_price: number;
    purchase_qty: number;
    unit_cost: number;
    qty_per_unit: number;
    contribution: number;
}

interface AvailableItem {
    id: number;
    name: string;
    category: string;
    unit_cost: number;
}

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
    status: string;
    images: { image: string; status: string }[];
}

const props = defineProps<{
    product: Product;
    costItems: AttachedItem[];
    allItems: AvailableItem[];
    categories: Record<string, string>;
}>();

const { fmtCurrency, confirmDelete } = useAdmin();
const page  = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

// ── Totals ─────────────────────────────────────────────────────────────────
const totalCost = computed(() =>
    props.costItems.reduce((sum, i) => sum + i.contribution, 0)
);
const profit = computed(() => props.product.cost - totalCost.value);
const margin = computed(() => {
    if (!props.product.cost) return null;
    return ((profit.value / props.product.cost) * 100).toFixed(1);
});

// ── Add item panel ─────────────────────────────────────────────────────────
const showAdd   = ref(false);
const addForm   = useForm({ cost_item_id: '', qty_per_unit: '' as string | number });
const attachedIds = computed(() => new Set(props.costItems.map(i => i.id)));

const availableItems = computed(() =>
    props.allItems.filter(i => !attachedIds.value.has(i.id))
);

const selectedItem = computed(() =>
    props.allItems.find(i => i.id === parseInt(String(addForm.cost_item_id))) ?? null
);

const submitAdd = () => {
    addForm.post(route('admin.finance.products.costs.add', props.product.id), {
        onSuccess: () => {
            addForm.reset();
            showAdd.value = false;
        },
    });
};

// ── Inline qty edit ────────────────────────────────────────────────────────
const editingQty = ref<number | null>(null);
const qtyValue   = ref('');

const startEditQty = (item: AttachedItem) => {
    editingQty.value = item.id;
    qtyValue.value   = String(item.qty_per_unit);
};

const saveQty = (item: AttachedItem) => {
    router.put(route('admin.finance.products.costs.update', [props.product.id, item.id]), {
        qty_per_unit: parseFloat(qtyValue.value),
    }, {
        onSuccess: () => { editingQty.value = null; },
    });
};

const cancelEditQty = () => { editingQty.value = null; };

// ── Remove ─────────────────────────────────────────────────────────────────
const removeItem = (item: AttachedItem) => {
    confirmDelete(`Remove "${item.name}" from this product's costs?`, () => {
        router.delete(route('admin.finance.products.costs.remove', [props.product.id, item.id]));
    });
};

// ── Category colour ────────────────────────────────────────────────────────
const categoryColour: Record<string, string> = {
    packaging: 'adm-badge--lav',
    fragrance: 'adm-badge--on',
    material:  'adm-badge--warn',
    labour:    'adm-badge--red',
    overhead:  'adm-badge--off',
    other:     'adm-badge--off',
};

const marginClass = (m: string | null): string => {
    if (m === null) return 'fi-margin fi-margin--none';
    const n = parseFloat(m);
    if (n >= 60) return 'fi-margin fi-margin--great';
    if (n >= 40) return 'fi-margin fi-margin--good';
    if (n >= 20) return 'fi-margin fi-margin--ok';
    return 'fi-margin fi-margin--low';
};

const coverImage = computed(() =>
    props.product.images?.find(i => i.status === 'active')?.image
    ?? props.product.images?.[0]?.image
    ?? null
);
</script>

<template>
    <AdminLayout>
        <Head :title="`${product.name} — Cost Breakdown`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.finance.index')">Finance</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <Link :href="route('admin.finance.products')">Product Costs</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ product.name }}</span>
                </div>
                <h1 class="adm-title">{{ product.name }}</h1>
                <p class="adm-sub">MPN: {{ product.mpn }} &nbsp;·&nbsp; Selling price: {{ fmtCurrency(product.cost) }}</p>
            </div>
            <button @click="showAdd = !showAdd" class="adm-btn adm-btn--primary adm-btn--sm">
                {{ showAdd ? 'Cancel' : '+ Add Cost' }}
            </button>
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

        <div class="pcd-layout">
            <!-- Left: cost breakdown -->
            <div class="pcd-main">

                <!-- Add item panel -->
                <div v-if="showAdd" class="adm-card pcd-add-panel">
                    <h3 class="pcd-panel-title">Add a cost item</h3>
                    <form @submit.prevent="submitAdd" class="pcd-add-form">
                        <div class="pcd-add-row">
                            <div class="fi-field" style="flex:3">
                                <label class="fi-label">Cost Item <span class="fi-req">*</span></label>
                                <select v-model="addForm.cost_item_id" class="adm-select">
                                    <option value="">Select an item…</option>
                                    <optgroup v-for="(label, key) in categories" :key="key" :label="label">
                                        <option v-for="item in availableItems.filter(i => i.category === key)"
                                            :key="item.id" :value="item.id">
                                            {{ item.name }} ({{ fmtCurrency(item.unit_cost) }}/unit)
                                        </option>
                                    </optgroup>
                                </select>
                                <p v-if="addForm.errors.cost_item_id" class="fi-error">{{ addForm.errors.cost_item_id }}</p>
                            </div>
                            <div class="fi-field" style="flex:1">
                                <label class="fi-label">Qty per product <span class="fi-req">*</span></label>
                                <input v-model="addForm.qty_per_unit" type="number" step="0.0001" min="0.0001"
                                    class="adm-input" placeholder="e.g. 1" />
                                <p v-if="addForm.errors.qty_per_unit" class="fi-error">{{ addForm.errors.qty_per_unit }}</p>
                            </div>
                            <div class="fi-field pcd-add-preview">
                                <label class="fi-label">Cost contribution</label>
                                <div class="fi-unit-preview">
                                    <template v-if="selectedItem && addForm.qty_per_unit">
                                        {{ fmtCurrency(selectedItem.unit_cost * parseFloat(String(addForm.qty_per_unit))) }}
                                    </template>
                                    <template v-else>—</template>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:.75rem;justify-content:flex-end">
                            <button type="button" @click="showAdd = false" class="adm-btn adm-btn--ghost adm-btn--sm">Cancel</button>
                            <button type="submit" class="adm-btn adm-btn--primary adm-btn--sm" :disabled="addForm.processing">
                                Add
                            </button>
                        </div>
                    </form>

                    <div v-if="availableItems.length === 0" class="adm-empty" style="padding:1rem 0 0">
                        <p>All cost items are already added. <Link :href="route('admin.finance.index')" class="pcd-link">Add more items →</Link></p>
                    </div>
                </div>

                <!-- Cost items table -->
                <div class="adm-card" style="padding:0">
                    <table class="adm-table" v-if="costItems.length">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="adm-table-num">Unit Cost</th>
                                <th class="adm-table-num">Qty / Product</th>
                                <th class="adm-table-num">Contribution</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in costItems" :key="item.id">
                                <td>
                                    <div class="fi-item-name">{{ item.name }}</div>
                                    <div style="display:flex;gap:.5rem;align-items:center;margin-top:.2rem">
                                        <span class="adm-badge" :class="categoryColour[item.category] ?? 'adm-badge--off'" style="font-size:.7rem">
                                            {{ categories[item.category] ?? item.category }}
                                        </span>
                                        <a v-if="item.supplier_url" :href="item.supplier_url" target="_blank"
                                            rel="noopener" class="fi-supplier-link" style="font-size:.75rem">
                                            {{ item.supplier_name || 'Supplier →' }}
                                        </a>
                                    </div>
                                </td>
                                <td class="adm-table-num">{{ fmtCurrency(item.unit_cost) }}</td>
                                <td class="adm-table-num">
                                    <template v-if="editingQty === item.id">
                                        <div style="display:flex;gap:.4rem;align-items:center;justify-content:flex-end">
                                            <input v-model="qtyValue" type="number" step="0.0001" min="0.0001"
                                                class="adm-input adm-input--sm pcd-qty-input" @keydown.enter="saveQty(item)" @keydown.escape="cancelEditQty" />
                                            <button @click="saveQty(item)" class="adm-btn adm-btn--primary adm-btn--xs">✓</button>
                                            <button @click="cancelEditQty" class="adm-btn adm-btn--ghost adm-btn--xs">✕</button>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <button @click="startEditQty(item)" class="pcd-qty-display">
                                            {{ item.qty_per_unit }}
                                        </button>
                                    </template>
                                </td>
                                <td class="adm-table-num pcd-contribution">{{ fmtCurrency(item.contribution) }}</td>
                                <td class="adm-table-actions">
                                    <button @click="removeItem(item)" class="adm-btn adm-btn--danger adm-btn--xs">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="pcd-totals-row">
                                <td colspan="3" style="text-align:right;font-weight:700">Total COGS</td>
                                <td class="adm-table-num pcd-total-cost">{{ fmtCurrency(totalCost) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div v-else class="adm-empty">
                        <p>No cost items added yet.</p>
                        <button @click="showAdd = true" class="adm-btn adm-btn--primary adm-btn--sm" style="margin-top:.75rem">
                            Add your first cost
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: summary card -->
            <div class="pcd-sidebar">
                <div class="adm-card pcd-summary">
                    <img v-if="coverImage" :src="`/storage/${coverImage}`"
                        :alt="product.name" class="pcd-product-img" />

                    <h3 class="pcd-summary-title">Summary</h3>

                    <div class="pcd-summary-row">
                        <span>Selling Price</span>
                        <strong>{{ fmtCurrency(product.cost) }}</strong>
                    </div>
                    <div class="pcd-summary-row">
                        <span>Total COGS</span>
                        <strong :class="totalCost > 0 ? 'pcd-cost-val' : ''">{{ fmtCurrency(totalCost) }}</strong>
                    </div>
                    <div class="pcd-summary-divider"></div>
                    <div class="pcd-summary-row">
                        <span>Gross Profit</span>
                        <strong :class="profit >= 0 ? 'pcd-profit-pos' : 'pcd-profit-neg'">
                            {{ fmtCurrency(profit) }}
                        </strong>
                    </div>
                    <div class="pcd-summary-row" style="margin-top:.35rem">
                        <span>Margin</span>
                        <span :class="marginClass(margin)">
                            {{ margin !== null ? `${margin}%` : '—' }}
                        </span>
                    </div>

                    <div v-if="costItems.length === 0" class="pcd-summary-hint">
                        Add cost items to see your margin.
                    </div>
                    <div v-else-if="margin !== null && parseFloat(margin) < 30" class="adm-flash adm-flash--warn" style="margin-top:1rem;font-size:.8rem">
                        Margin is below 30%. Consider reviewing your costs or pricing.
                    </div>
                </div>

                <div class="adm-card pcd-breakdown-legend">
                    <h4 class="pcd-legend-title">By Category</h4>
                    <template v-if="costItems.length">
                        <div v-for="(label, key) in categories" :key="key">
                            <template v-if="costItems.filter(i => i.category === key).length">
                                <div class="pcd-legend-row">
                                    <span class="adm-badge" :class="categoryColour[key] ?? 'adm-badge--off'" style="font-size:.7rem">{{ label }}</span>
                                    <strong>{{ fmtCurrency(costItems.filter(i => i.category === key).reduce((s, i) => s + i.contribution, 0)) }}</strong>
                                </div>
                            </template>
                        </div>
                    </template>
                    <p v-else class="adm-muted" style="font-size:.8rem">No items yet.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.pcd-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.5rem;
    align-items: start;
}

.pcd-main { display: flex; flex-direction: column; gap: 1.25rem; }

.pcd-add-panel { padding: 1.25rem; }
.pcd-panel-title { font-size: .95rem; font-weight: 700; margin: 0 0 1rem; }
.pcd-add-form { display: flex; flex-direction: column; gap: 1rem; }
.pcd-add-row { display: flex; gap: 1rem; align-items: flex-end; }

.fi-field { display: flex; flex-direction: column; gap: .35rem; }
.fi-label { font-size: .8rem; font-weight: 600; color: #555; }
.fi-req { color: #e05555; }
.fi-error { font-size: .75rem; color: #e05555; margin: 0; }

.fi-unit-preview {
    height: 2.4rem;
    display: flex;
    align-items: center;
    padding: 0 .75rem;
    background: #f7faf8;
    border: 1px solid #d0e8da;
    border-radius: 8px;
    font-weight: 700;
    color: #2e7d52;
    font-size: .9rem;
}

.fi-item-name { font-weight: 600; font-size: .875rem; }
.fi-supplier-link { color: #6b5ce7; text-decoration: underline; }

.pcd-qty-input { width: 80px; text-align: center; }
.pcd-qty-display {
    background: none;
    border: 1px dashed #ccc;
    border-radius: 6px;
    padding: .2rem .6rem;
    font-size: .85rem;
    cursor: pointer;
    color: inherit;
}
.pcd-qty-display:hover { border-color: #6b5ce7; background: #f5f2ff; }

.pcd-contribution { font-weight: 600; color: #444; }

.pcd-totals-row td { padding-top: .75rem; padding-bottom: .75rem; border-top: 2px solid #e8e8e8 !important; }
.pcd-total-cost { font-weight: 800; font-size: 1rem; color: #1a1a1a; }

/* Sidebar */
.pcd-sidebar { display: flex; flex-direction: column; gap: 1rem; }

.pcd-summary { padding: 1.25rem; }
.pcd-summary-title { font-size: .95rem; font-weight: 700; margin: 0 0 .85rem; }
.pcd-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .875rem;
    padding: .3rem 0;
}
.pcd-summary-divider { border-top: 1px solid #eee; margin: .5rem 0; }
.pcd-cost-val { color: #c0392b; }
.pcd-profit-pos { color: #2e7d52; }
.pcd-profit-neg { color: #c0392b; }

.pcd-summary-hint { font-size: .8rem; color: #888; margin-top: 1rem; text-align: center; }

.pcd-product-img {
    width: 100%;
    border-radius: 10px;
    object-fit: cover;
    height: 140px;
    margin-bottom: 1rem;
}

.pcd-breakdown-legend { padding: 1.25rem; }
.pcd-legend-title { font-size: .85rem; font-weight: 700; margin: 0 0 .75rem; color: #555; }
.pcd-legend-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .3rem 0;
    font-size: .85rem;
}

.pcd-add-preview { min-width: 120px; }

.pcd-link { color: #6b5ce7; text-decoration: underline; }

.fi-margin {
    display: inline-block;
    padding: .2rem .6rem;
    border-radius: 20px;
    font-size: .78rem;
    font-weight: 700;
}
.fi-margin--none  { background: #f0f0f0; color: #888; }
.fi-margin--great { background: #d4edda; color: #1a6b35; }
.fi-margin--good  { background: #d6ead8; color: #2e7d52; }
.fi-margin--ok    { background: #fff3cd; color: #856404; }
.fi-margin--low   { background: #fde2e2; color: #c0392b; }

@media (max-width: 860px) {
    .pcd-layout { grid-template-columns: 1fr; }
    .pcd-add-row { flex-direction: column; }
}
</style>
