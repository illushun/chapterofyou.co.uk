<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Receipt } from 'lucide-vue-next';

interface Voucher {
    id: number;
    code: string;
    type: 'percentage' | 'fixed';
    value: string;
    uses_count: number;
    max_uses: number | null;
}

interface Usage {
    id: number;
    created_at: string;
    discount_applied: string;
    order_total_before: string;
    order_total_after: string;
    ip_address: string | null;
    guest_email: string | null;
    user: { id: number; name: string; email: string } | null;
    order: {
        id: number;
        status: string;
        grand_total: string;
        created_at: string;
    } | null;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    voucher: Voucher;
    usages: Paginated<Usage>;
}>();

function fmt(val: string) {
    return '£' + parseFloat(val).toFixed(2);
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Usage Log: ${voucher.code} : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.vouchers.index')"
                        class="adm-breadcrumb a"
                        >Vouchers</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>Usage log</span>
                </div>
                <h1 class="adm-title">
                    <span
                        class="adm-td--mono"
                        style="color: var(--adm-stamp-deep)"
                        >{{ voucher.code }}</span
                    >
                </h1>
                <p class="adm-sub">
                    {{ usages.total }} use{{ usages.total !== 1 ? 's' : '' }}
                    recorded
                    <span v-if="voucher.max_uses !== null">
                        &middot; {{ voucher.uses_count }}/{{
                            voucher.max_uses
                        }}
                        total uses</span
                    >
                </p>
            </div>
            <Link
                :href="route('admin.vouchers.index')"
                class="adm-btn adm-btn--ghost adm-btn--sm"
                >Back to vouchers</Link
            >
        </div>

        <div class="adm-card adm-card--flush">
            <div
                v-if="usages.data.length"
                class="adm-table-wrap"
                style="display: block"
            >
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">Date</th>
                            <th class="adm-th">Customer</th>
                            <th class="adm-th">Order</th>
                            <th class="adm-th">Before</th>
                            <th class="adm-th">Discount</th>
                            <th class="adm-th">After</th>
                            <th class="adm-th">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="u in usages.data"
                            :key="u.id"
                            class="adm-row"
                        >
                            <td
                                class="adm-td"
                                style="
                                    color: var(--adm-ink-dim);
                                    font-size: 0.78rem;
                                    white-space: nowrap;
                                "
                            >
                                {{
                                    new Date(u.created_at).toLocaleString(
                                        'en-GB',
                                        {
                                            dateStyle: 'short',
                                            timeStyle: 'short',
                                        },
                                    )
                                }}
                            </td>

                            <td class="adm-td">
                                <template v-if="u.user">
                                    <span
                                        style="
                                            font-weight: 500;
                                            color: var(--adm-ink);
                                        "
                                        >{{ u.user.name }}</span
                                    >
                                    <p class="adm-sub">{{ u.user.email }}</p>
                                </template>
                                <template v-else-if="u.guest_email">
                                    <span style="color: var(--adm-ink)">{{
                                        u.guest_email
                                    }}</span>
                                    <p class="adm-sub">Guest</p>
                                </template>
                                <span
                                    v-else
                                    style="
                                        font-style: italic;
                                        color: var(--adm-ink-dim);
                                        font-size: 0.8rem;
                                    "
                                    >Unknown</span
                                >
                            </td>

                            <td class="adm-td">
                                <template v-if="u.order">
                                    <span
                                        class="adm-td--mono"
                                        style="
                                            font-weight: 600;
                                            color: var(--adm-ink);
                                        "
                                        >#{{ u.order.id }}</span
                                    >
                                    <p
                                        class="adm-sub"
                                        style="text-transform: capitalize"
                                    >
                                        {{ u.order.status }}
                                    </p>
                                </template>
                                <span
                                    v-else
                                    style="
                                        font-style: italic;
                                        color: var(--adm-ink-dim);
                                        font-size: 0.8rem;
                                    "
                                    >-</span
                                >
                            </td>

                            <td class="adm-td">
                                {{ fmt(u.order_total_before) }}
                            </td>
                            <td
                                class="adm-td"
                                style="
                                    font-weight: 700;
                                    color: var(--adm-success);
                                "
                            >
                                -{{ fmt(u.discount_applied) }}
                            </td>
                            <td class="adm-td adm-td--price">
                                {{ fmt(u.order_total_after) }}
                            </td>
                            <td class="adm-td adm-td--mono">
                                {{ u.ip_address ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="adm-empty">
                <div class="adm-empty-icon">
                    <Receipt :size="28" :stroke-width="1.5" />
                </div>
                <p class="adm-empty-title">No usages recorded yet</p>
                <p class="adm-empty-sub">
                    Uses of this voucher will appear here.
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="usages.last_page > 1" class="adm-pagination">
            <div class="adm-page-btns">
                <a
                    v-for="page in usages.last_page"
                    :key="page"
                    :href="
                        route('admin.vouchers.usage', {
                            voucher: voucher.id,
                            page,
                        })
                    "
                    class="adm-page-btn"
                    :class="{
                        'adm-page-btn--active': page === usages.current_page,
                    }"
                    >{{ page }}</a
                >
            </div>
        </div>
    </AdminLayout>
</template>
