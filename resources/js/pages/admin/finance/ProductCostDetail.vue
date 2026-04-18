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

const { fmtCurrency } = useAdmin();
const page  = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

// ── Totals ─────────────────────────────────────────────────────────────────
const totalCost = computed(() =>
    props.costItems.reduce((sum, i) => sum + i.contribution, 0)
);
const profit = computed(() => props.product.cost - totalCost.value);
const margin = computed((): string | null => {
    if (!props.product.cost || props.costItems.length === 0) return null;
    return ((profit.value / props.product.cost) * 100).toFixed(1);
});

// ── Add item form ──────────────────────────────────────────────────────────
const showAdd = ref(false);
const addForm = useForm({ cost_item_id: '', qty_per_unit: '' as string | number });

const attachedIds = computed(() => new Set(props.costItems.map(i => i.id)));
const availableItems = computed(() => props.allItems.filter(i => !attachedIds.value.has(i.id)));

const selectedItem = computed(() =>
    props.allItems.find(i => i.id === parseInt(String(addForm.cost_item_id))) ?? null
);

const addPreview = computed(() => {
    if (!selectedItem.value || !addForm.qty_per_unit) return null;
    return selectedItem.value.unit_cost * parseFloat(String(addForm.qty_per_unit));
});

const submitAdd = () => {
    addForm.post(route('admin.finance.products.costs.add', props.product.id), {
        onSuccess: () => { addForm.reset(); showAdd.value = false; },
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
    }, { onSuccess: () => { editingQty.value = null; } });
};
const cancelEditQty = () => { editingQty.value = null; };

// ── Remove ─────────────────────────────────────────────────────────────────
const removeItem = (item: AttachedItem) => {
    if (!confirm(`Remove "${item.name}" from this cost breakdown?`)) return;
    router.delete(route('admin.finance.products.costs.remove', [props.product.id, item.id]));
};

// ── Ideal Selling Price ────────────────────────────────────────────────────
const targetMargin = ref(50);
const idealSellingPrice = computed(() => {
    if (!props.costItems.length || targetMargin.value <= 0 || targetMargin.value >= 100) return null;
    return totalCost.value / (1 - targetMargin.value / 100);
});

// ── Helpers ────────────────────────────────────────────────────────────────
const categoryBadge: Record<string, string> = {
    packaging: 'adm-badge--lav',
    fragrance: 'adm-badge--on',
    material:  'adm-badge--warn',
    labour:    'adm-badge--red',
    overhead:  'adm-badge--off',
    other:     'adm-badge--off',
};

const marginBadgeClass = (m: string | null): string => {
    if (m === null) return 'adm-badge adm-badge--off';
    const n = parseFloat(m);
    if (n >= 60) return 'adm-badge pcd-margin--great';
    if (n >= 40) return 'adm-badge pcd-margin--good';
    if (n >= 20) return 'adm-badge pcd-margin--ok';
    return 'adm-badge pcd-margin--low';
};

const coverImage = computed(() =>
    props.product.images?.find(i => i.status === 'active')?.image
    ?? props.product.images?.[0]?.image
    ?? null
);

const categoryTotal = (key: string) =>
    props.costItems.filter(i => i.category === key).reduce((s, i) => s + i.contribution, 0);
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
                <p class="adm-sub">{{ product.mpn }} &nbsp;·&nbsp; Selling price: {{ fmtCurrency(product.cost) }}</p>
            </div>
            <button @click="showAdd = !showAdd" class="adm-btn adm-btn--primary adm-btn--sm">
                <svg v-if="!showAdd" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                {{ showAdd ? 'Cancel' : 'Add Cost' }}
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

        <div class="adm-form-grid pcd-grid">

            <!-- Main column -->
            <div class="adm-form-left">

                <!-- Add item panel -->
                <div v-if="showAdd" class="adm-card adm-card--sm">
                    <p class="adm-card-title">Add a cost item</p>

                    <div v-if="availableItems.length === 0" class="adm-empty" style="padding:1.5rem 0 0">
                        <p class="adm-empty-sub">All cost items are already added.</p>
                        <Link :href="route('admin.finance.index')" class="adm-btn adm-btn--ghost adm-btn--sm" style="margin-top:.5rem">
                            Manage cost items →
                        </Link>
                    </div>

                    <form v-else @submit.prevent="submitAdd">
                        <div class="pcd-add-row">
                            <div class="adm-field" style="flex:3">
                                <label class="adm-label">Item <span class="pcd-req">*</span></label>
                                <select v-model="addForm.cost_item_id" class="adm-select"
                                    :class="{ 'adm-select--err': addForm.errors.cost_item_id }">
                                    <option value="">Select an item…</option>
                                    <optgroup v-for="(label, key) in categories" :key="key" :label="label">
                                        <option v-for="item in availableItems.filter(i => i.category === key)"
                                            :key="item.id" :value="item.id">
                                            {{ item.name }} ({{ fmtCurrency(item.unit_cost) }}/unit)
                                        </option>
                                    </optgroup>
                                </select>
                                <p v-if="addForm.errors.cost_item_id" class="adm-err">{{ addForm.errors.cost_item_id }}</p>
                            </div>
                            <div class="adm-field" style="flex:1">
                                <label class="adm-label">Qty / product <span class="pcd-req">*</span></label>
                                <input v-model="addForm.qty_per_unit" type="number" step="0.0001" min="0.0001"
                                    class="adm-input"
                                    :class="{ 'adm-input--err': addForm.errors.qty_per_unit }"
                                    placeholder="1" />
                                <p v-if="addForm.errors.qty_per_unit" class="adm-err">{{ addForm.errors.qty_per_unit }}</p>
                            </div>
                            <div class="adm-field pcd-add-preview">
                                <label class="adm-label">Contribution</label>
                                <div class="pcd-preview-box">
                                    {{ addPreview !== null ? fmtCurrency(addPreview) : '—' }}
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:.6rem;margin-top:.85rem">
                            <button type="button" @click="showAdd = false" class="adm-btn adm-btn--ghost adm-btn--sm">Cancel</button>
                            <button type="submit" class="adm-btn adm-btn--primary adm-btn--sm" :disabled="addForm.processing">Add</button>
                        </div>
                    </form>
                </div>

                <!-- Cost breakdown table -->
                <div class="adm-card adm-card--flush">

                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead class="adm-thead">
                                <tr>
                                    <th class="adm-th">Item</th>
                                    <th class="adm-th adm-th--right">Unit Cost</th>
                                    <th class="adm-th adm-th--right">Qty / Product</th>
                                    <th class="adm-th adm-th--right">Contribution</th>
                                    <th class="adm-th adm-th--right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in costItems" :key="item.id" class="adm-row">
                                    <td class="adm-td">
                                        <p class="pcd-item-name">{{ item.name }}</p>
                                        <div style="display:flex;gap:.4rem;align-items:center;margin-top:.2rem">
                                            <span class="adm-badge" :class="categoryBadge[item.category] ?? 'adm-badge--off'"
                                                style="font-size:.68rem">
                                                {{ categories[item.category] ?? item.category }}
                                            </span>
                                            <a v-if="item.supplier_url" :href="item.supplier_url" target="_blank"
                                                rel="noopener" class="pcd-supplier-link">
                                                {{ item.supplier_name || 'Supplier →' }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="adm-td adm-td--right" style="font-size:.875rem;color:var(--bb-muted)">
                                        {{ fmtCurrency(item.unit_cost) }}
                                    </td>
                                    <td class="adm-td adm-td--right">
                                        <template v-if="editingQty === item.id">
                                            <div class="pcd-qty-edit">
                                                <input v-model="qtyValue" type="number" step="0.0001" min="0.0001"
                                                    class="adm-input adm-input--sm pcd-qty-input"
                                                    @keydown.enter="saveQty(item)"
                                                    @keydown.escape="cancelEditQty" />
                                                <button @click="saveQty(item)" class="adm-action adm-action--edit" title="Save">✓</button>
                                                <button @click="cancelEditQty" class="adm-action adm-action--del" title="Cancel">✕</button>
                                            </div>
                                        </template>
                                        <button v-else @click="startEditQty(item)" class="pcd-qty-btn">
                                            {{ item.qty_per_unit }}
                                        </button>
                                    </td>
                                    <td class="adm-td adm-td--right adm-td--price pcd-contribution">
                                        {{ fmtCurrency(item.contribution) }}
                                    </td>
                                    <td class="adm-td adm-td--actions">
                                        <button @click="removeItem(item)" class="adm-action adm-action--del">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-if="costItems.length">
                                <tr class="pcd-totals-row">
                                    <td colspan="3" class="adm-td" style="text-align:right;font-weight:700;font-size:.82rem;letter-spacing:.04em;text-transform:uppercase;color:var(--bb-muted)">
                                        Total COGS
                                    </td>
                                    <td class="adm-td adm-td--right pcd-total">{{ fmtCurrency(totalCost) }}</td>
                                    <td class="adm-td"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Mobile cards -->
                    <div class="adm-mob-list">
                        <div v-for="item in costItems" :key="item.id" class="adm-mob-card">
                            <div class="pcd-mob-head">
                                <div style="flex:1;min-width:0">
                                    <p class="pcd-item-name">{{ item.name }}</p>
                                    <span class="adm-badge" :class="categoryBadge[item.category] ?? 'adm-badge--off'"
                                        style="font-size:.68rem;margin-top:.2rem">
                                        {{ categories[item.category] ?? item.category }}
                                    </span>
                                </div>
                                <p class="pcd-contribution" style="font-size:1rem">{{ fmtCurrency(item.contribution) }}</p>
                            </div>
                            <div class="pcd-mob-meta">
                                <div>
                                    <p class="pcd-mob-label">Unit Cost</p>
                                    <p class="pcd-mob-val">{{ fmtCurrency(item.unit_cost) }}</p>
                                </div>
                                <div>
                                    <p class="pcd-mob-label">Qty / Product</p>
                                    <p class="pcd-mob-val">{{ item.qty_per_unit }}</p>
                                </div>
                            </div>
                            <div style="display:flex;gap:.5rem">
                                <button @click="startEditQty(item)" class="adm-btn adm-btn--ghost adm-btn--sm">Edit Qty</button>
                                <button @click="removeItem(item)" class="adm-btn adm-btn--danger adm-btn--sm">Remove</button>
                            </div>
                        </div>
                        <div v-if="costItems.length" class="adm-mob-card" style="background:var(--bb-cream)">
                            <div style="display:flex;justify-content:space-between;font-weight:700">
                                <span>Total COGS</span>
                                <span class="pcd-total">{{ fmtCurrency(totalCost) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="!costItems.length" class="adm-empty">
                        <div class="adm-empty-icon">🧮</div>
                        <p class="adm-empty-title">No costs added yet</p>
                        <p class="adm-empty-sub">Add the supplies that go into this product.</p>
                        <button @click="showAdd = true" class="adm-btn adm-btn--primary">Add First Cost</button>
                    </div>

                </div>
            </div>

            <!-- Sidebar -->
            <div class="adm-form-right">

                <!-- Product card -->
                <div class="adm-card adm-card--sm adm-card--sticky">
                    <img v-if="coverImage" :src="`/storage/${coverImage}`"
                        :alt="product.name" class="pcd-cover" />

                    <p class="adm-card-title">Summary</p>

                    <div class="pcd-summary-row">
                        <span>Selling Price</span>
                        <strong>{{ fmtCurrency(product.cost) }}</strong>
                    </div>
                    <div class="pcd-summary-row">
                        <span>Total COGS</span>
                        <strong :style="totalCost > 0 ? 'color:var(--bb-red)' : ''">
                            {{ fmtCurrency(totalCost) }}
                        </strong>
                    </div>
                    <div class="pcd-divider"></div>
                    <div class="pcd-summary-row">
                        <span>Gross Profit</span>
                        <strong :style="profit >= 0 ? 'color:var(--bb-sage-d)' : 'color:var(--bb-red)'">
                            {{ fmtCurrency(profit) }}
                        </strong>
                    </div>
                    <div class="pcd-summary-row" style="margin-top:.3rem">
                        <span>Margin</span>
                        <span :class="marginBadgeClass(margin)">
                            {{ margin !== null ? `${margin}%` : '—' }}
                        </span>
                    </div>

                    <div v-if="!costItems.length" class="pcd-hint">
                        Add cost items to see your margin.
                    </div>
                    <div v-else-if="margin !== null && parseFloat(margin) < 30"
                        class="adm-flash adm-flash--warn" style="margin-top:1rem;font-size:.78rem;margin-bottom:0">
                        Margin is below 30% — consider reviewing your costs or pricing.
                    </div>
                </div>

                <!-- Ideal Selling Price -->
                <div v-if="costItems.length" class="adm-card adm-card--sm">
                    <p class="adm-card-title">Ideal Selling Price</p>
                    <p class="pcd-hint" style="margin-top:0;margin-bottom:.75rem;text-align:left">
                        What to charge to hit your target margin.
                    </p>
                    <div class="pcd-summary-row">
                        <label class="pcd-target-label" for="targetMargin">Target margin</label>
                        <div class="pcd-target-input-wrap">
                            <input id="targetMargin" v-model.number="targetMargin" type="number"
                                min="1" max="99" step="1" class="adm-input adm-input--sm pcd-target-input" />
                            <span class="pcd-target-pct">%</span>
                        </div>
                    </div>
                    <div class="pcd-divider"></div>
                    <div class="pcd-summary-row">
                        <span>Ideal Price</span>
                        <strong class="pcd-ideal-price">
                            {{ idealSellingPrice !== null ? fmtCurrency(idealSellingPrice) : '—' }}
                        </strong>
                    </div>
                    <div v-if="idealSellingPrice !== null" class="pcd-summary-row" style="margin-top:.1rem">
                        <span style="font-size:.78rem;color:var(--bb-muted)">vs. current price</span>
                        <span :style="idealSellingPrice > product.cost ? 'color:var(--bb-red);font-size:.8rem;font-weight:600' : 'color:var(--bb-sage-d);font-size:.8rem;font-weight:600'">
                            {{ idealSellingPrice > product.cost ? '+' : '' }}{{ fmtCurrency(idealSellingPrice - product.cost) }}
                        </span>
                    </div>
                </div>

                <!-- By category -->
                <div v-if="costItems.length" class="adm-card adm-card--sm">
                    <p class="adm-card-title">By Category</p>
                    <template v-for="(label, key) in categories" :key="key">
                        <div v-if="costItems.some(i => i.category === key)" class="pcd-summary-row">
                            <span class="adm-badge" :class="categoryBadge[key] ?? 'adm-badge--off'"
                                style="font-size:.68rem">{{ label }}</span>
                            <strong>{{ fmtCurrency(categoryTotal(key)) }}</strong>
                        </div>
                    </template>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* Override form-grid right column width for this page */
.pcd-grid { grid-template-columns: 1fr 280px; }
@media (max-width: 1024px) { .pcd-grid { grid-template-columns: 1fr; } }

/* Add item row */
.pcd-add-row { display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
.pcd-add-preview { min-width: 110px; }
.pcd-preview-box {
    height: 2.5rem;
    display: flex;
    align-items: center;
    padding: 0 .85rem;
    border: 1px solid var(--bb-green-border);
    border-radius: var(--bb-radius);
    background: var(--bb-green-bg);
    font-weight: 700;
    color: var(--bb-sage-d);
    font-size: .9rem;
}

/* Table */
.pcd-item-name   { font-size: .875rem; font-weight: 600; color: var(--bb-text); }
.pcd-supplier-link { font-size: .72rem; color: var(--bb-lav-d); text-decoration: underline; }
.pcd-contribution { font-weight: 700; color: var(--bb-text); }
.pcd-total { font-weight: 800; font-size: 1rem; color: var(--bb-text); }

.pcd-totals-row td { border-top: 2px solid var(--bb-border) !important; }

/* Inline qty edit */
.pcd-qty-edit { display: flex; gap: .3rem; align-items: center; justify-content: flex-end; }
.pcd-qty-input { width: 72px; text-align: center; }
.pcd-qty-btn {
    background: none;
    border: 1px dashed var(--bb-border);
    border-radius: var(--bb-radius-sm);
    padding: .2rem .55rem;
    font-size: .85rem;
    cursor: pointer;
    color: var(--bb-text);
    font-family: var(--bb-font);
    transition: border-color .12s, background .12s;
}
.pcd-qty-btn:hover { border-color: var(--bb-lav-d); background: #faf8ff; }

/* Sidebar */
.pcd-cover {
    width: 100%; border-radius: var(--bb-radius-md);
    object-fit: cover; height: 130px; margin-bottom: 1rem;
}
.pcd-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .875rem;
    padding: .3rem 0;
}
.pcd-divider { border-top: 1px solid var(--bb-border); margin: .5rem 0; }
.pcd-hint { font-size: .78rem; color: var(--bb-muted); margin-top: 1rem; text-align: center; }

/* Mobile */
.pcd-mob-head { display: flex; align-items: flex-start; gap: .75rem; }
.pcd-mob-meta { display: flex; gap: 2rem; padding-top: .5rem; border-top: 1px solid var(--bb-border); }
.pcd-mob-label { font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--bb-muted); }
.pcd-mob-val   { font-size: .875rem; font-weight: 600; color: var(--bb-text); margin-top: .1rem; }

/* Margin badge colours */
.pcd-margin--great { background: var(--bb-green-bg); color: #1a6b35; }
.pcd-margin--good  { background: #d6ead8; color: var(--bb-sage-d); }
.pcd-margin--ok    { background: var(--bb-warn-bg); color: var(--bb-warn-text); }
.pcd-margin--low   { background: var(--bb-red-bg); color: var(--bb-red); }

.pcd-req { color: var(--bb-red); }

/* Ideal Selling Price */
.pcd-target-label { font-size: .875rem; color: var(--bb-text); }
.pcd-target-input-wrap { display: flex; align-items: center; gap: .3rem; }
.pcd-target-input { width: 56px; text-align: center; }
.pcd-target-pct { font-size: .875rem; color: var(--bb-muted); }
.pcd-ideal-price { font-size: 1.05rem; color: var(--bb-lav-d); }
</style>
