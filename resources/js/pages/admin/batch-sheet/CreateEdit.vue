<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, X } from 'lucide-vue-next';
import { computed } from 'vue';

interface OrderOption {
    id: number;
    label: string;
}
interface ProductOption {
    id: number;
    name: string;
    mpn: string;
}

interface IngredientRow {
    ingredient: string;
    supplier: string;
    lot_batch_no: string;
    percent_used: string;
    weight_g: string;
    sds_ifra_ref: string;
}

interface Sheet {
    id: number;
    batch_number: string;
    blend_name: string;
    date_of_manufacture: string;
    produced_by: string;
    bottle_size_ml: number;
    total_units_produced: string;
    ingredients: IngredientRow[];
    ifra_certificate_checked: boolean;
    max_percent_allowed: string;
    sds_hazards_noted: string;
    clp_label_prepared: boolean;
    notes: string;
    order_id: number | null;
    product_id: number | null;
}

const props = defineProps<{
    sheet?: Sheet;
    orders: OrderOption[];
    products: ProductOption[];
    batchNumber: string;
    isEditing: boolean;
    linkedOrder?: { id: number; label: string } | null;
}>();

const blankRow = (): IngredientRow => ({
    ingredient: '',
    supplier: '',
    lot_batch_no: '',
    percent_used: '',
    weight_g: '',
    sds_ifra_ref: '',
});

const form = useForm({
    order_id: props.sheet?.order_id ?? props.linkedOrder?.id ?? null,
    product_id: props.sheet?.product_id ?? null,
    batch_number: props.sheet?.batch_number ?? props.batchNumber,
    blend_name: props.sheet?.blend_name ?? '',
    date_of_manufacture:
        props.sheet?.date_of_manufacture ??
        new Date().toISOString().split('T')[0],
    produced_by: props.sheet?.produced_by ?? 'Chapter of You',
    bottle_size_ml: props.sheet?.bottle_size_ml ?? 100,
    total_units_produced: props.sheet?.total_units_produced ?? '',
    ingredients: props.sheet?.ingredients?.length
        ? props.sheet.ingredients
        : [blankRow(), blankRow(), blankRow()],
    ifra_certificate_checked: props.sheet?.ifra_certificate_checked ?? false,
    max_percent_allowed: props.sheet?.max_percent_allowed ?? '',
    sds_hazards_noted: props.sheet?.sds_hazards_noted ?? '',
    clp_label_prepared: props.sheet?.clp_label_prepared ?? false,
    notes: props.sheet?.notes ?? '',
});

const title = computed(() =>
    props.isEditing ? `Edit: ${props.sheet?.batch_number}` : 'New Batch Sheet',
);

function addRow() {
    form.ingredients.push(blankRow());
}

function removeRow(index: number) {
    if (form.ingredients.length > 1) {
        form.ingredients.splice(index, 1);
    }
}

function submit() {
    if (props.isEditing && props.sheet) {
        form.put(
            route('admin.batch-sheets.update', { batch_sheet: props.sheet.id }),
            {
                preserveScroll: true,
            },
        );
    } else {
        form.post(route('admin.batch-sheets.store'), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="`${title} : Admin`" />

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
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="adm-title">{{ title }}</h1>
                <p class="adm-sub">
                    Complete all fields. This document provides production
                    traceability.
                </p>
            </div>
            <div v-if="form.isDirty" class="adm-unsaved">Unsaved changes</div>
        </div>

        <form
            @submit.prevent="submit"
            style="
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
                max-width: 64rem;
            "
        >
            <!-- Batch Information -->
            <section class="adm-card">
                <h2 class="adm-card-title">Batch Information</h2>
                <div
                    class="adm-field-row"
                    style="grid-template-columns: repeat(2, 1fr)"
                >
                    <div class="adm-field">
                        <label class="adm-label">Batch Number *</label>
                        <input
                            v-model="form.batch_number"
                            type="text"
                            required
                            class="adm-input adm-td--mono"
                            :class="{
                                'adm-input--err': form.errors.batch_number,
                            }"
                        />
                        <p v-if="form.errors.batch_number" class="adm-err">
                            {{ form.errors.batch_number }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Blend Name *</label>
                        <input
                            v-model="form.blend_name"
                            type="text"
                            required
                            class="adm-input"
                            :class="{
                                'adm-input--err': form.errors.blend_name,
                            }"
                        />
                        <p v-if="form.errors.blend_name" class="adm-err">
                            {{ form.errors.blend_name }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Date of Manufacture *</label>
                        <input
                            v-model="form.date_of_manufacture"
                            type="date"
                            required
                            class="adm-input"
                        />
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Produced By *</label>
                        <input
                            v-model="form.produced_by"
                            type="text"
                            required
                            class="adm-input"
                        />
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Bottle Size (ml) *</label>
                        <input
                            v-model="form.bottle_size_ml"
                            type="number"
                            min="1"
                            required
                            class="adm-input"
                        />
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Total Units Produced *</label>
                        <input
                            v-model="form.total_units_produced"
                            type="text"
                            required
                            placeholder="e.g. 10 or 96-97 mix"
                            class="adm-input"
                        />
                    </div>
                    <div class="adm-field">
                        <label class="adm-label"
                            >Link to Order
                            <span class="adm-label-note">optional</span></label
                        >
                        <select v-model="form.order_id" class="adm-select">
                            <option :value="null">No order linked</option>
                            <option
                                v-for="o in orders"
                                :key="o.id"
                                :value="o.id"
                            >
                                {{ o.label }}
                            </option>
                        </select>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label"
                            >Product
                            <span class="adm-label-note">optional</span></label
                        >
                        <select v-model="form.product_id" class="adm-select">
                            <option :value="null">No product linked</option>
                            <option
                                v-for="p in products"
                                :key="p.id"
                                :value="p.id"
                            >
                                {{ p.name }} ({{ p.mpn }})
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Ingredients Table -->
            <section class="adm-card">
                <div
                    style="
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 1rem;
                        padding-bottom: 0.75rem;
                        border-bottom: 1px dashed var(--adm-line);
                    "
                >
                    <h2
                        class="adm-card-title"
                        style="border: none; padding: 0; margin: 0"
                    >
                        Ingredients Used
                    </h2>
                    <button
                        type="button"
                        @click="addRow"
                        class="adm-btn adm-btn--ghost adm-btn--sm"
                    >
                        <Plus :size="13" :stroke-width="2.5" /> Add Row
                    </button>
                </div>

                <div class="bs-ing-table-wrap">
                    <table class="adm-table bs-ing-table">
                        <thead>
                            <tr class="adm-thead">
                                <th class="adm-th">Ingredient</th>
                                <th class="adm-th">Supplier</th>
                                <th class="adm-th">Lot/Batch No.</th>
                                <th class="adm-th">% Used</th>
                                <th class="adm-th">Weight (g)</th>
                                <th class="adm-th">SDS/IFRA Ref.</th>
                                <th class="adm-th" style="width: 2rem"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in form.ingredients"
                                :key="i"
                                class="adm-row"
                            >
                                <td class="adm-td">
                                    <input
                                        v-model="row.ingredient"
                                        type="text"
                                        placeholder="e.g. Lavender EO"
                                        class="bs-cell-input"
                                    />
                                </td>
                                <td class="adm-td">
                                    <input
                                        v-model="row.supplier"
                                        type="text"
                                        placeholder="e.g. Nikura"
                                        class="bs-cell-input"
                                    />
                                </td>
                                <td class="adm-td">
                                    <input
                                        v-model="row.lot_batch_no"
                                        type="text"
                                        placeholder="001"
                                        class="bs-cell-input adm-td--mono"
                                    />
                                </td>
                                <td class="adm-td">
                                    <input
                                        v-model="row.percent_used"
                                        type="text"
                                        placeholder="7.73"
                                        class="bs-cell-input"
                                        style="text-align: center"
                                    />
                                </td>
                                <td class="adm-td">
                                    <input
                                        v-model="row.weight_g"
                                        type="text"
                                        placeholder="7.73"
                                        class="bs-cell-input"
                                        style="text-align: center"
                                    />
                                </td>
                                <td class="adm-td">
                                    <input
                                        v-model="row.sds_ifra_ref"
                                        type="text"
                                        placeholder="-"
                                        class="bs-cell-input"
                                    />
                                </td>
                                <td class="adm-td" style="text-align: center">
                                    <button
                                        type="button"
                                        @click="removeRow(i)"
                                        :disabled="form.ingredients.length <= 1"
                                        class="adm-action adm-action--del"
                                        title="Remove row"
                                    >
                                        <X :size="12" :stroke-width="2.5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p
                    v-if="form.errors.ingredients"
                    class="adm-err"
                    style="margin-top: 0.5rem"
                >
                    {{ form.errors.ingredients }}
                </p>
            </section>

            <!-- Compliance Checks -->
            <section class="adm-card">
                <h2 class="adm-card-title">Compliance Checks</h2>
                <div
                    class="adm-field-row"
                    style="grid-template-columns: repeat(2, 1fr)"
                >
                    <label
                        class="adm-check-item"
                        style="align-items: flex-start"
                    >
                        <input
                            type="checkbox"
                            v-model="form.ifra_certificate_checked"
                            class="adm-checkbox"
                            style="margin-top: 0.15rem"
                        />
                        <span>
                            <span
                                style="
                                    display: block;
                                    color: var(--adm-ink);
                                    font-weight: 500;
                                "
                                >IFRA Certificate Checked?</span
                            >
                            <span class="adm-field-note"
                                >Confirm IFRA compliance has been verified</span
                            >
                        </span>
                    </label>
                    <label
                        class="adm-check-item"
                        style="align-items: flex-start"
                    >
                        <input
                            type="checkbox"
                            v-model="form.clp_label_prepared"
                            class="adm-checkbox"
                            style="margin-top: 0.15rem"
                        />
                        <span>
                            <span
                                style="
                                    display: block;
                                    color: var(--adm-ink);
                                    font-weight: 500;
                                "
                                >CLP Label Prepared?</span
                            >
                            <span class="adm-field-note"
                                >Confirm CLP label has been generated</span
                            >
                        </span>
                    </label>
                    <div class="adm-field">
                        <label class="adm-label">Max % Allowed</label>
                        <input
                            v-model="form.max_percent_allowed"
                            type="text"
                            placeholder="e.g. Not limited"
                            class="adm-input"
                        />
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">SDS Hazards Noted</label>
                        <input
                            v-model="form.sds_hazards_noted"
                            type="text"
                            placeholder="e.g. H317, H410"
                            class="adm-input"
                        />
                    </div>
                </div>
            </section>

            <!-- Notes -->
            <section class="adm-card">
                <h2 class="adm-card-title">Notes &amp; Observations</h2>
                <textarea
                    v-model="form.notes"
                    rows="5"
                    placeholder="Record any observations about scent throw, performance, or quality notes..."
                    class="adm-textarea"
                ></textarea>
            </section>

            <!-- Submit -->
            <div style="display: flex; gap: 0.75rem">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="adm-btn adm-btn--primary"
                >
                    {{
                        form.processing
                            ? 'Saving...'
                            : isEditing
                              ? 'Update Batch Sheet'
                              : 'Save Batch Sheet'
                    }}
                </button>
                <Link
                    :href="route('admin.batch-sheets.index')"
                    class="adm-btn adm-btn--ghost"
                    >Cancel</Link
                >
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.bs-ing-table-wrap {
    overflow-x: auto;
}

.bs-ing-table {
    min-width: 640px;
}

.bs-cell-input {
    width: 100%;
    padding: 0.3rem 0.4rem;
    border: 1px solid transparent;
    border-radius: var(--adm-radius-sm);
    background: transparent;
    color: var(--adm-ink);
    font-family: var(--adm-font);
    font-size: 0.82rem;
    outline: none;
    transition:
        border-color 0.15s,
        background 0.15s;
}

.bs-cell-input:hover {
    background: var(--adm-paper);
}

.bs-cell-input:focus {
    background: var(--adm-paper-raised);
    border-color: var(--adm-stamp);
}

.bs-cell-input::placeholder {
    color: var(--adm-ink-faint);
}
</style>
