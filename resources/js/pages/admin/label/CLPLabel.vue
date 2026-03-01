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

interface CLPLabel {
    id: number;
    product?: Product;
    created_at: string;
};

const props = defineProps<{
    products: Product[];
    recentLabels: CLPLabel[];
    flash?: {
        success?: string;
    };
}>();

const form = ref({
    productId: '',
    signalWord: '',
    pictograms: [],
    hazardStatements: [] as string[],
});

const loading = ref(false);

const generateFromProduct = async () => {
    if (!form.value.productId) return;
    loading.value = true;

    try {
        const response = await axios.get(
            route('admin.clp-labels.calculate', form.value.productId)
        );

        const data = response.data;

        form.value.signalWord = data.signal_word ?? '';
        form.value.pictograms = data.required_pictograms ?? [];
        form.value.hazardStatements = data.hazard_statements ?? [];

    } catch (error) {
        console.error(error);
        alert('Failed to calculate CLP.');
    }

    loading.value = false;
};

const pictogramMap = {
    exclamation: '/images/clp/exclamation.png',
    flame: '/images/clp/flame.png',
    corrosion: '/images/clp/corrosion.png',
    environment: '/images/clp/environment.png'
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

        <div class="p-6 max-w-4xl mx-auto">
            <!-- Product Selection -->
            <div class="mb-6">
                <label class="block font-medium mb-2">Select Product</label>
                <select v-model="form.productId" @change="generateFromProduct" class="w-full border rounded p-2">
                    <option value="">Choose product...</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.name }}
                    </option>
                </select>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="mb-4 text-gray-500">
                Calculating CLP...
            </div>

            <!-- Label Preview -->
            <div v-if="form.signalWord" class="border-2 border-black p-6 bg-white">

                <h2 class="text-xl font-bold mb-4 text-center">
                    {{ form.signalWord }}
                </h2>

                <!-- Pictograms -->
                <div class="flex gap-4 justify-center mb-4">
                    <img v-for="pic in form.pictograms" :key="pic" :src="pictogramMap[pic]"
                        class="w-16 h-16 object-contain" />
                </div>

                <!-- Hazard Statements -->
                <div class="whitespace-pre-line text-sm text-center">
                    {{ form.hazardStatements.join('\n') }}
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
img {
    border: 2px solid black;
}
</style>
