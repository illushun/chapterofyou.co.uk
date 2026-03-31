<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

interface PopularProduct {
    product_id: number;
    name: string;
    mpn: string;
    cost: number;
    stock_qty: number;
    wishlist_count: number;
}

interface WishlistUser {
    id: number;
    name: string;
    email: string;
    wishlist_count: number;
}

defineProps<{
    popularProducts: PopularProduct[];
    users: WishlistUser[];
    totalItems: number;
    totalUsers: number;
}>();

const fmt = (v: number | string) => {
    const n = Number(v);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};
</script>

<template>
    <AdminLayout>

        <Head title="Wishlists" />

        <div class="mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Wishlists</h2>
            <p class="text-copy-light mt-1">See what customers are saving</p>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5 text-center">
                    <p class="text-xs uppercase tracking-wider text-copy-light mb-1">Total Saves</p>
                    <p class="text-4xl font-black text-primary">{{ totalItems }}</p>
                </div>
            </div>
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5 text-center">
                    <p class="text-xs uppercase tracking-wider text-copy-light mb-1">Users with Wishlists</p>
                    <p class="text-4xl font-black text-primary">{{ totalUsers }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Most wishlisted products -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden">
                    <div class="px-5 py-4 border-b-2 border-copy-light">
                        <h3 class="text-lg font-bold text-copy">Most Wishlisted Products</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-copy-light text-left text-xs uppercase tracking-wider text-copy-light">
                                <th class="px-4 py-3 font-medium">Product</th>
                                <th class="px-4 py-3 font-medium">Price</th>
                                <th class="px-4 py-3 font-medium">Stock</th>
                                <th class="px-4 py-3 font-medium text-right">Saves</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="popularProducts.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-copy-light italic">
                                    No wishlist data yet.
                                </td>
                            </tr>
                            <tr v-for="(p, i) in popularProducts" :key="p.product_id"
                                class="border-b border-copy-light last:border-b-0 hover:bg-secondary-light transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <!-- Rank badge -->
                                        <span
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black flex-shrink-0"
                                            :class="i === 0 ? 'bg-amber-100 text-amber-700' : i === 1 ? 'bg-gray-100 text-gray-600' : i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-secondary-light text-copy-light'">{{
                                            i + 1 }}</span>
                                        <div class="min-w-0">
                                            <p class="font-medium text-copy truncate">{{ p.name }}</p>
                                            <p class="text-xs text-copy-light font-mono">{{ p.mpn }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-copy">{{ fmt(p.cost) }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                        :class="p.stock_qty > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                        {{ p.stock_qty > 0 ? p.stock_qty + ' left' : 'Out of stock' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-black text-primary text-lg">
                                    {{ p.wishlist_count }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users with wishlists -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden">
                    <div class="px-5 py-4 border-b-2 border-copy-light">
                        <h3 class="text-lg font-bold text-copy">Users by Wishlist Size</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-copy-light text-left text-xs uppercase tracking-wider text-copy-light">
                                <th class="px-4 py-3 font-medium">User</th>
                                <th class="px-4 py-3 font-medium text-right">Saved Items</th>
                                <th class="px-4 py-3 font-medium text-right">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="users.length === 0">
                                <td colspan="3" class="px-4 py-8 text-center text-copy-light italic">
                                    No users with wishlists yet.
                                </td>
                            </tr>
                            <tr v-for="user in users" :key="user.id"
                                class="border-b border-copy-light last:border-b-0 hover:bg-secondary-light transition">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-copy">{{ user.name }}</p>
                                    <p class="text-xs text-copy-light">{{ user.email }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-copy">
                                    {{ user.wishlist_count }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a :href="route('admin.wishlists.show', { user: user.id })"
                                        class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy hover:bg-secondary-light transition">View</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
