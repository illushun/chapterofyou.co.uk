<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, reactive, nextTick } from 'vue';
import { debounce } from 'lodash';
import { usePage } from '@inertiajs/vue3';

import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    description: string;
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
const isMinPriceEditing = ref(false);
const isMaxPriceEditing = ref(false);
const minPriceInput = ref(null);
const maxPriceInput = ref(null);

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

const startEditMinPrice = () => {
    isMinPriceEditing.value = true;
    nextTick(() => {
        if (minPriceInput.value) {
            minPriceInput.value.focus();
        }
    });
};

const stopEditMinPrice = () => {
    form.min_price = Math.max(0, Math.min(form.max_price, Number(form.min_price)));
    isMinPriceEditing.value = false;
};

const startEditMaxPrice = () => {
    isMaxPriceEditing.value = true;
    nextTick(() => {
        if (maxPriceInput.value) {
            maxPriceInput.value.focus();
        }
    });
};

const stopEditMaxPrice = () => {
    form.max_price = Math.min(500, Math.max(form.min_price, Number(form.max_price)));
    isMaxPriceEditing.value = false;
};

const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);

const handleAddToCart = (product: ProductCardData) => {
    console.log(`Adding product ${product.id} to cart...`);
    // router.post('/cart', { product_id: product.id });

    if (successToastRef.value) {
        successToastRef.value.show(`${product.name} added to cart!`, 'cart');
    }
};

const handleFavourite = (product: ProductCardData) => {
    console.log(`Toggling favourite for product ${product.id}...`);
    // router.post(`/products/${product.id}/favourite`);

    if (successToastRef.value) {
        successToastRef.value.show(`${product.name} added to your wishlist!`, 'favourite');
    }
};
</script>

<template>
    <NavBar />

    <Head title="Browse Products | Chapter of You" />
    <section class="min-h-screen border-b-2 border-black" style="background-color: #f1efef;">
        <div class="mx-auto max-w-screen-2xl px-4 py-20 sm:px-6 lg:px-8">

            <header class="mb-8 lg:mb-10 text-gray-900">
                <h2 class="text-4xl font-extrabold tracking-tight">Products</h2>
                <p class="mt-2 text-gray-600 max-w-lg">
                    Find exactly what you need with powerful filtering tools.
                </p>
                <div class="mt-4 max-w-lg rounded-lg">
                    <input
                        type="text"
                        v-model="form.search"
                        placeholder="Search by product name or MPN..."
                        aria-label="Search products by name or MPN"
                        class="relative rounded-lg -m-0.5 w-full border-2 border-black bg-white py-2.5 px-4 text-sm text-gray-900 placeholder-gray-500 focus:border-sky-500 focus:ring-sky-500 shadow-sm transition"
                    />
                </div>
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_1fr]">

                <aside class="hidden lg:block">
                    <div class="sticky top-4 rounded-lg border-2 border-black bg-purple-200">
                        <div class="relative rounded-lg -m-0.5 p-6 bg-white border-2 border-black">
                            <h3 class="text-xl font-bold text-gray-900 mb-6 border-b-2 border-black pb-3">Refine Results</h3>

                            <div class="mb-6">
                                <h4 class="text-base font-semibold text-gray-800 mb-2">Availability</h4>
                                <label for="FilterInStock" class="inline-flex items-center gap-2 cursor-pointer transition hover:text-sky-600">
                                    <input type="checkbox" id="FilterInStock" v-model="form.in_stock" class="size-5 border-2 border-black text-sky-600 focus:ring-sky-500" />
                                    <span class="text-sm font-medium text-gray-700"> In Stock Only </span>
                                </label>
                            </div>

                            <div class="mb-6 border-t-2 border-black pt-4">
                                <h4 class="text-base font-semibold text-gray-800 mb-3">Product Types</h4>
                                <div class="max-h-56 overflow-y-auto pr-2">
                                    <ul class="space-y-3">
                                        <li v-for="category in categories" :key="category.id">
                                            <label :for="'FilterCategory-' + category.id" class="inline-flex items-center gap-3 cursor-pointer transition hover:text-sky-600">
                                                <input
                                                    type="checkbox"
                                                    :id="'FilterCategory-' + category.id"
                                                    :value="category.id"
                                                    v-model="form.categories"
                                                    class="size-4 border-2 border-black text-sky-600 focus:ring-sky-500"
                                                />
                                                <span class="text-sm text-gray-700 font-medium"> {{ category.name }} </span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="border-t-2 border-black pt-4">
                                <h4 class="text-base font-semibold text-gray-800 mb-3">Price Range</h4>
                                <div class="px-1">
                                    <div class="flex justify-between text-sm text-gray-600 mb-2 font-bold">
                                        <span class="cursor-pointer hover:text-sky-600" @click="startEditMinPrice">
                                            <span v-if="!isMinPriceEditing">Min: £{{ form.min_price }}</span>
                                            <div v-else class="border-2 border-black bg-gray-100">
                                                <input
                                                    ref="minPriceInput"
                                                    type="number"
                                                    v-model.number="form.min_price"
                                                    @blur="stopEditMinPrice"
                                                    @keyup.enter="stopEditMinPrice"
                                                    class="relative -m-0.5 w-20 p-0 text-sm border-2 border-black focus:border-sky-500 focus:ring-sky-500"
                                                    min="0"
                                                    max="500"
                                                />
                                            </div>
                                        </span>
                                        <span class="cursor-pointer hover:text-sky-600" @click="startEditMaxPrice">
                                            <span v-if="!isMaxPriceEditing">Max: £{{ form.max_price }}</span>
                                            <div v-else class="border-2 border-black bg-gray-100">
                                                <input
                                                    ref="maxPriceInput"
                                                    type="number"
                                                    v-model.number="form.max_price"
                                                    @blur="stopEditMaxPrice"
                                                    @keyup.enter="stopEditMaxPrice"
                                                    class="relative -m-0.5 w-20 p-0 text-sm border-2 border-black focus:border-sky-500 focus:ring-sky-500 text-right"
                                                    min="0"
                                                    max="500"
                                                />
                                            </div>
                                        </span>
                                    </div>
                                    <input
                                        type="range"
                                        id="MinPrice"
                                        v-model.number="form.min_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="w-full h-1 bg-gray-300 appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-sky-600 [&::-webkit-slider-thumb]:size-4 [&::-webkit-slider-thumb]:rounded-full"
                                        @mousedown="isMinPriceEditing = false"
                                    />
                                    <input
                                        type="range"
                                        id="MaxPrice"
                                        v-model.number="form.max_price"
                                        min="0"
                                        max="500"
                                        step="10"
                                        class="w-full h-1 bg-gray-300 appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-sky-600 [&::-webkit-slider-thumb]:size-4 [&::-webkit-slider-thumb]:rounded-full mt-2"
                                        @mousedown="isMaxPriceEditing = false"
                                    />
                                </div>
                            </div>

                             <button
                                v-if="form.search || form.categories.length > 0 || form.min_price > 0 || form.max_price < 500 || form.in_stock"
                                @click="clearFilters"
                                class="w-full mt-6 text-sm text-red-600 hover:text-red-700 transition font-semibold text-center py-2 border-t-2 border-black"
                            >
                                Clear All Filters
                            </button>
                        </div>
                    </div>
                </aside>

                <div class="lg:col-span-1">
                    <div class="relative rounded-lg -m-0.5 flex items-center justify-between mb-6 p-3 bg-white border-2 border-black">
                        <p class="text-sm text-gray-700 font-medium">
                            Showing **{{ products.data.length }} of {{ products.total }}** results
                        </p>
                        <div class="flex items-center gap-3">
                            <label for="SortBy" class="text-sm font-medium text-gray-700 whitespace-nowrap hidden sm:block">Sort by:</label>
                            <div class="border-2 rounded-lg border-black bg-gray-100">
                                <select
                                    id="SortBy"
                                    v-model="form.sort"
                                    class="relative rounded-lg -m-0.5 h-9 border-2 border-black bg-white text-sm text-gray-900 focus:border-sky-600 focus:ring-sky-600 shadow-inner"
                                >
                                    <option value="name,asc">Name (A-Z)</option>
                                    <option value="name,desc">Name (Z-A)</option>
                                    <option value="cost,desc">Price (High)</option>
                                    <option value="cost,asc">Price (Low)</option>
                                </select>
                            </div>
                            <button
                                @click="filterOpen = true"
                                class="lg:hidden rounded-lg text-white bg-blue-600 hover:bg-blue-700 border-2 border-black p-2 transition shadow-md"
                                aria-label="Toggle mobile filter panel"
                            >
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0012 14.586V21a1 1 0 01-2 0v-6.414a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div
                        :class="{'opacity-40 pointer-events-none': isLoading, 'opacity-100': !isLoading}"
                        class="transition duration-500 min-h-96"
                    >
                        <ul v-if="products.data.length" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <li v-for="product in props.products.data" :key="product.id">
                                <ProductSpringCard
                                    :product="product"
                                    @add-to-cart="handleAddToCart(product)"
                                    @favourite="handleFavourite(product)"
                                />
                            </li>
                        </ul>

                        <div v-else class="text-center py-20 border-2 border-black bg-red-100">
                            <p class="text-xl font-medium text-black">
                                No products found matching your refined search.
                            </p>
                            <button @click="clearFilters" class="mt-6 text-blue-600 hover:text-blue-700 font-semibold underline transition">
                                Reset All Filters
                            </button>
                        </div>
                    </div>

                    <div v-if="products.last_page > 1" class="mt-10">
                        <ol class="flex justify-center gap-4 text-sm font-medium">
                            <li v-for="link in products.links" :key="link.label">
                                <div v-if="link.url"
                                    :class="{'border-2 border-black bg-gray-200': true, 'bg-sky-600 text-white': link.active, 'bg-white text-gray-700 hover:bg-gray-100': !link.active}"
                                >
                                    <button
                                        @click.prevent="paginate(link.url)"
                                        :class="{'block px-4 py-2 text-center leading-5 transition relative -m-0.5 border-2 border-black': true, 'bg-sky-600 text-white border-sky-700 font-bold shadow-md': link.active, 'bg-white text-gray-700 hover:bg-gray-100': !link.active, 'cursor-not-allowed opacity-50': link.label.includes('Previous') && products.current_page === 1 || link.label.includes('Next') && products.current_page === products.last_page}"
                                        v-html="link.label.replace('Previous', '←').replace('Next', '→')"
                                        :aria-label="link.label.includes('Previous') ? 'Previous Page' : link.label.includes('Next') ? 'Next Page' : 'Go to page ' + link.label"
                                    >
                                    </button>
                                </div>
                                <span v-else class="block px-4 py-2 border-2 border-black bg-gray-100 text-gray-500 text-center leading-5 cursor-not-allowed" v-html="link.label.replace('Previous', '←').replace('Next', '→')" aria-disabled="true"></span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Transition name="slide-fade">
        <div v-if="filterOpen" @click.self="filterOpen = false" class="lg:hidden fixed inset-0 z-50 bg-black bg-opacity-70 transition-opacity">
            <div class="fixed inset-y-0 right-0 w-80 bg-white p-6 shadow-2xl overflow-y-auto border-l-2 border-black">
                <div class="flex justify-between items-center mb-6 border-b-2 border-black pb-3">
                    <h3 class="text-xl font-bold text-gray-900">Filters</h3>
                    <button @click="filterOpen = false" class="text-gray-500 hover:text-gray-900 transition" aria-label="Close mobile filter panel">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <input type="text" v-model="form.search" placeholder="Quick search..." class="relative rounded-lg -m-0.5 w-full border-2 border-black bg-white py-2.5 shadow-sm sm:text-sm focus:border-blue-500 focus:ring-blue-500" />

                    <div class="border-b-2 border-black pb-4">
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Availability</h4>
                        <label for="MobileFilterInStock" class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="MobileFilterInStock" v-model="form.in_stock" class="size-5 border-2 border-black text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm font-medium text-gray-700"> In Stock Only </span>
                        </label>
                    </div>

                    <div class="border-b-2 border-black pb-4">
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Product Types</h4>
                        <div class="max-h-48 overflow-y-auto pr-2">
                            <ul class="space-y-2">
                                <li v-for="category in categories" :key="category.id">
                                    <label :for="'MobileFilterCategory-' + category.id" class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" :id="'MobileFilterCategory-' + category.id" :value="category.id" v-model="form.categories" class="size-5 border-2 border-black text-blue-600 focus:ring-blue-500" />
                                        <span class="text-sm font-medium text-gray-700"> {{ category.name }} </span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="border-b-2 border-black pb-4">
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Price Range</h4>
                        <div class="px-1">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Min: £{{ form.min_price }}</span>
                                <span>Max: £{{ form.max_price }}</span>
                            </div>
                            <input type="range" v-model.number="form.min_price" min="0" max="500" step="10" class="w-full h-1 bg-gray-300 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-blue-600 mt-2" />
                            <input type="range" v-model.number="form.max_price" min="0" max="500" step="10" class="w-full h-1 bg-gray-300 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:bg-blue-600 mt-2" />
                        </div>
                    </div>


                    <div class="mt-8 space-y-3">
                        <button
                            @click="filterOpen = false"
                            class="relative rounded-lg -m-0.5 w-full inline-block bg-blue-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-700 border-2 border-black"
                        >
                            Apply Filters
                        </button>
                        <button
                            @click="clearFilters"
                            class="relative rounded-lg -m-0.5 w-full inline-block border-2 border-black bg-white px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <SuccessToast ref="successToastRef" />
</template>

<style scoped>
/* Mobile Filter Slide-Over Transition Styles */
.slide-fade-enter-active { transition: opacity 0.5s ease; }
.slide-fade-leave-active { transition: opacity 0.5s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; }
.slide-fade-enter-active > div { transition: transform 0.5s ease; }
.slide-fade-leave-active > div { transition: transform 0.5s ease; }
.slide-fade-enter-from > div { transform: translateX(100%); }
.slide-fade-leave-to > div { transform: translateX(100%); }
</style>
