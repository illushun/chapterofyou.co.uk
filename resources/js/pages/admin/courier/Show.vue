<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Courier {
    id: number;
    name: string;
    type: 'Royal Mail' | 'FedEx' | 'Evri' | 'DPD';
    status: 'enabled' | 'disabled';
    cost: number;
    created_at: string;
}

const props = defineProps<{
    courier: Courier;
}>();

const formatCurrency = (amount: number | string | null | undefined): string => {
    const numericAmount = Number(amount) || 0;
    return `£${numericAmount.toFixed(2)}`;
};

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const getStatusClasses = (status: Courier['status']) => {
    switch (status) {
        case 'enabled':
            return 'bg-green-500/20 text-green-700 border border-green-700';
        case 'disabled':
            return 'bg-red-500/20 text-error border border-error-dark';
        default:
            return 'bg-gray-500/20 text-gray-700 border border-gray-700';
    }
};
</script>

<template>
    <AdminLayout>

        <Head :title="`Courier #${courier.id}`" />

        <div class="flex justify-between items-start mb-6 border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Courier {{ courier.id }}</h2>
                <p class="text-copy-light">Created at {{ formatDate(courier.created_at) }}</p>
            </div>
            <span
                :class="['px-4 py-1.5 rounded-full text-lg font-bold uppercase border-2', getStatusClasses(courier.status)]">
                {{ courier.status }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Courier Details
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-copy">
                            <div><span class="font-semibold">Name:</span> {{ courier.name }}
                            </div>
                            <div><span class="font-semibold">Type:</span> {{ courier.type }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="sticky top-8 rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy-light pb-3">Actions</h3>

                        <button
                            class="mt-6 w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg bg-secondary-light hover:bg-secondary text-copy"
                            disabled>
                            Update Courier (Future Feature)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
