<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

declare const route: any;

interface CostItem {
    id: number;
    name: string;
    category: string;
    supplier_name: string | null;
    supplier_url: string | null;
    purchase_price: number;
    purchase_qty: number;
    unit_cost: number;
    notes: string | null;
}

const props = defineProps<{
    items: CostItem[];
    stats: { total_items: number; total_spend: number; categories_used: number };
    categories: Record<string, string>;
    filters: { search?: string; category?: string };
}>();

const { fmtCurrency } = useAdmin();
const page  = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const search   = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? '');

const applyFilters = () => {
    router.get(route('admin.finance.index'), {
        search:   search.value || undefined,
        category: category.value || undefined,
    }, { preserveState: true, replace: true });
};

// ── Modal ──────────────────────────────────────────────────────────────────
type ModalMode = 'create' | 'edit' | null;
const modalMode   = ref<ModalMode>(null);
const editingItem = ref<CostItem | null>(null);

const form = useForm({
    name:           '',
    category:       'other',
    supplier_name:  '',
    supplier_url:   '',
    purchase_price: '' as string | number,
    purchase_qty:   1 as number,
    notes:          '',
});

const openCreate = () => {
    form.reset();
    form.category     = 'other';
    form.purchase_qty = 1;
    editingItem.value = null;
    modalMode.value   = 'create';
};

const openEdit = (item: CostItem) => {
    form.name           = item.name;
    form.category       = item.category;
    form.supplier_name  = item.supplier_name ?? '';
    form.supplier_url   = item.supplier_url ?? '';
    form.purchase_price = item.purchase_price;
    form.purchase_qty   = item.purchase_qty;
    form.notes          = item.notes ?? '';
    editingItem.value   = item;
    modalMode.value     = 'edit';
};

const closeModal = () => {
    modalMode.value   = null;
    editingItem.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        form.post(route('admin.finance.cost-items.store'), { onSuccess: closeModal });
    } else if (editingItem.value) {
        form.put(route('admin.finance.cost-items.update', editingItem.value.id), { onSuccess: closeModal });
    }
};

const deleteItem = (item: CostItem) => {
    if (!confirm(`Delete "${item.name}"? This will also remove it from any product cost breakdowns.`)) return;
    router.delete(route('admin.finance.cost-items.destroy', item.id));
};

// ── Helpers ────────────────────────────────────────────────────────────────
const categoryBadge: Record<string, string> = {
    packaging: 'adm-badge--lav',
    fragrance: 'adm-badge--on',
    material:  'adm-badge--warn',
    labour:    'adm-badge--red',
    overhead:  'adm-badge--off',
    other:     'adm-badge--off',
};

const unitCostPreview = computed(() => {
    const price = parseFloat(String(form.purchase_price));
    const qty   = parseInt(String(form.purchase_qty));
    if (!price || !qty || qty < 1) return null;
    return (price / qty).toFixed(4);
});
</script>

<template>
    <AdminLayout>
        <Head title="Cost Items — Finance" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <span>Finance</span>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>Cost Items</span>
                </div>
                <h1 class="adm-title">Cost Items</h1>
                <p class="adm-sub">Track the cost of every supply used in your products.</p>
            </div>
            <div style="display:flex;gap:.6rem;align-items:center">
                <Link :href="route('admin.finance.products')" class="adm-btn adm-btn--ghost adm-btn--sm">
                    Product Costs
                </Link>
                <button @click="openCreate" class="adm-btn adm-btn--primary adm-btn--sm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Item
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

        <!-- Stats -->
        <div class="adm-stats">
            <div class="adm-stat adm-stat--sage">
                <p class="adm-stat-val">{{ stats.total_items }}</p>
                <p class="adm-stat-label">Cost Items</p>
            </div>
            <div class="adm-stat adm-stat--lav">
                <p class="adm-stat-val">{{ fmtCurrency(stats.total_spend) }}</p>
                <p class="adm-stat-label">Total Spend Tracked</p>
            </div>
            <div class="adm-stat adm-stat--peach">
                <p class="adm-stat-val">{{ stats.categories_used }}</p>
                <p class="adm-stat-label">Categories Used</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="fi-filters">
            <input v-model="search" type="text" placeholder="Search name or supplier…"
                class="adm-input adm-input--sm fi-search"
                @keydown.enter="applyFilters" />
            <select v-model="category" @change="applyFilters" class="adm-select adm-select--sm">
                <option value="">All categories</option>
                <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
            </select>
            <button @click="applyFilters" class="adm-btn adm-btn--ghost adm-btn--sm">Filter</button>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush">

            <!-- Desktop table -->
            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead class="adm-thead">
                        <tr>
                            <th class="adm-th">Name</th>
                            <th class="adm-th">Category</th>
                            <th class="adm-th">Supplier</th>
                            <th class="adm-th adm-th--right">Batch Cost</th>
                            <th class="adm-th adm-th--right">Qty</th>
                            <th class="adm-th adm-th--right">Unit Cost</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id" class="adm-row">
                            <td class="adm-td">
                                <p class="fi-item-name">{{ item.name }}</p>
                                <p v-if="item.notes" class="fi-item-note">{{ item.notes }}</p>
                            </td>
                            <td class="adm-td">
                                <span class="adm-badge" :class="categoryBadge[item.category] ?? 'adm-badge--off'">
                                    {{ categories[item.category] ?? item.category }}
                                </span>
                            </td>
                            <td class="adm-td">
                                <a v-if="item.supplier_url" :href="item.supplier_url" target="_blank" rel="noopener" class="fi-supplier-link">
                                    {{ item.supplier_name || 'View supplier →' }}
                                </a>
                                <span v-else-if="item.supplier_name" style="font-size:.875rem">{{ item.supplier_name }}</span>
                                <span v-else style="color:var(--bb-muted)">—</span>
                            </td>
                            <td class="adm-td adm-td--right adm-td--price">{{ fmtCurrency(item.purchase_price) }}</td>
                            <td class="adm-td adm-td--right" style="color:var(--bb-muted);font-size:.875rem">{{ item.purchase_qty }}</td>
                            <td class="adm-td adm-td--right fi-unit-cost">{{ fmtCurrency(item.unit_cost) }}</td>
                            <td class="adm-td adm-td--actions">
                                <button @click="openEdit(item)" class="adm-action adm-action--edit">Edit</button>
                                <button @click="deleteItem(item)" class="adm-action adm-action--del">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="adm-mob-list">
                <div v-for="item in items" :key="item.id" class="adm-mob-card">
                    <div class="fi-mob-head">
                        <div style="flex:1;min-width:0">
                            <p class="fi-item-name">{{ item.name }}</p>
                            <p v-if="item.notes" class="fi-item-note">{{ item.notes }}</p>
                        </div>
                        <span class="adm-badge" :class="categoryBadge[item.category] ?? 'adm-badge--off'">
                            {{ categories[item.category] ?? item.category }}
                        </span>
                    </div>
                    <div class="fi-mob-meta">
                        <div>
                            <p class="fi-mob-label">Batch Cost</p>
                            <p class="fi-mob-val">{{ fmtCurrency(item.purchase_price) }}</p>
                        </div>
                        <div>
                            <p class="fi-mob-label">Qty</p>
                            <p class="fi-mob-val">{{ item.purchase_qty }}</p>
                        </div>
                        <div>
                            <p class="fi-mob-label">Unit Cost</p>
                            <p class="fi-mob-val fi-unit-cost">{{ fmtCurrency(item.unit_cost) }}</p>
                        </div>
                    </div>
                    <a v-if="item.supplier_url" :href="item.supplier_url" target="_blank" rel="noopener" class="fi-supplier-link" style="font-size:.8rem">
                        {{ item.supplier_name || 'View supplier →' }}
                    </a>
                    <div class="fi-mob-foot">
                        <button @click="openEdit(item)" class="adm-btn adm-btn--ghost adm-btn--sm">Edit</button>
                        <button @click="deleteItem(item)" class="adm-btn adm-btn--danger adm-btn--sm">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="!items.length" class="adm-empty">
                <div class="adm-empty-icon">💰</div>
                <p class="adm-empty-title">No cost items yet</p>
                <p class="adm-empty-sub">Add your first supply to start tracking costs.</p>
                <button @click="openCreate" class="adm-btn adm-btn--primary">Add Item</button>
            </div>

        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="modalMode" class="fi-backdrop" @click.self="closeModal">
                <div class="fi-modal">

                    <div class="fi-modal-hd">
                        <h2 class="fi-modal-title">{{ modalMode === 'create' ? 'Add Cost Item' : 'Edit Cost Item' }}</h2>
                        <button @click="closeModal" class="fi-modal-close" aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="fi-modal-body">

                        <div class="adm-field-row">
                            <div class="adm-field" style="flex:2">
                                <label class="adm-label">Name <span class="fi-req">*</span></label>
                                <input v-model="form.name" type="text" class="adm-input"
                                    :class="{ 'adm-input--err': form.errors.name }"
                                    placeholder="e.g. Reed Diffuser Bottles 100ml" />
                                <p v-if="form.errors.name" class="adm-err">{{ form.errors.name }}</p>
                            </div>
                            <div class="adm-field">
                                <label class="adm-label">Category <span class="fi-req">*</span></label>
                                <select v-model="form.category" class="adm-select"
                                    :class="{ 'adm-select--err': form.errors.category }">
                                    <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <p v-if="form.errors.category" class="adm-err">{{ form.errors.category }}</p>
                            </div>
                        </div>

                        <div class="adm-field-row">
                            <div class="adm-field">
                                <label class="adm-label">Supplier Name</label>
                                <input v-model="form.supplier_name" type="text" class="adm-input"
                                    placeholder="e.g. Amazon, Etsy…" />
                            </div>
                            <div class="adm-field">
                                <label class="adm-label">Supplier URL</label>
                                <input v-model="form.supplier_url" type="url" class="adm-input"
                                    :class="{ 'adm-input--err': form.errors.supplier_url }"
                                    placeholder="https://…" />
                                <p v-if="form.errors.supplier_url" class="adm-err">{{ form.errors.supplier_url }}</p>
                            </div>
                        </div>

                        <div class="fi-three-col">
                            <div class="adm-field">
                                <label class="adm-label">Purchase Price <span class="fi-req">*</span></label>
                                <div class="adm-prefix-wrap">
                                    <span class="adm-prefix">£</span>
                                    <input v-model="form.purchase_price" type="number" step="0.01" min="0"
                                        class="adm-input adm-input--prefixed"
                                        :class="{ 'adm-input--err': form.errors.purchase_price }"
                                        placeholder="0.00" />
                                </div>
                                <p v-if="form.errors.purchase_price" class="adm-err">{{ form.errors.purchase_price }}</p>
                            </div>
                            <div class="adm-field">
                                <label class="adm-label">Quantity Received <span class="fi-req">*</span></label>
                                <input v-model.number="form.purchase_qty" type="number" min="1"
                                    class="adm-input"
                                    :class="{ 'adm-input--err': form.errors.purchase_qty }"
                                    placeholder="e.g. 50" />
                                <p v-if="form.errors.purchase_qty" class="adm-err">{{ form.errors.purchase_qty }}</p>
                            </div>
                            <div class="adm-field">
                                <label class="adm-label">Unit Cost</label>
                                <div class="fi-unit-preview">
                                    {{ unitCostPreview ? `£${unitCostPreview}` : '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="adm-field">
                            <label class="adm-label">Notes <span class="adm-label-note">(optional)</span></label>
                            <textarea v-model="form.notes" rows="2" class="adm-textarea"
                                placeholder="Any extra details…"></textarea>
                        </div>

                        <div class="fi-modal-ft">
                            <button type="button" @click="closeModal" class="adm-btn adm-btn--ghost">Cancel</button>
                            <button type="submit" class="adm-btn adm-btn--primary" :disabled="form.processing">
                                {{ modalMode === 'create' ? 'Add Item' : 'Save Changes' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
/* ── Filters ── */
.fi-filters {
    display: flex;
    gap: .6rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.fi-search { flex: 1; min-width: 180px; }

/* ── Table cells ── */
.fi-item-name { font-size: .875rem; font-weight: 600; color: var(--bb-text); }
.fi-item-note { font-size: .72rem; color: var(--bb-muted); margin-top: .1rem; }
.fi-unit-cost { font-weight: 700; color: var(--bb-sage-d); }
.fi-supplier-link { font-size: .875rem; color: var(--bb-lav-d); text-decoration: underline; }

/* ── Mobile cards ── */
.fi-mob-head { display: flex; align-items: flex-start; gap: .75rem; }
.fi-mob-meta { display: flex; gap: 2rem; padding-top: .5rem; border-top: 1px solid var(--bb-border); }
.fi-mob-label { font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--bb-muted); }
.fi-mob-val   { font-size: .875rem; font-weight: 600; color: var(--bb-text); margin-top: .1rem; }
.fi-mob-foot  { display: flex; gap: .5rem; }

/* ── Modal ── */
.fi-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(26,26,46,.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}
.fi-modal {
    background: var(--bb-surface);
    border-radius: var(--bb-radius-xl);
    width: 100%;
    max-width: 660px;
    box-shadow: 0 8px 40px rgba(26,26,46,.18);
    max-height: 90vh;
    overflow-y: auto;
}
.fi-modal-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem 0;
}
.fi-modal-title { font-size: 1.05rem; font-weight: 700; margin: 0; }
.fi-modal-close {
    background: none; border: none; cursor: pointer;
    color: var(--bb-muted); padding: .25rem; border-radius: var(--bb-radius-sm);
    display: flex; transition: background .12s;
}
.fi-modal-close:hover { background: var(--bb-cream); color: var(--bb-text); }

.fi-modal-body {
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.fi-modal-ft {
    display: flex;
    justify-content: flex-end;
    gap: .75rem;
    padding-top: .75rem;
    border-top: 1px solid var(--bb-border);
    margin-top: .25rem;
}

/* Three-col row for price/qty/unit */
.fi-three-col {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 520px) { .fi-three-col { grid-template-columns: 1fr; } }

/* Unit cost read-only preview */
.fi-unit-preview {
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

.fi-req { color: var(--bb-red); }
</style>
