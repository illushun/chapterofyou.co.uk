<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

interface WishlistItem {
    wishlist_id: number;
    product_id: number;
    name: string;
    mpn: string;
    cost: number;
    stock_qty: number;
    added_at: string;
}

interface WishlistUser {
    id: number;
    name: string;
    email: string;
}

defineProps<{
    user: WishlistUser;
    items: WishlistItem[];
}>();

const fmt = (v: number | string) => {
    const n = Number(v);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};
</script>

<template>
    <AdminLayout>

        <Head :title="`Wishlist: ${user.name}`" />

        <div class="mb-6 flex items-center justify-between border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">{{ user.name }}'s Wishlist</h2>
                <p class="text-copy-light mt-1">{{ user.email }} &nbsp;·&nbsp; {{ items.length }} item{{ items.length
                    !== 1 ? 's' : '' }}</p>
            </div>
            <a :href="route('admin.wishlists.index')"
                class="rounded-lg border-2 border-copy px-4 py-2 text-sm font-medium text-copy-light hover:text-copy hover:bg-secondary-light transition">
                ← All wishlists
            </a>
        </div>

        <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b-2 border-copy-light text-left text-xs uppercase tracking-wider text-copy-light">
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">MPN</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Stock</th>
                            <th class="px-4 py-3 font-medium">Saved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="items.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-copy-light italic">
                                This user has no wishlist items.
                            </td>
                        </tr>
                        <tr v-for="item in items" :key="item.wishlist_id"
                            class="border-b border-copy-light last:border-b-0 hover:bg-secondary-light transition">
                            <td class="px-4 py-3 font-medium text-copy">{{ item.name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-copy-light">{{ item.mpn }}</td>
                            <td class="px-4 py-3 text-copy">{{ fmt(item.cost) }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                    :class="item.stock_qty > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                    {{ item.stock_qty > 0 ? item.stock_qty + ' in stock' : 'Out of stock' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-copy-light">{{ item.added_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
