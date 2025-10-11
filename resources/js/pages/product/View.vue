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
        <section class="bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
                <header class="mb-10">
                    <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Our Collection</h2>
                    <p class="mt-2 max-w-xl text-gray-600">
                        Discover products tailored to your needs. <span class="font-semibold">{{ products.total }} products found.</span>
                    </p>
                </header>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 p-4 bg-white rounded-lg shadow-sm border border-gray-200">
                    <button
                        @click="filterOpen = true"
                        class="sm:hidden flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700"
                    >
                        <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0012 14.586V21a1 1 0 01-2 0v-6.414a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Refine Search
                    </button>

                    <div class="flex items-center gap-2">
                        <label for="SortBy" class="text-sm font-medium text-gray-700 whitespace-nowrap">Sort by:</label>
                        <select
                            id="SortBy"
                            v-model="form.sort"
                            class="h-10 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="mpn,asc">Title (A-Z)</option>
                            <option value="mpn,desc">Title (Z-A)</option>
                            <option value="cost,desc">Price (High to Low)</option>
                            <option value="cost,asc">Price (Low to High)</option>
                        </select>
                    </div>

                    <button
                        v-if="form.search || form.categories.length > 0 || form.min_price > 0 || form.max_price < 500 || form.in_stock"
                        @click="clearFilters"
                        class="text-sm text-gray-500 hover:text-gray-900 transition underline hidden sm:block"
                    >
                        Clear All Filters
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_1fr] lg:gap-12">

                    <aside class="hidden lg:block">
                        <div class="sticky top-20 p-6 bg-white rounded-lg shadow-md">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Filter Options</h3>

                            <div class="mb-6">
                                <label for="Search" class="sr-only">Search</label>
                                <input
                                    type="text"
                                    id="Search"
                                    v-model="form.search"
                                    placeholder="Search by MPN..."
                                    class="w-full rounded-md border-gray-300 py-2.5 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <details class="group [&_summary::-webkit-details-marker]:hidden mb-4" open>
                                <summary class="flex cursor-pointer items-center justify-between gap-2 border-b pb-2 text-gray-900 transition">
                                    <span class="text-base font-semibold"> Availability </span>
                                    <span class="transition group-open:rotate-180">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </summary>
                                <div class="pt-3">
                                    <label for="FilterInStock" class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" id="FilterInStock" v-model="form.in_stock" class="size-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <span class="text-sm font-medium text-gray-700"> In Stock Only </span>
                                    </label>
                                </div>
                            </details>

                            <details class="group [&_summary::-webkit-details-marker]:hidden mb-4" open>
                                <summary class="flex cursor-pointer items-center justify-between gap-2 border-b pb-2 text-gray-900 transition">
                                    <span class="text-base font-semibold"> Categories </span>
                                    <span class="transition group-open:rotate-180">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </summary>

                                <div class="pt-3 max-h-48 overflow-y-auto">
                                    <ul class="space-y-2">
                                        <li v-for="category in categories" :key="category.id">
                                            <label :for="'FilterCategory-' + category.id" class="inline-flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :id="'FilterCategory-' + category.id"
                                                    :value="category.id"
                                                    v-model="form.categories"
                                                    class="size-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <span class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition"> {{ category.name }} </span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </details>

                            <details class="group [&_summary::-webkit-details-marker]:hidden mb-4" open>
                                <summary class="flex cursor-pointer items-center justify-between gap-2 border-b pb-2 text-gray-900 transition">
                                    <span class="text-base font-semibold"> Price Range </span>
                                    <span class="transition group-open:rotate-180">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </summary>

                                <div class="pt-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="MinPrice" class="block text-sm font-medium text-gray-700"> Min: <span class="font-bold text-gray-900">£{{ form.min_price }}</span> </label>
                                    </div>
                                    <input
                                        type="range"
                                        id="MinPrice"
                                        v-model.number="form.min_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="w-full h-2 bg-indigo-100 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full"
                                    />

                                    <div class="flex items-center justify-between mt-4 mb-2">
                                        <label for="MaxPrice" class="block text-sm font-medium text-gray-700"> Max: <span class="font-bold text-gray-900">£{{ form.max_price }}</span> </label>
                                    </div>
                                    <input
                                        type="range"
                                        id="MaxPrice"
                                        v-model.number="form.max_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="w-full h-2 bg-indigo-100 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full"
                                    />
                                </div>
                            </details>

                            <button
                                @click="clearFilters"
                                class="w-full mt-4 inline-block rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none"
                            >
                                Reset Filters
                            </button>
                        </div>
                    </aside>

                    <div class="lg:col-span-1">
                        <div
                            :class="{'opacity-50 pointer-events-none': isLoading, 'opacity-100': !isLoading}"
                            class="transition duration-300 min-h-96"
                        >
                            <ul v-if="products.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <li v-for="product in props.products.data" :key="product.id" class="group block overflow-hidden bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300">
                                    <a :href="'/product/' + product.id" class="block">
                                        <div class="relative h-64 overflow-hidden">
                                            <img
                                                src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
                                                alt="Product Image"
                                                class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                                            />
                                        </div>

                                        <div class="p-4 border-t border-gray-100">
                                            <h3 class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition truncate">
                                                {{ product.mpn }}
                                            </h3>

                                            <p class="mt-1 text-lg font-bold text-gray-900">
                                                £{{ product.cost }}
                                            </p>
                                        </div>
                                    </a>
                                </li>
                            </ul>

                            <div v-else class="text-center py-20 bg-white rounded-lg shadow-md">
                                <p class="text-xl font-medium text-gray-500">
                                    Sorry, no products were found matching your criteria.
                                </p>
                                <button @click="clearFilters" class="mt-4 text-indigo-600 hover:text-indigo-800 font-semibold underline">
                                    Reset Filters
                                </button>
                            </div>
                        </div>

                        <div v-if="products.last_page > 1" class="mt-12">
                            <ol class="flex justify-center gap-2 text-sm font-medium">
                                <li v-for="link in products.links" :key="link.label">
                                    <button
                                        v-if="link.url"
                                        @click.prevent="paginate(link.url)"
                                        :class="{'block px-4 py-2 rounded-lg border text-center leading-5 transition': true, 'bg-gray-900 text-white border-gray-900': link.active, 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !link.active, 'cursor-not-allowed opacity-50': link.label.includes('Previous') && products.current_page === 1 || link.label.includes('Next') && products.current_page === products.last_page}"
                                        v-html="link.label.replace('Previous', '←').replace('Next', '→')"
                                    >
                                    </button>
                                    <span v-else class="block px-4 py-2 rounded-lg border bg-gray-100 text-gray-500 text-center leading-5 cursor-not-allowed" v-html="link.label.replace('Previous', '←').replace('Next', '→')"></span>
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
                         <input type="text" v-model="form.search" placeholder="Search by MPN..." class="w-full rounded-md border-gray-300 py-2.5 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500" />

                        <details open class="group [&_summary::-webkit-details-marker]:hidden border-b pb-4">
                            <summary class="flex cursor-pointer items-center justify-between gap-2 text-gray-900 font-semibold text-base"> Availability </summary>
                            <div class="pt-3">
                                <label for="MobileFilterInStock" class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="MobileFilterInStock" v-model="form.in_stock" class="size-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm font-medium text-gray-700"> In Stock Only </span>
                                </label>
                            </div>
                        </details>

                        <details open class="group [&_summary::-webkit-details-marker]:hidden border-b pb-4">
                            <summary class="flex cursor-pointer items-center justify-between gap-2 text-gray-900 font-semibold text-base"> Categories </summary>
                            <div class="pt-3 max-h-48 overflow-y-auto">
                                <ul class="space-y-2">
                                    <li v-for="category in categories" :key="category.id">
                                        <label :for="'MobileFilterCategory-' + category.id" class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" :id="'MobileFilterCategory-' + category.id" :value="category.id" v-model="form.categories" class="size-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                            <span class="text-sm font-medium text-gray-700"> {{ category.name }} </span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </details>

                        <details open class="group [&_summary::-webkit-details-marker]:hidden border-b pb-4">
                            <summary class="flex cursor-pointer items-center justify-between gap-2 text-gray-900 font-semibold text-base"> Price Range </summary>
                            <div class="pt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <label for="MobileMinPrice" class="block text-sm font-medium text-gray-700"> Min: <span class="font-bold text-gray-900">£{{ form.min_price }}</span> </label>
                                </div>
                                <input type="range" id="MobileMinPrice" v-model.number="form.min_price" min="0" max="500" step="10" class="w-full h-2 bg-indigo-100 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full" />
                                <div class="flex items-center justify-between mt-4 mb-2">
                                    <label for="MobileMaxPrice" class="block text-sm font-medium text-gray-700"> Max: <span class="font-bold text-gray-900">£{{ form.max_price }}</span> </label>
                                </div>
                                <input type="range" id="MobileMaxPrice" v-model.number="form.max_price" min="0" max="500" step="10" class="w-full h-2 bg-indigo-100 rounded-lg appearance-none cursor-pointer range-lg [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full" />
                            </div>
                        </details>

                        <div class="mt-8 space-y-3">
                            <button
                                @click="filterOpen = false"
                                class="w-full inline-block rounded-lg bg-indigo-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none"
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
</template>

<style scoped>
/* Mobile Filter Slide-Over Transition Styles */
.slide-fade-enter-active {
  transition: opacity 0.5s ease;
}

.slide-fade-leave-active {
  transition: opacity 0.5s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active > div {
    transition: transform 0.5s ease;
}
.slide-fade-leave-active > div {
    transition: transform 0.5s ease;
}

.slide-fade-enter-from > div {
  transform: translateX(100%);
}
.slide-fade-leave-to > div {
  transform: translateX(100%);
}
</style>
