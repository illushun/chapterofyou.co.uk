<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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
        onSuccess: () => { confirmingDelete.value = null; },
    });
}
</script>

<template>
    <AdminLayout>

        <Head title="Batch Sheets" />

        <div class="mb-6 flex items-center justify-between border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Batch Sheets</h2>
                <p class="text-copy-light mt-1">{{ sheets.total }} sheet{{ sheets.total !== 1 ? 's' : '' }} recorded</p>
            </div>
            <a :href="route('admin.batch-sheets.create')"
                class="rounded-lg border-2 border-copy px-4 py-2 font-bold text-sm transition"
                style="background-color: var(--primary); color: var(--primary-content);">+ New Batch Sheet</a>
        </div>

        <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b-2 border-copy-light text-left text-xs uppercase tracking-wider text-copy-light">
                            <th class="px-4 py-3">Batch No.</th>
                            <th class="px-4 py-3">Blend</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Made On</th>
                            <th class="px-4 py-3">By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="sheets.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-copy-light italic">
                                No batch sheets yet. Create one above.
                            </td>
                        </tr>
                        <tr v-for="sheet in sheets.data" :key="sheet.id"
                            class="border-b border-copy-light last:border-b-0 hover:bg-secondary-light transition">
                            <td class="px-4 py-3">
                                <span class="font-mono font-bold text-copy text-xs">{{ sheet.batch_number }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium text-copy">{{ sheet.blend_name }}</td>
                            <td class="px-4 py-3">
                                <span v-if="sheet.product" class="text-copy text-xs">
                                    {{ sheet.product.name }}<br>
                                    <span class="font-mono text-copy-light">{{ sheet.product.mpn }}</span>
                                </span>
                                <span v-else class="text-copy-light text-xs italic">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="sheet.order" class="font-mono text-xs text-copy">#{{ sheet.order.id
                                    }}</span>
                                <span v-else class="text-copy-light text-xs italic">—</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-copy-light">{{ sheet.date_of_manufacture }}</td>
                            <td class="px-4 py-3 text-xs text-copy-light">{{ sheet.produced_by }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- PDF download -->
                                    <a :href="route('admin.batch-sheets.pdf', { batch_sheet: sheet.id })"
                                        target="_blank"
                                        class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy-light hover:text-copy transition"
                                        title="Download PDF">PDF</a>

                                    <a :href="route('admin.batch-sheets.show', { batch_sheet: sheet.id })"
                                        class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy hover:bg-secondary-light transition">View</a>

                                    <a :href="route('admin.batch-sheets.edit', { batch_sheet: sheet.id })"
                                        class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy hover:bg-secondary-light transition">Edit</a>

                                    <!-- Inline delete confirm -->
                                    <template v-if="confirmingDelete === sheet.id">
                                        <span class="text-xs text-error font-medium">Sure?</span>
                                        <button @click="deleteSheet(sheet.id)"
                                            class="rounded border border-error px-2 py-1 text-xs font-medium text-error hover:bg-red-50 transition">Yes</button>
                                        <button @click="confirmingDelete = null"
                                            class="rounded border border-copy-light px-2 py-1 text-xs font-medium text-copy-light hover:text-copy transition">No</button>
                                    </template>
                                    <button v-else @click="confirmingDelete = sheet.id"
                                        class="rounded border border-error px-2 py-1 text-xs font-medium text-error hover:bg-red-50 transition">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="sheets.last_page > 1" class="mt-4 flex justify-center gap-2">
            <a v-for="page in sheets.last_page" :key="page" :href="route('admin.batch-sheets.index', { page })"
                class="rounded border px-3 py-1 text-sm transition" :class="page === sheets.current_page
                    ? 'border-copy bg-copy text-foreground font-bold'
                    : 'border-copy-light text-copy-light hover:border-copy hover:text-copy'">{{ page }}</a>
        </div>
    </AdminLayout>
</template>
