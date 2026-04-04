<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

interface OilHazard {
    id: number;
    hazard_code: string;
    hazard_class: string | null;
    category: string | null;
    signal_word: string | null;
    pictogram: string | null;
}
interface OilComponent {
    id: number;
    name: string;
    cas: string | null;
    concentration_min: number | null;
    concentration_max: number | null;
    clp_classification: string | null;
}
interface SdsDocument {
    id: number;
    version: string | null;
    issue_date: string | null;
    parsed: boolean;
    file_path: string;
}
interface Oil {
    id: number;
    name: string;
    supplier: string | null;
    cas_primary: string | null;
    sds_documents: SdsDocument[] | undefined;
    hazards: OilHazard[];
    components: OilComponent[];
}

function sdsDocs(oil: Oil): SdsDocument[] { return oil.sds_documents ?? []; }

const props = defineProps<{ oils: Oil[] }>();

const searchQuery = ref('');
const openOilId = ref<number | null>(null);
const activeTab = ref<Record<number, 'hazards' | 'components' | 'sds'>>({});
const showAddForm = ref(false);
const editingHazard = ref<number | null>(null);
const newOil = ref({ name: '', supplier: '', cas_primary: '' });
const hazardEdits = ref<Record<number, OilHazard>>({});
const showAddHazard = ref<number | null>(null);
const newHazard = ref({ hazard_code: '', hazard_class: '', category: '', signal_word: 'Warning', pictogram: 'exclamation' });
const uploadingFor = ref<number | null>(null);
const fileInputRefs = ref<Record<number, HTMLInputElement | null>>({});

const filteredOils = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.oils;
    return props.oils.filter(o => o.name.toLowerCase().includes(q) || (o.supplier ?? '').toLowerCase().includes(q));
});

function initials(name: string) { return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase(); }
function tabFor(id: number): 'hazards' | 'components' | 'sds' { return activeTab.value[id] ?? 'hazards'; }
function toggleOil(id: number) { openOilId.value = openOilId.value === id ? null : id; }
function setTab(id: number, tab: 'hazards' | 'components' | 'sds') { activeTab.value[id] = tab; }
function startEditHazard(h: OilHazard) { editingHazard.value = h.id; hazardEdits.value[h.id] = { ...h }; }
function cancelEditHazard() { editingHazard.value = null; }

async function deleteHazard(oilId: number, hazardId: number) {
    if (!confirm('Remove this hazard?')) return;
    await axios.delete(route('admin.oils.hazards.destroy', { oil: oilId, hazard: hazardId }));
    router.reload({ only: ['oils'] });
}
async function deleteComponent(oilId: number, componentId: number) {
    if (!confirm('Remove this component?')) return;
    await axios.delete(route('admin.oils.components.destroy', { oil: oilId, component: componentId }));
    router.reload({ only: ['oils'] });
}
async function submitAddOil() {
    if (!newOil.value.name.trim()) return;
    await axios.post(route('admin.oils.store'), newOil.value);
    newOil.value = { name: '', supplier: '', cas_primary: '' };
    showAddForm.value = false;
    router.reload({ only: ['oils'] });
}
async function submitUpdateHazard(oilId: number, hazardId: number) {
    await axios.put(route('admin.oils.hazards.update', { oil: oilId, hazard: hazardId }), hazardEdits.value[hazardId]);
    editingHazard.value = null;
    router.reload({ only: ['oils'] });
}
async function submitAddHazard(oilId: number) {
    await axios.post(route('admin.oils.hazards.store', { oil: oilId }), newHazard.value);
    showAddHazard.value = null;
    newHazard.value = { hazard_code: '', hazard_class: '', category: '', signal_word: 'Warning', pictogram: 'exclamation' };
    router.reload({ only: ['oils'] });
}
function triggerUpload(oilId: number) { fileInputRefs.value[oilId]?.click(); }
async function handleFileUpload(oilId: number, event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    uploadingFor.value = oilId;
    const form = new FormData();
    form.append('sds', file);
    try {
        await axios.post(route('admin.oils.sds.upload', { oil: oilId }), form, { headers: { 'Content-Type': 'multipart/form-data' } });
        router.reload({ only: ['oils'] });
    } finally { uploadingFor.value = null; }
}
</script>

<template>
    <AdminLayout>

        <Head title="Oils — Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Oils</h1>
                <p class="adm-sub">Manage fragrance &amp; essential oils and their SDS data</p>
            </div>
            <button class="adm-btn adm-btn--primary" @click="showAddForm = !showAddForm">
                <svg v-if="!showAddForm" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                {{ showAddForm ? 'Cancel' : 'Add Oil' }}
            </button>
        </div>

        <!-- Add oil form -->
        <div v-if="showAddForm" class="adm-card adm-card--sm oi-add-card">
            <h2 class="adm-card-title">New Oil</h2>
            <div class="oi-add-grid">
                <div class="adm-field">
                    <label class="adm-label">Name</label>
                    <input v-model="newOil.name" type="text" class="adm-input"
                        placeholder="e.g. Lavender Essential Oil" />
                </div>
                <div class="adm-field">
                    <label class="adm-label">Supplier</label>
                    <input v-model="newOil.supplier" type="text" class="adm-input" placeholder="e.g. Nikura" />
                </div>
                <div class="adm-field">
                    <label class="adm-label">Primary CAS</label>
                    <input v-model="newOil.cas_primary" type="text" class="adm-input" placeholder="e.g. 8000-28-0" />
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; margin-top:0.75rem">
                <button class="adm-btn adm-btn--primary" @click="submitAddOil">Save Oil</button>
            </div>
        </div>

        <!-- Search -->
        <div class="oi-search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </svg>
            <input v-model="searchQuery" type="text" class="oi-search-input"
                placeholder="Search by name or supplier…" />
            <button v-if="searchQuery" @click="searchQuery = ''" class="oi-search-clear" aria-label="Clear">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Oil list -->
        <div class="oi-list">
            <div v-if="!filteredOils.length" class="adm-empty">
                <div class="adm-empty-icon">🧴</div>
                <p class="adm-empty-title">No oils found</p>
                <p class="adm-empty-sub">Try a different search or add a new oil above.</p>
            </div>

            <div v-for="oil in filteredOils" :key="oil.id" class="oi-row">

                <!-- Row header -->
                <div class="oi-row-head" @click="toggleOil(oil.id)">
                    <div class="oi-avatar">{{ initials(oil.name) }}</div>
                    <div class="oi-row-info">
                        <p class="oi-row-name">{{ oil.name }}</p>
                        <p class="oi-row-meta">
                            {{ oil.supplier || '—' }}
                            <span class="oi-meta-sep">·</span>
                            CAS {{ oil.cas_primary || '—' }}
                        </p>
                    </div>
                    <div class="oi-row-badges">
                        <span class="adm-badge"
                            :class="sdsDocs(oil).some(d => d.parsed) ? 'adm-badge--on' : 'adm-badge--warn'">
                            {{sdsDocs(oil).some(d => d.parsed) ? 'SDS parsed' : 'No SDS'}}
                        </span>
                        <span class="adm-badge adm-badge--lav">
                            {{ oil.hazards.length }} hazard{{ oil.hazards.length !== 1 ? 's' : '' }}
                        </span>
                        <svg class="oi-chevron" :class="{ 'oi-chevron--open': openOilId === oil.id }" width="14"
                            height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                </div>

                <!-- Expanded detail -->
                <div v-if="openOilId === oil.id" class="oi-detail">

                    <!-- Tabs -->
                    <div class="oi-tabs">
                        <button class="oi-tab" :class="{ 'oi-tab--active': tabFor(oil.id) === 'hazards' }"
                            @click="setTab(oil.id, 'hazards')">Section 2 — Hazards</button>
                        <button class="oi-tab" :class="{ 'oi-tab--active': tabFor(oil.id) === 'components' }"
                            @click="setTab(oil.id, 'components')">Section 3 — Components</button>
                        <button class="oi-tab" :class="{ 'oi-tab--active': tabFor(oil.id) === 'sds' }"
                            @click="setTab(oil.id, 'sds')">SDS Documents</button>
                    </div>

                    <!-- ── Hazards ── -->
                    <div v-if="tabFor(oil.id) === 'hazards'" class="oi-tab-body">
                        <p v-if="!oil.hazards.length" class="oi-empty">
                            No hazards parsed yet. Upload an SDS to auto-populate, or add manually.
                        </p>

                        <div v-else class="adm-card adm-card--flush" style="margin-bottom:0.85rem">
                            <table class="adm-table">
                                <thead>
                                    <tr class="adm-thead">
                                        <th class="adm-th">Code</th>
                                        <th class="adm-th">Class</th>
                                        <th class="adm-th">Category</th>
                                        <th class="adm-th">Signal Word</th>
                                        <th class="adm-th">Pictogram</th>
                                        <th class="adm-th adm-th--right"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="hazard in oil.hazards" :key="hazard.id">
                                        <!-- Display row -->
                                        <tr v-if="editingHazard !== hazard.id" class="adm-row">
                                            <td class="adm-td"><span class="oi-hcode">{{ hazard.hazard_code }}</span>
                                            </td>
                                            <td class="adm-td" style="color:var(--bb-muted)">{{ hazard.hazard_class ||
                                                '—' }}</td>
                                            <td class="adm-td" style="color:var(--bb-muted)">{{ hazard.category || '—'
                                            }}</td>
                                            <td class="adm-td">
                                                <span class="oi-signal"
                                                    :class="{ 'oi-signal--danger': hazard.signal_word === 'Danger', 'oi-signal--warning': hazard.signal_word === 'Warning' }">
                                                    {{ hazard.signal_word || '—' }}
                                                </span>
                                            </td>
                                            <td class="adm-td adm-td--mono">{{ hazard.pictogram || '—' }}</td>
                                            <td class="adm-td adm-td--actions">
                                                <button class="adm-action adm-action--edit"
                                                    @click="startEditHazard(hazard)">Edit</button>
                                                <button class="adm-action adm-action--del"
                                                    @click="deleteHazard(oil.id, hazard.id)">Delete</button>
                                            </td>
                                        </tr>

                                        <!-- Edit row -->
                                        <tr v-else class="adm-row">
                                            <td colspan="6" class="adm-td">
                                                <div class="oi-edit-panel">
                                                    <div class="oi-edit-grid">
                                                        <div class="adm-field">
                                                            <label class="adm-label--sm adm-label">Code</label>
                                                            <input v-model="hazardEdits[hazard.id].hazard_code"
                                                                type="text" class="adm-input adm-input--sm" />
                                                        </div>
                                                        <div class="adm-field">
                                                            <label class="adm-label--sm adm-label">Class</label>
                                                            <input v-model="hazardEdits[hazard.id].hazard_class"
                                                                type="text" class="adm-input adm-input--sm" />
                                                        </div>
                                                        <div class="adm-field">
                                                            <label class="adm-label--sm adm-label">Category</label>
                                                            <input v-model="hazardEdits[hazard.id].category" type="text"
                                                                class="adm-input adm-input--sm" />
                                                        </div>
                                                        <div class="adm-field">
                                                            <label class="adm-label--sm adm-label">Signal Word</label>
                                                            <select v-model="hazardEdits[hazard.id].signal_word"
                                                                class="adm-select adm-input--sm">
                                                                <option>Warning</option>
                                                                <option>Danger</option>
                                                            </select>
                                                        </div>
                                                        <div class="adm-field">
                                                            <label class="adm-label--sm adm-label">Pictogram</label>
                                                            <input v-model="hazardEdits[hazard.id].pictogram"
                                                                type="text" class="adm-input adm-input--sm" />
                                                        </div>
                                                        <div class="oi-edit-actions">
                                                            <button class="adm-btn adm-btn--primary adm-btn--sm"
                                                                @click="submitUpdateHazard(oil.id, hazard.id)">Save</button>
                                                            <button class="adm-btn adm-btn--ghost adm-btn--sm"
                                                                @click="cancelEditHazard">Cancel</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Add hazard toggle -->
                        <div class="oi-add-hazard">
                            <button v-if="showAddHazard !== oil.id" class="adm-btn adm-btn--ghost adm-btn--sm"
                                @click="showAddHazard = oil.id">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                Add Hazard
                            </button>
                            <div v-else class="oi-edit-panel">
                                <div class="oi-edit-grid">
                                    <div class="adm-field">
                                        <label class="adm-label--sm adm-label">Code</label>
                                        <input v-model="newHazard.hazard_code" type="text"
                                            class="adm-input adm-input--sm" placeholder="H317" />
                                    </div>
                                    <div class="adm-field">
                                        <label class="adm-label--sm adm-label">Class</label>
                                        <input v-model="newHazard.hazard_class" type="text"
                                            class="adm-input adm-input--sm" placeholder="Skin Sensitisation" />
                                    </div>
                                    <div class="adm-field">
                                        <label class="adm-label--sm adm-label">Category</label>
                                        <input v-model="newHazard.category" type="text" class="adm-input adm-input--sm"
                                            placeholder="1B" />
                                    </div>
                                    <div class="adm-field">
                                        <label class="adm-label--sm adm-label">Signal Word</label>
                                        <select v-model="newHazard.signal_word" class="adm-select adm-input--sm">
                                            <option>Warning</option>
                                            <option>Danger</option>
                                        </select>
                                    </div>
                                    <div class="adm-field">
                                        <label class="adm-label--sm adm-label">Pictogram</label>
                                        <input v-model="newHazard.pictogram" type="text" class="adm-input adm-input--sm"
                                            placeholder="exclamation" />
                                    </div>
                                    <div class="oi-edit-actions">
                                        <button class="adm-btn adm-btn--primary adm-btn--sm"
                                            @click="submitAddHazard(oil.id)">Add</button>
                                        <button class="adm-btn adm-btn--ghost adm-btn--sm"
                                            @click="showAddHazard = null">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Components ── -->
                    <div v-if="tabFor(oil.id) === 'components'" class="oi-tab-body">
                        <p v-if="!oil.components.length" class="oi-empty">
                            No components parsed yet. Upload an SDS to auto-populate.
                        </p>
                        <div v-else class="adm-card adm-card--flush">
                            <table class="adm-table">
                                <thead>
                                    <tr class="adm-thead">
                                        <th class="adm-th">Name</th>
                                        <th class="adm-th">CAS</th>
                                        <th class="adm-th">Concentration (%)</th>
                                        <th class="adm-th">CLP Classification</th>
                                        <th class="adm-th adm-th--right"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="comp in oil.components" :key="comp.id" class="adm-row">
                                        <td class="adm-td" style="font-weight:500">{{ comp.name }}</td>
                                        <td class="adm-td adm-td--mono">{{ comp.cas || '—' }}</td>
                                        <td class="adm-td" style="color:var(--bb-muted)">
                                            <span
                                                v-if="comp.concentration_min !== null && comp.concentration_max !== null">
                                                {{ comp.concentration_min }}–{{ comp.concentration_max }}%
                                            </span>
                                            <span v-else>—</span>
                                        </td>
                                        <td class="adm-td">
                                            <template v-if="comp.clp_classification">
                                                <span v-for="code in comp.clp_classification.split(',')" :key="code"
                                                    class="oi-hcode" style="margin-right:0.25rem">{{ code.trim()
                                                    }}</span>
                                            </template>
                                            <span v-else style="color:var(--bb-muted)">—</span>
                                        </td>
                                        <td class="adm-td adm-td--actions">
                                            <button class="adm-action adm-action--del"
                                                @click="deleteComponent(oil.id, comp.id)">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ── SDS ── -->
                    <div v-if="tabFor(oil.id) === 'sds'" class="oi-tab-body">
                        <p v-if="!sdsDocs(oil).length" class="oi-empty">No SDS documents uploaded yet.</p>
                        <div class="oi-sds-list">
                            <div v-for="doc in sdsDocs(oil)" :key="doc.id" class="oi-sds-item">
                                <div class="oi-sds-icon">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                    </svg>
                                </div>
                                <div class="oi-sds-info">
                                    <p class="oi-sds-version">{{ doc.version ? `v${doc.version}` : 'Unknown version'
                                    }}<span v-if="doc.issue_date"> — {{ doc.issue_date }}</span></p>
                                    <p class="oi-sds-status">{{ doc.parsed ? 'Auto-parsed ✓' : 'Not yet parsed' }}</p>
                                </div>
                                <span class="adm-badge" :class="doc.parsed ? 'adm-badge--on' : 'adm-badge--warn'">
                                    {{ doc.parsed ? 'Parsed' : 'Pending' }}
                                </span>
                            </div>
                        </div>

                        <input :ref="el => fileInputRefs[oil.id] = el as HTMLInputElement" type="file"
                            accept="application/pdf" style="display:none" @change="handleFileUpload(oil.id, $event)" />

                        <button class="adm-upload-zone" :disabled="uploadingFor === oil.id"
                            @click="triggerUpload(oil.id)">
                            <svg v-if="uploadingFor !== oil.id" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                            <svg v-else class="adm-spinner" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="rgba(26,26,46,0.15)" stroke-width="3" />
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="#9b84d4" stroke-width="3"
                                    stroke-linecap="round" />
                            </svg>
                            <span>{{ uploadingFor === oil.id ? 'Uploading…' : 'Click to upload SDS PDF' }}</span>
                            <span class="adm-upload-note">Max 10 MB · PDF only</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── Page-specific styles only — shared styles come from admin-design-system.css ── */

/* Add oil form grid */
.oi-add-card {
    margin-bottom: 1.25rem;
}

.oi-add-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .oi-add-grid {
        grid-template-columns: 1fr;
    }
}

/* Search bar */
.oi-search-wrap {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    max-width: 340px;
    padding: 0.6rem 0.85rem;
    border-radius: var(--bb-radius);
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    margin-bottom: 1.25rem;
    color: var(--bb-muted);
}

.oi-search-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    font-family: var(--bb-font);
    font-size: 0.88rem;
    color: var(--bb-text);
}

.oi-search-input::placeholder {
    color: var(--bb-muted);
}

.oi-search-clear {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: none;
    background: none;
    color: var(--bb-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.12s;
}

.oi-search-clear:hover {
    background: var(--bb-border);
}

/* Oil list */
.oi-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

/* Oil row card */
.oi-row {
    background: var(--bb-surface);
    border-radius: var(--bb-radius-lg);
    border: 1px solid var(--bb-border);
    box-shadow: var(--bb-shadow-sm);
    overflow: hidden;
    transition: box-shadow 0.15s;
}

.oi-row:hover {
    box-shadow: 0 2px 10px rgba(26, 26, 46, 0.08);
}

.oi-row-head {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem 1.1rem;
    cursor: pointer;
    user-select: none;
    transition: background 0.12s;
}

.oi-row-head:hover {
    background: var(--bb-cream);
}

.oi-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #f0edf8;
    color: var(--bb-lav-d);
    font-size: 0.72rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    letter-spacing: 0.03em;
}

.oi-row-info {
    flex: 1;
    min-width: 0;
}

.oi-row-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--bb-text);
}

.oi-row-meta {
    font-size: 0.75rem;
    color: var(--bb-muted);
    margin-top: 0.1rem;
}

.oi-meta-sep {
    margin: 0 0.25rem;
    opacity: 0.5;
}

.oi-row-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
    flex-wrap: wrap;
}

.oi-chevron {
    color: var(--bb-muted);
    transition: transform 0.2s;
    flex-shrink: 0;
}

.oi-chevron--open {
    transform: rotate(180deg);
}

/* Detail area */
.oi-detail {
    border-top: 1px solid var(--bb-border);
    background: var(--bb-cream);
}

/* Tabs */
.oi-tabs {
    display: flex;
    padding: 0 1rem;
    border-bottom: 1px solid var(--bb-border);
    background: var(--bb-surface);
}

.oi-tab {
    font-family: var(--bb-font);
    font-size: 0.82rem;
    font-weight: 500;
    padding: 0.7rem 0.85rem;
    border: none;
    border-bottom: 2px solid transparent;
    background: transparent;
    color: var(--bb-muted);
    cursor: pointer;
    margin-bottom: -1px;
    transition: color 0.15s, border-color 0.15s;
    white-space: nowrap;
}

.oi-tab:hover {
    color: var(--bb-text);
}

.oi-tab--active {
    color: var(--bb-navy);
    border-bottom-color: var(--bb-lav-d);
    font-weight: 600;
}

/* Tab body */
.oi-tab-body {
    padding: 1rem 1.1rem;
}

.oi-empty {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-style: italic;
}

/* Hazard code chip — reused in both tabs */
.oi-hcode {
    display: inline-block;
    font-family: var(--bb-mono);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 4px;
    background: var(--bb-red-bg);
    color: var(--bb-red);
}

/* Signal word colours */
.oi-signal {
    font-weight: 500;
}

.oi-signal--danger {
    color: var(--bb-red);
}

.oi-signal--warning {
    color: var(--bb-peach-d);
}

/* Inline edit panel */
.oi-edit-panel {
    background: var(--bb-cream);
    border: 1px solid var(--bb-border);
    border-radius: var(--bb-radius);
    padding: 1rem;
}

.oi-edit-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr) repeat(2, 1fr) auto;
    gap: 0.75rem;
    align-items: end;
}

@media (max-width: 900px) {
    .oi-edit-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .oi-edit-grid {
        grid-template-columns: 1fr;
    }
}

.oi-edit-actions {
    display: flex;
    align-items: flex-end;
    gap: 0.4rem;
}

/* Add hazard trigger area */
.oi-add-hazard {
    margin-top: 0.75rem;
}

/* SDS list */
.oi-sds-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.oi-sds-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    border-radius: var(--bb-radius);
    background: var(--bb-surface);
    border: 1px solid var(--bb-border);
}

.oi-sds-icon {
    width: 30px;
    height: 30px;
    border-radius: var(--bb-radius-sm);
    background: #f0edf8;
    color: var(--bb-lav-d);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.oi-sds-info {
    flex: 1;
}

.oi-sds-version {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--bb-text);
}

.oi-sds-status {
    font-size: 0.72rem;
    color: var(--bb-muted);
    margin-top: 0.1rem;
}
</style>
