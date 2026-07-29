<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';

interface Sheet {
    id: number;
    batch_number: string;
    blend_name: string;
    date_of_manufacture: string;
    produced_by: string;
    bottle_size_ml: number;
    total_units_produced: string;
    ingredients: {
        ingredient: string;
        supplier: string;
        lot_batch_no: string;
        percent_used: string;
        weight_g: string;
        sds_ifra_ref: string;
    }[];
    ifra_certificate_checked: boolean;
    max_percent_allowed: string | null;
    sds_hazards_noted: string | null;
    clp_label_prepared: boolean;
    notes: string | null;
    order: { id: number; label: string } | null;
    product: { id: number; name: string; mpn: string } | null;
    created_by_name: string | null;
    created_at: string;
}

defineProps<{ sheet: Sheet }>();
</script>

<template>
    <AdminLayout>
        <Head :title="`Batch Sheet: ${sheet.batch_number} : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.batch-sheets.index')"
                        class="adm-breadcrumb a"
                        >Batch Sheets</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ sheet.batch_number }}</span>
                </div>
                <h1 class="adm-title adm-td--mono" style="font-style: normal">
                    {{ sheet.batch_number }}
                </h1>
                <p class="adm-sub">
                    {{ sheet.blend_name }} &middot; Created
                    {{ sheet.created_at }}
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap">
                <a
                    :href="
                        route('admin.batch-sheets.pdf', {
                            batch_sheet: sheet.id,
                        })
                    "
                    target="_blank"
                    class="adm-btn adm-btn--ghost"
                >
                    <Download :size="13" :stroke-width="2.5" /> Download PDF
                </a>
                <Link
                    :href="
                        route('admin.batch-sheets.edit', {
                            batch_sheet: sheet.id,
                        })
                    "
                    class="adm-btn adm-btn--primary"
                    >Edit</Link
                >
            </div>
        </div>

        <div
            style="
                max-width: 56rem;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            "
        >
            <!-- Batch info -->
            <section class="adm-card">
                <h2 class="adm-card-title">Batch Information</h2>
                <div
                    class="adm-field-row"
                    style="grid-template-columns: repeat(2, 1fr); row-gap: 1rem"
                >
                    <div class="adm-field">
                        <p class="adm-label--sm">Batch Number</p>
                        <p
                            class="adm-td--mono"
                            style="font-weight: 700; color: var(--adm-ink)"
                        >
                            {{ sheet.batch_number }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">Blend Name</p>
                        <p style="font-weight: 600; color: var(--adm-ink)">
                            {{ sheet.blend_name }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">Date of Manufacture</p>
                        <p style="color: var(--adm-ink)">
                            {{ sheet.date_of_manufacture }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">Produced By</p>
                        <p style="color: var(--adm-ink)">
                            {{ sheet.produced_by }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">Bottle Size</p>
                        <p style="color: var(--adm-ink)">
                            {{ sheet.bottle_size_ml }} ml
                        </p>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">Total Units</p>
                        <p style="color: var(--adm-ink)">
                            {{ sheet.total_units_produced }}
                        </p>
                    </div>
                    <div v-if="sheet.order" class="adm-field">
                        <p class="adm-label--sm">Linked Order</p>
                        <p class="adm-td--mono">{{ sheet.order.label }}</p>
                    </div>
                    <div v-if="sheet.product" class="adm-field">
                        <p class="adm-label--sm">Product</p>
                        <p style="color: var(--adm-ink)">
                            {{ sheet.product.name }}
                            <span class="adm-td--mono"
                                >({{ sheet.product.mpn }})</span
                            >
                        </p>
                    </div>
                </div>
            </section>

            <!-- Ingredients -->
            <section class="adm-card adm-card--flush">
                <div style="padding: 1.1rem 1.25rem 0.85rem">
                    <h2
                        class="adm-card-title"
                        style="border: none; padding: 0; margin: 0"
                    >
                        Ingredients Used
                    </h2>
                </div>
                <div class="adm-table-wrap" style="display: block">
                    <table class="adm-table">
                        <thead>
                            <tr class="adm-thead">
                                <th class="adm-th">Ingredient</th>
                                <th class="adm-th">Supplier</th>
                                <th class="adm-th">Lot/Batch No.</th>
                                <th class="adm-th">% Used</th>
                                <th class="adm-th">Weight (g)</th>
                                <th class="adm-th">SDS/IFRA Ref.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!sheet.ingredients.length">
                                <td
                                    colspan="6"
                                    class="adm-empty-sub"
                                    style="
                                        text-align: center;
                                        padding: 2rem 1rem;
                                    "
                                >
                                    No ingredients recorded.
                                </td>
                            </tr>
                            <tr
                                v-for="(row, i) in sheet.ingredients"
                                :key="i"
                                class="adm-row"
                            >
                                <td class="adm-td" style="font-weight: 500">
                                    {{ row.ingredient || '-' }}
                                </td>
                                <td
                                    class="adm-td"
                                    style="color: var(--adm-ink-dim)"
                                >
                                    {{ row.supplier || '-' }}
                                </td>
                                <td class="adm-td adm-td--mono">
                                    {{ row.lot_batch_no || '-' }}
                                </td>
                                <td class="adm-td">
                                    {{ row.percent_used || '-' }}
                                </td>
                                <td class="adm-td">
                                    {{ row.weight_g || '-' }}
                                </td>
                                <td
                                    class="adm-td"
                                    style="color: var(--adm-ink-dim)"
                                >
                                    {{ row.sds_ifra_ref || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Compliance -->
            <section class="adm-card">
                <h2 class="adm-card-title">Compliance Checks</h2>
                <div
                    class="adm-field-row"
                    style="grid-template-columns: repeat(2, 1fr); row-gap: 1rem"
                >
                    <div class="adm-field">
                        <p class="adm-label--sm">IFRA Certificate Checked</p>
                        <span
                            class="adm-badge"
                            :class="
                                sheet.ifra_certificate_checked
                                    ? 'adm-badge--on'
                                    : 'adm-badge--red'
                            "
                        >
                            {{ sheet.ifra_certificate_checked ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">CLP Label Prepared</p>
                        <span
                            class="adm-badge"
                            :class="
                                sheet.clp_label_prepared
                                    ? 'adm-badge--on'
                                    : 'adm-badge--red'
                            "
                        >
                            {{ sheet.clp_label_prepared ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">Max % Allowed</p>
                        <p style="color: var(--adm-ink)">
                            {{ sheet.max_percent_allowed || '-' }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <p class="adm-label--sm">SDS Hazards Noted</p>
                        <p style="color: var(--adm-ink)">
                            {{ sheet.sds_hazards_noted || '-' }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Notes -->
            <section v-if="sheet.notes" class="adm-card">
                <h2 class="adm-card-title">Notes &amp; Observations</h2>
                <p
                    style="
                        font-size: 0.9rem;
                        color: var(--adm-ink);
                        line-height: 1.7;
                        white-space: pre-wrap;
                    "
                >
                    {{ sheet.notes }}
                </p>
            </section>
        </div>
    </AdminLayout>
</template>
