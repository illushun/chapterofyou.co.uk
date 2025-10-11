<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, reactive } from 'vue';
import { debounce } from 'lodash'; // Make sure to run: npm install lodash

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

// Watch for changes in the form object and automatically submit the filter
watch(form, debounce(() => {
    const dataToSend = {
        ...form,
        in_stock: form.in_stock ? 'true' : undefined,
        search: form.search || undefined,
        categories: form.categories.length > 0 ? form.categories : undefined,
        min_price: form.min_price > 0 ? form.min_price : undefined,
        max_price: form.max_price !== 500 ? form.max_price : undefined,
    };

    // Make an Inertia GET request to the current URL with the new query parameters
    router.get(
        route('product.view'),
        dataToSend,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}, 300)); // Debounce by 300ms

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
    router.get(url, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Product Collection" />
        <section>
            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
                <header>
                    <h2 class="text-xl font-bold text-gray-900 sm:text-3xl">Product Collection</h2>
                    <p class="mt-4 max-w-md text-gray-500">
                        Filter, sort, and find the perfect product from our collection.
                    </p>
                </header>

                <div class="mt-8 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button
                            @click="filterOpen = !filterOpen"
                            class="inline-flex items-center rounded-sm border border-gray-100 bg-white px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-700 lg:hidden"
                        >
                            <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0012 14.586V21a1 1 0 01-2 0v-6.414a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filters
                        </button>

                        <div class="flex rounded-sm border border-gray-100">
                            <button class="inline-flex size-10 items-center justify-center border-e text-gray-600 transition hover:bg-gray-50 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                            </button>
                            <button class="inline-flex size-10 items-center justify-center text-gray-600 transition hover:bg-gray-50 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="SortBy" class="sr-only">Sort By</label>
                        <select
                            id="SortBy"
                            v-model="form.sort"
                            class="h-10 rounded-sm border-gray-300 text-sm"
                        >
                            <option value="mpn,asc">Title, ASC</option>
                            <option value="mpn,desc">Title, DESC</option>
                            <option value="cost,desc">Price, DESC</option>
                            <option value="cost,asc">Price, ASC</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_3fr] lg:gap-8">
                    <aside
                        :class="{'fixed inset-0 z-50 bg-white shadow-xl p-8 lg:static lg:block lg:p-0 lg:shadow-none': filterOpen, 'hidden lg:block': !filterOpen}"
                    >
                        <div class="sticky top-10">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>

                            <button
                                v-if="filterOpen"
                                @click="filterOpen = false"
                                class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 lg:hidden"
                            >
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <div class="mb-6">
                                <label for="Search" class="sr-only">Search</label>
                                <input
                                    type="text"
                                    id="Search"
                                    v-model="form.search"
                                    placeholder="Search by MPN..."
                                    class="w-full rounded-md border-gray-200 py-2.5 pe-10 shadow-sm sm:text-sm"
                                />
                            </div>

                            <details
                                class="overflow-hidden rounded border border-gray-300 [&_summary::-webkit-details-marker]:hidden mb-4"
                                open
                            >
                                <summary
                                    class="flex cursor-pointer items-center justify-between gap-2 p-4 text-gray-900 transition"
                                >
                                    <span class="text-sm font-medium"> Availability </span>
                                    <span class="transition group-open:-rotate-180">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </summary>

                                <div class="border-t border-gray-200 bg-white">
                                    <ul class="space-y-1 border-t border-gray-200 p-4">
                                        <li>
                                            <label
                                                for="FilterInStock"
                                                class="inline-flex items-center gap-2"
                                            >
                                                <input
                                                    type="checkbox"
                                                    id="FilterInStock"
                                                    v-model="form.in_stock"
                                                    class="size-5 rounded border-gray-300"
                                                />

                                                <span class="text-sm font-medium text-gray-700">
                                                    In Stock Only
                                                </span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </details>

                            <details
                                class="overflow-hidden rounded border border-gray-300 [&_summary::-webkit-details-marker]:hidden mb-4"
                                open
                            >
                                <summary
                                    class="flex cursor-pointer items-center justify-between gap-2 p-4 text-gray-900 transition"
                                >
                                    <span class="text-sm font-medium"> Categories </span>

                                    <span class="transition group-open:-rotate-180">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </summary>

                                <div class="border-t border-gray-200 bg-white">
                                    <ul class="space-y-1 border-t border-gray-200 p-4">
                                        <li v-for="category in categories" :key="category.id">
                                            <label
                                                :for="'FilterCategory-' + category.id"
                                                class="inline-flex items-center gap-2"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :id="'FilterCategory-' + category.id"
                                                    :value="category.id"
                                                    v-model="form.categories"
                                                    class="size-5 rounded border-gray-300"
                                                />

                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ category.name }}
                                                </span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </details>

                            <details
                                class="overflow-hidden rounded border border-gray-300 [&_summary::-webkit-details-marker]:hidden mb-6"
                                open
                            >
                                <summary
                                    class="flex cursor-pointer items-center justify-between gap-2 p-4 text-gray-900 transition"
                                >
                                    <span class="text-sm font-medium"> Price Range </span>
                                    <span class="transition group-open:-rotate-180">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </summary>

                                <div class="border-t border-gray-200 p-4">
                                    <div class="flex items-center justify-between">
                                        <label for="MinPrice" class="block text-sm font-medium text-gray-700"> Min Price </label>
                                        <p class="text-sm text-gray-600">£{{ form.min_price }}</p>
                                    </div>
                                    <input
                                        type="range"
                                        id="MinPrice"
                                        v-model.number="form.min_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="mt-2 w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg"
                                    />

                                    <div class="mt-4 flex items-center justify-between">
                                        <label for="MaxPrice" class="block text-sm font-medium text-gray-700"> Max Price </label>
                                        <p class="text-sm text-gray-600">£{{ form.max_price }}</p>
                                    </div>
                                    <input
                                        type="range"
                                        id="MaxPrice"
                                        v-model.number="form.max_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="mt-2 w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg"
                                    />
                                </div>
                            </details>


                            <button
                                @click="clearFilters"
                                class="w-full inline-block rounded border border-gray-600 bg-white px-12 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50 focus:outline-none focus:ring active:bg-indigo-50"
                            >
                                Clear Filters
                            </button>

                        </div>
                    </aside>

                    <div class="lg:col-span-1">
                        <ul v-if="props.products.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <li v-for="product in props.products.data" :key="product.id">
                                <a :href="'/product/' + product.id" class="group block overflow-hidden">
                                    <img
                                        src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
                                        alt=""
                                        class="h-[350px] w-full object-cover transition duration-500 group-hover:scale-105 sm:h-[450px]"
                                    />

                                    <div class="relative bg-white pt-3">
                                        <h3 class="text-xs text-gray-700 group-hover:underline group-hover:underline-offset-4">
                                            {{ product.mpn }}
                                        </h3>

                                        <p class="mt-2">
                                            <span class="sr-only"> Regular Price </span>
                                            <span class="tracking-wider text-gray-900"> £{{ product.cost }} </span>
                                        </p>
                                    </div>
                                </a>
                            </li>

                        </ul>
                        <div v-else class="text-center py-10 text-gray-500">
                            No products found matching your filter criteria.
                        </div>

                        <div v-if="products.last_page > 1" class="mt-8">
                            <ol class="flex justify-center gap-1 text-xs font-medium">
                                <li v-for="link in products.links" :key="link.label">
                                    <button
                                        v-if="link.url"
                                        @click.prevent="paginate(link.url)"
                                        :class="{'block size-8 rounded border border-gray-100 bg-white text-gray-900 text-center leading-8': true, 'bg-gray-900 !text-white': link.active}"
                                        v-html="link.label"
                                    >
                                    </button>
                                    <span v-else class="block size-8 rounded border border-gray-100 bg-gray-50 text-gray-500 text-center leading-8 cursor-not-allowed" v-html="link.label"></span>
                                </li>
                            </ol>
                        </div>
                    </div>
                    </div>
            </div>
        </section>
</template>
