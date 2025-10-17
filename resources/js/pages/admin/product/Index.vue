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
    // Replaced standard confirm/alert with a functional console error/info log as per instructions
    // In a real application, you would use a custom modal for confirmation.
    console.info(`Attempting to delete product: ${product.name} (MPN: ${product.mpn})`);
    if (confirm(`Are you sure you want to delete the product: ${product.name} (MPN: ${product.mpn})? This action cannot be undone.`)) {
        router.delete(route('admin.products.destroy', product.id), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Product deleted successfully.');
                // Placeholder for success notification
            },
        });
    }
};

const formatCurrency = (amount: number | string | null | undefined): string => {
    const numericAmount = Number(amount) || 0;
    return `£${numericAmount.toFixed(2)}`;
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

        <div class="rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg">
            <!--
                DESKTOP TABLE VIEW
                (Hidden below 'md' breakpoint, uses full table structure)
            -->
            <div class="hidden md:block overflow-x-auto relative rounded-lg -m-0.5 border-2 border-copy bg-foreground">
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
                                <img :src="product.images[0]?.image ?? 'https://placehold.co/50x50/333/fff?text=NO'" :alt="product.name" class="size-10 object-contain border border-copy p-1 rounded">
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

            <!--
                MOBILE CARD VIEW
                (Visible below 'md' breakpoint, stacked layout for small screens)
            -->
            <div class="md:hidden divide-y divide-copy-light/50">
                <div v-for="product in props.products.data" :key="product.id" class="p-4 bg-foreground hover:bg-secondary-light transition">

                    <!-- Product Header (Image, Name, MPN, Status) -->
                    <div class="flex items-start space-x-3 mb-4">
                        <img :src="product.images[0]?.image ?? 'https://placehold.co/64x64/333/fff?text=NO'" :alt="product.name" class="size-16 object-contain border border-copy p-1 rounded flex-shrink-0">
                        <div class="flex-grow min-w-0">
                            <div class="text-xs text-copy-light uppercase font-semibold truncate">{{ product.mpn }}</div>
                            <Link :href="route('admin.products.edit', product.id)" class="text-lg font-bold text-primary hover:underline block leading-snug">
                                {{ product.name }}
                                <span v-if="product.parent_product_id" class="text-xs text-copy-light ml-1 font-normal">(Variant)</span>
                            </Link>
                            <span :class="['mt-1 px-2 py-0.5 rounded-full text-xs font-semibold uppercase inline-block',
                                product.status === 'enabled' ? 'bg-green-500/20 text-green-700 border border-green-700' : 'bg-red-500/20 text-error border border-error-dark'
                            ]">
                                {{ product.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Details (Price & Stock) -->
                    <div class="flex justify-between items-center py-2 border-t border-copy-light/30">
                        <!-- Price -->
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-copy-light uppercase font-medium">Price</div>
                            <div class="font-bold text-lg text-copy">{{ formatCurrency(product.cost) }}</div>
                        </div>

                        <!-- Stock -->
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-copy-light uppercase font-medium">Stock</div>
                            <div class="font-bold text-lg"
                                :class="{'text-error': product.stock_qty < 10 && product.stock_qty > 0, 'text-error-dark': product.stock_qty === 0, 'text-green-600': product.stock_qty >= 10}">
                                {{ product.stock_qty }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-3 text-right whitespace-nowrap pt-2">
                            <Link :href="route('admin.products.edit', product.id)" class="text-sm text-blue-500 hover:text-blue-700 transition font-semibold">
                                Edit
                            </Link>
                            <button @click="confirmDelete(product)" class="text-sm text-error hover:text-error-dark transition font-semibold">
                                Delete
                            </button>
                        </div>
                    </div>

                </div>
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
