<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

interface Product { id: number; name: string; }

interface ClpResult {
    signal_word: string | null;
    required_pictograms: string[];
    hazard_statements: Record<string, string>;
    precautionary_statements: Record<string, string>;
    reasoning?: ClpReasoning;
}

interface ClpReasoning {
    ingredients: { name: string; percentage: number; is_base: boolean; hazards: string[] }[];
    triggered_classes: {
        class: string; h_code: string; h_text: string;
        category: string | null; signal: string | null; pictogram: string | null;
        threshold: string; sum: number | null; contributors: string[];
    }[];
    signal_word: string;
    p_statement_sources: { code: string; text: string; triggered_by: string }[];
}

const props = defineProps<{ products: Product[] }>();

const selectedProductId = ref<number | null>(null);
const loading = ref(false);
const clpResult = ref<ClpResult | null>(null);
const productName = ref('');
const showReasoning = ref(false);
const exportingPdf = ref(false);

const pictogramMap: Record<string, string> = {
    'exclamation': '/storage/images/Pictograms/GHS07.png',
    'health-hazard': '/storage/images/Pictograms/GHS08.png',
    'environment': '/storage/images/Pictograms/GHS09.png',
    'flame': '/storage/images/Pictograms/GHS02.png',
    'skull': '/storage/images/Pictograms/GHS06.png',
    'corrosion': '/storage/images/Pictograms/GHS05.png',
    'oxidizer': '/storage/images/Pictograms/GHS03.png',
    'gas-cylinder': '/storage/images/Pictograms/GHS04.png',
    'explosion': '/storage/images/Pictograms/GHS01.png',
};

const pictogramLabels: Record<string, string> = {
    'exclamation': 'GHS07 Exclamation',
    'health-hazard': 'GHS08 Health Hazard',
    'environment': 'GHS09 Environment',
    'flame': 'GHS02 Flame',
    'skull': 'GHS06 Skull & Crossbones',
    'corrosion': 'GHS05 Corrosion',
    'oxidizer': 'GHS03 Oxidiser',
    'gas-cylinder': 'GHS04 Gas Cylinder',
    'explosion': 'GHS01 Explosion',
};

async function onProductChange() {
    if (!selectedProductId.value) return;
    loading.value = true;
    clpResult.value = null;
    showReasoning.value = false;
    try {
        const res = await axios.get(`/admin/clp-labels/${selectedProductId.value}/calculate`);
        clpResult.value = res.data.clp;
        productName.value = res.data.product.name;
    } finally {
        loading.value = false;
    }
}

watch(selectedProductId, () => { clpResult.value = null; showReasoning.value = false; });

// ── Pictogram PDF export ──────────────────────────────────────────────────
// Builds a hidden HTML sheet of all pictograms at exactly 1cm × 1cm and
// triggers window.print() in a new window (print-to-PDF).
async function exportPictogramsPdf() {
    exportingPdf.value = true;
    try {
        // Convert all pictogram images to base64 so they render in the print window
        const entries = Object.entries(pictogramMap);

        const toBase64 = (url: string): Promise<string> =>
            new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth || 128;
                    canvas.height = img.naturalHeight || 128;
                    const ctx = canvas.getContext('2d')!;
                    ctx.drawImage(img, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                };
                img.onerror = () => resolve(url); // fallback: use original URL
                img.src = url;
            });

        const b64Images: Record<string, string> = {};
        for (const [key, url] of entries) {
            b64Images[key] = await toBase64(url);
        }

        // Build the print document
        const rows = entries.map(([key]) => `
            <div class="pic-item">
                <img src="${b64Images[key]}" alt="${pictogramLabels[key] ?? key}" />
                <p>${pictogramLabels[key] ?? key}</p>
            </div>
        `).join('');

        const html = `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>GHS Pictograms — Chapter of You</title>
<style>
  @page { size: A4; margin: 15mm; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: #fff; color: #1a1a2e; }
  h1 { font-size: 11pt; font-weight: 700; margin-bottom: 6mm; letter-spacing: 0.05em; text-transform: uppercase; }
  .grid { display: flex; flex-wrap: wrap; gap: 6mm; }
  .pic-item {
    display: flex; flex-direction: column; align-items: center;
    gap: 1.5mm; width: 25mm;
  }
  .pic-item img {
    width: 10mm; height: 10mm;   /* 1cm × 1cm */
    object-fit: contain;
    border: 0.5pt solid #1a1a2e;
    display: block;
  }
  .pic-item p {
    font-size: 6pt; text-align: center;
    color: #555; line-height: 1.3;
  }
  .meta { font-size: 7pt; color: #888; margin-top: 8mm; border-top: 0.5pt solid #ccc; padding-top: 3mm; }
</style>
</head>
<body>
  <h1>GHS / CLP Pictograms Reference Sheet</h1>
  <div class="grid">${rows}</div>
  <p class="meta">Chapter of You — printed ${new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
</body>
</html>`;

        const win = window.open('', '_blank');
        if (!win) { alert('Pop-up blocked. Please allow pop-ups for this page.'); return; }
        win.document.write(html);
        win.document.close();
        win.onload = () => { win.focus(); win.print(); };
    } finally {
        exportingPdf.value = false;
    }
}
</script>

<template>
    <AdminLayout>

        <Head title="CLP Label Generator — Admin" />

        <!-- Page header -->
        <div class="cl-header">
            <div>
                <h1 class="cl-title">CLP Label Generator</h1>
                <p class="cl-sub">Select a product to calculate its CLP hazard classification.</p>
            </div>
            <!-- Pictogram sheet export -->
            <button @click="exportPictogramsPdf" :disabled="exportingPdf" class="cl-export-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                {{ exportingPdf ? 'Preparing…' : 'Export Pictogram Sheet' }}
            </button>
        </div>

        <!-- Product selector -->
        <div class="cl-selector-card">
            <label class="cl-label" for="product-select">Product</label>
            <select id="product-select" v-model="selectedProductId" @change="onProductChange" class="cl-select">
                <option :value="null" disabled>Select a product to calculate…</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="cl-loading">
            <svg class="cl-spinner" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="rgba(26,26,46,0.15)" stroke-width="3" />
                <path d="M12 2a10 10 0 0 1 10 10" stroke="#9b84d4" stroke-width="3" stroke-linecap="round" />
            </svg>
            Calculating CLP classification…
        </div>

        <!-- Result -->
        <div v-if="clpResult" class="cl-result">

            <!-- Result header -->
            <div class="cl-result-head">
                <div>
                    <p class="cl-result-eyebrow">CLP Label Preview</p>
                    <h2 class="cl-result-name">{{ productName }}</h2>
                </div>
                <a :href="`/admin/clp-labels/${selectedProductId}/print`" target="_blank" class="cl-print-btn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect x="6" y="14" width="12" height="8" />
                    </svg>
                    Print Label
                </a>
            </div>

            <!-- Signal word -->
            <div class="cl-section">
                <p class="cl-section-label">Signal Word</p>
                <div v-if="clpResult.signal_word" class="cl-signal-word" :class="{
                    'cl-signal--danger': clpResult.signal_word === 'Danger',
                    'cl-signal--warning': clpResult.signal_word === 'Warning',
                }">
                    {{ clpResult.signal_word }}
                </div>
                <div v-else class="cl-no-hazard">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    No hazard classification required at this concentration
                </div>
            </div>

            <!-- Pictograms -->
            <div v-if="clpResult.required_pictograms.length" class="cl-section">
                <p class="cl-section-label">Pictograms</p>
                <div class="cl-pictograms">
                    <div v-for="pic in clpResult.required_pictograms" :key="pic" class="cl-pic" :title="pic">
                        <img v-if="pictogramMap[pic]" :src="pictogramMap[pic]" :alt="pic" class="cl-pic-img" />
                        <span v-else class="cl-pic-nil">{{ pic }}</span>
                        <span class="cl-pic-label">{{ pictogramLabels[pic] ?? pic }}</span>
                    </div>
                </div>
            </div>

            <!-- Hazard statements -->
            <div v-if="Object.keys(clpResult.hazard_statements).length" class="cl-section">
                <p class="cl-section-label">Hazard Statements</p>
                <div class="cl-statement-list">
                    <div v-for="(statement, code) in clpResult.hazard_statements" :key="code" class="cl-statement">
                        <span class="cl-statement-code cl-statement-code--h">{{ code }}</span>
                        <span class="cl-statement-text">{{ statement }}</span>
                    </div>
                </div>
            </div>

            <!-- Precautionary statements -->
            <div v-if="Object.keys(clpResult.precautionary_statements).length" class="cl-section">
                <p class="cl-section-label">Precautionary Statements</p>
                <div class="cl-statement-list">
                    <div v-for="(text, code) in clpResult.precautionary_statements" :key="code" class="cl-statement">
                        <span class="cl-statement-code cl-statement-code--p">{{ code }}</span>
                        <span class="cl-statement-text">{{ text }}</span>
                    </div>
                </div>
            </div>

            <!-- No hazards at all -->
            <div v-if="!clpResult.signal_word && !Object.keys(clpResult.hazard_statements).length"
                class="cl-no-statements">
                No hazard statements triggered for this formulation.
            </div>

            <!-- ── Reasoning panel ── -->
            <div v-if="clpResult.reasoning" class="cl-reasoning">
                <button type="button" @click="showReasoning = !showReasoning" class="cl-reasoning-toggle">
                    <div>
                        <p class="cl-reasoning-toggle-title">How was this classification reached?</p>
                        <p class="cl-reasoning-toggle-sub">CLP mixture calculation audit trail</p>
                    </div>
                    <svg class="cl-chevron" :class="{ 'cl-chevron--open': showReasoning }" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>

                <div v-if="showReasoning" class="cl-reasoning-body">

                    <!-- Ingredients -->
                    <div class="cl-r-section">
                        <h4 class="cl-r-title">Ingredients in this product</h4>
                        <div class="cl-r-table-wrap">
                            <table class="cl-r-table">
                                <thead>
                                    <tr>
                                        <th class="cl-r-th">Ingredient</th>
                                        <th class="cl-r-th cl-r-th--right">% in blend</th>
                                        <th class="cl-r-th">Hazard codes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ing in clpResult.reasoning.ingredients" :key="ing.name" class="cl-r-row">
                                        <td class="cl-r-td cl-r-td--name">
                                            {{ ing.name }}
                                            <span v-if="ing.is_base" class="cl-r-base-tag">base</span>
                                        </td>
                                        <td class="cl-r-td cl-r-td--right cl-r-td--pct">{{ ing.percentage }}%</td>
                                        <td class="cl-r-td">
                                            <span v-if="!ing.hazards.length" class="cl-r-none">None</span>
                                            <span v-for="code in ing.hazards" :key="code" class="cl-r-hcode">{{ code
                                            }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Triggered classes -->
                    <div class="cl-r-section">
                        <h4 class="cl-r-title">Hazard classes triggered</h4>
                        <p v-if="!clpResult.reasoning.triggered_classes.length" class="cl-r-empty">
                            No hazard classes triggered — product is not classified as hazardous.
                        </p>
                        <div v-for="cls in clpResult.reasoning.triggered_classes" :key="cls.h_code"
                            class="cl-r-class-card">
                            <div class="cl-r-class-head">
                                <div class="cl-r-class-left">
                                    <span class="cl-r-hcode cl-r-hcode--lg">{{ cls.h_code }}</span>
                                    <span class="cl-r-class-text">{{ cls.h_text }}</span>
                                </div>
                                <div class="cl-r-class-right">
                                    <span v-if="cls.signal" class="cl-r-signal"
                                        :class="cls.signal === 'Danger' ? 'cl-r-signal--danger' : 'cl-r-signal--warning'">
                                        {{ cls.signal }}
                                    </span>
                                    <span class="cl-r-cat">Cat {{ cls.category }}</span>
                                </div>
                            </div>
                            <div class="cl-r-class-meta">
                                <span><strong>Threshold:</strong> {{ cls.threshold }}</span>
                                <span v-if="cls.sum !== null"><strong>Sum:</strong> {{ cls.sum }}%</span>
                                <span><strong>Contributors:</strong> {{ cls.contributors.join(', ') || 'n/a' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Signal word determination -->
                    <div class="cl-r-section">
                        <h4 class="cl-r-title">Signal word determination</h4>
                        <p class="cl-r-prose">{{ clpResult.reasoning.signal_word }}</p>
                    </div>

                    <!-- P statement sources -->
                    <div class="cl-r-section">
                        <h4 class="cl-r-title">Precautionary statements — why each was included</h4>
                        <p v-if="!clpResult.reasoning.p_statement_sources.length" class="cl-r-empty">
                            No precautionary statements required.
                        </p>
                        <div v-else class="cl-r-table-wrap">
                            <table class="cl-r-table">
                                <thead>
                                    <tr>
                                        <th class="cl-r-th" style="width:80px">Code</th>
                                        <th class="cl-r-th">Statement</th>
                                        <th class="cl-r-th">Triggered by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in clpResult.reasoning.p_statement_sources" :key="p.code"
                                        class="cl-r-row">
                                        <td class="cl-r-td"><span class="cl-r-pcode">{{ p.code }}</span></td>
                                        <td class="cl-r-td cl-r-td--stmt">{{ p.text }}</td>
                                        <td class="cl-r-td"><span class="cl-r-trigger">{{ p.triggered_by }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── Tokens ── */
.cl-header,
.cl-selector-card,
.cl-result {
    --bb-navy: #1a1a2e;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;
    --bb-red-bg: #fdeef0;
    --bb-green: #4caf7d;
    --bb-green-bg: #eef7f2;
    --bb-lav: #c9b8f0;
    --bb-lav-d: #9b84d4;
    --bb-blush: #f2c4ce;
    --bb-blush-d: #d4899a;
    --bb-peach: #f5d5b8;
    --bb-peach-d: #c8820a;
    font-family: 'DM Sans', sans-serif;
}

/* ── Header ── */
.cl-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.cl-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--bb-text);
    margin-bottom: 0.2rem;
}

.cl-sub {
    font-size: 0.82rem;
    color: var(--bb-muted);
}

.cl-export-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.58rem 1rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    color: var(--bb-muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.15s;
    white-space: nowrap;
}

.cl-export-btn:hover:not(:disabled) {
    background: var(--bb-lav);
    border-color: var(--bb-lav-d);
    color: var(--bb-navy);
    transform: translateY(-1px);
}

.cl-export-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ── Selector ── */
.cl-selector-card {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 6px rgba(26, 26, 46, 0.05);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 1.25rem;
}

.cl-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--bb-text);
}

.cl-select {
    width: 100%;
    padding: 0.65rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.cl-select:focus {
    border-color: var(--bb-lav-d);
    box-shadow: 0 0 0 3px rgba(201, 184, 240, 0.2);
}

/* ── Loading ── */
.cl-loading {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 1.25rem;
    font-size: 0.85rem;
    color: var(--bb-muted);
    font-style: italic;
}

.cl-spinner {
    width: 18px;
    height: 18px;
    animation: cl-spin 0.8s linear infinite;
    flex-shrink: 0;
}

@keyframes cl-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* ── Result card ── */
.cl-result {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 8px rgba(26, 26, 46, 0.06);
    overflow: hidden;
}

.cl-result-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--bb-border);
    background: var(--bb-cream);
    flex-wrap: wrap;
    gap: 0.75rem;
}

.cl-result-eyebrow {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--bb-muted);
    margin-bottom: 0.2rem;
}

.cl-result-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--bb-text);
}

.cl-print-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.9rem;
    border-radius: 8px;
    border: none;
    background: var(--bb-navy);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: opacity 0.15s, transform 0.15s;
    white-space: nowrap;
}

.cl-print-btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
}

/* ── Sections ── */
.cl-section {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--bb-border);
}

.cl-section:last-child {
    border-bottom: none;
}

.cl-section-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--bb-muted);
    margin-bottom: 0.75rem;
}

/* Signal word */
.cl-signal-word {
    display: inline-block;
    font-size: 1rem;
    font-weight: 700;
    padding: 0.4rem 1rem;
    border-radius: 8px;
    border: 2px solid;
}

.cl-signal--danger {
    background: #fff0f0;
    color: var(--bb-red);
    border-color: var(--bb-red);
}

.cl-signal--warning {
    background: #fffbf0;
    color: var(--bb-peach-d);
    border-color: var(--bb-peach-d);
}

.cl-no-hazard {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--bb-green);
    background: var(--bb-green-bg);
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    border: 1px solid #b8dfc8;
}

/* Pictograms */
.cl-pictograms {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.cl-pic {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    text-align: center;
}

.cl-pic-img {
    width: 56px;
    height: 56px;
    border: 2px solid #1a1a2e;
    border-radius: 6px;
    object-fit: contain;
    background: #fff;
}

.cl-pic-nil {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    color: var(--bb-muted);
    border: 2px solid var(--bb-border);
    border-radius: 6px;
    background: var(--bb-cream);
}

.cl-pic-label {
    font-size: 0.6rem;
    color: var(--bb-muted);
    max-width: 60px;
    line-height: 1.3;
}

/* Statements */
.cl-statement-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.cl-statement {
    display: flex;
    align-items: baseline;
    gap: 0.6rem;
    font-size: 0.85rem;
    line-height: 1.5;
}

.cl-statement-code {
    font-family: monospace;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 4px;
    flex-shrink: 0;
}

.cl-statement-code--h {
    background: #fff8e6;
    color: var(--bb-peach-d);
}

.cl-statement-code--p {
    background: #f0edf8;
    color: var(--bb-lav-d);
}

.cl-statement-text {
    color: var(--bb-muted);
}

.cl-no-statements {
    padding: 1.25rem 1.5rem;
    font-size: 0.85rem;
    color: var(--bb-muted);
    font-style: italic;
}

/* ── Reasoning panel ── */
.cl-reasoning {
    border-top: 1px solid var(--bb-border);
}

.cl-reasoning-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background 0.12s;
    font-family: 'DM Sans', sans-serif;
}

.cl-reasoning-toggle:hover {
    background: var(--bb-cream);
}

.cl-reasoning-toggle-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--bb-text);
}

.cl-reasoning-toggle-sub {
    font-size: 0.72rem;
    color: var(--bb-muted);
    margin-top: 0.1rem;
}

.cl-chevron {
    color: var(--bb-muted);
    transition: transform 0.2s;
    flex-shrink: 0;
}

.cl-chevron--open {
    transform: rotate(180deg);
}

.cl-reasoning-body {
    padding: 0 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    border-top: 1px solid var(--bb-border);
    background: var(--bb-cream);
}

/* Reasoning sections */
.cl-r-section {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding-top: 1.25rem;
}

.cl-r-title {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--bb-muted);
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--bb-border);
}

.cl-r-empty {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-style: italic;
}

.cl-r-prose {
    font-size: 0.82rem;
    color: var(--bb-muted);
    line-height: 1.6;
}

/* Tables */
.cl-r-table-wrap {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
}

.cl-r-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.78rem;
    background: var(--bb-surface);
}

.cl-r-th {
    padding: 0.55rem 0.85rem;
    text-align: left;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
    background: var(--bb-cream);
    border-bottom: 1px solid var(--bb-border);
    white-space: nowrap;
}

.cl-r-th--right {
    text-align: right;
}

.cl-r-row {
    border-bottom: 1px solid var(--bb-border);
    transition: background 0.1s;
}

.cl-r-row:last-child {
    border-bottom: none;
}

.cl-r-row:hover {
    background: #fdfcfb;
}

.cl-r-td {
    padding: 0.6rem 0.85rem;
    vertical-align: top;
    color: var(--bb-text);
}

.cl-r-td--name {
    font-weight: 500;
}

.cl-r-td--right {
    text-align: right;
}

.cl-r-td--pct {
    color: var(--bb-muted);
    font-family: monospace;
}

.cl-r-td--stmt {
    color: var(--bb-muted);
}

.cl-r-base-tag {
    display: inline-block;
    margin-left: 0.35rem;
    font-size: 0.6rem;
    padding: 0.05rem 0.35rem;
    border-radius: 4px;
    background: var(--bb-lav);
    color: #3d2e8a;
}

.cl-r-none {
    font-style: italic;
    color: var(--bb-muted);
}

.cl-r-hcode {
    display: inline-block;
    margin-right: 0.25rem;
    font-family: monospace;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    background: #fff8e6;
    color: var(--bb-peach-d);
}

.cl-r-hcode--lg {
    font-size: 0.82rem;
    padding: 0.15rem 0.5rem;
}

.cl-r-pcode {
    font-family: monospace;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    background: #f0edf8;
    color: var(--bb-lav-d);
}

.cl-r-trigger {
    font-family: monospace;
    font-size: 0.72rem;
    color: var(--bb-muted);
}

/* Triggered class cards */
.cl-r-class-card {
    padding: 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.cl-r-class-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.cl-r-class-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cl-r-class-right {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-shrink: 0;
}

.cl-r-class-text {
    font-size: 0.8rem;
    color: var(--bb-text);
}

.cl-r-signal {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.12rem 0.5rem;
    border-radius: 999px;
}

.cl-r-signal--danger {
    background: #fff0f0;
    color: var(--bb-red);
}

.cl-r-signal--warning {
    background: #fffbf0;
    color: var(--bb-peach-d);
}

.cl-r-cat {
    font-size: 0.72rem;
    color: var(--bb-muted);
}

.cl-r-class-meta {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
    font-size: 0.75rem;
    color: var(--bb-muted);
}

.cl-r-class-meta strong {
    color: var(--bb-text);
    font-weight: 600;
}
</style>
