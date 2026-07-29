<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Tag } from 'lucide-vue-next';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
}

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
        onSuccess: () => {
            confirmingDelete.value = null;
        },
    });
}

function formatDiscount(v: Voucher) {
    return v.type === 'percentage'
        ? `${v.value}%`
        : `£${parseFloat(v.value).toFixed(2)}`;
}

function statusBadge(v: Voucher): { label: string; cls: string } {
    if (!v.is_active) return { label: 'Inactive', cls: 'adm-badge--off' };
    if (v.valid_until && new Date(v.valid_until) < new Date())
        return { label: 'Expired', cls: 'adm-badge--red' };
    if (v.max_uses !== null && v.uses_count >= v.max_uses)
        return { label: 'Exhausted', cls: 'adm-badge--warn' };
    return { label: 'Active', cls: 'adm-badge--on' };
}
</script>

<template>
    <AdminLayout>
        <Head title="Vouchers : Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Vouchers &amp; Discounts</h1>
                <p class="adm-sub">
                    {{ vouchers.total }} voucher{{
                        vouchers.total !== 1 ? 's' : ''
                    }}
                    total
                </p>
            </div>
            <Link
                :href="route('admin.vouchers.create')"
                class="adm-btn adm-btn--primary"
            >
                <svg
                    width="14"
                    height="14"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                >
                    <path d="M12 5v14M5 12h14" />
                </svg>
                New Voucher
            </Link>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom: 1.5rem">
            <div v-if="vouchers.data.length" class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">Code</th>
                            <th class="adm-th">Discount</th>
                            <th class="adm-th">Status</th>
                            <th class="adm-th">Uses</th>
                            <th class="adm-th">Valid Until</th>
                            <th class="adm-th">Restrictions</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="v in vouchers.data"
                            :key="v.id"
                            class="adm-row"
                        >
                            <!-- Code -->
                            <td class="adm-td">
                                <span
                                    class="adm-td--mono"
                                    style="
                                        font-weight: 700;
                                        color: var(--adm-ink);
                                    "
                                    >{{ v.code }}</span
                                >
                                <p
                                    v-if="v.description"
                                    class="adm-sub"
                                    style="
                                        margin-top: 0.15rem;
                                        max-width: 180px;
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        white-space: nowrap;
                                    "
                                >
                                    {{ v.description }}
                                </p>
                            </td>

                            <!-- Discount -->
                            <td class="adm-td adm-td--price">
                                {{ formatDiscount(v) }}
                            </td>

                            <!-- Status -->
                            <td class="adm-td">
                                <span
                                    class="adm-badge"
                                    :class="statusBadge(v).cls"
                                    >{{ statusBadge(v).label }}</span
                                >
                            </td>

                            <!-- Uses -->
                            <td class="adm-td">
                                {{ v.uses_count }}
                                <span style="color: var(--adm-ink-dim)"
                                    >/
                                    {{
                                        v.max_uses !== null
                                            ? v.max_uses
                                            : 'unlimited'
                                    }}</span
                                >
                            </td>

                            <!-- Valid Until -->
                            <td
                                class="adm-td"
                                style="
                                    color: var(--adm-ink-dim);
                                    font-size: 0.8rem;
                                "
                            >
                                <span v-if="v.valid_until">{{
                                    new Date(v.valid_until).toLocaleDateString(
                                        'en-GB',
                                    )
                                }}</span>
                                <span v-else>No expiry</span>
                            </td>

                            <!-- Restrictions -->
                            <td class="adm-td">
                                <div
                                    style="
                                        display: flex;
                                        flex-wrap: wrap;
                                        gap: 0.3rem;
                                    "
                                >
                                    <span
                                        v-if="!v.applies_to_all_products"
                                        class="adm-badge adm-badge--lav"
                                    >
                                        {{ v.products.length }} product{{
                                            v.products.length !== 1 ? 's' : ''
                                        }}
                                    </span>
                                    <span
                                        v-if="v.new_customers_only"
                                        class="adm-badge adm-badge--blush"
                                        >New customers</span
                                    >
                                    <span
                                        v-if="v.single_use_per_user"
                                        class="adm-badge adm-badge--warn"
                                        >1/user</span
                                    >
                                    <span
                                        v-if="v.minimum_order_value"
                                        class="adm-badge adm-badge--off"
                                    >
                                        Min £{{
                                            parseFloat(
                                                v.minimum_order_value,
                                            ).toFixed(2)
                                        }}
                                    </span>
                                    <span
                                        v-if="v.stackable"
                                        class="adm-badge adm-badge--on"
                                        >Stackable</span
                                    >
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="adm-td adm-td--actions">
                                <Link
                                    :href="
                                        route('admin.vouchers.usage', {
                                            voucher: v.id,
                                        })
                                    "
                                    class="adm-action adm-action--edit"
                                    >Usage log</Link
                                >
                                <Link
                                    :href="
                                        route('admin.vouchers.edit', {
                                            voucher: v.id,
                                        })
                                    "
                                    class="adm-action adm-action--edit"
                                    >Edit</Link
                                >

                                <!-- Delete confirm inline -->
                                <template v-if="confirmingDelete === v.id">
                                    <span
                                        style="
                                            font-size: 0.75rem;
                                            color: var(--adm-danger);
                                            font-weight: 600;
                                        "
                                        >Sure?</span
                                    >
                                    <button
                                        @click="deleteVoucher(v.id)"
                                        class="adm-action adm-action--del"
                                    >
                                        Yes
                                    </button>
                                    <button
                                        @click="confirmingDelete = null"
                                        class="adm-action adm-action--edit"
                                    >
                                        No
                                    </button>
                                </template>
                                <button
                                    v-else
                                    @click="confirmingDelete = v.id"
                                    class="adm-action adm-action--del"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="adm-empty">
                <div class="adm-empty-icon">
                    <Tag :size="28" :stroke-width="1.5" />
                </div>
                <p class="adm-empty-title">No vouchers yet</p>
                <p class="adm-empty-sub">
                    Create one to start offering discounts.
                </p>
                <Link
                    :href="route('admin.vouchers.create')"
                    class="adm-btn adm-btn--primary"
                    >Add Voucher</Link
                >
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="vouchers.last_page > 1" class="adm-pagination">
            <p class="adm-page-info">
                Page <strong>{{ vouchers.current_page }}</strong> of
                <strong>{{ vouchers.last_page }}</strong>
            </p>
            <div class="adm-page-btns">
                <a
                    v-for="page in vouchers.last_page"
                    :key="page"
                    :href="route('admin.vouchers.index', { page })"
                    class="adm-page-btn"
                    :class="{
                        'adm-page-btn--active': page === vouchers.current_page,
                    }"
                    >{{ page }}</a
                >
            </div>
        </div>
    </AdminLayout>
</template>
