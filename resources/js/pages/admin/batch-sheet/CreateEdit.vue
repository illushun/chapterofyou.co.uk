<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface OrderOption { id: number; label: string; }
interface ProductOption { id: number; name: string; mpn: string; }

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
    ingredient: '', supplier: '', lot_batch_no: '',
    percent_used: '', weight_g: '', sds_ifra_ref: '',
});

const form = useForm({
    order_id: props.sheet?.order_id ?? props.linkedOrder?.id ?? null,
    product_id: props.sheet?.product_id ?? null,
    batch_number: props.sheet?.batch_number ?? props.batchNumber,
    blend_name: props.sheet?.blend_name ?? '',
    date_of_manufacture: props.sheet?.date_of_manufacture ?? new Date().toISOString().split('T')[0],
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
    props.isEditing ? `Edit: ${props.sheet?.batch_number}` : 'New Batch Sheet'
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
        form.put(route('admin.batch-sheets.update', { batch_sheet: props.sheet.id }), {
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.batch-sheets.store'), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AdminLayout>

        <Head :title="title" />

        <div class="mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">{{ title }}</h2>
            <p class="text-copy-light mt-1">Complete all fields — this document provides production traceability.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-5xl">

            <!-- ── Batch Information ── -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                    <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">
                        Batch Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <!-- Batch Number -->
                        <div>
                            <label class="form-label">Batch Number *</label>
                            <input v-model="form.batch_number" type="text" required class="field font-mono"
                                :class="{ 'border-error': form.errors.batch_number }" />
                            <p v-if="form.errors.batch_number" class="err">{{ form.errors.batch_number }}</p>
                        </div>

                        <!-- Blend Name -->
                        <div>
                            <label class="form-label">Blend Name *</label>
                            <input v-model="form.blend_name" type="text" required class="field"
                                :class="{ 'border-error': form.errors.blend_name }" />
                            <p v-if="form.errors.blend_name" class="err">{{ form.errors.blend_name }}</p>
                        </div>

                        <!-- Date of Manufacture -->
                        <div>
                            <label class="form-label">Date of Manufacture *</label>
                            <input v-model="form.date_of_manufacture" type="date" required class="field" />
                        </div>

                        <!-- Produced By -->
                        <div>
                            <label class="form-label">Produced By *</label>
                            <input v-model="form.produced_by" type="text" required class="field" />
                        </div>

                        <!-- Bottle Size -->
                        <div>
                            <label class="form-label">Bottle Size (ml) *</label>
                            <input v-model="form.bottle_size_ml" type="number" min="1" required class="field" />
                        </div>

                        <!-- Total Units -->
                        <div>
                            <label class="form-label">Total Units Produced *</label>
                            <input v-model="form.total_units_produced" type="text" required
                                placeholder="e.g. 10 or 96–97 mix" class="field" />
                        </div>

                        <!-- Link to Order -->
                        <div>
                            <label class="form-label">Link to Order <span
                                    class="text-copy-light">(optional)</span></label>
                            <select v-model="form.order_id" class="field">
                                <option :value="null">— No order linked —</option>
                                <option v-for="o in orders" :key="o.id" :value="o.id">{{ o.label }}</option>
                            </select>
                        </div>

                        <!-- Link to Product -->
                        <div>
                            <label class="form-label">Product <span class="text-copy-light">(optional)</span></label>
                            <select v-model="form.product_id" class="field">
                                <option :value="null">— No product linked —</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">
                                    {{ p.name }} ({{ p.mpn }})
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Ingredients Table ── -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                    <div class="flex items-center justify-between mb-4 border-b-2 border-copy-light pb-2">
                        <h3 class="text-xl font-bold text-copy">Ingredients Used</h3>
                        <button type="button" @click="addRow"
                            class="rounded border border-copy-light px-3 py-1 text-sm font-medium text-copy hover:bg-secondary-light transition">
                            + Add Row
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-xs uppercase tracking-wider text-copy-light">
                                    <th class="border border-copy-light px-3 py-2 text-left font-semibold">Ingredient
                                    </th>
                                    <th class="border border-copy-light px-3 py-2 text-left font-semibold">Supplier</th>
                                    <th class="border border-copy-light px-3 py-2 text-left font-semibold">Lot/Batch No.
                                    </th>
                                    <th class="border border-copy-light px-3 py-2 text-center font-semibold">% Used</th>
                                    <th class="border border-copy-light px-3 py-2 text-center font-semibold">Weight (g)
                                    </th>
                                    <th class="border border-copy-light px-3 py-2 text-left font-semibold">SDS/IFRA Ref.
                                    </th>
                                    <th class="border border-copy-light px-2 py-2 w-8"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, i) in form.ingredients" :key="i"
                                    class="hover:bg-secondary-light transition">
                                    <td class="border border-copy-light p-1">
                                        <input v-model="row.ingredient" type="text" placeholder="e.g. Lavender EO"
                                            class="w-full rounded border border-copy-light px-2 py-1 text-sm focus:border-copy focus:outline-none bg-foreground text-copy" />
                                    </td>
                                    <td class="border border-copy-light p-1">
                                        <input v-model="row.supplier" type="text" placeholder="e.g. Nikura"
                                            class="w-full rounded border border-copy-light px-2 py-1 text-sm focus:border-copy focus:outline-none bg-foreground text-copy" />
                                    </td>
                                    <td class="border border-copy-light p-1">
                                        <input v-model="row.lot_batch_no" type="text" placeholder="001"
                                            class="w-full rounded border border-copy-light px-2 py-1 text-sm font-mono focus:border-copy focus:outline-none bg-foreground text-copy" />
                                    </td>
                                    <td class="border border-copy-light p-1">
                                        <input v-model="row.percent_used" type="text" placeholder="7.73"
                                            class="w-full rounded border border-copy-light px-2 py-1 text-sm text-center focus:border-copy focus:outline-none bg-foreground text-copy" />
                                    </td>
                                    <td class="border border-copy-light p-1">
                                        <input v-model="row.weight_g" type="text" placeholder="7.73"
                                            class="w-full rounded border border-copy-light px-2 py-1 text-sm text-center focus:border-copy focus:outline-none bg-foreground text-copy" />
                                    </td>
                                    <td class="border border-copy-light p-1">
                                        <input v-model="row.sds_ifra_ref" type="text" placeholder="—"
                                            class="w-full rounded border border-copy-light px-2 py-1 text-sm focus:border-copy focus:outline-none bg-foreground text-copy" />
                                    </td>
                                    <td class="border border-copy-light p-1 text-center">
                                        <button type="button" @click="removeRow(i)"
                                            :disabled="form.ingredients.length <= 1"
                                            class="text-error hover:text-red-800 transition disabled:opacity-30 text-xs font-bold px-1"
                                            title="Remove row">✕</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-if="form.errors.ingredients" class="err mt-2">{{ form.errors.ingredients }}</p>
                </div>
            </div>

            <!-- ── Compliance Checks ── -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                    <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">
                        Compliance Checks
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <!-- IFRA checked -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.ifra_certificate_checked"
                                class="mt-0.5 size-5 border-2 border-copy text-primary" />
                            <div>
                                <span class="block text-sm font-medium text-copy">IFRA Certificate Checked?</span>
                                <span class="text-xs text-copy-light">Confirm IFRA compliance has been verified</span>
                            </div>
                        </label>

                        <!-- CLP label -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.clp_label_prepared"
                                class="mt-0.5 size-5 border-2 border-copy text-primary" />
                            <div>
                                <span class="block text-sm font-medium text-copy">CLP Label Prepared?</span>
                                <span class="text-xs text-copy-light">Confirm CLP label has been generated</span>
                            </div>
                        </label>

                        <!-- Max % allowed -->
                        <div>
                            <label class="form-label">Max % Allowed</label>
                            <input v-model="form.max_percent_allowed" type="text" placeholder="e.g. Not limited"
                                class="field" />
                        </div>

                        <!-- SDS hazards noted -->
                        <div>
                            <label class="form-label">SDS Hazards Noted</label>
                            <input v-model="form.sds_hazards_noted" type="text" placeholder="e.g. H317, H410"
                                class="field" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Notes & Observations ── -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                    <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">
                        Notes &amp; Observations
                    </h3>
                    <textarea v-model="form.notes" rows="5"
                        placeholder="Record any observations about scent throw, performance, or quality notes…"
                        class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-copy focus:outline-none shadow-sm resize-y"></textarea>
                </div>
            </div>

            <!-- ── Submit ── -->
            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing"
                    class="rounded-lg border-2 border-copy px-8 py-3 font-bold text-lg transition disabled:opacity-50"
                    style="background-color: var(--primary); color: var(--primary-content);">
                    {{ form.processing ? 'Saving…' : (isEditing ? 'Update Batch Sheet' : 'Save Batch Sheet') }}
                </button>
                <a :href="route('admin.batch-sheets.index')"
                    class="rounded-lg border-2 border-copy px-6 py-3 font-bold text-sm text-copy-light hover:text-copy hover:bg-secondary-light transition">
                    Cancel
                </a>
            </div>

        </form>
    </AdminLayout>
</template>

<style scoped>
.form-label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 4px;
}

.field {
    display: block;
    width: 100%;
    font-size: 0.8125rem;
    color: #111827;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 6px 10px;
    outline: none;
    transition: border-color 0.15s;
}

.field:focus {
    border-color: #374151;
}

.err {
    font-size: 0.75rem;
    color: #b91c1c;
    margin-top: 2px;
}
</style>
