<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';

const IconStar = `<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.152A1.5 1.5 0 0113.951 2.152l1.621 3.436a1.5 1.5 0 001.194.887l3.778.548a1.5 1.5 0 01.832 2.578l-2.73 2.66a1.5 1.5 0 00-.435 1.334l.643 3.766a1.5 1.5 0 01-2.175 1.583l-3.38-1.777a1.5 1.5 0 00-1.396 0l-3.38 1.777a1.5 1.5 0 01-2.175-1.583l.643-3.766a1.5 1.5 0 00-.435-1.334l-2.73-2.66a1.5 1.5 0 01.832-2.578l3.778-.548a1.5 1.5 0 001.194-.887l1.621-3.436z" /></svg>`;
const IconCart = `<svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`;

interface ProductImage {
    image: string;
}

interface ProductVariation {
    id: number;
    mpn: string;
    name: string;
    cost: number;
    stock_qty: number;
    parent_product_id: number;
}

interface ProductDetailData {
    id: number;
    name: string;
    mpn: string;
    description: string;
    cost: number;
    stock_qty: number;
    total_unique_views: number;
    images: ProductImage[];
    categories: { id: number; name: string }[];
    children: ProductVariation[];
    seo?: { meta_title: string; meta_description: string };
}

interface ProductProps {
    product: ProductDetailData;
    parent?: ProductDetailData | null;
    related: ProductDetailData[];
}

const props = defineProps<ProductProps>();

const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);

// --- State for Image Gallery ---
const selectedImageIndex = ref(0);
const mainImageUrl = computed(() => props.product.images[selectedImageIndex.value]?.image || 'https://via.placeholder.com/600?text=No+Image');

// --- State for Variations ---
// If the product has variations, we need to track which one is selected
const selectedVariationId = ref<number | null>(null);
const currentVariation = computed(() => {
    // If a variation is selected, use it
    if (selectedVariationId.value) {
        return props.product.children.find(v => v.id === selectedVariationId.value);
    }
    // If no variations or none selected, use the main product data
    return {
        id: props.product.id,
        cost: props.product.cost,
        stock_qty: props.product.stock_qty,
        mpn: props.product.mpn,
    };
});

// --- Computed Properties ---
const isOutOfStock = computed(() => currentVariation.value.stock_qty <= 0);
const formattedCost = computed(() => `£${currentVariation.value.cost.toFixed(2)}`);

const isPopular = computed(() => props.product.total_unique_views > 100);

// --- Actions ---

const handleAddToCart = () => {
    const itemToAdd = currentVariation.value;
    console.log(`Adding product ${itemToAdd.id} to cart...`);
    // router.post('/cart', { product_id: itemToAdd.id });

    if (successToastRef.value) {
        successToastRef.value.show(`${props.product.name} (${itemToAdd.mpn}) added to cart!`, 'cart');
    }
};

const handleFavourite = () => {
    const itemToFavourite = currentVariation.value;
    console.log(`Toggling favourite for product ${itemToFavourite.id}...`);
    // router.post(`/products/${itemToFavourite.id}/favourite`);

    if (successToastRef.value) {
        successToastRef.value.show(`${props.product.name} added to your wishlist!`, 'favourite');
    }
};

// --- SEO Title ---
const pageTitle = computed(() => {
    if (props.product.seo && props.product.seo.meta_title) {
        return props.product.seo.meta_title;
    }
    return `${props.product.name} | Chapter of You`;
});
</script>

<template>
    <NavBar />

    <Head :title="pageTitle" />

    <section class="py-10 md:py-20 border-b-2 border-copy" style="background-color: var(--background);">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-16">

                <div class="lg:sticky lg:top-8 self-start">
                    <div
                        class="relative rounded-lg border-2 border-copy bg-primary-light"
                        style="background-color: var(--secondary);"
                    >
                        <div class="relative rounded-lg -m-0.5 border-2 border-copy bg-foreground overflow-hidden h-[400px] sm:h-[500px] flex items-center justify-center p-6">
                            <span v-if="isPopular" class="absolute top-3 left-3 z-10 inline-flex items-center rounded-full bg-error px-3 py-0.5 text-xs font-bold text-error-content shadow-lg ring-1 ring-inset ring-error-content/50">
                                🔥 POPULAR
                            </span>
                            <img
                                :src="mainImageUrl"
                                :alt="'Main image of ' + props.product.name"
                                class="w-full h-full object-contain transition duration-500"
                            />
                        </div>
                    </div>

                    <div v-if="props.product.images.length > 1" class="mt-6">
                        <div class="grid grid-cols-4 gap-4">
                            <button
                                v-for="(image, index) in props.product.images"
                                :key="index"
                                @click="selectedImageIndex = index"
                                :class="[
                                    'rounded-lg border-2 p-1 transition relative',
                                    selectedImageIndex === index ? 'border-primary-content bg-primary-light' : 'border-copy bg-secondary-light hover:bg-primary-light'
                                ]"
                            >
                                <img
                                    :src="image.image"
                                    :alt="'Thumbnail ' + (index + 1)"
                                    class="size-full object-cover rounded-md"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-8 lg:pt-0">
                    <nav class="text-sm font-medium text-copy-lighter mb-2">
                        <ol class="flex space-x-1">
                            <li><a href="/products" class="hover:text-primary-content transition">Products</a></li>
                            <li v-for="category in props.product.categories" :key="category.id">
                                <span class="mx-2">/</span>
                                <a :href="'/products?categories=' + category.id" class="hover:text-primary-content transition">{{ category.name }}</a>
                            </li>
                        </ol>
                    </nav>

                    <h1 class="text-4xl font-extrabold text-copy mb-2">{{ props.product.name }}</h1>
                    <p class="text-copy-lighter mb-6">MPN: {{ currentVariation.mpn }}</p>

                    <div class="mb-6">
                        <span
                            :class="[
                                'inline-flex items-center rounded-full px-3 py-1 text-sm font-bold',
                                isOutOfStock ? 'bg-error text-error-content' : 'bg-success text-success-content'
                            ]"
                        >
                            {{ isOutOfStock ? 'Out of Stock' : `In Stock (${currentVariation.stock_qty})` }}
                        </span>
                    </div>

                    <p class="text-5xl font-black text-primary-content mb-8">
                        {{ formattedCost }}
                    </p>

                    <div v-if="props.product.children && props.product.children.length > 0" class="mb-8 border-t-2 border-copy pt-4">
                        <h3 class="text-lg font-bold text-copy mb-3">Choose Option:</h3>
                        <div class="flex flex-wrap gap-3">
                            <button
                                v-for="variation in props.product.children"
                                :key="variation.id"
                                @click="selectedVariationId = variation.id"
                                :disabled="variation.stock_qty <= 0"
                                :class="[
                                    'px-4 py-2 rounded-lg border-2 border-copy text-sm font-semibold transition-all shadow-md',
                                    variation.id === selectedVariationId
                                        ? 'bg-primary text-primary-content'
                                        : 'bg-foreground text-copy-light hover:bg-secondary-light',
                                    variation.stock_qty <= 0 && 'opacity-50 cursor-not-allowed line-through'
                                ]"
                            >
                                {{ variation.name }}
                            </button>
                        </div>
                    </div>


                    <div class="flex gap-4 mb-8">
                        <button
                            @click="handleAddToCart"
                            :disabled="isOutOfStock"
                            :class="[
                                'relative rounded-lg -m-0.5 flex-1 inline-flex items-center justify-center gap-2 border-2 border-copy px-8 py-3 text-lg font-bold text-primary-content transition hover:bg-primary-dark shadow-lg',
                                isOutOfStock ? 'bg-border text-copy-lighter cursor-not-allowed' : 'bg-primary'
                            ]"
                            style="background-color: var(--primary);"
                        >
                            <div v-html="IconCart"></div>
                            {{ isOutOfStock ? 'Notify Me' : 'Add to Cart' }}
                        </button>

                        <button
                            @click="handleFavourite"
                            class="rounded-lg border-2 border-copy p-3 text-copy-light transition hover:bg-error-light hover:text-error-content shadow-lg"
                            aria-label="Add to favourites"
                        >
                            <div v-html="IconStar" class="size-6"></div>
                        </button>
                    </div>

                    <div class="border-t-2 border-copy pt-6">
                        <h2 class="text-2xl font-bold text-copy mb-3">Details</h2>
                        <div class="text-copy-light leading-relaxed whitespace-pre-wrap">{{ props.product.description }}</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section v-if="props.related.length" class="py-16 md:py-20" style="background-color: var(--secondary-light);">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-copy mb-10 border-b-2 border-copy pb-3">Related Products</h2>

            <ul class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <li v-for="product in props.related" :key="product.id">
                    <ProductSpringCard
                        :product="product"
                        @add-to-cart="handleAddToCart(product)"
                        @favourite="handleFavourite(product)"
                    />
                </li>
            </ul>
        </div>
    </section>

    <SuccessToast ref="successToastRef" />
</template>
