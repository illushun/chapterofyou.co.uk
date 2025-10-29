<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

import NavBar from '@/components/NavBar.vue';

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
}

interface OrderItem {
    id: number;
    product_id: number;
    product: Product;
    quantity: number;
    product_cost: number;
    product_total: number;
}

interface Order {
    id: number;
    user_id: number | null;
    user: User | null;
    payment_intent_id: string;
    payment_type: string;
    first_name: string;
    last_name: string;
    email: string;
    telephone: string | null;
    cost_total: number;
    shipping_total: number;
    tax_total: number;
    grand_total: number;
    billing_line_1: string;
    billing_line_2: string | null;
    billing_city: string;
    billing_postcode: string;
    shipping_line_1: string;
    shipping_line_2: string | null;
    shipping_city: string;
    shipping_postcode: string;
    status: 'pending' | 'successful' | 'failed' | 'processing' | 'shipped' | 'cancelled';
    created_at: string;
    items: OrderItem[];
}

const props = defineProps<{
    order: Order;
}>();
</script>

<template>
    <NavBar />

    <Head title="Order " />

    <section class="py-20">

        <div class="min-h-[70vh] bg-background text-copy p-4 md:p-8 lg:p-12">

            <div class="max-w-3xl mx-auto text-center">

                <p># {{ order.id }}</p>

            </div>

        </div>

    </section>
</template>
