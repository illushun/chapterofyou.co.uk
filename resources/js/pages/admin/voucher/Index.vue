<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Product { id: number; name: string; }

interface Voucher {
    id: number;
    code: string;
    description: string | null;
    type: 'percentage' | 'fixed';
    value: string;
    minimum_order_value: string | null;
    applies_to_all_products: boolean;
    stackable: boolean;
    new_customers_only: boolean;
    single_use_per_user: boolean;
    max_uses: number | null;
    uses_count: number;
    valid_from: string | null;
    valid_until: string | null;
    is_active: boolean;
    usages_count: number;
    products: Product[];
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    vouchers: Paginated<Voucher>;
}>();

const confirmingDelete = ref<number | null>(null);

function deleteVoucher(id: number) {
    router.delete(route('admin.vouchers.destroy', { voucher: id }), {
        preserveScroll: true,
        onSuccess: () => { confirmingDelete.value = null; },
    });
}

function formatDiscount(v: Voucher) {
    return v.type === 'percentage'
        ? `${v.value}%`
        : `£${parseFloat(v.value).toFixed(2)}`;
}

function statusBadge(v: Voucher): { label: string; class: string } {
    if (!v.is_active) return { label: 'Inactive', class: 'bg-gray-100 text-gray-600' };
    if (v.valid_until && new Date(v.valid_until) < new Date()) return { label: 'Expired', class: 'bg-red-100 text-red-700' };
    if (v.max_uses !== null && v.uses_count >= v.max_uses) return { label: 'Exhausted', class: 'bg-amber-100 text-amber-700' };
    return { label: 'Active', class: 'bg-green-100 text-green-700' };
}
</script>

<template>
    <AdminLayout>

        <Head title="Vouchers & Discounts" />

        <div class="mb-6 flex items-center justify-between border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Vouchers & Discounts</h2>
                <p class="text-copy-light mt-1">{{ vouchers.total }} voucher{{ vouchers.total !== 1 ? 's' : '' }} total
                </p>
            </div>
            <a :href="route('admin.vouchers.create')"
                class="rounded-lg border-2 border-copy px-4 py-2 font-bold text-sm transition hover:bg-foreground"
                style="background-color: var(--primary); color: var(--primary-content);">
                + New Voucher
            </a>
        </div>

        <!-- Table -->
        <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b-2 border-copy-light text-left text-xs uppercase tracking-wider text-copy-light">
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Discount</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Uses</th>
                            <th class="px-4 py-3">Valid Until</th>
                            <th class="px-4 py-3">Restrictions</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="vouchers.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-copy-light italic">
                                No vouchers yet. Create one above.
                            </td>
                        </tr>
                        <tr v-for="v in vouchers.data" :key="v.id"
                            class="border-b border-copy-light last:border-b-0 hover:bg-secondary-light transition">
                            <!-- Code -->
                            <td class="px-4 py-3">
                                <span class="font-mono font-bold text-copy">{{ v.code }}</span>
                                <p v-if="v.description" class="text-xs text-copy-light mt-0.5 max-w-[180px] truncate">{{
                                    v.description }}</p>
                            </td>

                            <!-- Discount -->
                            <td class="px-4 py-3 font-bold text-primary">{{ formatDiscount(v) }}</td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="statusBadge(v).class">{{ statusBadge(v).label }}</span>
                            </td>

                            <!-- Uses -->
                            <td class="px-4 py-3 text-copy">
                                {{ v.uses_count }}
                                <span v-if="v.max_uses !== null" class="text-copy-light">/ {{ v.max_uses }}</span>
                                <span v-else class="text-copy-light">/ ∞</span>
                            </td>

                            <!-- Valid Until -->
                            <td class="px-4 py-3 text-copy-light text-xs">
                                <span v-if="v.valid_until">{{ new Date(v.valid_until).toLocaleDateString('en-GB')
                                    }}</span>
                                <span v-else>No expiry</span>
                            </td>

                            <!-- Restrictions -->
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span v-if="!v.applies_to_all_products"
                                        class="rounded bg-blue-50 px-1.5 py-0.5 text-xs text-blue-700">
                                        {{ v.products.length }} product{{ v.products.length !== 1 ? 's' : '' }}
                                    </span>
                                    <span v-if="v.new_customers_only"
                                        class="rounded bg-purple-50 px-1.5 py-0.5 text-xs text-purple-700">New
                                        customers</span>
                                    <span v-if="v.single_use_per_user"
                                        class="rounded bg-orange-50 px-1.5 py-0.5 text-xs text-orange-700">1/user</span>
                                    <span v-if="v.minimum_order_value"
                                        class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">
                                        Min £{{ parseFloat(v.minimum_order_value).toFixed(2) }}
                                    </span>
                                    <span v-if="v.stackable"
                                        class="rounded bg-teal-50 px-1.5 py-0.5 text-xs text-teal-700">Stackable</span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="route('admin.vouchers.usage', { voucher: v.id })"
                                        class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy-light hover:text-copy transition">Usage
                                        log</a>
                                    <a :href="route('admin.vouchers.edit', { voucher: v.id })"
                                        class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy hover:bg-secondary-light transition">Edit</a>

                                    <!-- Delete confirm inline -->
                                    <template v-if="confirmingDelete === v.id">
                                        <span class="text-xs text-error font-medium">Sure?</span>
                                        <button @click="deleteVoucher(v.id)"
                                            class="rounded border border-error px-2 py-1 text-xs font-medium text-error hover:bg-red-50 transition">Yes</button>
                                        <button @click="confirmingDelete = null"
                                            class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy-light hover:text-copy transition">No</button>
                                    </template>
                                    <button v-else @click="confirmingDelete = v.id"
                                        class="rounded border border-error px-2 py-1 text-xs font-medium text-error hover:bg-red-50 transition">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="vouchers.last_page > 1" class="mt-4 flex justify-center gap-2">
            <a v-for="page in vouchers.last_page" :key="page" :href="route('admin.vouchers.index', { page })"
                class="rounded border px-3 py-1 text-sm transition" :class="page === vouchers.current_page
                    ? 'border-copy bg-copy text-foreground font-bold'
                    : 'border-copy-light text-copy-light hover:border-copy hover:text-copy'">{{ page }}</a>
        </div>
    </AdminLayout>
</template>
