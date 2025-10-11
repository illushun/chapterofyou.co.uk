<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, reactive } from 'vue';
import { debounce } from 'lodash';
import { usePage } from '@inertiajs/vue3';

// --- TypeScript Interface Definitions for Type Safety ---

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    mpn: string;
    cost: number;
    stock_qty: number;
    parent_product_id: number;
}

interface ProductsPaginated {
    current_page: number;
    data: Product[];
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

interface ProductProps {
    products: ProductsPaginated;
    categories: Category[];
    filters: {
        search: string;
        categories: number[];
        min_price: string | null;
        max_price: string | null;
        sort: string;
        in_stock: string | boolean;
    };
}

// Define props
const props = defineProps<ProductProps>();

const form = reactive({
    search: props.filters.search || '',
    categories: Array.isArray(props.filters.categories)
        ? props.filters.categories.map(id => Number(id))
        : [],
    min_price: Number(props.filters.min_price) || 0,
    max_price: Number(props.filters.max_price) || 500,
    sort: props.filters.sort || 'mpn,asc',
    in_stock: props.filters.in_stock === 'true' || props.filters.in_stock === true,
});

// UI State
const filterOpen = ref(false);
const isLoading = ref(false);

// Watch for changes in the form object and automatically submit the filter
watch(form, debounce(() => {
    isLoading.value = true;

    const dataToSend = {
        ...form,
        in_stock: form.in_stock ? 'true' : undefined,
        search: form.search || undefined,
        categories: form.categories.length > 0 ? form.categories : undefined,
        min_price: form.min_price > 0 ? form.min_price : undefined,
        max_price: form.max_price !== 500 ? form.max_price : undefined,
    };

    router.get(
        '/products',
        dataToSend,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => { isLoading.value = false; },
        }
    );
}, 300));

const clearFilters = () => {
    form.search = '';
    form.categories = [];
    form.min_price = 0;
    form.max_price = 500;
    form.sort = 'mpn,asc';
    form.in_stock = false;
    filterOpen.value = false;
}

// Function to handle pagination link clicks
const paginate = (url: string) => {
    isLoading.value = true;
    router.get(url, {}, { preserveScroll: true, onFinish: () => { isLoading.value = false; } });
}
</script>

<template>
    <Head title="Product Collection" />
    <AppLayout>
        <section class="bg-white min-h-screen">
            <div class="mx-auto max-w-screen-2xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

                <header class="mb-6 lg:mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <h2 class="text-3xl font-bold text-gray-900">All Products</h2>
                    <div class="flex-grow max-w-lg">
                         <input
                            type="text"
                            v-model="form.search"
                            placeholder="Search by MPN..."
                            class="w-full rounded-md border border-gray-300 py-2.5 px-4 shadow-sm text-sm focus:border-yellow-500 focus:ring-yellow-500"
                        />
                    </div>
                </header>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[250px_1fr]">

                    <aside class="hidden lg:block">
                        <div class="sticky top-4 p-4 border border-gray-200 rounded-lg bg-white shadow-sm">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Filter By</h3>

                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Availability</h4>
                                <label for="FilterInStock" class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="FilterInStock" v-model="form.in_stock" class="size-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                    <span class="text-sm text-gray-700"> In Stock </span>
                                </label>
                            </div>

                            <div class="mb-4 border-t pt-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Departments</h4>
                                <div class="max-h-48 overflow-y-auto pr-2">
                                    <ul class="space-y-2">
                                        <li v-for="category in categories" :key="category.id">
                                            <label :for="'FilterCategory-' + category.id" class="inline-flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :id="'FilterCategory-' + category.id"
                                                    :value="category.id"
                                                    v-model="form.categories"
                                                    class="size-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm text-gray-700 hover:text-blue-600 transition"> {{ category.name }} </span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Price</h4>
                                <div class="px-1">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                                        <span>Min: £{{ form.min_price }}</span>
                                        <span>Max: £{{ form.max_price }}</span>
                                    </div>
                                    <input
                                        type="range"
                                        id="MinPrice"
                                        v-model.number="form.min_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-blue-600"
                                    />
                                    <input
                                        type="range"
                                        id="MaxPrice"
                                        v-model.number="form.max_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-blue-600 mt-2"
                                    />
                                </div>
                            </div>

                             <button
                                v-if="form.search || form.categories.length > 0 || form.min_price > 0 || form.max_price < 500 || form.in_stock"
                                @click="clearFilters"
                                class="w-full mt-6 text-sm text-blue-700 hover:text-blue-800 transition underline font-medium text-left"
                            >
                                Clear All Filters
                            </button>
                        </div>
                    </aside>

                    <div class="lg:col-span-1">
                        <div class="flex items-center justify-between mb-4 p-3 bg-gray-100 rounded-md border border-gray-200">
                             <p class="text-sm text-gray-700 font-medium">
                                Showing {{ products.data.length }} of {{ products.total }} results
                            </p>
                            <div class="flex items-center gap-2">
                                <label for="SortBy" class="text-sm font-medium text-gray-700 whitespace-nowrap">Sort by:</label>
                                <select
                                    id="SortBy"
                                    v-model="form.sort"
                                    class="h-9 rounded-md border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500"
                                >
                                    <option value="mpn,asc">Title A-Z</option>
                                    <option value="mpn,desc">Title Z-A</option>
                                    <option value="cost,desc">Price High to Low</option>
                                    <option value="cost,asc">Price Low to High</option>
                                </select>
                            </div>
                        </div>

                        <div
                            :class="{'opacity-50 pointer-events-none': isLoading, 'opacity-100': !isLoading}"
                            class="transition duration-300 min-h-96"
                        >
                            <ul v-if="products.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <li v-for="product in props.products.data" :key="product.id" class="block overflow-hidden bg-white border border-gray-200 rounded-lg group hover:border-yellow-500 transition duration-150 shadow-sm">
                                    <a :href="'/product/' + product.id" class="block">
                                        <div class="relative h-48 overflow-hidden bg-gray-50">
                                            <img
                                                src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
                                                alt="Product Image"
                                                class="w-full h-full object-contain transition duration-300 group-hover:scale-105"
                                            />
                                        </div>

                                        <div class="p-3">
                                            <h3 class="text-sm text-blue-700 group-hover:text-yellow-600 transition truncate font-medium">
                                                {{ product.mpn }}
                                            </h3>

                                            <p class="mt-1 text-lg font-bold text-gray-900">
                                                £{{ product.cost }}
                                            </p>
                                        </div>
                                    </a>
                                </li>
                            </ul>

                            <div v-else class="text-center py-20 bg-white rounded-lg border border-gray-200">
                                <p class="text-xl font-medium text-gray-500">
                                    No products found.
                                </p>
                                <button @click="clearFilters" class="mt-4 text-blue-600 hover:text-blue-800 font-semibold underline">
                                    Reset Filters
                                </button>
                            </div>
                        </div>

                        <div v-if="products.last_page > 1" class="mt-8">
                            <ol class="flex justify-center gap-1 text-sm font-medium">
                                <li v-for="link in products.links" :key="link.label">
                                    <button
                                        v-if="link.url"
                                        @click.prevent="paginate(link.url)"
                                        :class="{'block px-3 py-1.5 rounded-sm border text-center leading-5 transition': true, 'bg-yellow-400 text-gray-900 border-yellow-500 font-bold': link.active, 'bg-white text-blue-700 border-gray-300 hover:bg-gray-50': !link.active, 'cursor-not-allowed opacity-50': link.label.includes('Previous') && products.current_page === 1 || link.label.includes('Next') && products.current_page === products.last_page}"
                                        v-html="link.label.replace('Previous', '←').replace('Next', '→')"
                                    >
                                    </button>
                                    <span v-else class="block px-3 py-1.5 rounded-sm border bg-gray-100 text-gray-500 text-center leading-5 cursor-not-allowed" v-html="link.label.replace('Previous', '←').replace('Next', '→')"></span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <Transition name="slide-fade">
            <div v-if="filterOpen" @click.self="filterOpen = false" class="lg:hidden fixed inset-0 z-50 bg-black bg-opacity-50 transition-opacity">
                <div class="fixed inset-y-0 right-0 w-80 bg-white p-6 shadow-2xl overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Filter Options</h3>
                        <button @click="filterOpen = false" class="text-gray-500 hover:text-gray-900 transition">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <input type="text" v-model="form.search" placeholder="Search by MPN..." class="w-full rounded-md border-gray-300 py-2.5 shadow-sm sm:text-sm focus:border-yellow-500 focus:ring-yellow-500" />

                        <div class="border-b pb-4">
                            <h4 class="text-base font-semibold text-gray-900 mb-2">Availability</h4>
                            <label for="MobileFilterInStock" class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="MobileFilterInStock" v-model="form.in_stock" class="size-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm font-medium text-gray-700"> In Stock Only </span>
                            </label>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-base font-semibold text-gray-900 mb-2">Departments</h4>
                            <div class="max-h-48 overflow-y-auto pr-2">
                                <ul class="space-y-2">
                                    <li v-for="category in categories" :key="category.id">
                                        <label :for="'MobileFilterCategory-' + category.id" class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" :id="'MobileFilterCategory-' + category.id" :value="category.id" v-model="form.categories" class="size-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                            <span class="text-sm font-medium text-gray-700"> {{ category.name }} </span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-base font-semibold text-gray-900 mb-2">Price Range</h4>
                            <div class="px-1">
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>Min: £{{ form.min_price }}</span>
                                    <span>Max: £{{ form.max_price }}</span>
                                </div>
                                <input type="range" v-model.number="form.min_price" min="0" max="500" step="10" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-blue-600" />
                                <input type="range" v-model.number="form.max_price" min="0" max="500" step="10" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-blue-600 mt-2" />
                            </div>
                        </div>


                        <div class="mt-8 space-y-3">
                            <button
                                @click="filterOpen = false"
                                class="w-full inline-block rounded-lg bg-yellow-400 px-5 py-3 text-sm font-medium text-gray-900 transition hover:bg-yellow-500 focus:outline-none"
                            >
                                Apply Filters
                            </button>
                            <button
                                @click="clearFilters"
                                class="w-full inline-block rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none"
                            >
                                Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<style scoped>
/* Mobile Filter Slide-Over Transition Styles */
/* Kept the same transition logic for smooth UX */
.slide-fade-enter-active { transition: opacity 0.5s ease; }
.slide-fade-leave-active { transition: opacity 0.5s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; }
.slide-fade-enter-active > div { transition: transform 0.5s ease; }
.slide-fade-leave-active > div { transition: transform 0.5s ease; }
.slide-fade-enter-from > div { transform: translateX(100%); }
.slide-fade-leave-to > div { transform: translateX(100%); }
</style>
