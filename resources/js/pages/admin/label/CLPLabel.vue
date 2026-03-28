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
}

const props = defineProps<{
    products: Product[];
}>();

const selectedProductId = ref<number | null>(null);
const loading = ref(false);
const clpResult = ref<ClpResult | null>(null);
const productName = ref('');

// Pictogram SVG paths (GHS/CLP standard)
const pictogramMap: Record<string, string> = {
    exclamation: '/images/clp/GHS07.svg',
    'health-hazard': '/images/clp/GHS08.svg',
    environment: '/images/clp/GHS09.svg',
    flame: '/images/clp/GHS02.svg',
    skull: '/images/clp/GHS06.svg',
    corrosion: '/images/clp/GHS05.svg',
    oxidizer: '/images/clp/GHS03.svg',
    'gas-cylinder': '/images/clp/GHS04.svg',
    explosion: '/images/clp/GHS01.svg',
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

            <!-- No hazards -->
            <div v-if="!clpResult.signal_word && !Object.keys(clpResult.hazard_statements).length"
                class="text-sm text-gray-500 italic">
                No hazard statements triggered for this formulation.
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
img {
    border: 2px solid black;
}
</style>
