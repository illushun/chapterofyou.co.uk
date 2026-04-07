<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps<{
    giftVouchers: {
        data: Array<{
            id: number;
            delivery_type: 'email' | 'physical';
            amount: string;
            recipient_name: string;
            recipient_email: string | null;
            personal_message: string | null;
            fulfilled_at: string | null;
            created_at: string;
            order: { id: number; first_name: string; last_name: string; };
            voucher: { code: string; valid_until: string; } | null;
        }>;
        links: any[];
        meta: any;
    };
}>();

function markDispatched(id: number) {
    router.post(route('admin.gift-vouchers.dispatch', id), {}, {
        preserveScroll: true,
    });
}

function resendEmail(id: number) {
    router.post(route('admin.gift-vouchers.resend', id), {}, {
        preserveScroll: true,
    });
}

const fmt = (v: string | number) => `£${Number(v).toFixed(2)}`;
</script>

<template>
    <AdminLayout>
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Gift Vouchers</h1>
                <p class="adm-sub">Manage purchased gift vouchers and track fulfilment.</p>
            </div>
        </div>

        <!-- Stats strip -->
        <div class="adm-stats" style="margin-bottom: 1.5rem;">
            <div class="adm-stat--blush">
                <div class="adm-stat-val">{{ giftVouchers.meta?.total ?? giftVouchers.data.length }}</div>
                <div class="adm-stat-label">Total Sold</div>
            </div>
            <div class="adm-stat--lav">
                <div class="adm-stat-val">{{giftVouchers.data.filter(g => !g.fulfilled_at).length}}</div>
                <div class="adm-stat-label">Awaiting Fulfilment</div>
            </div>
            <div class="adm-stat--sage">
                <div class="adm-stat-val">{{giftVouchers.data.filter(g => g.delivery_type === 'physical').length}}
                </div>
                <div class="adm-stat-label">Physical Vouchers</div>
            </div>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead class="adm-thead">
                    <tr>
                        <th class="adm-th">Order</th>
                        <th class="adm-th">Purchaser</th>
                        <th class="adm-th">Recipient</th>
                        <th class="adm-th">Amount</th>
                        <th class="adm-th">Type</th>
                        <th class="adm-th">Code</th>
                        <th class="adm-th">Status</th>
                        <th class="adm-th">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="gv in giftVouchers.data" :key="gv.id" class="adm-row">
                        <td class="adm-td adm-td--mono">#{{ gv.order?.id }}</td>
                        <td class="adm-td">{{ gv.order?.first_name }} {{ gv.order?.last_name }}</td>
                        <td class="adm-td">
                            <div>{{ gv.recipient_name }}</div>
                            <div v-if="gv.recipient_email" style="font-size:0.75rem; color:#9a7070;">{{
                                gv.recipient_email }}</div>
                        </td>
                        <td class="adm-td adm-td--price">{{ fmt(gv.amount) }}</td>
                        <td class="adm-td">
                            <span :class="gv.delivery_type === 'email' ? 'adm-badge--lav' : 'adm-badge--peach'">
                                {{ gv.delivery_type === 'email' ? 'E-Voucher' : 'Physical' }}
                            </span>
                        </td>
                        <td class="adm-td adm-td--mono" style="font-size:0.78rem;">
                            {{ gv.voucher?.code ?? '—' }}
                        </td>
                        <td class="adm-td">
                            <span v-if="gv.fulfilled_at" class="adm-badge--on">Fulfilled</span>
                            <span v-else class="adm-badge--warn">Pending</span>
                        </td>
                        <td class="adm-td adm-td--actions">
                            <!-- Physical: mark dispatched -->
                            <button v-if="gv.delivery_type === 'physical' && !gv.fulfilled_at"
                                @click="markDispatched(gv.id)" class="adm-btn adm-btn--sm adm-btn--primary">
                                Mark Dispatched
                            </button>
                            <!-- E-voucher: resend -->
                            <button v-if="gv.delivery_type === 'email'" @click="resendEmail(gv.id)"
                                class="adm-btn adm-btn--sm adm-btn--ghost">
                                Resend Email
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!giftVouchers.data.length">
                        <td colspan="8" class="adm-td"
                            style="text-align:center; padding: 3rem; color: #9a7070; font-style: italic;">
                            No gift vouchers sold yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="giftVouchers.links?.length > 3" class="adm-pagination">
            <template v-for="link in giftVouchers.links" :key="link.label">
                <button v-if="link.url" @click="router.visit(link.url)" class="adm-page-btn"
                    :class="{ 'adm-page-btn--active': link.active }" v-html="link.label" />
                <span v-else class="adm-page-btn" style="opacity:0.35;" v-html="link.label" />
            </template>
        </div>
    </AdminLayout>
</template>
