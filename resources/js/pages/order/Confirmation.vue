<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

import NavBar from '@/components/NavBar.vue';

const IconArrowLeft = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>`;
const IconCheckCircle = `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>`;
const IconUser = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;

const props = defineProps<{
    id: number;
    request: {
        total: string;
        email: string;
    };
    total: number;
}>();

const formatCurrency = (amount: number | string): string => {
    const numAmount = Number(amount);
    if (isNaN(numAmount)) return '£0.00';
    return `£${numAmount.toFixed(2)}`;
};

const totalAmount = computed(() => {
    const rawTotal = props.total ?? '0.00';
    return formatCurrency(rawTotal);
});

const customerEmail = computed(() => props.request?.email ?? 'your_email@example.com');

</script>

<template>
    <NavBar />

    <Head title="Order Confirmed" />

    <section class="py-20">

        <div class="min-h-[70vh] bg-background text-copy p-4 md:p-8 lg:p-12">
            <div class="max-w-3xl mx-auto text-center">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl p-0.5">
                    <div class="relative rounded-xl border-2 border-copy bg-foreground p-8 sm:p-12">

                        <div class="text-green-600 mx-auto mb-6 flex items-center justify-center" v-html="IconCheckCircle"></div>

                        <h1 class="text-4xl sm:text-5xl font-black text-copy mb-4">
                            Order Successfully Placed!
                        </h1>

                        <p class="text-lg text-copy-lighter mb-6">
                            Thank you for your order. Your payment has been successfully processed.
                        </p>

                        <div class="bg-background border-2 border-copy rounded-lg p-5 mb-8">
                            <div class="flex justify-between items-center mb-3 border-b border-copy-light pb-3">
                                <span class="text-xl font-bold text-copy-lighter">Order ID:</span>
                                <span class="text-3xl font-extrabold text-primary">#{{ props.id }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-copy-lighter">Total Paid:</span>
                                <span class="text-3xl font-extrabold text-primary">{{ totalAmount }}</span>
                            </div>
                        </div>

                        <p class="text-sm text-copy-lighter mb-10">
                            A confirmation email has been sent to **{{ customerEmail }}**. Please check your inbox (and spam folder) for details.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="/products" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 border-2 border-copy rounded-lg text-lg font-bold text-secondary-content bg-secondary hover:bg-secondary-dark transition">
                                <div v-html="IconArrowLeft"></div>
                                Continue Shopping
                            </a>

                            <a href="/account" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 border-2 border-copy rounded-lg text-lg font-bold text-copy hover:bg-copy-light/20 transition">
                                <div v-html="IconUser"></div>
                                My Account
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section>

</template>
