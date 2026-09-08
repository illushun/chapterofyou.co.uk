<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import JsonLdSchema from '@/components/JsonLdSchema.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
}
interface Product {
    id: number;
    name: string;
    description: string;
    details: string;
    mpn: string;
    cost: number;
    stock_qty: number;
    parent_product_id: number;
    scent_families?: string | null;
    images?: { image: string }[];
    total_unique_views?: number;
    seo?: { slug: string };
}
interface ProductPage {
    current_page: number;
    data: Product[];
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}
interface Props {
    products: ProductPage;
    categories: Category[];
    filters: {
        search: string;
        categories: number[];
        min_price: string | null;
        max_price: string | null;
        sort: string;
        in_stock: string | boolean;
    };
    wishlistedIds: number[];
}

const props = defineProps<Props>();
const form = reactive({
    search: props.filters.search || '',
    categories: Array.isArray(props.filters.categories)
        ? props.filters.categories
        : [],
    min_price: Number(props.filters.min_price) || 0,
    max_price: Number(props.filters.max_price) || 500,
    sort: props.filters.sort || 'name,asc',
    in_stock:
        props.filters.in_stock === 'true' || props.filters.in_stock === true,
});
const filterOpen = ref(false);
const isLoading = ref(false);
const toast = ref<InstanceType<typeof SuccessToast> | null>(null);
const wishlistedIds = ref(props.wishlistedIds ?? []);
const activeFilterCount = computed(
    () =>
        form.categories.length +
        Number(form.in_stock) +
        Number(form.min_price > 0 || form.max_price < 500),
);
const hasFilters = computed(() =>
    Boolean(form.search || activeFilterCount.value),
);
const seo = computed(() =>
    useSeoHead({
        title: 'Shop Reed Diffusers',
        description:
            'Browse handmade reed diffusers and find a fragrance that feels right for your home. Free UK delivery on orders of £50 or more.',
        canonical:
            props.products.current_page > 1
                ? `/products?page=${props.products.current_page}`
                : '/products',
    }),
);
const productListSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    name: 'Reed Diffusers',
    url: 'https://www.chapterofyou.co.uk/products',
    itemListElement: props.products.data.map((product, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        url: `https://www.chapterofyou.co.uk/product/${product.seo?.slug || product.id}`,
        name: product.name,
    })),
}));

watch(
    form,
    useDebounceFn(() => {
        isLoading.value = true;
        router.get(
            '/products',
            {
                sort: form.sort,
                search: form.search || undefined,
                category: form.categories.length
                    ? form.categories.join(',')
                    : undefined,
                min_price: form.min_price > 0 ? form.min_price : undefined,
                max_price: form.max_price < 500 ? form.max_price : undefined,
                in_stock: form.in_stock ? 'true' : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => {
                    isLoading.value = false;
                },
            },
        );
    }, 300),
);

function clearFilters() {
    form.search = '';
    form.categories = [];
    form.min_price = 0;
    form.max_price = 500;
    form.sort = 'name,asc';
    form.in_stock = false;
    filterOpen.value = false;
}
function paginationLabel(label: string) {
    if (label.includes('Previous')) return '← Previous';
    if (label.includes('Next')) return 'Next →';
    return label.replace(/&[^;]+;/g, '').trim();
}
function paginate(url: string) {
    isLoading.value = true;
    router.get(
        url,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
}
function addToCart(product: Product, quantity = 1) {
    router.post(
        '/cart/add',
        { product_id: product.id, quantity },
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.value?.show(`${product.name} added to basket`, 'cart'),
        },
    );
}
async function favourite(product: Product) {
    const index = wishlistedIds.value.indexOf(product.id);
    index === -1
        ? wishlistedIds.value.push(product.id)
        : wishlistedIds.value.splice(index, 1);
    try {
        const { data } = await axios.post(route('wishlist.toggle'), {
            product_id: product.id,
        });
        toast.value?.show(
            data.message,
            data.wishlisted ? 'favourite' : 'trash',
        );
    } catch (error: any) {
        index === -1
            ? wishlistedIds.value.splice(
                  wishlistedIds.value.indexOf(product.id),
                  1,
              )
            : wishlistedIds.value.splice(index, 0, product.id);
        if (error.response?.status === 401)
            window.location.href = route('login');
    }
}
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />
    <JsonLdSchema :schema="productListSchema" />
    <main class="collection coy-storefront">
        <header class="collection-hero coy-page-header">
            <div class="coy-container coy-page-header__inner">
                <div>
                    <h1 class="coy-page-header__title">Reed diffusers</h1>
                    <p class="coy-page-header__meta">
                        Handmade fragrances for every room and mood.
                    </p>
                </div>
                <a href="/scent-finder" class="coy-page-header__action"
                    >Find your scent <span aria-hidden="true">→</span></a
                >
            </div>
        </header>

        <div class="coy-container collection-layout">
            <aside class="filters" aria-label="Product filters">
                <div class="filters-heading">
                    <h2>Filter products</h2>
                    <button
                        v-if="hasFilters"
                        type="button"
                        @click="clearFilters"
                    >
                        Clear all
                    </button>
                </div>
                <div class="filter-group">
                    <h3>Availability</h3>
                    <label
                        ><input v-model="form.in_stock" type="checkbox" /> In
                        stock only</label
                    >
                </div>
                <fieldset class="filter-group">
                    <legend>Product type</legend>
                    <label v-for="category in categories" :key="category.id"
                        ><input
                            v-model="form.categories"
                            type="checkbox"
                            :value="category.slug"
                        />
                        {{ category.name }}</label
                    >
                </fieldset>
                <div class="filter-group">
                    <h3>Price</h3>
                    <div class="price-inputs">
                        <label
                            >Minimum
                            <span
                                >£<input
                                    v-model.number="form.min_price"
                                    type="number"
                                    min="0"
                                    :max="form.max_price" /></span></label
                        ><label
                            >Maximum
                            <span
                                >£<input
                                    v-model.number="form.max_price"
                                    type="number"
                                    :min="form.min_price"
                                    max="500" /></span
                        ></label>
                    </div>
                </div>
            </aside>

            <section class="results" aria-labelledby="results-heading">
                <div class="toolbar">
                    <div>
                        <h2 id="results-heading">All products</h2>
                        <p>
                            {{ products.total }}
                            {{ products.total === 1 ? 'product' : 'products' }}
                        </p>
                    </div>
                    <div class="toolbar-actions">
                        <button
                            type="button"
                            class="filter-trigger"
                            @click="filterOpen = true"
                        >
                            <svg aria-hidden="true" viewBox="0 0 24 24">
                                <path d="M4 6h16M7 12h10M10 18h4" /></svg
                            >Filters
                            <b v-if="activeFilterCount">{{
                                activeFilterCount
                            }}</b></button
                        ><label
                            >Sort by
                            <select v-model="form.sort">
                                <option value="name,asc">Name: A to Z</option>
                                <option value="name,desc">Name: Z to A</option>
                                <option value="cost,asc">
                                    Price: low to high
                                </option>
                                <option value="cost,desc">
                                    Price: high to low
                                </option>
                            </select></label
                        >
                    </div>
                </div>

                <div v-if="form.search" class="search-summary">
                    Results for <strong>“{{ form.search }}”</strong
                    ><button type="button" @click="form.search = ''">
                        Clear search
                    </button>
                </div>
                <div
                    class="product-area"
                    :class="{ 'product-area--loading': isLoading }"
                    aria-live="polite"
                    :aria-busy="isLoading"
                >
                    <ul v-if="products.data.length" class="product-grid">
                        <li v-for="product in products.data" :key="product.id">
                            <ProductSpringCard
                                :product="product"
                                :wishlisted="wishlistedIds.includes(product.id)"
                                @add-to-cart="addToCart(product, $event)"
                                @favourite="favourite(product)"
                            />
                        </li>
                    </ul>
                    <div v-else class="empty">
                        <span aria-hidden="true">◇</span>
                        <h2>No products found</h2>
                        <p>
                            Try a different search or remove some filters to see
                            more of the collection.
                        </p>
                        <button
                            type="button"
                            class="coy-button coy-button--primary"
                            @click="clearFilters"
                        >
                            Reset filters
                        </button>
                    </div>
                </div>

                <nav
                    v-if="products.last_page > 1"
                    class="pagination"
                    aria-label="Product pages"
                >
                    <template v-for="link in products.links" :key="link.label"
                        ><button
                            v-if="link.url"
                            type="button"
                            :class="{ active: link.active }"
                            :aria-current="link.active ? 'page' : undefined"
                            :aria-label="link.label"
                            @click="paginate(link.url)"
                        >
                            {{ paginationLabel(link.label) }}
                        </button></template
                    >
                </nav>
            </section>
        </div>
    </main>

    <Transition name="drawer"
        ><div
            v-if="filterOpen"
            class="drawer-backdrop"
            @click.self="filterOpen = false"
        >
            <aside class="drawer-panel" aria-label="Mobile product filters">
                <header>
                    <h2>Filter products</h2>
                    <button
                        type="button"
                        aria-label="Close filters"
                        @click="filterOpen = false"
                    >
                        ×
                    </button>
                </header>
                <div class="drawer-body">
                    <div class="filter-group">
                        <h3>Availability</h3>
                        <label
                            ><input v-model="form.in_stock" type="checkbox" />
                            In stock only</label
                        >
                    </div>
                    <fieldset class="filter-group">
                        <legend>Product type</legend>
                        <label v-for="category in categories" :key="category.id"
                            ><input
                                v-model="form.categories"
                                type="checkbox"
                                :value="category.slug"
                            />
                            {{ category.name }}</label
                        >
                    </fieldset>
                    <div class="filter-group">
                        <h3>Price</h3>
                        <div class="price-inputs">
                            <label
                                >Minimum
                                <span
                                    >£<input
                                        v-model.number="form.min_price"
                                        type="number"
                                        min="0"
                                        :max="form.max_price" /></span></label
                            ><label
                                >Maximum
                                <span
                                    >£<input
                                        v-model.number="form.max_price"
                                        type="number"
                                        :min="form.min_price"
                                        max="500" /></span
                            ></label>
                        </div>
                    </div>
                </div>
                <footer>
                    <button
                        type="button"
                        class="coy-button coy-button--primary"
                        @click="filterOpen = false"
                    >
                        Show {{ products.total }} products</button
                    ><button
                        v-if="hasFilters"
                        type="button"
                        class="clear-button"
                        @click="clearFilters"
                    >
                        Clear all filters
                    </button>
                </footer>
            </aside>
        </div></Transition
    >
    <SuccessToast ref="toast" />
    <Footer />
</template>

<style scoped>
.collection {
    min-height: 100vh;
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
    font-size: 1.0625rem;
}
.collection-hero {
    background: var(--coy-color-blush);
}
.collection-hero a {
    text-underline-offset: 0.3rem;
}
.collection-layout {
    display: grid;
    grid-template-columns: 15.5rem minmax(0, 1fr);
    gap: clamp(2rem, 4vw, 4rem);
    padding-block: clamp(1.5rem, 4vw, 3rem) clamp(3rem, 6vw, 5rem);
}
.filters {
    position: sticky;
    top: calc(var(--coy-nav-height) + 1.5rem);
    align-self: start;
    padding: 0;
    background: transparent;
    border-top: 2px solid var(--coy-color-heading);
}
.filters-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid var(--coy-color-border-soft);
}
.filters-heading h2,
.toolbar h2,
.drawer-panel h2 {
    margin: 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 1.45rem;
    font-weight: 600;
}
.filters-heading button,
.search-summary button,
.clear-button {
    padding: 0;
    color: var(--coy-color-accent);
    background: none;
    border: 0;
    font: inherit;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: underline;
    text-underline-offset: 0.2rem;
    cursor: pointer;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin: 0;
    padding: 1.25rem 0;
    border: 0;
    border-bottom: 1px solid var(--coy-color-border-soft);
}
.filter-group:last-child {
    border-bottom: 0;
}
.filter-group h3,
.filter-group legend {
    margin: 0 0 0.15rem;
    padding: 0;
    color: var(--coy-color-heading);
    font-size: 1rem;
    font-weight: 700;
}
.filter-group label {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    min-height: 2.5rem;
    padding: 0.35rem 0.5rem;
    border-radius: var(--coy-radius-sm);
    font-size: 1rem;
    cursor: pointer;
}
.filter-group label:hover {
    background: var(--coy-color-surface);
}
.filter-group input[type='checkbox'] {
    width: 1.15rem;
    height: 1.15rem;
    accent-color: var(--coy-color-accent);
}
.price-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}
.price-inputs label {
    display: block;
    min-height: auto;
    padding: 0;
}
.price-inputs label:hover {
    background: transparent;
}
.price-inputs span {
    height: 2.75rem;
    display: flex;
    align-items: center;
    margin-top: 0.35rem;
    padding: 0 0.6rem;
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
}
.price-inputs input {
    min-width: 0;
    width: 100%;
    padding: 0.4rem;
    background: transparent;
    border: 0;
    outline: 0;
    font: inherit;
    font-size: 1rem;
}
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}
.toolbar p {
    margin: 0.15rem 0 0;
    font-size: 1rem;
}
.toolbar-actions {
    display: flex;
    align-items: end;
    gap: 0.75rem;
}
.toolbar-actions label {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    color: var(--coy-color-heading);
    font-size: 1rem;
    font-weight: 700;
}
.toolbar select {
    min-height: 2.75rem;
    padding: 0.5rem 2.2rem 0.5rem 0.8rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    font: inherit;
    font-size: 1rem;
}
.filter-trigger {
    min-height: 2.75rem;
    display: none;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.8rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    font: inherit;
    font-size: 1rem;
    font-weight: 700;
}
.filter-trigger svg {
    width: 1.2rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
}
.filter-trigger b {
    min-width: 1.3rem;
    height: 1.3rem;
    display: grid;
    place-items: center;
    color: white;
    background: var(--coy-color-accent);
    border-radius: 50%;
    font-size: 0.875rem;
}
.search-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
    padding: 0.8rem 1rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    font-size: 1rem;
}
.product-area {
    transition: opacity 0.2s;
}
.product-area--loading {
    opacity: 0.45;
    pointer-events: none;
}
.product-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
    margin: 0;
    padding: 0;
    list-style: none;
}
.empty {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: 4rem 1.5rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    text-align: center;
}
.empty > span {
    color: var(--coy-color-rose-gold);
    font-size: 2.5rem;
}
.empty h2 {
    margin: 0.75rem 0 0.5rem;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 2rem;
}
.empty p {
    max-width: 32rem;
    margin: 0 0 1.5rem;
}
.pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2.5rem;
}
.pagination button {
    min-width: 2.75rem;
    min-height: 2.75rem;
    padding: 0.5rem 0.8rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    font: inherit;
    font-size: 1rem;
    cursor: pointer;
}
.pagination button.active {
    color: white;
    background: var(--coy-color-accent);
    border-color: var(--coy-color-accent);
}
.drawer-backdrop {
    position: fixed;
    inset: 0;
    z-index: 60;
    display: flex;
    justify-content: flex-end;
    background: rgb(52 42 40/45%);
}
.drawer-panel {
    width: min(100%, 25rem);
    height: 100%;
    display: flex;
    flex-direction: column;
    background: var(--coy-color-surface);
    box-shadow: var(--coy-shadow-md);
}
.drawer-panel > header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    border-bottom: 1px solid var(--coy-color-border);
}
.drawer-panel > header button {
    width: 2.75rem;
    height: 2.75rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: 50%;
    font-size: 1.75rem;
    cursor: pointer;
}
.drawer-body {
    flex: 1;
    overflow: auto;
    padding: 0 1.25rem;
}
.drawer-panel > footer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.25rem;
    border-top: 1px solid var(--coy-color-border);
}
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.2s;
}
.drawer-enter-active .drawer-panel,
.drawer-leave-active .drawer-panel {
    transition: transform 0.25s;
}
.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}
.drawer-enter-from .drawer-panel,
.drawer-leave-to .drawer-panel {
    transform: translateX(100%);
}
@media (max-width: 1100px) {
    .product-grid {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 900px) {
    .collection-layout {
        grid-template-columns: 1fr;
    }
    .filters {
        display: none;
    }
    .filter-trigger {
        display: flex;
    }
}
@media (max-width: 620px) {
    .collection {
        padding-bottom: 5.5rem;
        font-size: 1rem;
    }
    .toolbar {
        align-items: start;
        flex-direction: column;
    }
    .toolbar-actions {
        width: 100%;
        align-items: end;
        justify-content: space-between;
    }
    .toolbar-actions label {
        flex: 1;
    }
    .toolbar select {
        width: 100%;
    }
    .filter-trigger {
        position: fixed;
        z-index: 50;
        right: 1rem;
        bottom: calc(1rem + env(safe-area-inset-bottom));
        left: 1rem;
        min-height: 3.25rem;
        justify-content: center;
        padding: 0.7rem 1.25rem;
        color: white;
        background: var(--coy-color-heading);
        border-color: var(--coy-color-heading);
        border-radius: 999px;
        box-shadow: 0 12px 32px rgb(52 42 40 / 28%);
    }
    .filter-trigger b {
        color: var(--coy-color-heading);
        background: var(--coy-color-blush);
    }
    .product-grid {
        gap: 0.75rem;
    }
    .search-summary {
        align-items: flex-start;
        flex-direction: column;
    }
    .search-summary button {
        align-self: flex-end;
    }
}
@media (max-width: 390px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
}
</style>
