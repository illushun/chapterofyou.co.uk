<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    mpn: string;
    name: string;
    cost: number;
}

interface CartItem {
    id: number;
    product_id: number;
    product: Product;
    quantity: number;
}

interface Cart {
    id: number;
    user_id: number | null;
    user: User | null;
    session_id: string | null;
    expires_at: string | null;
    updated_at: string;
    items: CartItem[];
}

const props = defineProps<{
    cart: Cart;
}>();

const formatCurrency = (amount: number): string => `£${amount.toFixed(2)}`;

const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const subtotal = computed(() => {
    return props.cart.items.reduce((sum, item) => sum + (item.product.cost * item.quantity), 0);
});

const cartType = computed(() => props.cart.user_id ? 'Registered User Cart' : 'Guest Cart');
</script>

<template>
    <AdminLayout>
        <Head :title="`Cart #${cart.id}`" />

        <div class="mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Cart Details #{{ cart.id }}</h2>
            <p class="text-copy-light">Last updated: {{ formatDate(cart.updated_at) }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Cart Contents ({{ cart.items.length }} unique products)</h3>
                        <div v-if="cart.items.length">
                            <div v-for="item in cart.items" :key="item.id" class="flex items-center justify-between py-3 border-b border-copy-light/50 last:border-b-0">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-copy">{{ item.product.name }}</span>
                                    <span class="text-xs text-copy-light">MPN: {{ item.product.mpn }} | Unit Price: {{ formatCurrency(item.product.cost) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-lg block">{{ formatCurrency(item.product.cost * item.quantity) }}</span>
                                    <span class="text-sm text-copy-light">Qty: {{ item.quantity }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-copy-light italic">This cart is currently empty.</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="sticky top-8 rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy-light pb-3">Cart Status</h3>

                        <div class="space-y-3 text-copy text-lg">
                            <div class="flex justify-between">
                                <span>Type</span>
                                <span class="font-bold">{{ cartType }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Value</span>
                                <span class="text-2xl font-black text-primary">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-copy-light pt-3">
                                <span>Expires</span>
                                <span class="font-bold">{{ formatDate(cart.expires_at) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-copy-light text-sm">
                            <p v-if="cart.user">
                                <span class="font-semibold">Associated User:</span>
                                <Link :href="route('admin.users.show', cart.user_id)" class="text-primary hover:underline">
                                    {{ cart.user.name }} ({{ cart.user.email }})
                                </Link>
                            </p>
                            <p v-else>
                                <span class="font-semibold">Session ID:</span>
                                <span class="text-xs break-all">{{ cart.session_id || 'N/A' }}</span>
                            </p>
                        </div>

                        <button
                            class="mt-6 w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg bg-error-light hover:bg-error text-error-content"
                            disabled
                        >
                            Clear Cart (Future Feature)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
