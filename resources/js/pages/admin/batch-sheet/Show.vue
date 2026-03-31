<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

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

        <Head :title="`Batch Sheet: ${sheet.batch_number}`" />

        <!-- Actions bar -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black font-mono">{{ sheet.batch_number }}</h2>
                <p class="text-copy-light mt-1">{{ sheet.blend_name }} · Created {{ sheet.created_at }}</p>
            </div>
            <div class="flex gap-2">
                <a :href="route('admin.batch-sheets.pdf', { batch_sheet: sheet.id })" target="_blank"
                    class="rounded-lg border-2 border-copy px-4 py-2 text-sm font-bold transition hover:bg-secondary-light">⬇
                    Download PDF</a>
                <a :href="route('admin.batch-sheets.edit', { batch_sheet: sheet.id })"
                    class="rounded-lg border-2 border-copy px-4 py-2 text-sm font-bold transition"
                    style="background-color: var(--primary); color: var(--primary-content);">Edit</a>
                <a :href="route('admin.batch-sheets.index')"
                    class="rounded-lg border-2 border-copy px-4 py-2 text-sm font-medium text-copy-light hover:text-copy hover:bg-secondary-light transition">←
                    All Sheets</a>
            </div>
        </div>

        <div class="max-w-4xl space-y-6">

            <!-- Batch info -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                    <h3 class="text-lg font-bold text-copy mb-4 border-b border-copy-light pb-2">Batch Information</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Batch Number</dt>
                            <dd class="font-mono font-bold text-copy mt-0.5">{{ sheet.batch_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Blend Name</dt>
                            <dd class="font-semibold text-copy mt-0.5">{{ sheet.blend_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Date of Manufacture
                            </dt>
                            <dd class="text-copy mt-0.5">{{ sheet.date_of_manufacture }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Produced By</dt>
                            <dd class="text-copy mt-0.5">{{ sheet.produced_by }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Bottle Size</dt>
                            <dd class="text-copy mt-0.5">{{ sheet.bottle_size_ml }} ml</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Total Units</dt>
                            <dd class="text-copy mt-0.5">{{ sheet.total_units_produced }}</dd>
                        </div>
                        <div v-if="sheet.order">
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Linked Order</dt>
                            <dd class="font-mono text-copy mt-0.5">{{ sheet.order.label }}</dd>
                        </div>
                        <div v-if="sheet.product">
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Product</dt>
                            <dd class="text-copy mt-0.5">{{ sheet.product.name }} <span
                                    class="font-mono text-xs text-copy-light">({{ sheet.product.mpn }})</span></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Ingredients -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden">
                    <div class="px-5 py-4 border-b border-copy-light">
                        <h3 class="text-lg font-bold text-copy">Ingredients Used</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="bg-gray-50 text-xs uppercase tracking-wider text-copy-light border-b border-copy-light">
                                    <th class="px-4 py-2 text-left font-medium">Ingredient</th>
                                    <th class="px-4 py-2 text-left font-medium">Supplier</th>
                                    <th class="px-4 py-2 text-left font-medium">Lot/Batch No.</th>
                                    <th class="px-4 py-2 text-center font-medium">% Used</th>
                                    <th class="px-4 py-2 text-center font-medium">Weight (g)</th>
                                    <th class="px-4 py-2 text-left font-medium">SDS/IFRA Ref.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!sheet.ingredients.length">
                                    <td colspan="6" class="px-4 py-6 text-center text-copy-light italic">No ingredients
                                        recorded.</td>
                                </tr>
                                <tr v-for="(row, i) in sheet.ingredients" :key="i"
                                    class="border-b border-copy-light last:border-b-0"
                                    :class="i % 2 === 0 ? '' : 'bg-gray-50'">
                                    <td class="px-4 py-2 font-medium text-copy">{{ row.ingredient || '—' }}</td>
                                    <td class="px-4 py-2 text-copy-light">{{ row.supplier || '—' }}</td>
                                    <td class="px-4 py-2 font-mono text-xs text-copy-light">{{ row.lot_batch_no || '—'
                                        }}</td>
                                    <td class="px-4 py-2 text-center text-copy">{{ row.percent_used || '—' }}</td>
                                    <td class="px-4 py-2 text-center text-copy">{{ row.weight_g || '—' }}</td>
                                    <td class="px-4 py-2 text-copy-light">{{ row.sds_ifra_ref || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Compliance -->
            <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                    <h3 class="text-lg font-bold text-copy mb-4 border-b border-copy-light pb-2">Compliance Checks</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">IFRA Certificate
                                Checked</dt>
                            <dd class="mt-0.5 font-semibold"
                                :class="sheet.ifra_certificate_checked ? 'text-green-700' : 'text-red-700'">
                                {{ sheet.ifra_certificate_checked ? '✓ Yes' : '✗ No' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">CLP Label Prepared
                            </dt>
                            <dd class="mt-0.5 font-semibold"
                                :class="sheet.clp_label_prepared ? 'text-green-700' : 'text-red-700'">
                                {{ sheet.clp_label_prepared ? '✓ Yes' : '✗ No' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">Max % Allowed</dt>
                            <dd class="text-copy mt-0.5">{{ sheet.max_percent_allowed || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-copy-light font-medium">SDS Hazards Noted
                            </dt>
                            <dd class="text-copy mt-0.5">{{ sheet.sds_hazards_noted || '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="sheet.notes" class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                    <h3 class="text-lg font-bold text-copy mb-3 border-b border-copy-light pb-2">Notes &amp;
                        Observations</h3>
                    <p class="text-sm text-copy leading-relaxed whitespace-pre-wrap">{{ sheet.notes }}</p>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
