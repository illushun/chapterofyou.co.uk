<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import ModalImageViewer from '@/components/ui/coy/ModalImageViewer.vue';

const IconStar = `<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.152A1.5 1.5 0 0113.951 2.152l1.621 3.436a1.5 1.5 0 001.194.887l3.778.548a1.5 1.5 0 01.832 2.578l-2.73 2.66a1.5 1.5 0 00-.435 1.334l.643 3.766a1.5 1.5 0 01-2.175 1.583l-3.38-1.777a1.5 1.5 0 00-1.396 0l-3.38 1.777a1.5 1.5 0 01-2.175-1.583l.643-3.766a1.5 1.5 0 00-.435-1.334l-2.73-2.66a1.5 1.5 0 01.832-2.578l3.778-.548a1.5 1.5 0 001.194-.887l1.621-3.436z" /></svg>`;
const IconCart = `<svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`;
const IconMinus = `<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>`;
const IconPlus = `<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m-8-8h16" /></svg>`;

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
const isModalOpen = ref(false);

const quantity = ref(1);
const increaseQuantity = () => {
    if (currentVariation.value.stock_qty > quantity.value) {
        quantity.value++;
    }
};
const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

// for Image Gallery
const selectedImageIndex = ref(0);
const mainImageUrl = computed(() => props.product.images[selectedImageIndex.value]?.image || 'https://via.placeholder.com/600?text=No+Image');

const openImageModal = () => {
    if ((props.product.images || []).length > 0) {
        isModalOpen.value = true;
    }
};

const getInitialVariationId = (): number | null => {
    if (props.product.children && props.product.children.length > 0) {
        // Find the first variation that is in stock to set as default
        return props.product.children.find(v => v.stock_qty > 0)?.id ?? null;
    }
    return null;
};

// If the product has variations, we need to track which one is selected
const selectedVariationId = ref<number | null>(getInitialVariationId());
const currentVariation = computed(() => {
    // If a variation is selected, use it
    if (selectedVariationId.value) {
        const variation = props.product.children.find(v => v.id === selectedVariationId.value);
        if (variation) return variation;
    }

    // If no variations selected (or no children exist), use the main product data.
    return {
        id: props.product.id,
        cost: props.product.cost,
        stock_qty: props.product.stock_qty || 0,
        mpn: props.product.mpn,
    };
});

const isOutOfStock = computed(() => currentVariation.value.stock_qty <= 0);
const isPopular = computed(() => props.product.total_unique_views > 100);


// Actions


/**
 * Handles adding an item to the cart, supporting both the main product
 * and quick-add from a related product card.
 *
 * @param quickAddProduct The product object if adding from a related card (defaults to main product logic if null).
 */
const handleAddToCart = (quickAddProduct: ProductDetailData | null = null) => {
    let itemToAdd;
    let qty;
    let productName;
    let productMpn;

    if (quickAddProduct) {
        // Quick add from a related product card (quantity fixed at 1)
        itemToAdd = { id: quickAddProduct.id, stock_qty: quickAddProduct.stock_qty };
        qty = 1;
        productName = quickAddProduct.name;
        productMpn = quickAddProduct.mpn;
    } else {
        itemToAdd = currentVariation.value;
        qty = quantity.value;
        productName = props.product.name;
        productMpn = itemToAdd.mpn || props.product.mpn || '';

        // If it's a variable product and nothing is explicitly selected (defaulting to the parent)
        // AND the parent doesn't have an ID, we stop.
        if (props.product.children.length > 0 && !selectedVariationId.value) {
            console.error('Cannot add variable product to cart without selecting an option.');
            return;
        }
    }

    if (!itemToAdd || !itemToAdd.id || qty < 1 || itemToAdd.stock_qty < qty) {
        console.error('Invalid product ID or quantity provided for cart addition. Check product data integrity.', itemToAdd, qty);
        return;
    }
    // ------------------------------------

    router.post(
        '/cart/add',
        { product_id: itemToAdd.id, quantity: qty },
        {
            preserveScroll: true,
            onSuccess: () => {
                if (successToastRef.value) {
                    successToastRef.value.show(`${qty} x ${productName} (${productMpn}) added to cart!`, 'cart');
                }

                // Reset quantity on the main page to 1 for the next purchase
                if (!quickAddProduct) {
                    quantity.value = 1;
                }
            },
            onError: (errors) => {
                console.error('Error adding to cart:', errors);
            }
        }
    );
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

const formattedCost = computed(() => {
  const numericCost = Number(currentVariation.value.cost);
  if (isNaN(numericCost)) {
    return 'N/A';
  }
  return `£${numericCost.toFixed(2)}`;
});
</script>

<template>
    <NavBar />

    <Head :title="pageTitle" />

    <section class="py-20">
        <div class="mx-auto max-w-screen-xl p-4 md:p-8 lg:p-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-16">

                <div class="lg:sticky lg:top-8 self-start">
                    <button
                        @click="openImageModal"
                        class="relative rounded-lg border-2 border-copy bg-primary-light w-full transition hover:opacity-80 focus:ring-4 focus:ring-primary-content"
                        style="background-color: var(--secondary);"
                        aria-label="View product image large"
                    >
                        <div class="relative rounded-lg -m-0.5 border-2 border-copy bg-foreground overflow-hidden h-[400px] sm:h-[500px] flex items-center justify-center p-6 cursor-pointer">
                            <span v-if="isPopular" class="absolute top-3 left-3 z-10 inline-flex items-center rounded-full bg-error px-3 py-0.5 text-xs font-bold text-error-content shadow-lg ring-1 ring-inset ring-error-content/50">
                                🔥 POPULAR
                            </span>
                            <img
                                :src="mainImageUrl"
                                :alt="'Main image of ' + props.product.name"
                                class="w-full h-full object-contain transition duration-500"
                            />
                            <div class="absolute inset-0 flex items-center justify-center bg-primary-light bg-opacity-10 opacity-0 hover:opacity-100 transition duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-12 text-primary-content" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </div>
                        </div>
                    </button>

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
                    <p class="text-copy-lighter mb-6">{{ currentVariation.mpn }}</p>

                    <div class="mb-6">
                        <span
                            :class="[
                                'inline-flex items-center rounded-full px-3 py-1 text-sm font-bold',
                                isOutOfStock ? 'bg-error text-error-content' : 'bg-success text-success-content'
                            ]"
                        >
                            {{ isOutOfStock ? 'Out of Stock' : `In Stock` }}
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
                                        ? 'bg-secondary text-secondary-content'
                                        : 'bg-foreground text-copy-light hover:bg-secondary-light',
                                    variation.stock_qty <= 0 && 'opacity-50 cursor-not-allowed line-through'
                                ]"
                            >
                                {{ variation.name }}
                            </button>
                        </div>
                    </div>


                    <div class="flex flex-col gap-4 mb-8 lg:flex-row">

                        <div class="flex flex-grow gap-4">

                            <div class="flex items-center rounded-lg border-2 border-copy bg-foreground flex-shrink-0 w-1/3 max-w-[150px] lg:w-auto">
                                <button
                                    @click="decreaseQuantity"
                                    :disabled="quantity <= 1"
                                    class="rounded-l-md border-r-2 border-copy p-3 text-copy-light transition hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed"
                                    aria-label="Decrease quantity"
                                >
                                    <div v-html="IconMinus"></div>
                                </button>

                                <input
                                    type="number"
                                    v-model.number="quantity"
                                    min="1"
                                    :max="currentVariation.stock_qty"
                                    @change="quantity = Math.max(1, Math.min(currentVariation.stock_qty, Number(quantity) || 1))"
                                    class="w-12 h-full text-center text-sm font-bold bg-foreground text-copy border-none focus:ring-0 p-0 m-0"
                                    aria-label="Product quantity"
                                />

                                <button
                                    @click="increaseQuantity"
                                    :disabled="quantity >= currentVariation.stock_qty"
                                    class="rounded-r-md border-l-2 border-copy p-3 text-copy-light transition hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed"
                                    aria-label="Increase quantity"
                                >
                                    <div v-html="IconPlus"></div>
                                </button>
                            </div>

                            <button
                                @click="handleAddToCart()"
                                :disabled="isOutOfStock || quantity > currentVariation.stock_qty || quantity < 1"
                                :class="[
                                    'relative rounded-lg -m-0.5 flex-1 inline-flex items-center justify-center gap-2 border-2 border-copy px-8 py-3 text-lg font-bold text-primary-content transition hover:bg-primary-dark shadow-lg',
                                    isOutOfStock ? 'bg-border text-copy-lighter cursor-not-allowed' : 'bg-primary'
                                ]"
                                style="background-color: var(--primary);"
                            >
                                <div v-html="IconCart"></div>
                                {{ isOutOfStock ? 'Notify Me' : 'Add to Cart' }}
                            </button>
                        </div>

                        <button
                            @click="handleFavourite()"
                            class="rounded-lg border-2 border-copy p-3 text-copy-light transition hover:bg-error-light hover:text-error-content shadow-lg w-fit lg:flex-shrink-0 flex items-center justify-center"
                            aria-label="Add to favourites"
                        >
                            <div v-html="IconStar" class="size-6 flex items-center justify-center"></div>
                        </button>

                    </div>

                    <div class="border-t-2 border-copy pt-6">
                        <h2 class="text-2xl font-bold text-copy mb-3">Details</h2>
                        <div
                            class="text-copy-light leading-relaxed"
                            v-html="props.product.description"
                        ></div>
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

    <ModalImageViewer
        :images="props.product.images || []"
        :initial-index="selectedImageIndex"
        :open="isModalOpen"
        @update:open="isModalOpen = $event"
    />
</template>
