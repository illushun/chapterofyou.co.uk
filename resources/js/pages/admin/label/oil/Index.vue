<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
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

function sdsDocs(oil: Oil): SdsDocument[] {
    return oil.sds_documents ?? [];
}

const props = defineProps<{ oils: Oil[] }>();

const searchQuery = ref('');
const openOilId = ref<number | null>(null);
const activeTab = ref<Record<number, 'hazards' | 'components' | 'sds'>>({});
const showAddForm = ref(false);
const editingHazard = ref<number | null>(null);

// Add oil form
const newOil = ref({ name: '', supplier: '', cas_primary: '' });

// Hazard edit form (keyed by hazard id)
const hazardEdits = ref<Record<number, OilHazard>>({});

// Add hazard form (keyed by oil id)
const showAddHazard = ref<number | null>(null);
const newHazard = ref({
    hazard_code: '',
    hazard_class: '',
    category: '',
    signal_word: 'Warning',
    pictogram: 'exclamation',
});

// Upload state per oil
const uploadingFor = ref<number | null>(null);
const fileInputRefs = ref<Record<number, HTMLInputElement | null>>({});

const filteredOils = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.oils;
    return props.oils.filter(
        o => o.name.toLowerCase().includes(q) || (o.supplier ?? '').toLowerCase().includes(q)
    );
});

function initials(name: string): string {
    return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
}

function tabFor(oilId: number): 'hazards' | 'components' | 'sds' {
    return activeTab.value[oilId] ?? 'hazards';
}

function toggleOil(id: number) {
    openOilId.value = openOilId.value === id ? null : id;
}

function setTab(oilId: number, tab: 'hazards' | 'components' | 'sds') {
    activeTab.value[oilId] = tab;
}

function signalClass(word: string | null) {
    if (word === 'Danger') return 'signal-danger';
    if (word === 'Warning') return 'signal-warning';
    return '';
}

function startEditHazard(hazard: OilHazard) {
    editingHazard.value = hazard.id;
    hazardEdits.value[hazard.id] = { ...hazard };
}

function cancelEditHazard() {
    editingHazard.value = null;
}

async function submitAddOil() {
    if (!newOil.value.name.trim()) return;

    await axios.post(`oils`, newOil.value);
    newOil.value = { name: '', supplier: '', cas_primary: '' };
    showAddForm.value = false;
    router.reload({ only: ['oils'] });
}

async function submitUpdateHazard(oilId: number, hazardId: number) {
    const data = hazardEdits.value[hazardId];

    await axios.put(`oils/${oilId}/hazards/${hazardId}`, data);
    editingHazard.value = null;
    router.reload({ only: ['oils'] });
}

async function submitAddHazard(oilId: number) {
    await axios.post(`oils/${oilId}/hazard`, newHazard.value);
    showAddHazard.value = null;
    newHazard.value = { hazard_code: '', hazard_class: '', category: '', signal_word: 'Warning', pictogram: 'exclamation' };
    router.reload({ only: ['oils'] });
}

function triggerUpload(oilId: number) {
    fileInputRefs.value[oilId]?.click();
}

async function handleFileUpload(oilId: number, event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    uploadingFor.value = oilId;
    const form = new FormData();
    form.append('sds', file);

    try {
        await axios.post(`oils/${oilId}/sds`, form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        router.reload({ only: ['oils'] });
    } finally {
        uploadingFor.value = null;
    }
}
</script>

<template>
    <AdminLayout>

        <Head title="Oils" />

        <!-- ── Page header ── -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Oils</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage fragrance &amp; essential oils and their SDS data</p>
            </div>
            <button class="btn btn-primary" @click="showAddForm = !showAddForm">
                {{ showAddForm ? 'Cancel' : '+ Add oil' }}
            </button>
        </div>

        <!-- ── Add oil form ── -->
        <div v-if="showAddForm" class="card mb-6">
            <div class="card-header">
                <span class="card-title">New oil</span>
            </div>
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div>
                    <label class="form-label">Name</label>
                    <input v-model="newOil.name" type="text" placeholder="e.g. Lavender Essential Oil" />
                </div>
                <div>
                    <label class="form-label">Supplier</label>
                    <input v-model="newOil.supplier" type="text" placeholder="e.g. Nikura" />
                </div>
                <div>
                    <label class="form-label">Primary CAS</label>
                    <input v-model="newOil.cas_primary" type="text" placeholder="e.g. 8000-28-0" />
                </div>
            </div>
            <div class="flex justify-end">
                <button class="btn btn-primary" @click="submitAddOil">Save oil</button>
            </div>
        </div>

        <!-- ── Search ── -->
        <div class="mb-4">
            <input v-model="searchQuery" type="text" placeholder="Search by name or supplier…"
                class="w-full max-w-xs" />
        </div>

        <!-- ── Oil list ── -->
        <div class="flex flex-col gap-2">
            <p v-if="!filteredOils.length" class="text-sm text-gray-400 italic py-4">No oils found.</p>

            <div v-for="oil in filteredOils" :key="oil.id" class="oil-row">
                <!-- Row header -->
                <div class="oil-row-header" @click="toggleOil(oil.id)">
                    <div class="oil-icon">{{ initials(oil.name) }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">{{ oil.name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ oil.supplier || '—' }}&nbsp;·&nbsp;CAS {{ oil.cas_primary || '—' }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge"
                            :class="sdsDocs(oil).some(d => d.parsed) ? 'badge-success' : 'badge-warning'">
                            {{sdsDocs(oil).some(d => d.parsed) ? 'SDS parsed' : 'No SDS'}}
                        </span>
                        <span class="badge badge-gray">
                            {{ oil.hazards.length }} hazard{{ oil.hazards.length !== 1 ? 's' : '' }}
                        </span>
                        <span class="chevron" :class="{ open: openOilId === oil.id }">▶</span>
                    </div>
                </div>

                <!-- Expanded detail -->
                <div v-if="openOilId === oil.id" class="oil-detail">

                    <!-- Tabs -->
                    <div class="tabs">
                        <button class="tab" :class="{ active: tabFor(oil.id) === 'hazards' }"
                            @click="setTab(oil.id, 'hazards')">Section 2 — Hazards</button>
                        <button class="tab" :class="{ active: tabFor(oil.id) === 'components' }"
                            @click="setTab(oil.id, 'components')">Section 3 — Components</button>
                        <button class="tab" :class="{ active: tabFor(oil.id) === 'sds' }"
                            @click="setTab(oil.id, 'sds')">SDS documents</button>
                    </div>

                    <!-- ── HAZARDS TAB ── -->
                    <div v-if="tabFor(oil.id) === 'hazards'">
                        <p v-if="!oil.hazards.length" class="empty-msg">
                            No hazards parsed yet. Upload an SDS to auto-populate, or add manually.
                        </p>

                        <table v-else class="data-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Class</th>
                                    <th>Category</th>
                                    <th>Signal word</th>
                                    <th>Pictogram</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="hazard in oil.hazards" :key="hazard.id">
                                    <!-- Display row -->
                                    <tr v-if="editingHazard !== hazard.id">
                                        <td>
                                            <span class="badge badge-code">{{ hazard.hazard_code }}</span>
                                        </td>
                                        <td>{{ hazard.hazard_class || '—' }}</td>
                                        <td>{{ hazard.category || '—' }}</td>
                                        <td>
                                            <span :class="signalClass(hazard.signal_word)">
                                                {{ hazard.signal_word || '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-gray">{{ hazard.pictogram || '—' }}</span>
                                        </td>
                                        <td class="text-right">
                                            <button class="btn btn-sm" @click="startEditHazard(hazard)">Edit</button>
                                        </td>
                                    </tr>

                                    <!-- Edit row -->
                                    <tr v-else>
                                        <td colspan="6">
                                            <div class="edit-panel">
                                                <div class="grid grid-cols-3 gap-2 mb-2">
                                                    <div>
                                                        <label class="form-label">Code</label>
                                                        <input v-model="hazardEdits[hazard.id].hazard_code"
                                                            type="text" />
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Class</label>
                                                        <input v-model="hazardEdits[hazard.id].hazard_class"
                                                            type="text" />
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Category</label>
                                                        <input v-model="hazardEdits[hazard.id].category" type="text" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-2">
                                                    <div>
                                                        <label class="form-label">Signal word</label>
                                                        <select v-model="hazardEdits[hazard.id].signal_word">
                                                            <option>Warning</option>
                                                            <option>Danger</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Pictogram</label>
                                                        <input v-model="hazardEdits[hazard.id].pictogram" type="text" />
                                                    </div>
                                                    <div class="flex items-end gap-2">
                                                        <button class="btn btn-primary btn-sm"
                                                            @click="submitUpdateHazard(oil.id, hazard.id)">Save</button>
                                                        <button class="btn btn-sm btn-danger"
                                                            @click="cancelEditHazard">Cancel</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- Add hazard -->
                        <div class="mt-3">
                            <button v-if="showAddHazard !== oil.id" class="btn btn-sm" @click="showAddHazard = oil.id">+
                                Add hazard</button>

                            <div v-else class="edit-panel mt-2">
                                <div class="grid grid-cols-3 gap-2 mb-2">
                                    <div>
                                        <label class="form-label">Code</label>
                                        <input v-model="newHazard.hazard_code" type="text" placeholder="H317" />
                                    </div>
                                    <div>
                                        <label class="form-label">Class</label>
                                        <input v-model="newHazard.hazard_class" type="text"
                                            placeholder="Skin Sensitisation" />
                                    </div>
                                    <div>
                                        <label class="form-label">Category</label>
                                        <input v-model="newHazard.category" type="text" placeholder="1B" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="form-label">Signal word</label>
                                        <select v-model="newHazard.signal_word">
                                            <option>Warning</option>
                                            <option>Danger</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Pictogram</label>
                                        <input v-model="newHazard.pictogram" type="text" placeholder="exclamation" />
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <button class="btn btn-primary btn-sm"
                                            @click="submitAddHazard(oil.id)">Add</button>
                                        <button class="btn btn-sm" @click="showAddHazard = null">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── COMPONENTS TAB ── -->
                    <div v-if="tabFor(oil.id) === 'components'">
                        <p v-if="!oil.components.length" class="empty-msg">
                            No components parsed yet. Upload an SDS to auto-populate.
                        </p>
                        <table v-else class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>CAS</th>
                                    <th>Concentration (%)</th>
                                    <th>CLP classification</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="comp in oil.components" :key="comp.id">
                                    <td>{{ comp.name }}</td>
                                    <td class="font-mono text-xs">{{ comp.cas || '—' }}</td>
                                    <td>
                                        <span v-if="comp.concentration_min !== null && comp.concentration_max !== null">
                                            {{ comp.concentration_min }}–{{ comp.concentration_max }}%
                                        </span>
                                        <span v-else>—</span>
                                    </td>
                                    <td>
                                        <template v-if="comp.clp_classification">
                                            <span v-for="code in comp.clp_classification.split(',')" :key="code"
                                                class="badge badge-code mr-1">{{ code.trim() }}</span>
                                        </template>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── SDS TAB ── -->
                    <div v-if="tabFor(oil.id) === 'sds'">
                        <div class="flex flex-col gap-2 mb-3">
                            <p v-if="!sdsDocs(oil).length" class="empty-msg">No SDS uploaded yet.</p>

                            <div v-for="doc in sdsDocs(oil)" :key="doc.id" class="sds-item">
                                <span class="text-base">📄</span>
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-gray-800">
                                        {{ doc.version ? `v${doc.version}` : 'Unknown version' }}
                                        <span v-if="doc.issue_date"> — {{ doc.issue_date }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ doc.parsed ? 'Auto-parsed ✓' : 'Not yet parsed' }}
                                    </div>
                                </div>
                                <span class="badge" :class="doc.parsed ? 'badge-success' : 'badge-warning'">
                                    {{ doc.parsed ? 'Parsed' : 'Pending' }}
                                </span>
                            </div>
                        </div>

                        <!-- Upload zone -->
                        <input :ref="el => fileInputRefs[oil.id] = el as HTMLInputElement" type="file"
                            accept="application/pdf" class="hidden" @change="handleFileUpload(oil.id, $event)" />
                        <button class="upload-zone w-full" :disabled="uploadingFor === oil.id"
                            @click="triggerUpload(oil.id)">
                            <span v-if="uploadingFor === oil.id" class="text-sm text-gray-400">Uploading…</span>
                            <template v-else>
                                <p class="text-sm text-gray-500">Click to upload SDS PDF</p>
                                <p class="text-xs text-gray-400 mt-1">Max 10MB · PDF only</p>
                            </template>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── Layout ────────────────────────────────────────── */
.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.25rem;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}

.card-title {
    font-size: 0.9375rem;
    font-weight: 500;
    color: #111827;
}

/* ── Form elements ─────────────────────────────────── */
.form-label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 4px;
}

input[type="text"],
input[type="email"],
select {
    display: block;
    width: 100%;
    font-size: 0.8125rem;
    color: #111827;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 6px 10px;
    outline: none;
    transition: border-color 0.15s;
}

input:focus,
select:focus {
    border-color: #6b7280;
}

/* ── Buttons ───────────────────────────────────────── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    background: transparent;
    color: #374151;
    cursor: pointer;
    transition: background 0.1s;
}

.btn:hover {
    background: #f9fafb;
}

.btn-primary {
    background: #111827;
    color: white;
    border-color: transparent;
}

.btn-primary:hover {
    opacity: 0.85;
    background: #111827;
}

.btn-sm {
    padding: 4px 10px;
    font-size: 0.75rem;
}

.btn-danger {
    color: #b91c1c;
    border-color: #fca5a5;
}

.btn-danger:hover {
    background: #fef2f2;
}

/* ── Badges ────────────────────────────────────────── */
.badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.6875rem;
    font-weight: 500;
    padding: 2px 8px;
    border-radius: 9999px;
    white-space: nowrap;
}

.badge-success {
    background: #dcfce7;
    color: #15803d;
}

.badge-warning {
    background: #fef9c3;
    color: #a16207;
}

.badge-gray {
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 0.25rem;
}

.badge-code {
    background: #fef2f2;
    color: #b91c1c;
    border-radius: 0.25rem;
    font-family: ui-monospace, monospace;
    font-size: 0.6875rem;
}

/* ── Oil row ───────────────────────────────────────── */
.oil-row {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.oil-row-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    user-select: none;
}

.oil-row-header:hover {
    background: #f9fafb;
}

.oil-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #eff6ff;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8125rem;
    font-weight: 500;
    flex-shrink: 0;
}

.chevron {
    font-size: 0.6875rem;
    color: #9ca3af;
    transition: transform 0.2s;
}

.chevron.open {
    transform: rotate(90deg);
}

/* ── Detail panel ──────────────────────────────────── */
.oil-detail {
    border-top: 1px solid #f3f4f6;
    padding: 16px;
}

/* ── Tabs ──────────────────────────────────────────── */
.tabs {
    display: flex;
    gap: 0;
    margin-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.tab {
    font-size: 0.8125rem;
    padding: 6px 14px;
    cursor: pointer;
    color: #6b7280;
    border: none;
    border-bottom: 2px solid transparent;
    background: transparent;
    margin-bottom: -1px;
    transition: color 0.15s, border-color 0.15s;
}

.tab:hover {
    color: #374151;
}

.tab.active {
    color: #111827;
    border-bottom-color: #111827;
    font-weight: 500;
}

/* ── Data tables ───────────────────────────────────── */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
}

.data-table th {
    text-align: left;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.75rem;
    padding: 4px 10px;
    border-bottom: 1px solid #f3f4f6;
}

.data-table td {
    padding: 7px 10px;
    border-bottom: 1px solid #f9fafb;
    vertical-align: middle;
    color: #374151;
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

/* ── Edit panel ────────────────────────────────────── */
.edit-panel {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 12px;
}

/* ── SDS ───────────────────────────────────────────── */
.sds-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: #f9fafb;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
}

.upload-zone {
    border: 1px dashed #d1d5db;
    border-radius: 0.375rem;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    background: transparent;
    transition: background 0.1s;
}

.upload-zone:hover:not(:disabled) {
    background: #f9fafb;
}

.upload-zone:disabled {
    cursor: default;
    opacity: 0.6;
}

/* ── Misc ──────────────────────────────────────────── */
.empty-msg {
    font-size: 0.8125rem;
    color: #9ca3af;
    font-style: italic;
    padding: 4px 0;
}

.signal-danger {
    color: #b91c1c;
    font-weight: 500;
}

.signal-warning {
    color: #b45309;
    font-weight: 500;
}

.text-right {
    text-align: right;
}

.hidden {
    display: none;
}

.mr-1 {
    margin-right: 0.25rem;
}
</style>
