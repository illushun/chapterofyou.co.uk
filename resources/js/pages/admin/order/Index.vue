<script setup lang="ts">
import { useAdmin } from '@/composables/useAdmin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PackageSearch } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Order {
    id: number;
    user_id: number | null;
    user: User | null;
    grand_total: number;
    status:
        | 'pending'
        | 'successful'
        | 'failed'
        | 'processing'
        | 'shipped'
        | 'cancelled';
    created_at: string;
}

interface OrdersPaginated {
    data: Order[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

defineProps<{
    orders: OrdersPaginated;
}>();

const { paginate, fmtCurrency } = useAdmin();

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
</script>

<template>
    <AdminLayout>
        <Head title="Orders : Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Orders</h1>
                <p class="adm-sub">Recent orders placed through the store</p>
            </div>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom: 1.5rem">
            <!-- Desktop table -->
            <div v-if="orders.data.length" class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">Order</th>
                            <th class="adm-th">Customer</th>
                            <th class="adm-th">Date</th>
                            <th class="adm-th">Total</th>
                            <th class="adm-th">Status</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="adm-row"
                        >
                            <td class="adm-td adm-td--mono">
                                COY-{{ order.id }}
                            </td>
                            <td class="adm-td">
                                <span v-if="order.user">
                                    <Link
                                        :href="
                                            route(
                                                'admin.users.show',
                                                order.user.id,
                                            )
                                        "
                                        class="or-name-link"
                                        >{{ order.user.name }}</Link
                                    >
                                    <p
                                        class="adm-sub"
                                        style="margin-top: 0.1rem"
                                    >
                                        {{ order.user.email }}
                                    </p>
                                </span>
                                <span
                                    v-else
                                    class="adm-td--mono"
                                    style="font-style: italic"
                                    >Guest</span
                                >
                            </td>
                            <td
                                class="adm-td"
                                style="color: var(--adm-ink-dim)"
                            >
                                {{ formatDate(order.created_at) }}
                            </td>
                            <td class="adm-td adm-td--price">
                                {{ fmtCurrency(order.grand_total) }}
                            </td>
                            <td class="adm-td">
                                <span
                                    class="adm-badge"
                                    :class="{
                                        'adm-badge--on':
                                            order.status === 'successful' ||
                                            order.status === 'shipped',
                                        'adm-badge--lav':
                                            order.status === 'processing',
                                        'adm-badge--warn':
                                            order.status === 'pending',
                                        'adm-badge--red':
                                            order.status === 'cancelled' ||
                                            order.status === 'failed',
                                    }"
                                    >{{ order.status }}</span
                                >
                            </td>
                            <td class="adm-td adm-td--actions">
                                <Link
                                    :href="route('admin.orders.show', order.id)"
                                    class="adm-action adm-action--edit"
                                >
                                    View</Link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div v-if="orders.data.length" class="adm-mob-list">
                <Link
                    v-for="order in orders.data"
                    :key="order.id"
                    :href="route('admin.orders.show', order.id)"
                    class="adm-mob-card"
                    style="text-decoration: none"
                >
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                        "
                    >
                        <span
                            class="adm-td--mono"
                            style="font-weight: 600; color: var(--adm-ink)"
                            >COY-{{ order.id }}</span
                        >
                        <span
                            class="adm-badge"
                            :class="{
                                'adm-badge--on':
                                    order.status === 'successful' ||
                                    order.status === 'shipped',
                                'adm-badge--lav': order.status === 'processing',
                                'adm-badge--warn': order.status === 'pending',
                                'adm-badge--red':
                                    order.status === 'cancelled' ||
                                    order.status === 'failed',
                            }"
                            >{{ order.status }}</span
                        >
                    </div>
                    <div>
                        <p class="adm-label--sm">Customer</p>
                        <p v-if="order.user" style="color: var(--adm-ink)">
                            {{ order.user.name }}
                        </p>
                        <p
                            v-else
                            style="
                                font-style: italic;
                                color: var(--adm-ink-dim);
                            "
                        >
                            Guest checkout
                        </p>
                    </div>
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                        "
                    >
                        <div>
                            <p class="adm-label--sm">Total</p>
                            <p
                                class="adm-td--price"
                                style="color: var(--adm-ink)"
                            >
                                {{ fmtCurrency(order.grand_total) }}
                            </p>
                        </div>
                        <p
                            style="
                                font-size: 0.72rem;
                                color: var(--adm-ink-dim);
                                font-style: italic;
                            "
                        >
                            {{ formatDate(order.created_at) }}
                        </p>
                    </div>
                </Link>
            </div>

            <div v-if="!orders.data.length" class="adm-empty">
                <div class="adm-empty-icon">
                    <PackageSearch :size="28" :stroke-width="1.5" />
                </div>
                <p class="adm-empty-title">No orders yet</p>
                <p class="adm-empty-sub">
                    Orders placed through the store will appear here.
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="adm-pagination">
            <div class="adm-page-btns">
                <button
                    v-for="link in orders.links"
                    :key="link.label"
                    @click.prevent="paginate(link.url)"
                    :disabled="!link.url"
                    class="adm-page-btn"
                    :class="{ 'adm-page-btn--active': link.active }"
                    v-html="
                        link.label
                            .replace('&laquo; Previous', '←')
                            .replace('Next &raquo;', '→')
                    "
                ></button>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.or-name-link {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--adm-ink);
    text-decoration: none;
    transition: color 0.15s;
}

.or-name-link:hover {
    color: var(--adm-stamp-deep);
    text-decoration: underline;
}
</style>
