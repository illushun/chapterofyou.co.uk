<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, reactive, nextTick } from 'vue';
import { debounce } from 'lodash';
import axios from 'axios';

import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';

interface Category { id: number; name: string; }
interface Product { id: number; name: string; description: string; mpn: string; cost: number; stock_qty: number; parent_product_id: number; }
interface ProductsPaginated {
    current_page: number; data: Product[]; last_page: number;
    links: { url: string | null; label: string; active: boolean }[]; total: number;
}
interface ProductProps {
    products: ProductsPaginated;
    categories: Category[];
    filters: { search: string; categories: number[]; min_price: string | null; max_price: string | null; sort: string; in_stock: string | boolean; };
}

const props = defineProps<ProductProps & { wishlistedIds: number[] }>();

const form = reactive({
    search: props.filters.search || '',
    categories: Array.isArray(props.filters.categories) ? props.filters.categories.map(Number) : [],
    min_price: Number(props.filters.min_price) || 0,
    max_price: Number(props.filters.max_price) || 500,
    sort: props.filters.sort || 'mpn,asc',
    in_stock: props.filters.in_stock === 'true' || props.filters.in_stock === true,
});

const filterOpen = ref(false);
const isLoading = ref(false);
const isMinPriceEditing = ref(false);
const isMaxPriceEditing = ref(false);
const minPriceInput = ref(null);
const maxPriceInput = ref(null);
const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);
const wishlistedIds = ref<number[]>(props.wishlistedIds ?? []);

watch(form, debounce(() => {
    isLoading.value = true;
    router.get('/products', {
        ...form,
        in_stock: form.in_stock ? 'true' : undefined,
        search: form.search || undefined,
        categories: form.categories.length > 0 ? form.categories : undefined,
        min_price: form.min_price > 0 ? form.min_price : undefined,
        max_price: form.max_price !== 500 ? form.max_price : undefined,
    }, { preserveState: true, preserveScroll: true, replace: true, onFinish: () => { isLoading.value = false; } });
}, 300));

const clearFilters = () => {
    form.search = ''; form.categories = []; form.min_price = 0;
    form.max_price = 500; form.sort = 'mpn,asc'; form.in_stock = false;
    filterOpen.value = false;
};

const paginate = (url: string) => {
    isLoading.value = true;
    router.get(url, {}, { preserveScroll: true, onFinish: () => { isLoading.value = false; } });
};

const startEditMinPrice = () => {
    isMinPriceEditing.value = true;
    nextTick(() => (minPriceInput.value as any)?.focus());
};
const stopEditMinPrice = () => {
    form.min_price = Math.max(0, Math.min(form.max_price, Number(form.min_price)));
    isMinPriceEditing.value = false;
};
const startEditMaxPrice = () => {
    isMaxPriceEditing.value = true;
    nextTick(() => (maxPriceInput.value as any)?.focus());
};
const stopEditMaxPrice = () => {
    form.max_price = Math.min(500, Math.max(form.min_price, Number(form.max_price)));
    isMaxPriceEditing.value = false;
};

interface ProductCardData { id: number; name: string; mpn: string; cost: number; stock_qty: number; images?: { image: string }[]; total_unique_views?: number; }

const handleAddToCart = (product: ProductCardData) => {
    router.post('/cart/add', { product_id: product.id, quantity: 1 }, {
        preserveScroll: true,
        onSuccess: () => successToastRef.value?.show(`${product.name} added to cart!`, 'cart'),
        onError: (e) => console.error('Failed to add to cart:', e),
    });
};

const handleFavourite = async (product: ProductCardData) => {
    const idx = wishlistedIds.value.indexOf(product.id);
    idx === -1 ? wishlistedIds.value.push(product.id) : wishlistedIds.value.splice(idx, 1);
    try {
        const { data } = await axios.post(route('wishlist.toggle'), { product_id: product.id });
        successToastRef.value?.show(data.message, data.wishlisted ? 'favourite' : 'trash');
    } catch (err: any) {
        idx === -1 ? wishlistedIds.value.splice(wishlistedIds.value.indexOf(product.id), 1) : wishlistedIds.value.splice(idx, 0, product.id);
        if (err.response?.status === 401) window.location.href = route('login');
    }
};
</script>

<template>
    <NavBar />

    <Head title="Browse Products | Chapter of You" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="pv">
        <div class="pv-wrap">

            <!-- Page header + search -->
            <header class="pv-header">
                <div>
                    <h1 class="pv-title">Our Collection</h1>
                    <p class="pv-sub">Handcrafted reed diffusers, made just for you.</p>
                </div>
                <div class="pv-search-wrap">
                    <input type="text" v-model="form.search" placeholder="Search by name or product code..."
                        aria-label="Search products" class="pv-search" />
                    <svg class="pv-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
            </header>

            <div class="pv-grid">

                <!-- ── Desktop sidebar filter ── -->
                <aside class="pv-sidebar">
                    <div class="pv-filter-card">
                        <h3 class="pv-filter-title">Refine Results</h3>

                        <!-- Availability -->
                        <div class="pv-filter-section">
                            <h4 class="pv-filter-label">Availability</h4>
                            <Label for="FilterInStock" class="pv-check-label">
                                <Checkbox id="FilterInStock" name="FilterInStock"
                                    class="border-copy data-[state=checked]:bg-[var(--primary)] data-[state=checked]:text-primary-content"
                                    v-model="form.in_stock" />
                                <span>In Stock Only</span>
                            </Label>
                        </div>

                        <!-- Categories -->
                        <div class="pv-filter-section pv-filter-section--border">
                            <h4 class="pv-filter-label">Product Types</h4>
                            <div class="pv-category-scroll">
                                <ul class="pv-category-list">
                                    <li v-for="cat in categories" :key="cat.id">
                                        <Label :for="'FilterCategory-' + cat.id" class="pv-check-label">
                                            <Checkbox :id="'FilterCategory-' + cat.id" :value="cat.id"
                                                class="border-copy data-[state=checked]:bg-[var(--primary)] data-[state=checked]:text-primary-content"
                                                v-model="form.categories" />
                                            <span>{{ cat.name }}</span>
                                        </Label>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Price range -->
                        <div class="pv-filter-section pv-filter-section--border">
                            <h4 class="pv-filter-label">Price Range</h4>
                            <div class="pv-price-labels">
                                <span class="pv-price-val" @click="startEditMinPrice">
                                    <template v-if="!isMinPriceEditing">Min: £{{ form.min_price }}</template>
                                    <input v-else ref="minPriceInput" type="number" v-model.number="form.min_price"
                                        @blur="stopEditMinPrice" @keyup.enter="stopEditMinPrice" class="pv-price-input"
                                        min="0" max="500" />
                                </span>
                                <span class="pv-price-val" @click="startEditMaxPrice">
                                    <template v-if="!isMaxPriceEditing">Max: £{{ form.max_price }}</template>
                                    <input v-else ref="maxPriceInput" type="number" v-model.number="form.max_price"
                                        @blur="stopEditMaxPrice" @keyup.enter="stopEditMaxPrice" class="pv-price-input"
                                        min="0" max="500" />
                                </span>
                            </div>
                            <input type="range" v-model.number="form.min_price" min="0" max="500" step="10"
                                class="pv-range" @mousedown="isMinPriceEditing = false" />
                            <input type="range" v-model.number="form.max_price" min="0" max="500" step="10"
                                class="pv-range" @mousedown="isMaxPriceEditing = false" />
                        </div>

                        <!-- Clear -->
                        <button
                            v-if="form.search || form.categories.length > 0 || form.min_price > 0 || form.max_price < 500 || form.in_stock"
                            @click="clearFilters" class="pv-clear-btn">
                            Clear all filters
                        </button>
                    </div>
                </aside>

                <!-- ── Product grid column ── -->
                <div class="pv-main">

                    <!-- Toolbar -->
                    <div class="pv-toolbar">
                        <p class="pv-count">
                            Showing <strong>{{ products.data.length }}</strong> of <strong>{{ products.total }}</strong>
                            products
                        </p>
                        <div class="pv-toolbar-right">
                            <label for="SortBy" class="pv-sort-label">Sort by</label>
                            <select id="SortBy" v-model="form.sort" class="pv-sort-select">
                                <option value="name,asc">Name (A–Z)</option>
                                <option value="name,desc">Name (Z–A)</option>
                                <option value="cost,desc">Price (High–Low)</option>
                                <option value="cost,asc">Price (Low–High)</option>
                            </select>
                            <!-- Mobile filter trigger -->
                            <button @click="filterOpen = true" class="pv-filter-btn lg:hidden"
                                aria-label="Open filters">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0012 14.586V21a1 1 0 01-2 0v-6.414a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filters
                            </button>
                        </div>
                    </div>

                    <!-- Product grid -->
                    <div :class="{ 'opacity-40 pointer-events-none': isLoading }" class="transition duration-300">
                        <ul v-if="products.data.length" class="pv-product-grid">
                            <li v-for="product in products.data" :key="product.id">
                                <ProductSpringCard :product="product" :wishlisted="wishlistedIds.includes(product.id)"
                                    @add-to-cart="handleAddToCart(product)" @favourite="handleFavourite(product)" />
                            </li>
                        </ul>

                        <!-- No results -->
                        <div v-else class="pv-empty">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="pv-empty-icon">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                            <p>No products match your search.</p>
                            <button @click="clearFilters" class="btn-rose btn-rose--sm">Reset filters</button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.last_page > 1" class="pv-pagination">
                        <ol class="pv-page-list">
                            <li v-for="link in products.links" :key="link.label">
                                <button v-if="link.url" @click.prevent="paginate(link.url)" class="pv-page-btn"
                                    :class="{ 'pv-page-btn--active': link.active }"
                                    v-html="link.label.replace('Previous', '←').replace('Next', '→')"
                                    :aria-label="link.label">
                                </button>
                                <span v-else class="pv-page-btn pv-page-btn--disabled"
                                    v-html="link.label.replace('Previous', '←').replace('Next', '→')">
                                </span>
                            </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Mobile filter slide-over -->
    <Transition name="slide-fade">
        <div v-if="filterOpen" @click.self="filterOpen = false" class="pv-mobile-backdrop">
            <div class="pv-mobile-panel">
                <div class="pv-mobile-head">
                    <h3 class="pv-filter-title" style="margin-bottom:0">Filters</h3>
                    <button @click="filterOpen = false" class="pv-mobile-close" aria-label="Close filters">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="pv-mobile-body">
                    <div class="pv-filter-section pv-filter-section--border">
                        <h4 class="pv-filter-label">Availability</h4>
                        <label for="MobileFilterInStock" class="pv-check-label-native">
                            <input type="checkbox" id="MobileFilterInStock" v-model="form.in_stock"
                                class="pv-native-check" />
                            <span>In Stock Only</span>
                        </label>
                    </div>

                    <div class="pv-filter-section pv-filter-section--border">
                        <h4 class="pv-filter-label">Product Types</h4>
                        <ul class="pv-category-list">
                            <li v-for="cat in categories" :key="cat.id">
                                <label :for="'MobileFilterCategory-' + cat.id" class="pv-check-label-native">
                                    <input type="checkbox" :id="'MobileFilterCategory-' + cat.id" :value="cat.id"
                                        v-model="form.categories" class="pv-native-check" />
                                    <span>{{ cat.name }}</span>
                                </label>
                            </li>
                        </ul>
                    </div>

                    <div class="pv-filter-section pv-filter-section--border">
                        <h4 class="pv-filter-label">Price Range</h4>
                        <div class="pv-price-labels">
                            <span class="pv-price-val">Min: £{{ form.min_price }}</span>
                            <span class="pv-price-val">Max: £{{ form.max_price }}</span>
                        </div>
                        <input type="range" v-model.number="form.min_price" min="0" max="500" step="10"
                            class="pv-range" />
                        <input type="range" v-model.number="form.max_price" min="0" max="500" step="10"
                            class="pv-range" />
                    </div>

                    <div class="pv-mobile-actions">
                        <button @click="filterOpen = false" class="btn-rose" style="width:100%;justify-content:center;">
                            Show Results
                        </button>
                        <button @click="clearFilters" class="pv-clear-btn" style="width:100%;text-align:center;">
                            Reset All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <SuccessToast ref="successToastRef" />
</template>

<style scoped>
.pv {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.pv-wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

/* ── Header ── */
.pv-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.25rem;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5c9c7;
}

.pv-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.2rem;
}

.pv-sub {
    font-size: 0.92rem;
    color: #6b4f4f;
    font-style: italic;
}

.pv-search-wrap {
    position: relative;
    flex: 1;
    max-width: 360px;
    min-width: 200px;
}

.pv-search {
    width: 100%;
    padding: 0.65rem 2.5rem 0.65rem 0.9rem;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    background: #fffafa;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.pv-search:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.pv-search::placeholder {
    color: #9a7070;
}

.pv-search-icon {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #c9a4a4;
    pointer-events: none;
}

/* ── Layout ── */
.pv-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .pv-grid {
        grid-template-columns: 1fr;
    }
}

.pv-sidebar {
    display: none;
}

@media (min-width: 1024px) {
    .pv-sidebar {
        display: block;
        position: sticky;
        top: 88px;
    }
}

/* ── Filter card ── */
.pv-filter-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 1.4rem;
    position: relative;
    overflow: hidden;
}

.pv-filter-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.pv-filter-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5c9c7;
}

.pv-filter-section {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
}

.pv-filter-section--border {
    border-top: 1px solid #e5c9c7;
    padding-top: 1rem;
}

.pv-filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #8c4a50;
    margin-bottom: 0.65rem;
}

.pv-check-label {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    cursor: pointer;
}

.pv-check-label-native {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    cursor: pointer;
    margin-bottom: 0.4rem;
}

.pv-native-check {
    width: 15px;
    height: 15px;
    accent-color: #8c4a50;
    cursor: pointer;
}

.pv-category-scroll {
    max-height: 200px;
    overflow-y: auto;
    padding-right: 4px;
}

.pv-category-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

/* Price range */
.pv-price-labels {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.pv-price-val {
    font-size: 0.8rem;
    color: #6b4f4f;
    font-weight: 600;
    cursor: pointer;
    transition: color 0.2s;
}

.pv-price-val:hover {
    color: #8c4a50;
}

.pv-price-input {
    width: 64px;
    padding: 0.1rem 0.3rem;
    border: 1px solid #e5c9c7;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #2d1a1a;
    background: #fdf4f3;
    outline: none;
}

.pv-range {
    width: 100%;
    height: 4px;
    appearance: none;
    background: #e5c9c7;
    border-radius: 999px;
    cursor: pointer;
    margin-top: 0.4rem;
    accent-color: #8c4a50;
}

.pv-clear-btn {
    width: 100%;
    padding-top: 0.75rem;
    margin-top: 0.5rem;
    border-top: 1px solid #e5c9c7;
    font-size: 0.82rem;
    font-weight: 600;
    color: #8c4a50;
    background: none;
    border-left: none;
    border-right: none;
    border-bottom: none;
    cursor: pointer;
    transition: color 0.2s;
    text-align: center;
}

.pv-clear-btn:hover {
    color: #6a3038;
}

/* ── Toolbar ── */
.pv-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding: 0.75rem 1rem;
    border: 1px solid #e5c9c7;
    border-radius: 12px;
    background: #fffafa;
}

.pv-count {
    font-size: 0.88rem;
    color: #6b4f4f;
}

.pv-count strong {
    color: #2d1a1a;
}

.pv-toolbar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.pv-sort-label {
    font-size: 0.82rem;
    color: #6b4f4f;
    white-space: nowrap;
    display: none;
}

@media (min-width: 640px) {
    .pv-sort-label {
        display: block;
    }
}

.pv-sort-select {
    padding: 0.45rem 0.8rem;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    outline: none;
    cursor: pointer;
    transition: border-color 0.2s;
}

.pv-sort-select:focus {
    border-color: #8c4a50;
}

.pv-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(168, 80, 88, 0.18);
    transition: transform 0.2s;
}

.pv-filter-btn:hover {
    transform: translateY(-1px);
}

/* ── Product grid ── */
.pv-product-grid {
    list-style: none;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.1rem;
}

@media (min-width: 640px) {
    .pv-product-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 900px) {
    .pv-product-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .pv-product-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* ── Empty state ── */
.pv-empty {
    text-align: center;
    padding: 4rem 2rem;
    border: 1.5px dashed #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.9rem;
}

.pv-empty-icon {
    color: #c9a4a4;
}

.pv-empty p {
    font-size: 0.95rem;
    color: #6b4f4f;
    font-style: italic;
}

/* ── Pagination ── */
.pv-pagination {
    margin-top: 2.5rem;
}

.pv-page-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.4rem;
    list-style: none;
}

.pv-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 0.6rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}

.pv-page-btn:hover:not(.pv-page-btn--disabled) {
    background: #f5e4e4;
    border-color: #c9a4a4;
    color: #2d1a1a;
}

.pv-page-btn--active {
    background: linear-gradient(135deg, #c47078, #a85058);
    border-color: #a85058;
    color: #fff;
    font-weight: 700;
}

.pv-page-btn--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Buttons ── */
.btn-rose {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.4rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.btn-rose:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.btn-rose--sm {
    padding: 0.5rem 1.1rem;
    font-size: 0.85rem;
}

/* ── Mobile filter backdrop + panel ── */
.pv-mobile-backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(45, 26, 26, 0.45);
    backdrop-filter: blur(3px);
}

.pv-mobile-panel {
    position: fixed;
    inset-y: 0;
    right: 0;
    width: 300px;
    background: #fffafa;
    border-left: 1px solid #e5c9c7;
    box-shadow: -8px 0 32px rgba(45, 26, 26, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.pv-mobile-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid #e5c9c7;
    flex-shrink: 0;
}

.pv-mobile-close {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid #e5c9c7;
    background: transparent;
    color: #6b4f4f;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.pv-mobile-close:hover {
    background: #faeaea;
    color: #8c4a50;
}

.pv-mobile-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.pv-mobile-actions {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-top: 1rem;
}

/* ── Slide transition ── */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active .pv-mobile-panel,
.slide-fade-leave-active .pv-mobile-panel {
    transition: transform 0.3s ease;
}

.slide-fade-enter-from .pv-mobile-panel,
.slide-fade-leave-to .pv-mobile-panel {
    transform: translateX(100%);
}
</style>
