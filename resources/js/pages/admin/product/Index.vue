<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    mpn: string;
    cost: number;
    stock_qty: number;
    status: 'enabled' | 'disabled';
    parent_product_id: number | null;
    images: { image: string }[];
}

interface ProductsPaginated {
    data: Product[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    products: ProductsPaginated;
}>();

const confirmDelete = (product: Product) => {
    if (confirm(`Are you sure you want to delete the product: ${product.name} (MPN: ${product.mpn})? This action cannot be undone.`)) {
        router.delete(route('admin.products.destroy', product.id), {
            preserveScroll: true,
            onSuccess: () => {
                alert('Product deleted successfully.');
            },
        });
    }
};

const formatCurrency = (amount: number): string => {
    return `£${amount.toFixed(2)}`;
};

const paginate = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Manage Products" />

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Product Catalog</h2>
            <Link :href="route('admin.products.create')" class="relative rounded-lg -m-0.5 px-4 py-2 text-sm font-bold text-primary-content transition border-2 border-copy bg-primary hover:bg-primary-dark shadow-md">
                + Add New Product
            </Link>
        </div>

        <div class="overflow-x-auto rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg">
            <div class="relative rounded-lg -m-0.5 border-2 border-copy bg-foreground">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="text-left bg-secondary-light font-bold text-copy uppercase border-b-2 border-copy">
                            <th class="px-4 py-3"></th>
                            <th class="px-4 py-3">MPN</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="product in props.products.data" :key="product.id" class="hover:bg-secondary-light transition">
                            <td class="px-4 py-3">
                                <img :src="product.images[0]?.image ?? 'https://via.placeholder.com/50?text=NO+IMG'" :alt="product.name" class="size-10 object-contain border border-copy p-1 rounded">
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ product.mpn }}</td>
                            <td class="px-4 py-3">
                                <Link :href="route('admin.products.edit', product.id)" class="text-primary hover:underline font-medium">
                                    {{ product.name }}
                                </Link>
                                <span v-if="product.parent_product_id" class="text-xs text-copy-light ml-2">(Variant)</span>
                            </td>
                            <td class="px-4 py-3 font-bold">{{ formatCurrency(product.cost) }}</td>
                            <td class="px-4 py-3 font-bold" :class="{'text-error': product.stock_qty < 10 && product.stock_qty > 0, 'text-error-dark': product.stock_qty === 0, 'text-green-600': product.stock_qty >= 10}">
                                {{ product.stock_qty }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold uppercase',
                                    product.status === 'enabled' ? 'bg-green-500/20 text-green-700 border border-green-700' : 'bg-red-500/20 text-error border border-error-dark'
                                ]">
                                    {{ product.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <Link :href="route('admin.products.edit', product.id)" class="text-blue-500 hover:text-blue-700 transition font-semibold">
                                    Edit
                                </Link>
                                <button @click="confirmDelete(product)" class="text-error hover:text-error-dark transition font-semibold">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="products.last_page > 1" class="mt-6 flex justify-center">
            <ol class="flex gap-2 text-sm font-medium">
                <li v-for="link in products.links" :key="link.label">
                    <button
                        @click.prevent="paginate(link.url)"
                        :disabled="!link.url"
                        :class="{'px-4 py-2 border-2 border-copy transition relative -m-0.5 font-bold': true, 'bg-primary text-primary-content shadow-md': link.active, 'bg-foreground hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed': !link.active}"
                        v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')"
                        :aria-label="link.label"
                    >
                    </button>
                </li>
            </ol>
        </div>
    </AdminLayout>
</template>
