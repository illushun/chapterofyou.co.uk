<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import html2pdf from 'html2pdf.js';

interface Product {
    id: number;
    name: string;
};

interface ClpResult {
    signal_word: string | null
    required_pictograms: string[]
    hazard_statements: Record<string, string>
    precautionary_statements: Record<string, string>
    reasoning?: ClpReasoning
}

interface ClpReasoning {
    ingredients: {
        name: string
        percentage: number
        is_base: boolean
        hazards: string[]
    }[]
    triggered_classes: {
        class: string
        h_code: string
        h_text: string
        category: string | null
        signal: string | null
        pictogram: string | null
        threshold: string
        sum: number | null
        contributors: string[]
    }[]
    signal_word: string
    p_statement_sources: {
        code: string
        text: string
        triggered_by: string
    }[]
}

const props = defineProps<{
    products: Product[];
}>();

const showReasoning = ref(false);
const selectedProductId = ref<number | null>(null);
const loading = ref(false);
const clpResult = ref<ClpResult | null>(null);
const productName = ref('');

// Pictogram SVG paths (GHS/CLP standard)
const pictogramMap: Record<string, string> = {
    exclamation: '/storage/images/Pictograms/GHS07.png',
    'health-hazard': '/storage/images/Pictograms/GHS08.png',
    environment: '/storage/images/Pictograms/GHS09.png',
    flame: '/storage/images/Pictograms/GHS02.png',
    skull: '/storage/images/Pictograms/GHS06.png',
    corrosion: '/storage/images/Pictograms/GHS05.png',
    oxidizer: '/storage/images/Pictograms/GHS03.png',
    'gas-cylinder': '/storage/images/Pictograms/GHS04.png',
    explosion: '/images/Pictograms/GHS01.png',
}

async function onProductChange() {
    if (!selectedProductId.value) return

    loading.value = true
    clpResult.value = null

    try {
        const res = await axios.get(`/admin/clp-labels/${selectedProductId.value}/calculate`)
        clpResult.value = res.data.clp
        productName.value = res.data.product.name
    } finally {
        loading.value = false
    }
}

const signalWordClass = (word: string | null) => {
    if (word === 'Danger') return 'text-red-700 border-red-500'
    if (word === 'Warning') return 'text-amber-600 border-amber-400'
    return 'text-gray-500 border-gray-300'
}

watch(selectedProductId, () => {
    clpResult.value = null
    showReasoning.value = false
})
</script>

<template>
    <AdminLayout>

        <Head title="CLP Label Generator" />

        <!-- Product selector -->
        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Product</label>
            <select v-model="selectedProductId" @change="onProductChange"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option :value="null" disabled>— choose a product —</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-gray-400 text-sm">Calculating CLP classification…</div>

        <!-- Label Preview -->
        <div v-if="clpResult" class="border-2 border-gray-900 rounded-lg p-6 bg-white shadow-sm space-y-6">
            <!-- Header -->
            <div class="border-b pb-4">
                <p class="text-xs uppercase text-gray-400 tracking-widest">CLP Label Preview</p>
                <h2 class="text-xl font-bold mt-1">{{ productName }}</h2>
            </div>

            <!-- Signal Word -->
            <div v-if="clpResult.signal_word" class="inline-block border-2 rounded px-4 py-1 font-bold text-lg"
                :class="signalWordClass(clpResult.signal_word)">
                {{ clpResult.signal_word }}
            </div>
            <div v-else class="text-green-600 font-medium">
                ✓ No hazard classification required at this concentration
            </div>

            <!-- Pictograms -->
            <div v-if="clpResult.required_pictograms.length" class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Pictograms</p>
                <div class="flex flex-wrap gap-3">
                    <div v-for="pic in clpResult.required_pictograms" :key="pic"
                        class="w-16 h-16 border border-red-600 rounded flex items-center justify-center bg-white"
                        :title="pic">
                        <img v-if="pictogramMap[pic]" :src="pictogramMap[pic]" :alt="pic" class="w-12 h-12" />
                        <span v-else class="text-xs text-center text-gray-400 capitalize">{{ pic }}</span>
                    </div>
                </div>
            </div>

            <!-- Hazard Statements -->
            <div v-if="Object.keys(clpResult.hazard_statements).length" class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Hazard Statements</p>
                <ul class="space-y-1">
                    <li v-for="(statement, code) in clpResult.hazard_statements" :key="code" class="text-sm">
                        <span class="font-mono font-medium text-gray-800">{{ code }}</span>
                        <span class="text-gray-600"> — {{ statement }}</span>
                    </li>
                </ul>
            </div>

            <!-- Precautionary Statements -->
            <div v-if="Object.keys(clpResult.precautionary_statements).length" class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Precautionary Statements</p>
                <ul class="space-y-1">
                    <li v-for="(text, code) in clpResult.precautionary_statements" :key="code" class="text-sm">
                        <span class="font-mono font-medium text-gray-800">{{ code }}</span>
                        <span class="text-gray-600"> — {{ text }}</span>
                    </li>
                </ul>
            </div>

            <!-- No hazards -->
            <div v-if="!clpResult.signal_word && !Object.keys(clpResult.hazard_statements).length"
                class="text-sm text-gray-500 italic">
                No hazard statements triggered for this formulation.
            </div>

            <!-- Reasoning Panel -->
            <div v-if="clpResult?.reasoning" class="mt-6 rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden">

                    <!-- Header with toggle -->
                    <button type="button" @click="showReasoning = !showReasoning"
                        class="w-full flex items-center justify-between px-5 py-4 border-b border-copy-light hover:bg-secondary-light transition text-left">
                        <div>
                            <h3 class="text-base font-bold text-copy">How was this classification reached?</h3>
                            <p class="text-xs text-copy-light mt-0.5">CLP mixture calculation audit trail</p>
                        </div>
                        <span class="text-copy-light text-lg">{{ showReasoning ? '▲' : '▼' }}</span>
                    </button>

                    <div v-if="showReasoning" class="p-5 space-y-6 text-sm">

                        <!-- 1. Ingredients -->
                        <div>
                            <h4
                                class="font-bold text-copy mb-2 text-xs uppercase tracking-wider border-b border-copy-light pb-1">
                                Ingredients in this product
                            </h4>
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="text-left text-copy-light border-b border-copy-light">
                                        <th class="py-1 pr-3 font-medium">Ingredient</th>
                                        <th class="py-1 pr-3 font-medium text-right">% in blend</th>
                                        <th class="py-1 font-medium">Hazard codes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ing in clpResult.reasoning.ingredients" :key="ing.name"
                                        class="border-b border-copy-light last:border-b-0">
                                        <td class="py-1.5 pr-3 font-medium text-copy">
                                            {{ ing.name }}
                                            <span v-if="ing.is_base" class="ml-1 text-xs text-copy-light">(base)</span>
                                        </td>
                                        <td class="py-1.5 pr-3 text-right text-copy-light">{{ ing.percentage }}%</td>
                                        <td class="py-1.5">
                                            <span v-if="ing.hazards.length === 0"
                                                class="text-copy-light italic">None</span>
                                            <span v-for="code in ing.hazards" :key="code"
                                                class="inline-block mr-1 px-1.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-100 text-amber-800">{{
                                                    code }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. Triggered hazard classes -->
                        <div>
                            <h4
                                class="font-bold text-copy mb-2 text-xs uppercase tracking-wider border-b border-copy-light pb-1">
                                Hazard classes triggered
                            </h4>
                            <div v-if="clpResult.reasoning.triggered_classes.length === 0"
                                class="text-copy-light italic text-xs">
                                No hazard classes triggered — product is not classified as hazardous.
                            </div>
                            <div v-for="cls in clpResult.reasoning.triggered_classes" :key="cls.h_code"
                                class="mb-3 p-3 rounded-lg border border-copy-light bg-background">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-sm text-copy">{{ cls.h_code }}</span>
                                        <span class="text-copy text-xs">{{ cls.h_text }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span v-if="cls.signal" class="text-xs font-bold px-2 py-0.5 rounded-full"
                                            :class="cls.signal === 'Danger'
                                                ? 'bg-red-100 text-red-700'
                                                : 'bg-amber-100 text-amber-700'">{{ cls.signal }}</span>
                                        <span class="text-xs text-copy-light">Cat {{ cls.category }}</span>
                                    </div>
                                </div>
                                <div class="text-xs text-copy-light space-y-0.5">
                                    <div><span class="font-medium">Threshold:</span> {{ cls.threshold }}</div>
                                    <div v-if="cls.sum !== null">
                                        <span class="font-medium">Calculated sum:</span>
                                        <span class="font-mono">{{ cls.sum }}%</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Contributing ingredients:</span>
                                        {{ cls.contributors.join(', ') || 'n/a' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Signal word -->
                        <div>
                            <h4
                                class="font-bold text-copy mb-2 text-xs uppercase tracking-wider border-b border-copy-light pb-1">
                                Signal word determination
                            </h4>
                            <p class="text-xs text-copy-light">{{ clpResult.reasoning.signal_word }}</p>
                        </div>

                        <!-- 4. P statement sources -->
                        <div>
                            <h4
                                class="font-bold text-copy mb-2 text-xs uppercase tracking-wider border-b border-copy-light pb-1">
                                Precautionary statements — why each was included
                            </h4>
                            <div v-if="clpResult.reasoning.p_statement_sources.length === 0"
                                class="text-copy-light italic text-xs">
                                No precautionary statements required.
                            </div>
                            <table v-else class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="text-left text-copy-light border-b border-copy-light">
                                        <th class="py-1 pr-3 font-medium w-24">Code</th>
                                        <th class="py-1 pr-3 font-medium">Statement</th>
                                        <th class="py-1 font-medium">Triggered by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in clpResult.reasoning.p_statement_sources" :key="p.code"
                                        class="border-b border-copy-light last:border-b-0">
                                        <td class="py-1.5 pr-3 font-mono font-bold text-copy">{{ p.code }}</td>
                                        <td class="py-1.5 pr-3 text-copy-light">{{ p.text }}</td>
                                        <td class="py-1.5">
                                            <span class="font-mono text-xs text-copy-light">{{ p.triggered_by }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Print Label -->
            <div class="pt-4 border-t border-gray-100">

                <a :href="`/admin/clp-labels/${selectedProductId}/print`" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg
                hover:opacity-80 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                    </svg>
                    Print Label
                </a>

            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
img {
    border: 2px solid black;
}
</style>
