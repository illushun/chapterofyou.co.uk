<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

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
    order: { id: number; status: string; grand_total: string; created_at: string } | null;
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

        <Head :title="`Usage Log: ${voucher.code}`" />

        <div class="mb-6 flex items-center justify-between border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Usage Log: <span class="font-mono text-primary">{{ voucher.code
                }}</span></h2>
                <p class="text-copy-light mt-1">
                    {{ usages.total }} use{{ usages.total !== 1 ? 's' : '' }} recorded
                    <span v-if="voucher.max_uses !== null"> · {{ voucher.uses_count }}/{{ voucher.max_uses }} total
                        uses</span>
                </p>
            </div>
            <a :href="route('admin.vouchers.index')"
                class="rounded-lg border-2 border-copy px-4 py-2 text-sm font-medium text-copy-light hover:text-copy hover:bg-secondary-light transition">
                ← Back to vouchers
            </a>
        </div>

        <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b-2 border-copy-light text-left text-xs uppercase tracking-wider text-copy-light">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Before</th>
                            <th class="px-4 py-3">Discount</th>
                            <th class="px-4 py-3">After</th>
                            <th class="px-4 py-3">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="usages.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-copy-light italic">
                                No usages recorded yet.
                            </td>
                        </tr>
                        <tr v-for="u in usages.data" :key="u.id"
                            class="border-b border-copy-light last:border-b-0 hover:bg-secondary-light transition">
                            <td class="px-4 py-3 text-xs text-copy-light whitespace-nowrap">
                                {{ new Date(u.created_at).toLocaleString('en-GB', {
                                    dateStyle: 'short', timeStyle:
                                        'short'
                                }) }}
                            </td>

                            <td class="px-4 py-3">
                                <template v-if="u.user">
                                    <span class="font-medium text-copy">{{ u.user.name }}</span>
                                    <p class="text-xs text-copy-light">{{ u.user.email }}</p>
                                </template>
                                <template v-else-if="u.guest_email">
                                    <span class="text-copy">{{ u.guest_email }}</span>
                                    <p class="text-xs text-copy-light">Guest</p>
                                </template>
                                <span v-else class="text-copy-light italic text-xs">Unknown</span>
                            </td>

                            <td class="px-4 py-3">
                                <template v-if="u.order">
                                    <span class="font-mono font-medium text-copy">#{{ u.order.id }}</span>
                                    <p class="text-xs text-copy-light capitalize">{{ u.order.status }}</p>
                                </template>
                                <span v-else class="text-copy-light italic text-xs">—</span>
                            </td>

                            <td class="px-4 py-3 text-copy">{{ fmt(u.order_total_before) }}</td>
                            <td class="px-4 py-3 font-bold text-green-700">-{{ fmt(u.discount_applied) }}</td>
                            <td class="px-4 py-3 font-bold text-copy">{{ fmt(u.order_total_after) }}</td>
                            <td class="px-4 py-3 text-xs text-copy-light font-mono">{{ u.ip_address ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="usages.last_page > 1" class="mt-4 flex justify-center gap-2">
            <a v-for="page in usages.last_page" :key="page"
                :href="route('admin.vouchers.usage', { voucher: voucher.id, page })"
                class="rounded border px-3 py-1 text-sm transition" :class="page === usages.current_page
                    ? 'border-copy bg-copy text-foreground font-bold'
                    : 'border-copy-light text-copy-light hover:border-copy hover:text-copy'">{{ page }}</a>
        </div>
    </AdminLayout>
</template>
