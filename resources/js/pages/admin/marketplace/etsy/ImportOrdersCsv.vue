<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface ImportResult {
    imported: number;
    skipped: number;
    errors: string[];
}

interface Connection {
    shop_name: string | null;
    shop_id: string | null;
}

const props = defineProps<{
    connection: Connection | null;
    result: ImportResult | null;
}>();

const page = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const form = useForm({ csv_file: null as File | null });
const fileInput = ref<HTMLInputElement>();
const fileName = ref('');

const onFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    form.csv_file = file;
    fileName.value = file?.name ?? '';
};

const submit = () => {
    form.post(route('admin.marketplace.etsy.orders.import-csv.upload'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Import Etsy Orders — Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.marketplace.etsy.index')">Marketplaces</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <Link :href="route('admin.marketplace.etsy.orders')">Etsy Orders</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>Import from CSV</span>
                </div>
                <h1 class="adm-title">Import Etsy Orders from CSV</h1>
                <p class="adm-sub">Upload an Etsy orders CSV to import orders without the API</p>
            </div>
            <Link :href="route('admin.marketplace.etsy.orders')" class="adm-btn adm-btn--ghost adm-btn--sm">
                View Orders
            </Link>
        </div>

        <!-- Flash -->
        <div v-if="flash.success" class="adm-flash adm-flash--success" style="margin-bottom:1.25rem">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="adm-flash adm-flash--error" style="margin-bottom:1.25rem">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            {{ flash.error }}
        </div>

        <div class="ic-layout">

            <!-- Instructions card -->
            <div class="adm-card ic-instructions">
                <h2 class="ic-section-title">How to download your Etsy orders CSV</h2>
                <ol class="ic-steps">
                    <li class="ic-step">
                        <span class="ic-step-num">1</span>
                        <div>
                            <p class="ic-step-title">Open Etsy Shop Manager</p>
                            <p class="ic-step-desc">Go to <strong>etsy.com/your/shops/me/sold</strong> or navigate to <em>Shop Manager → Orders &amp; Shipping</em>.</p>
                        </div>
                    </li>
                    <li class="ic-step">
                        <span class="ic-step-num">2</span>
                        <div>
                            <p class="ic-step-title">Download the CSV</p>
                            <p class="ic-step-desc">Click the <strong>Download CSV</strong> button (usually a cloud/download icon near the top of the orders list). Choose your date range if prompted.</p>
                        </div>
                    </li>
                    <li class="ic-step">
                        <span class="ic-step-num">3</span>
                        <div>
                            <p class="ic-step-title">Upload it here</p>
                            <p class="ic-step-desc">Select the downloaded CSV file below. Duplicate orders are automatically skipped.</p>
                        </div>
                    </li>
                </ol>
                <div class="ic-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    Products are matched automatically via Listing ID or SKU/MPN. Unmatched items are still imported — you can review them in the order detail.
                </div>
            </div>

            <!-- Upload card -->
            <div class="adm-card ic-upload-card">
                <h2 class="ic-section-title">Upload CSV</h2>

                <form @submit.prevent="submit">
                    <div class="ic-drop-zone" :class="{ 'ic-drop-zone--filled': !!form.csv_file }"
                        @click="fileInput?.click()">
                        <svg v-if="!form.csv_file" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            class="ic-drop-icon">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <svg v-else width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            class="ic-drop-icon ic-drop-icon--ok">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <p v-if="!form.csv_file" class="ic-drop-label">
                            Click to select an Etsy orders <strong>.csv</strong> file
                        </p>
                        <p v-else class="ic-drop-label ic-drop-label--file">{{ fileName }}</p>
                        <input ref="fileInput" type="file" accept=".csv,text/csv" class="ic-file-input"
                            @change="onFileChange" />
                    </div>

                    <div v-if="form.errors.csv_file" class="ic-field-error">{{ form.errors.csv_file }}</div>

                    <div class="ic-upload-actions">
                        <button v-if="form.csv_file" type="button" class="adm-btn adm-btn--ghost adm-btn--sm"
                            @click="form.csv_file = null; fileName = ''">
                            Clear
                        </button>
                        <button type="submit" :disabled="!form.csv_file || form.processing"
                            class="adm-btn adm-btn--primary">
                            <svg v-if="form.processing" class="adm-spinner" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5">
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                            </svg>
                            {{ form.processing ? 'Importing…' : 'Import Orders' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results card -->
            <div v-if="result" class="adm-card ic-result-card">
                <h2 class="ic-section-title">Import Results</h2>

                <div class="ic-result-stats">
                    <div class="ic-result-stat ic-result-stat--green">
                        <div class="ic-result-val">{{ result.imported }}</div>
                        <div class="ic-result-label">Imported</div>
                    </div>
                    <div class="ic-result-stat ic-result-stat--muted">
                        <div class="ic-result-val">{{ result.skipped }}</div>
                        <div class="ic-result-label">Skipped (duplicates)</div>
                    </div>
                    <div class="ic-result-stat" :class="result.errors.length ? 'ic-result-stat--red' : 'ic-result-stat--muted'">
                        <div class="ic-result-val">{{ result.errors.length }}</div>
                        <div class="ic-result-label">Errors</div>
                    </div>
                </div>

                <div v-if="result.errors.length" class="ic-errors">
                    <p class="ic-errors-title">The following orders could not be imported:</p>
                    <ul class="ic-error-list">
                        <li v-for="err in result.errors" :key="err" class="ic-error-item">{{ err }}</li>
                    </ul>
                </div>

                <div v-if="result.imported > 0" class="ic-result-action">
                    <Link :href="route('admin.marketplace.etsy.orders')" class="adm-btn adm-btn--primary">
                        View Imported Orders
                    </Link>
                </div>
            </div>

        </div>

    </AdminLayout>
</template>

<style scoped>
.ic-layout {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    max-width: 680px;
}

.ic-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--bb-text);
    margin-bottom: 1rem;
}

/* Instructions */
.ic-steps {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1rem;
}

.ic-step {
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
}

.ic-step-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--bb-lav, #a78bfa);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.ic-step-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--bb-text);
    margin-bottom: 0.2rem;
}

.ic-step-desc {
    font-size: 0.8rem;
    color: var(--bb-muted);
    line-height: 1.5;
}

.ic-note {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.78rem;
    color: var(--bb-muted);
    background: var(--bb-surface, #f8f8f8);
    border-radius: var(--bb-radius-sm, 6px);
    padding: 0.65rem 0.75rem;
    line-height: 1.5;
}

.ic-note svg { flex-shrink: 0; margin-top: 1px; }

/* Upload */
.ic-drop-zone {
    border: 2px dashed var(--bb-border);
    border-radius: var(--bb-radius-md, 10px);
    padding: 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    position: relative;
    margin-bottom: 1rem;
}

.ic-drop-zone:hover,
.ic-drop-zone--filled {
    border-color: var(--bb-accent, #7c3aed);
    background: var(--bb-surface, #f8f8f8);
}

.ic-drop-icon { color: var(--bb-muted); }
.ic-drop-icon--ok { color: var(--bb-green, #16a34a); }

.ic-drop-label {
    font-size: 0.85rem;
    color: var(--bb-muted);
    text-align: center;
}

.ic-drop-label--file {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--bb-text);
}

.ic-file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.ic-field-error {
    font-size: 0.78rem;
    color: var(--bb-red, #dc2626);
    margin-top: -0.5rem;
    margin-bottom: 0.75rem;
}

.ic-upload-actions {
    display: flex;
    gap: 0.6rem;
    align-items: center;
    justify-content: flex-end;
}

/* Results */
.ic-result-stats {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.ic-result-stat { text-align: center; min-width: 80px; }

.ic-result-val {
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1.2;
}

.ic-result-label {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bb-muted);
    margin-top: 0.2rem;
}

.ic-result-stat--green .ic-result-val { color: var(--bb-green, #16a34a); }
.ic-result-stat--red   .ic-result-val { color: var(--bb-red, #dc2626); }
.ic-result-stat--muted .ic-result-val { color: var(--bb-muted); }

.ic-errors {
    background: var(--bb-red-bg, #fef2f2);
    border-radius: var(--bb-radius-sm, 6px);
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}

.ic-errors-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--bb-red, #dc2626);
    margin-bottom: 0.5rem;
}

.ic-error-list { list-style: none; display: flex; flex-direction: column; gap: 0.25rem; }

.ic-error-item {
    font-size: 0.78rem;
    font-family: monospace;
    color: var(--bb-red, #dc2626);
}

.ic-result-action { margin-top: 0.5rem; }
</style>
