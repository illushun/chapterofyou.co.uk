<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ClipboardList, Download } from 'lucide-vue-next';
import { ref } from 'vue';

interface Sheet {
    id: number;
    batch_number: string;
    blend_name: string;
    date_of_manufacture: string;
    produced_by: string;
    bottle_size_ml: number;
    total_units_produced: string;
    created_at: string;
    order: { id: number } | null;
    product: { id: number; name: string; mpn: string } | null;
    created_by: { name: string } | null;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{ sheets: Paginated<Sheet> }>();

const confirmingDelete = ref<number | null>(null);

function deleteSheet(id: number) {
    router.delete(route('admin.batch-sheets.destroy', { batch_sheet: id }), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDelete.value = null;
        },
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Batch Sheets : Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Batch Sheets</h1>
                <p class="adm-sub">
                    {{ sheets.total }} sheet{{ sheets.total !== 1 ? 's' : '' }}
                    recorded
                </p>
            </div>
            <Link
                :href="route('admin.batch-sheets.create')"
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
                New Batch Sheet
            </Link>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom: 1.5rem">
            <div v-if="sheets.data.length" class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">Batch No.</th>
                            <th class="adm-th">Blend</th>
                            <th class="adm-th">Product</th>
                            <th class="adm-th">Order</th>
                            <th class="adm-th">Made On</th>
                            <th class="adm-th">By</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="sheet in sheets.data"
                            :key="sheet.id"
                            class="adm-row"
                        >
                            <td class="adm-td adm-td--mono">
                                {{ sheet.batch_number }}
                            </td>
                            <td class="adm-td" style="font-weight: 500">
                                {{ sheet.blend_name }}
                            </td>
                            <td class="adm-td">
                                <span v-if="sheet.product">
                                    {{ sheet.product.name }}<br />
                                    <span class="adm-td--mono">{{
                                        sheet.product.mpn
                                    }}</span>
                                </span>
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
                                <span v-if="sheet.order" class="adm-td--mono"
                                    >#{{ sheet.order.id }}</span
                                >
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
                            <td
                                class="adm-td"
                                style="
                                    color: var(--adm-ink-dim);
                                    font-size: 0.8rem;
                                "
                            >
                                {{ sheet.date_of_manufacture }}
                            </td>
                            <td
                                class="adm-td"
                                style="
                                    color: var(--adm-ink-dim);
                                    font-size: 0.8rem;
                                "
                            >
                                {{ sheet.produced_by }}
                            </td>
                            <td class="adm-td adm-td--actions">
                                <a
                                    :href="
                                        route('admin.batch-sheets.pdf', {
                                            batch_sheet: sheet.id,
                                        })
                                    "
                                    target="_blank"
                                    class="adm-action adm-action--edit"
                                    title="Download PDF"
                                >
                                    <Download :size="12" :stroke-width="2.5" />
                                    PDF
                                </a>
                                <Link
                                    :href="
                                        route('admin.batch-sheets.show', {
                                            batch_sheet: sheet.id,
                                        })
                                    "
                                    class="adm-action adm-action--edit"
                                    >View</Link
                                >
                                <Link
                                    :href="
                                        route('admin.batch-sheets.edit', {
                                            batch_sheet: sheet.id,
                                        })
                                    "
                                    class="adm-action adm-action--edit"
                                    >Edit</Link
                                >

                                <template v-if="confirmingDelete === sheet.id">
                                    <span
                                        style="
                                            font-size: 0.75rem;
                                            color: var(--adm-danger);
                                            font-weight: 600;
                                        "
                                        >Sure?</span
                                    >
                                    <button
                                        @click="deleteSheet(sheet.id)"
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
                                    @click="confirmingDelete = sheet.id"
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
                    <ClipboardList :size="28" :stroke-width="1.5" />
                </div>
                <p class="adm-empty-title">No batch sheets yet</p>
                <p class="adm-empty-sub">
                    Create one to start tracking production traceability.
                </p>
                <Link
                    :href="route('admin.batch-sheets.create')"
                    class="adm-btn adm-btn--primary"
                    >New Batch Sheet</Link
                >
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="sheets.last_page > 1" class="adm-pagination">
            <div class="adm-page-btns">
                <a
                    v-for="page in sheets.last_page"
                    :key="page"
                    :href="route('admin.batch-sheets.index', { page })"
                    class="adm-page-btn"
                    :class="{
                        'adm-page-btn--active': page === sheets.current_page,
                    }"
                    >{{ page }}</a
                >
            </div>
        </div>
    </AdminLayout>
</template>
