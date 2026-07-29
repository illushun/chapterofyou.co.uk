<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
        <Head title="Wishlists : Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Wishlists</h1>
                <p class="adm-sub">See what customers are saving</p>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="adm-stats" style="grid-template-columns: repeat(2, 1fr)">
            <div class="adm-stat">
                <p class="adm-stat-val">{{ totalItems }}</p>
                <p class="adm-stat-label">Total Saves</p>
            </div>
            <div class="adm-stat">
                <p class="adm-stat-val">{{ totalUsers }}</p>
                <p class="adm-stat-label">Users with Wishlists</p>
            </div>
        </div>

        <div class="wi-grid">
            <!-- Most wishlisted products -->
            <div class="adm-card adm-card--flush">
                <div style="padding: 1.1rem 1.25rem 0.85rem">
                    <h2
                        class="adm-card-title"
                        style="border: none; padding: 0; margin: 0"
                    >
                        Most Wishlisted Products
                    </h2>
                </div>
                <div class="adm-table-wrap" style="display: block">
                    <table class="adm-table">
                        <thead>
                            <tr class="adm-thead">
                                <th class="adm-th">Product</th>
                                <th class="adm-th">Price</th>
                                <th class="adm-th">Stock</th>
                                <th class="adm-th adm-th--right">Saves</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="popularProducts.length === 0">
                                <td
                                    colspan="4"
                                    class="adm-empty-sub"
                                    style="
                                        text-align: center;
                                        padding: 2rem 1rem;
                                    "
                                >
                                    No wishlist data yet.
                                </td>
                            </tr>
                            <tr
                                v-for="(p, i) in popularProducts"
                                :key="p.product_id"
                                class="adm-row"
                            >
                                <td class="adm-td">
                                    <div
                                        style="
                                            display: flex;
                                            align-items: center;
                                            gap: 0.6rem;
                                        "
                                    >
                                        <span
                                            class="wi-rank"
                                            :class="`wi-rank--${Math.min(i + 1, 3)}`"
                                            >{{ i + 1 }}</span
                                        >
                                        <div style="min-width: 0">
                                            <p
                                                style="
                                                    font-weight: 500;
                                                    color: var(--adm-ink);
                                                "
                                            >
                                                {{ p.name }}
                                            </p>
                                            <p class="adm-td--mono">
                                                {{ p.mpn }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="adm-td">{{ fmt(p.cost) }}</td>
                                <td class="adm-td">
                                    <span
                                        class="adm-stock"
                                        :class="
                                            p.stock_qty > 0
                                                ? 'adm-stock--ok'
                                                : 'adm-stock--nil'
                                        "
                                    >
                                        {{
                                            p.stock_qty > 0
                                                ? p.stock_qty + ' left'
                                                : 'Out of stock'
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="adm-td adm-td--right"
                                    style="
                                        font-weight: 700;
                                        color: var(--adm-stamp-deep);
                                    "
                                >
                                    {{ p.wishlist_count }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users with wishlists -->
            <div class="adm-card adm-card--flush">
                <div style="padding: 1.1rem 1.25rem 0.85rem">
                    <h2
                        class="adm-card-title"
                        style="border: none; padding: 0; margin: 0"
                    >
                        Users by Wishlist Size
                    </h2>
                </div>
                <div class="adm-table-wrap" style="display: block">
                    <table class="adm-table">
                        <thead>
                            <tr class="adm-thead">
                                <th class="adm-th">User</th>
                                <th class="adm-th adm-th--right">
                                    Saved Items
                                </th>
                                <th class="adm-th adm-th--right">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="users.length === 0">
                                <td
                                    colspan="3"
                                    class="adm-empty-sub"
                                    style="
                                        text-align: center;
                                        padding: 2rem 1rem;
                                    "
                                >
                                    No users with wishlists yet.
                                </td>
                            </tr>
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="adm-row"
                            >
                                <td class="adm-td">
                                    <p
                                        style="
                                            font-weight: 500;
                                            color: var(--adm-ink);
                                        "
                                    >
                                        {{ user.name }}
                                    </p>
                                    <p class="adm-sub">{{ user.email }}</p>
                                </td>
                                <td
                                    class="adm-td adm-td--right"
                                    style="font-weight: 600"
                                >
                                    {{ user.wishlist_count }}
                                </td>
                                <td class="adm-td adm-td--right">
                                    <Link
                                        :href="
                                            route('admin.wishlists.show', {
                                                user: user.id,
                                            })
                                        "
                                        class="adm-action adm-action--edit"
                                        >View</Link
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.wi-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .wi-grid {
        grid-template-columns: 1fr;
    }
}

.wi-rank {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 0.68rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--adm-paper);
    color: var(--adm-ink-dim);
}

.wi-rank--1 {
    background: var(--adm-warning-bg);
    color: var(--adm-warning);
}

.wi-rank--2 {
    background: var(--adm-paper-sunk);
    color: var(--adm-ink-dim);
}

.wi-rank--3 {
    background: var(--adm-stamp-dim);
    color: var(--adm-stamp-deep);
}
</style>
