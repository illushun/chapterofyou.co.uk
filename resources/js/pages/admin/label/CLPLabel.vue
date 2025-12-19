<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import html2pdf from 'html2pdf.js';

// Types
interface Product {
    id: number;
    name: string;
}

const props = defineProps<{
    products?: Product[];
    flash?: { success?: string };
}>();

const form = useForm({
    productId: null as number | null,
    productName: '',
    allergenInfo: '',
    signalWord: null as 'Danger' | 'Warning' | null,
    pictograms: [] as string[],
    warningStatements: '',
    precautionaryStatements: '',
    massVolume: '',
    units: 'g',
    supplierName: '',
    supplierAddress: '',
    supplierPhone: '',
});

const saveLabel = () => {
    form.post(route('admin.clp-labels.store'), {
        data: {
            product_id: form.productId,
            product_name: form.productName,
            concentration_percent: 0, // Set as needed
            supplier_name: form.supplierName,
            supplier_address: form.supplierAddress,
            supplier_phone: form.supplierPhone,
            signal_word: form.signalWord,
            required_pictograms: form.pictograms,
            hazard_statements: form.warningStatements.split('\n').filter(Boolean),
            precautionary_statements: form.precautionaryStatements.split('\n').filter(Boolean),
            supplementary_info: form.allergenInfo,
            ingredients_json: [], // Fill as needed
        },
        onSuccess: () => form.reset(),
    });
};

const exportPdf = () => {
    const labelElement = document.getElementById('label-preview');
    if (labelElement) {
        html2pdf().from(labelElement).save(`${form.productName || 'label'}.pdf`);
    }
};

// SVGs for pictograms
const pictogramSvgs: Record<string, string> = {
    exclamation: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>`,
    health: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 0 1-9 9m9-9A9 9 0 0 0 3 12m18 0h-3M3 12h3M3 12A9 9 0 0 1 12 3m-4.5 9h9" /></svg>`,
    environment: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 0 0 9-9c0-.447-.035-.884-.105-1.312M12 21V12a9 9 0 0 1-9-9 9 9 0 0 0 9 9Z" /></svg>`,
};
</script>

<template>
    <AdminLayout>

        <Head title="CLP Label Generator" />

        <div v-if="props.flash?.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg"
            role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ props.flash.success }}</p>
        </div>

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">CLP Label Generator</h2>
            <Link :href="route('admin.products.index')"
                class="text-sm font-semibold text-blue-500 hover:text-blue-700 transition">
            &larr; Back to Products
            </Link>
        </div>

        <form @submit.prevent="saveLabel" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Inputs -->
            <div class="lg:col-span-1 p-6 rounded-lg border-2 border-copy bg-foreground h-full space-y-5">
                <h2 class="text-xl font-bold mb-4 text-copy">Label Data</h2>
                <div class="space-y-4">
                    <!-- Product Selection -->
                    <div v-if="props.products?.length">
                        <label class="block text-sm font-medium mb-1">Link to Product</label>
                        <select v-model="form.productId" class="w-full px-3 py-2 border rounded-lg">
                            <option :value="null">-- Select Product (Optional) --</option>
                            <option v-for="p in props.products" :key="p.id" :value="p.id">{{ p.name }} (ID: {{ p.id }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Product Name</label>
                        <input type="text" v-model="form.productName" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Allergen Information</label>
                        <input type="text" v-model="form.allergenInfo" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Signal Word</label>
                        <select v-model="form.signalWord" class="w-full px-3 py-2 border rounded-lg">
                            <option value="">Select Signal Word</option>
                            <option value="Danger">Danger</option>
                            <option value="Warning">Warning</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pictograms</label>
                        <div class="flex gap-4">
                            <label v-for="picto in ['exclamation', 'health', 'environment']" :key="picto"
                                class="flex items-center gap-1">
                                <input type="checkbox" :value="picto" v-model="form.pictograms" />
                                <span v-html="pictogramSvgs[picto]" class="w-6 h-6"></span>
                                {{ picto }}
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Warning Statements</label>
                        <textarea v-model="form.warningStatements" class="w-full px-3 py-2 border rounded-lg"
                            rows="2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Precautionary Statements</label>
                        <textarea v-model="form.precautionaryStatements" class="w-full px-3 py-2 border rounded-lg"
                            rows="2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Mass / Volume</label>
                        <input type="text" v-model="form.massVolume" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Units</label>
                        <select v-model="form.units" class="w-full px-3 py-2 border rounded-lg">
                            <option value="g">Grams (g)</option>
                            <option value="ml">Milliliters (ml)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Supplier Name</label>
                        <input type="text" v-model="form.supplierName" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Supplier Address</label>
                        <input type="text" v-model="form.supplierAddress" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Supplier Phone</label>
                        <input type="text" v-model="form.supplierPhone" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>Label Shape: Rectangle (76mm x 50mm)</div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full px-4 py-2 border-2 border-copy font-bold bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ form.processing ? 'Saving...' : 'Save Label' }}
                    </button>
                    <button type="button" @click="exportPdf"
                        class="w-full px-4 py-2 border-2 border-copy font-bold bg-secondary text-black rounded-lg mt-2">
                        Export as PDF
                    </button>
                </div>
            </div>

            <!-- Label Preview -->
            <div class="lg:col-span-2 flex justify-center items-start">
                <div id="label-preview"
                    class="w-full max-w-sm p-4 bg-white border-2 border-black shadow-xl min-h-[300px]">
                    <h3 class="text-lg font-extrabold text-center uppercase mb-3">{{ form.productName || 'PRODUCT NAME'
                    }}</h3>
                    <div class="flex items-center border-b border-black pb-3 mb-3">
                        <div class="flex gap-2">
                            <span v-for="picto in form.pictograms" :key="picto" v-html="pictogramSvgs[picto]"
                                class="w-10 h-10 border-2 border-red-600 bg-white p-1"></span>
                        </div>
                        <div class="ml-4 flex-grow">
                            <h4 class="text-2xl font-black uppercase">{{ form.signalWord }}</h4>
                        </div>
                    </div>
                    <div class="text-xs text-black space-y-2 mb-2">
                        <div v-if="form.warningStatements">
                            <h5 class="font-bold uppercase text-red-600">Warning Statements:</h5>
                            <ul>
                                <li v-for="(w, idx) in form.warningStatements.split('\n')" :key="idx">{{ w }}</li>
                            </ul>
                        </div>
                        <div v-if="form.precautionaryStatements">
                            <h5 class="font-bold uppercase">Precautionary Statements:</h5>
                            <ul>
                                <li v-for="(p, idx) in form.precautionaryStatements.split('\n')" :key="idx">{{ p }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="text-xs text-black border-t border-black pt-2 mt-2">
                        <h5 class="font-bold uppercase mb-1">Allergen Information:</h5>
                        <p>{{ form.allergenInfo }}</p>
                    </div>
                    <div class="text-xs text-black border-t border-black pt-2 mt-2">
                        <h5 class="font-bold uppercase mb-1">Mass / Volume:</h5>
                        <p>{{ form.massVolume }} {{ form.units }}</p>
                    </div>
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
