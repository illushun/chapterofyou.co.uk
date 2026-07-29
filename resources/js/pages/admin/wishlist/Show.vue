<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';

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
        <Head :title="`${user.name}'s Wishlist : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.wishlists.index')"
                        class="adm-breadcrumb a"
                        >Wishlists</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ user.name }}</span>
                </div>
                <h1 class="adm-title">{{ user.name }}'s Wishlist</h1>
                <p class="adm-sub">
                    {{ user.email }} &middot; {{ items.length }} item{{
                        items.length !== 1 ? 's' : ''
                    }}
                </p>
            </div>
            <Link
                :href="route('admin.wishlists.index')"
                class="adm-btn adm-btn--ghost adm-btn--sm"
            >
                All wishlists
            </Link>
        </div>

        <div class="adm-card adm-card--flush">
            <div class="adm-table-wrap" style="display: block">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">Product</th>
                            <th class="adm-th">MPN</th>
                            <th class="adm-th">Price</th>
                            <th class="adm-th">Stock</th>
                            <th class="adm-th">Saved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="items.length === 0">
                            <td colspan="5" style="padding: 0">
                                <div class="adm-empty">
                                    <div class="adm-empty-icon">
                                        <Heart :size="28" :stroke-width="1.5" />
                                    </div>
                                    <p class="adm-empty-title">
                                        No wishlist items
                                    </p>
                                    <p class="adm-empty-sub">
                                        This user has not saved any products.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-for="item in items"
                            :key="item.wishlist_id"
                            class="adm-row"
                        >
                            <td class="adm-td" style="font-weight: 500">
                                {{ item.name }}
                            </td>
                            <td class="adm-td adm-td--mono">{{ item.mpn }}</td>
                            <td class="adm-td">{{ fmt(item.cost) }}</td>
                            <td class="adm-td">
                                <span
                                    class="adm-stock"
                                    :class="
                                        item.stock_qty > 0
                                            ? 'adm-stock--ok'
                                            : 'adm-stock--nil'
                                    "
                                >
                                    {{
                                        item.stock_qty > 0
                                            ? item.stock_qty + ' in stock'
                                            : 'Out of stock'
                                    }}
                                </span>
                            </td>
                            <td
                                class="adm-td"
                                style="
                                    color: var(--adm-ink-dim);
                                    font-size: 0.8rem;
                                "
                            >
                                {{ item.added_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
