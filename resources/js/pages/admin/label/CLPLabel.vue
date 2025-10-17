<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// 1. Typescript Declaration for route helper
import type { Route } from 'ziggy-js';
declare const route: Route;

// --- Mock Data Structures ---

interface Product {
    id: number;
    name: string;
}

interface Ingredient {
    id: string;
    name: string;
    cas: string;
    required_h: string[];
    required_p: string[];
    pictogram: 'exclamation' | 'health' | 'environment' | null;
    signal_word: 'Danger' | 'Warning' | null;
}

const mockIngredients: Ingredient[] = [
    { id: '1', name: 'Limonene', cas: '5989-27-5', required_h: ['H317', 'H411'], required_p: ['P102', 'P280'], pictogram: 'exclamation', signal_word: 'Warning' },
    { id: '2', name: 'Linalool', cas: '78-70-6', required_h: ['H319'], required_p: ['P305+P351+P338'], pictogram: 'exclamation', signal_word: 'Warning' },
    { id: '3', name: 'Coumarin', cas: '91-64-5', required_h: ['H302', 'H317'], required_p: ['P101'], pictogram: 'exclamation', signal_word: 'Warning' },
    { id: '4', name: 'Alpha-Isomethyl Ionone', cas: '127-51-5', required_h: ['H317', 'H412'], required_p: ['P273'], pictogram: 'environment', signal_word: 'Warning' },
];

const mockStatements: { [key: string]: string } = {
    'H302': 'Harmful if swallowed.',
    'H317': 'May cause an allergic skin reaction.',
    'H319': 'Causes serious eye irritation.',
    'H411': 'Toxic to aquatic life with long lasting effects.',
    'H412': 'Harmful to aquatic life with long lasting effects.',
    'P101': 'If medical advice is needed, have product container or label at hand.',
    'P102': 'Keep out of reach of children.',
    'P273': 'Avoid release to the environment.',
    'P280': 'Wear protective gloves/eye protection/face protection.',
    'P305+P351+P338': 'IF IN EYES: Rinse cautiously with water for several minutes. Remove contact lenses, if present and easy to do. Continue rinsing.',
};

// --- Props & Form Setup ---

const props = defineProps<{
    // Mock Product Data to enable selection
    products?: Product[];
    flash?: { success?: string };
}>();

// Inertia Form Setup: Matches the structure of the CLPLabelController@store method's validation
const form = useForm({
    // Input Fields
    productId: null as number | null,
    productName: 'Example Candle Scent',
    concentration: 10 as number,
    supplierName: 'The Wax Emporium Ltd.',
    supplierAddress: '123 Maker Street, London, E1 7AT',
    supplierPhone: '020 7946 0000',
    selectedIngredientIds: ['1', '4'] as string[], // IDs of selected ingredients

    // CLP Calculated Output (These will be dynamically added to the payload on submit)
    signal_word: null as string | null,
    required_pictograms: [] as string[],
    hazard_statements: [] as string[],
    precautionary_statements: [] as string[],
    supplementary_info: '' as string,
    ingredients_json: [] as Ingredient[],
});

// --- Computed CLP Output (Same as before, drives the preview) ---

const activeIngredients = computed(() => {
    return mockIngredients.filter(ing => form.selectedIngredientIds.includes(ing.id));
});

const requiredPictograms = computed(() => {
    const pictos = activeIngredients.value
        .map(i => i.pictogram)
        .filter((p): p is NonNullable<typeof p> => p !== null);
    return [...new Set(pictos)];
});

const requiredSignalWord = computed(() => {
    if (activeIngredients.value.some(i => i.signal_word === 'Danger')) {
        return 'DANGER';
    }
    if (activeIngredients.value.some(i => i.signal_word === 'Warning')) {
        return 'WARNING';
    }
    return null;
});

const requiredHStatements = computed(() => {
    const hCodes = activeIngredients.value.flatMap(i => i.required_h);
    return [...new Set(hCodes)].sort();
});

const requiredPStatements = computed(() => {
    if (requiredHStatements.value.length === 0) return [];

    let pCodes = ['P102']; // Always include P102

    activeIngredients.value.forEach(i => {
        pCodes = [...pCodes, ...i.required_p];
    });

    return [...new Set(pCodes)].sort();
});

const supplementaryInformation = computed(() => {
    const containsList = activeIngredients.value.map(i => `${i.name} (${i.cas})`).join(', ');

    const baseText = `Contains: ${containsList}.`;

    if (form.concentration > 5) {
        return baseText + " May produce an allergic reaction.";
    }

    return baseText;
});

// Helper for pictogram SVGs
const getPictogramSvg = (type: 'exclamation' | 'health' | 'environment'): string => {
    switch (type) {
        case 'exclamation':
            // GHS07 - Exclamation Mark
            return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>`;
        case 'health':
            // GHS08 - Health Hazard (Mocked for brevity)
            return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9m9-9A9 9 0 0 0 3 12m18 0h-3M3 12h3M3 12A9 9 0 0 1 12 3m-4.5 9h9" /></svg>`;
        case 'environment':
            // GHS09 - Environmental Hazard
            return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 0 0 9-9c0-.447-.035-.884-.105-1.312M12 21V12a9 9 0 0 1-9-9 9 9 0 0 0 9 9Z" /></svg>`;
        default:
            return '';
    }
};

// --- Submission Logic ---

const saveLabel = () => {
    // 1. Map computed values into the form data object before submission
    form.signal_word = requiredSignalWord.value;
    form.required_pictograms = requiredPictograms.value;
    form.hazard_statements = requiredHStatements.value;
    form.precautionary_statements = requiredPStatements.value;
    form.supplementary_info = supplementaryInformation.value;
    form.ingredients_json = activeIngredients.value; // Store the raw ingredients used

    // 2. Map form keys to match PHP validation keys
    const submissionPayload = {
        product_id: form.productId,
        product_name: form.productName,
        concentration_percent: form.concentration,
        supplier_name: form.supplierName,
        supplier_address: form.supplierAddress,
        supplier_phone: form.supplierPhone,

        signal_word: form.signal_word,
        required_pictograms: form.required_pictograms,
        hazard_statements: form.hazard_statements,
        precautionary_statements: form.precautionary_statements,
        supplementary_info: form.supplementary_info,
        ingredients_json: form.ingredients_json,
    };


    // 3. Submit the form
    form.post(route('admin.clp-labels.store'), {
        data: submissionPayload,
        preserveScroll: true,
        // Reset only the temporary calculated fields and ingredient selection on success
        onSuccess: () => {
            form.reset('selectedIngredientIds');
        },
    });
};

</script>

<template>
    <AdminLayout>
        <Head title="CLP Label Generator" />

        <!-- Success Notification -->
        <div v-if="props.flash?.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ props.flash.success }}</p>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">CLP Label Generator</h2>
            <Link
                :href="route('admin.products.index')"
                class="text-sm font-semibold text-blue-500 hover:text-blue-700 transition"
            >
                &larr; Back to Products
            </Link>
        </div>

        <!-- Main Content Grid: Inputs (1/3) and Preview (2/3) -->
        <form @submit.prevent="saveLabel" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 1. CLP Control Panel (Inputs) -->
            <div class="lg:col-span-1 p-6 rounded-lg border-2 border-copy bg-foreground shadow-lg h-full space-y-5">
                <h2 class="text-xl font-bold mb-4 text-copy">Label Data & Ingredients</h2>
                <p class="text-copy-light mb-6 text-sm">
                    Enter product details and select ingredients to generate and save the CLP label.
                </p>


                <div class="space-y-4 p-4 border border-copy-light/50 rounded-lg bg-secondary-light">
                    <h3 class="font-bold text-copy">Product Information</h3>

                    <!-- Product Selection -->
                    <div v-if="props.products?.length">
                        <label for="product_id" class="block text-sm font-medium text-copy mb-1">Link to Product</label>
                        <select
                            id="product_id"
                            v-model="form.productId"
                            class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-white text-copy focus:ring-primary focus:border-primary transition"
                            :class="{'border-error': form.errors.product_id}"
                        >
                            <option :value="null">-- Select Product (Optional) --</option>
                            <option v-for="p in props.products" :key="p.id" :value="p.id">{{ p.name }} (ID: {{ p.id }})</option>
                        </select>
                        <div v-if="form.errors.product_id" class="text-error text-xs mt-1">{{ form.errors.product_id }}</div>
                    </div>

                    <div>
                        <label for="product_name" class="block text-sm font-medium text-copy mb-1">Product Name</label>
                        <input type="text" id="product_name" v-model="form.productName" class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-white text-copy focus:ring-primary focus:border-primary transition" :class="{'border-error': form.errors.product_name}">
                        <div v-if="form.errors.product_name" class="text-error text-xs mt-1">{{ form.errors.product_name }}</div>
                    </div>
                    <div>
                        <label for="concentration" class="block text-sm font-medium text-copy mb-1">Concentration (%)</label>
                        <input type="number" id="concentration" v-model.number="form.concentration" min="0" max="100" step="0.01" class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-white text-copy focus:ring-primary focus:border-primary transition" :class="{'border-error': form.errors.concentration_percent}">
                        <div v-if="form.errors.concentration_percent" class="text-error text-xs mt-1">{{ form.errors.concentration_percent }}</div>
                    </div>
                </div>

                <!-- Supplier Info -->
                <div class="space-y-4 p-4 border border-copy-light/50 rounded-lg bg-secondary-light">
                    <h3 class="font-bold text-copy">Supplier Details</h3>
                    <div>
                        <label for="supplier_name" class="block text-sm font-medium text-copy mb-1">Supplier Name</label>
                        <input type="text" id="supplier_name" v-model="form.supplierName" class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-white text-copy focus:ring-primary focus:border-primary transition" :class="{'border-error': form.errors.supplier_name}">
                        <div v-if="form.errors.supplier_name" class="text-error text-xs mt-1">{{ form.errors.supplier_name }}</div>
                    </div>
                    <div>
                        <label for="supplier_address" class="block text-sm font-medium text-copy mb-1">Address</label>
                        <input type="text" id="supplier_address" v-model="form.supplierAddress" class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-white text-copy focus:ring-primary focus:border-primary transition" :class="{'border-error': form.errors.supplier_address}">
                        <div v-if="form.errors.supplier_address" class="text-error text-xs mt-1">{{ form.errors.supplier_address }}</div>
                    </div>
                    <div>
                        <label for="supplier_phone" class="block text-sm font-medium text-copy mb-1">Contact Phone</label>
                        <input type="text" id="supplier_phone" v-model="form.supplierPhone" class="w-full px-3 py-2 border border-copy-light/50 rounded-lg bg-white text-copy focus:ring-primary focus:border-primary transition" :class="{'border-error': form.errors.supplier_phone}">
                        <div v-if="form.errors.supplier_phone" class="text-error text-xs mt-1">{{ form.errors.supplier_phone }}</div>
                    </div>
                </div>

                <!-- Ingredient Selection -->
                <div class="space-y-3 p-4 border border-copy-light/50 rounded-lg bg-secondary-light">
                    <h3 class="font-bold text-copy">Hazardous Ingredients</h3>
                    <div v-for="ing in mockIngredients" :key="ing.id" class="flex items-center">
                        <input
                            type="checkbox"
                            :id="`ing-${ing.id}`"
                            :value="ing.id"
                            v-model="form.selectedIngredientIds"
                            class="w-4 h-4 text-primary bg-white border-copy-light/50 rounded focus:ring-primary transition"
                        >
                        <label :for="`ing-${ing.id}`" class="ml-2 text-sm text-copy cursor-pointer">
                            {{ ing.name }} <span class="text-copy-light">({{ ing.signal_word }})</span>
                        </label>
                    </div>
                </div>

                <!-- Save Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 border-2 border-copy transition relative -m-0.5 font-bold bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ form.processing ? 'Saving...' : 'Save Generated Label' }}
                </button>
                <div v-if="form.errors.hazard_statements" class="text-error text-xs mt-1">
                    {{ form.errors.hazard_statements }} (Must select ingredients if saving hazard data)
                </div>
            </div>

            <!-- 2. Live Label Preview (Visualization) -->
            <div class="lg:col-span-2 relative p-6 rounded-lg border-2 border-copy bg-secondary-light shadow-lg min-h-[600px] flex justify-center items-start">

                <div class="w-full max-w-sm p-4 bg-white border-2 border-black shadow-xl" :class="{'border-dashed border-copy-light/50 text-copy-light': requiredHStatements.length === 0}">

                    <h3 class="text-lg font-extrabold text-center uppercase mb-3 text-copy">{{ form.productName || 'PRODUCT NAME' }}</h3>

                    <!-- Signal Word & Pictograms -->
                    <div v-if="requiredHStatements.length > 0" class="flex items-center border-b border-black pb-3 mb-3">
                        <div class="flex-shrink-0 flex space-x-2">
                            <div v-for="picto in requiredPictograms" :key="picto" class="size-16 border-4 border-red-600 bg-white p-1 text-black font-bold flex items-center justify-center">
                                <span v-html="getPictogramSvg(picto)" class="size-10 text-red-600"></span>
                            </div>
                        </div>
                        <div class="ml-4 flex-grow">
                            <h4 class="text-3xl font-black uppercase text-black leading-none">{{ requiredSignalWord }}</h4>
                            <div v-if="form.errors.signal_word" class="text-error text-xs mt-1">{{ form.errors.signal_word }}</div>
                        </div>
                    </div>
                    <div v-else class="text-center p-3 border border-dashed border-copy-light rounded mb-3">
                        No CLP hazard information required based on selection.
                    </div>


                    <!-- H & P Statements -->
                    <div v-if="requiredHStatements.length > 0" class="text-xs text-black space-y-2 mb-3">
                        <h5 class="font-bold uppercase text-red-600">Hazard Statements:</h5>
                        <ul class="list-none space-y-0.5">
                            <li v-for="h in requiredHStatements" :key="h" class="flex">
                                <span class="font-bold mr-1">{{ h }}:</span>
                                <span>{{ mockStatements[h] || 'Statement not found' }}</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="requiredPStatements.length > 0" class="text-xs text-black space-y-2 mb-3">
                        <h5 class="font-bold uppercase">Precautionary Statements:</h5>
                        <ul class="list-none space-y-0.5">
                            <li v-for="p in requiredPStatements" :key="p" class="flex">
                                <span class="font-bold mr-1">{{ p }}:</span>
                                <span>{{ mockStatements[p] || 'Statement not found' }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Supplementary Info -->
                    <div class="text-xs text-black border-t border-black pt-2 mt-2">
                        <h5 class="font-bold uppercase mb-1">Supplementary Information:</h5>
                        <p class="mb-1">{{ supplementaryInformation }}</p>
                    </div>

                    <!-- Supplier Details -->
                    <div class="text-[10px] text-black border-t border-black pt-2 mt-2">
                        <h5 class="font-bold uppercase mb-1">Supplier:</h5>
                        <p>{{ form.supplierName || 'SUPPLIER NAME' }}</p>
                        <p>{{ form.supplierAddress || 'Supplier Address Line 1' }}</p>
                        <p>Tel: {{ form.supplierPhone || '0000 000 000' }}</p>
                    </div>
                </div>
            </div>

        </form>
    </AdminLayout>
</template>
