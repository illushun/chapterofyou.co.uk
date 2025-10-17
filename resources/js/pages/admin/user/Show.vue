<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Address {
    id: number;
    type: 'shipping' | 'billing';
    is_default: boolean;
    line_1: string;
    line_2: string | null;
    city: string;
    postcode: string;
}

interface Order {
    id: number;
    grand_total: number;
    status: string;
    created_at: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
    addresses: Address[];
    orders: Order[];
}

const props = defineProps<{
    user: User;
}>();

const formatCurrency = (amount: number): string => `£${amount.toFixed(2)}`;

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });

const addressToString = (address: Address) => {
    return [address.line_1, address.line_2, address.city, address.postcode].filter(Boolean).join(', ');
};

const defaultShippingAddress = computed(() => props.user.addresses.find(a => a.type === 'shipping' && a.is_default));
const defaultBillingAddress = computed(() => props.user.addresses.find(a => a.type === 'billing' && a.is_default));
</script>

<template>
    <AdminLayout>
        <Head :title="`User: ${user.name}`" />

        <div class="flex justify-between items-start mb-6 border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">{{ user.name }}</h2>
                <p class="text-copy-light">Joined on {{ formatDate(user.created_at) }}</p>
            </div>
            <span :class="['px-4 py-1.5 rounded-full text-lg font-bold uppercase border-2', user.is_admin ? 'bg-primary-light text-primary border-primary-dark' : 'bg-gray-500/20 text-gray-700 border border-gray-700']">
                {{ user.is_admin ? 'Admin' : 'Customer' }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Account Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-copy">
                            <div><span class="font-semibold">Email:</span> <a :href="`mailto:${user.email}`" class="text-primary hover:underline">{{ user.email }}</a></div>
                            <div><span class="font-semibold">User ID:</span> #{{ user.id }}</div>
                            </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Default Addresses</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-copy">
                            <div>
                                <p class="font-bold mb-2 text-lg">Shipping</p>
                                <div v-if="defaultShippingAddress">
                                    <p>{{ defaultShippingAddress.line_1 }}</p>
                                    <p v-if="defaultShippingAddress.line_2">{{ defaultShippingAddress.line_2 }}</p>
                                    <p>{{ defaultShippingAddress.city }}</p>
                                    <p>{{ defaultShippingAddress.postcode }}</p>
                                </div>
                                <p v-else class="text-copy-light italic">No default shipping address found.</p>
                            </div>
                            <div>
                                <p class="font-bold mb-2 text-lg">Billing</p>
                                <div v-if="defaultBillingAddress">
                                    <p>{{ defaultBillingAddress.line_1 }}</p>
                                    <p v-if="defaultBillingAddress.line_2">{{ defaultBillingAddress.line_2 }}</p>
                                    <p>{{ defaultBillingAddress.city }}</p>
                                    <p>{{ defaultBillingAddress.postcode }}</p>
                                </div>
                                <p v-else class="text-copy-light italic">No default billing address found.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="sticky top-8 rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy-light pb-3">Recent Orders</h3>

                        <div v-if="user.orders.length" class="space-y-3">
                            <div v-for="order in user.orders" :key="order.id" class="flex justify-between items-center py-2 border-b border-copy-light/50 last:border-b-0">
                                <Link :href="route('admin.orders.show', order.id)" class="font-semibold text-primary hover:underline">
                                    #{{ order.id }}
                                </Link>
                                <div class="text-right">
                                    <span class="font-bold text-copy block">{{ formatCurrency(order.grand_total) }}</span>
                                    <span class="text-xs text-copy-light">{{ order.status }}</span>
                                </div>
                            </div>
                            <Link :href="route('admin.orders.index')" class="pt-2 w-full block text-center text-sm font-semibold text-primary hover:underline">
                                View All Orders (Future Feature)
                            </Link>
                        </div>
                        <p v-else class="text-copy-light italic">No recent orders found.</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
