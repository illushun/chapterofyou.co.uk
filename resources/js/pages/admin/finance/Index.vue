<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
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

const { fmtCurrency, confirmDelete } = useAdmin();
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

// ── Modal state ────────────────────────────────────────────────────────────
type ModalMode = 'create' | 'edit' | null;
const modalMode    = ref<ModalMode>(null);
const editingItem  = ref<CostItem | null>(null);

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
    form.category = 'other';
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
        form.post(route('admin.finance.cost-items.store'), {
            onSuccess: closeModal,
        });
    } else if (editingItem.value) {
        form.put(route('admin.finance.cost-items.update', editingItem.value.id), {
            onSuccess: closeModal,
        });
    }
};

const deleteItem = (item: CostItem) => {
    confirmDelete(`Delete "${item.name}"?`, () => {
        router.delete(route('admin.finance.cost-items.destroy', item.id));
    });
};

// ── Helpers ────────────────────────────────────────────────────────────────
const categoryColour: Record<string, string> = {
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
            <div style="display:flex;gap:.75rem;align-items:center">
                <Link :href="route('admin.finance.products')" class="adm-btn adm-btn--ghost adm-btn--sm">
                    Product Costs
                </Link>
                <button @click="openCreate" class="adm-btn adm-btn--primary adm-btn--sm">+ Add Item</button>
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
        <div class="adm-stats-row">
            <div class="adm-stat">
                <p class="adm-stat-label">Total Items</p>
                <p class="adm-stat-value">{{ stats.total_items }}</p>
            </div>
            <div class="adm-stat">
                <p class="adm-stat-label">Total Spend Tracked</p>
                <p class="adm-stat-value">{{ fmtCurrency(stats.total_spend) }}</p>
            </div>
            <div class="adm-stat">
                <p class="adm-stat-label">Categories</p>
                <p class="adm-stat-value">{{ stats.categories_used }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="fi-filters">
            <input v-model="search" type="text" placeholder="Search name or supplier…"
                class="adm-input adm-input--sm" style="flex:1;min-width:180px"
                @keydown.enter="applyFilters" />
            <select v-model="category" @change="applyFilters" class="adm-select adm-select--sm">
                <option value="">All categories</option>
                <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
            </select>
            <button @click="applyFilters" class="adm-btn adm-btn--ghost adm-btn--sm">Filter</button>
        </div>

        <!-- Table -->
        <div class="adm-card" style="padding:0">
            <table class="adm-table" v-if="items.length">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th class="adm-table-num">Batch Cost</th>
                        <th class="adm-table-num">Qty</th>
                        <th class="adm-table-num">Unit Cost</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id">
                        <td>
                            <div class="fi-item-name">{{ item.name }}</div>
                            <div v-if="item.notes" class="fi-item-note">{{ item.notes }}</div>
                        </td>
                        <td>
                            <span class="adm-badge" :class="categoryColour[item.category] ?? 'adm-badge--off'">
                                {{ categories[item.category] ?? item.category }}
                            </span>
                        </td>
                        <td>
                            <template v-if="item.supplier_url">
                                <a :href="item.supplier_url" target="_blank" rel="noopener" class="fi-supplier-link">
                                    {{ item.supplier_name || 'View →' }}
                                </a>
                            </template>
                            <template v-else-if="item.supplier_name">
                                {{ item.supplier_name }}
                            </template>
                            <span v-else class="adm-muted">—</span>
                        </td>
                        <td class="adm-table-num">{{ fmtCurrency(item.purchase_price) }}</td>
                        <td class="adm-table-num">{{ item.purchase_qty }}</td>
                        <td class="adm-table-num fi-unit-cost">{{ fmtCurrency(item.unit_cost) }}</td>
                        <td class="adm-table-actions">
                            <button @click="openEdit(item)" class="adm-btn adm-btn--ghost adm-btn--xs">Edit</button>
                            <button @click="deleteItem(item)" class="adm-btn adm-btn--danger adm-btn--xs">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="adm-empty">
                <p>No cost items yet.</p>
                <button @click="openCreate" class="adm-btn adm-btn--primary adm-btn--sm" style="margin-top:.75rem">
                    Add your first item
                </button>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="modalMode" class="fi-modal-backdrop" @click.self="closeModal">
                <div class="fi-modal">
                    <div class="fi-modal-header">
                        <h2 class="fi-modal-title">{{ modalMode === 'create' ? 'Add Cost Item' : 'Edit Cost Item' }}</h2>
                        <button @click="closeModal" class="fi-modal-close" aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="fi-modal-body">
                        <div class="fi-form-row">
                            <div class="fi-field" style="flex:2">
                                <label class="fi-label">Name <span class="fi-req">*</span></label>
                                <input v-model="form.name" type="text" class="adm-input"
                                    placeholder="e.g. Reed Diffuser Bottles 100ml" />
                                <p v-if="form.errors.name" class="fi-error">{{ form.errors.name }}</p>
                            </div>
                            <div class="fi-field" style="flex:1">
                                <label class="fi-label">Category <span class="fi-req">*</span></label>
                                <select v-model="form.category" class="adm-select">
                                    <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <p v-if="form.errors.category" class="fi-error">{{ form.errors.category }}</p>
                            </div>
                        </div>

                        <div class="fi-form-row">
                            <div class="fi-field" style="flex:1">
                                <label class="fi-label">Supplier Name</label>
                                <input v-model="form.supplier_name" type="text" class="adm-input"
                                    placeholder="e.g. Amazon, eBay…" />
                            </div>
                            <div class="fi-field" style="flex:2">
                                <label class="fi-label">Supplier URL</label>
                                <input v-model="form.supplier_url" type="url" class="adm-input"
                                    placeholder="https://…" />
                                <p v-if="form.errors.supplier_url" class="fi-error">{{ form.errors.supplier_url }}</p>
                            </div>
                        </div>

                        <div class="fi-form-row">
                            <div class="fi-field" style="flex:1">
                                <label class="fi-label">Purchase Price (£) <span class="fi-req">*</span></label>
                                <div class="adm-input-prefix">
                                    <span>£</span>
                                    <input v-model="form.purchase_price" type="number" step="0.01" min="0" class="adm-input"
                                        placeholder="0.00" />
                                </div>
                                <p v-if="form.errors.purchase_price" class="fi-error">{{ form.errors.purchase_price }}</p>
                            </div>
                            <div class="fi-field" style="flex:1">
                                <label class="fi-label">Quantity Received <span class="fi-req">*</span></label>
                                <input v-model.number="form.purchase_qty" type="number" min="1" class="adm-input"
                                    placeholder="e.g. 50" />
                                <p v-if="form.errors.purchase_qty" class="fi-error">{{ form.errors.purchase_qty }}</p>
                            </div>
                            <div class="fi-field" style="flex:1">
                                <label class="fi-label">Unit Cost</label>
                                <div class="fi-unit-preview">
                                    {{ unitCostPreview ? `£${unitCostPreview}` : '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="fi-field">
                            <label class="fi-label">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="adm-input"
                                placeholder="Any extra details…"></textarea>
                        </div>

                        <div class="fi-modal-footer">
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
.fi-filters {
    display: flex;
    gap: .6rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.fi-item-name { font-weight: 600; font-size: .875rem; }
.fi-item-note { font-size: .75rem; color: #888; margin-top: .15rem; }
.fi-unit-cost { font-weight: 700; color: #2e7d52; }
.fi-supplier-link { color: #6b5ce7; text-decoration: underline; font-size: .875rem; }

/* Modal */
.fi-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.fi-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 680px;
    box-shadow: 0 8px 40px rgba(0,0,0,.18);
    max-height: 90vh;
    overflow-y: auto;
}

.fi-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem 0;
}

.fi-modal-title { font-size: 1.1rem; font-weight: 700; margin: 0; }

.fi-modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: #888;
    padding: .25rem;
    border-radius: 6px;
    display: flex;
}
.fi-modal-close:hover { background: #f0f0f0; color: #333; }

.fi-modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }

.fi-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: .75rem;
    padding-top: .5rem;
    border-top: 1px solid #f0f0f0;
    margin-top: .5rem;
}

.fi-form-row { display: flex; gap: 1rem; }
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

.adm-input-prefix {
    display: flex;
    align-items: center;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}
.adm-input-prefix span {
    padding: 0 .6rem;
    color: #777;
    font-size: .875rem;
    border-right: 1px solid #e0e0e0;
    background: #f8f8f8;
    height: 100%;
    display: flex;
    align-items: center;
}
.adm-input-prefix .adm-input {
    border: none;
    border-radius: 0;
    flex: 1;
}

@media (max-width: 580px) {
    .fi-form-row { flex-direction: column; }
}
</style>
