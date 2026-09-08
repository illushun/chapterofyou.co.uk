<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import JsonLdSchema from '@/components/JsonLdSchema.vue';
import NavBar from '@/components/NavBar.vue';
import ProductFaqAccordion from '@/components/product/ProductFaqAccordion.vue';
import ProductGallery from '@/components/product/ProductGallery.vue';
import RecentJournalPosts from '@/components/RecentJournalPosts.vue';
import SeoHead from '@/components/SeoHead.vue';
import {
    useBreadcrumbSchema,
    useProductSchema,
} from '@/composables/useProductSchema';
import { useSeoHead } from '@/composables/useSeoHead';
import type { ProductFaq, ProductImage } from '@/types/product';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';

import ModalImageViewer from '@/components/ui/coy/ModalImageViewer.vue';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import StarRating from '@/components/ui/coy/StarRating.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';

interface ProductReview {
    id: number;
    product_id: number;
    user_id: number;
    message: string;
    rating: number;
    review_images: string[];
    created_at: string;
    user: { id: number; name: string };
    admin_reply: string | null;
}
interface ProductVariation {
    id: number;
    mpn: string;
    name: string;
    cost: number;
    stock_qty: number;
    parent_product_id: number;
}
interface ProductRefill {
    id: number;
    name: string;
    cost: number;
    stock_qty: number;
}
interface ProductDetailData {
    id: number;
    name: string;
    mpn: string;
    description: string;
    cost: number;
    stock_qty: number;
    total_unique_views: number;
    average_rating: number;
    approved_reviews_count: number;
    images: ProductImage[];
    reviews: ProductReview[];
    categories: { id: number; name: string; slug: string | null }[];
    children: ProductVariation[];
    refills?: ProductRefill[];
    details: string;
    how_to_use?: string | null;
    faqs?: ProductFaq[] | null;
    seo?: { slug: string; meta_title: string; meta_description: string };
}
interface JournalPostSummary {
    title: string;
    slug: string;
    excerpt: string | null;
    cover_image: string | null;
    published_at: string;
    reading_time: number;
}
interface ProductProps {
    product: ProductDetailData;
    parent?: ProductDetailData | null;
    related: ProductDetailData[];
    journalPosts?: JournalPostSummary[];
    canReview: boolean;
    wishlisted: boolean;
    wishlistedIds: number[];
}

const props = defineProps<ProductProps>();
const page = usePage();
const recentPosts = computed(() =>
    props.journalPosts?.length
        ? props.journalPosts
        : ((page.props.recentJournalPosts as any[]) ?? []),
);
const auth = computed(() => page.props.auth as any);
const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);
const isModalOpen = ref(false);
const isWishlisted = ref(props.wishlisted ?? false);
const wishlistedIds = ref<number[]>(props.wishlistedIds ?? []);

const withDiffuserKeyword = (text: string) =>
    /\bdiffusers?\b/i.test(text) ? text : `${text} Reed Diffuser`;

const displayTitle = computed(() => withDiffuserKeyword(props.product.name));

const seo = useSeoHead({
    title: props.product.seo?.meta_title || displayTitle.value,
    description:
        props.product.seo?.meta_description ||
        props.product.description?.replace(/<[^>]*>/g, '').slice(0, 155),
    canonical: `/product/${props.product.seo?.slug || props.product.id}`,
    ogImage: props.product.images?.[0]?.image,
    ogType: 'product',
});

const productSlug = computed(
    () => props.product.seo?.slug || String(props.product.id),
);

const productSchema = computed(() =>
    useProductSchema({
        product: props.product,
        slug: productSlug.value,
    }),
);

const breadcrumbSchema = computed(() => {
    const crumbs = [
        { name: 'Home', url: '/' },
        { name: 'Products', url: '/products' },
    ];

    if (props.product.categories?.length) {
        const cat = props.product.categories[0];
        crumbs.push({
            name: cat.name,
            url: cat.slug
                ? `/category/${cat.slug}`
                : `/products?categories=${cat.id}`,
        });
    }

    crumbs.push({
        name: props.product.name,
        url: `/product/${productSlug.value}`,
    });

    return useBreadcrumbSchema(crumbs);
});

const quantity = ref(1);
const increaseQuantity = () => {
    if (currentVariation.value.stock_qty > quantity.value) quantity.value++;
};
const decreaseQuantity = () => {
    if (quantity.value > 1) quantity.value--;
};

const selectedImageIndex = ref(0);
const modalImages = ref<ProductImage[]>(props.product.images);
const modalImageIndex = ref(0);
const openImageModal = () => {
    if (!props.product.images.length) return;
    modalImages.value = props.product.images;
    modalImageIndex.value = selectedImageIndex.value;
    isModalOpen.value = true;
};
const openReviewImage = (images: string[], index: number) => {
    modalImages.value = images.map((image) => ({ image }));
    modalImageIndex.value = index;
    isModalOpen.value = true;
};
const reviewForm = useForm({ rating: 0, message: '', images: [] as File[] });

const submitReview = () => {
    reviewForm.post(route('products.review.store', props.product.id), {
        forceFormData: true,
        onSuccess: () => {
            successToastRef.value?.show(
                'Review submitted! Awaiting approval.',
                'star',
            );
            reviewForm.reset('rating', 'message', 'images');
        },
    });
};
const deleteReview = (reviewId: number) => {
    router.delete(route('products.review.destroy', reviewId), {
        preserveScroll: true,
        onSuccess: () =>
            successToastRef.value?.show('Review deleted.', 'trash'),
    });
};
const handleImageUpload = (event: Event) => {
    const t = event.target as HTMLInputElement;
    if (t.files) reviewForm.images = Array.from(t.files).slice(0, 3);
};

const getInitialVariationId = (): number | null => {
    if (props.product.children?.length > 0)
        return props.product.children.find((v) => v.stock_qty > 0)?.id ?? null;
    return null;
};
const selectedVariationId = ref<number | null>(getInitialVariationId());
const currentVariation = computed(() => {
    if (selectedVariationId.value) {
        const v = props.product.children.find(
            (v) => v.id === selectedVariationId.value,
        );
        if (v) return v;
    }
    return {
        id: props.product.id,
        cost: props.product.cost,
        stock_qty: props.product.stock_qty || 0,
        mpn: props.product.mpn,
    };
});

const isOutOfStock = computed(() => currentVariation.value.stock_qty <= 0);
const fmt = (v: number | string) => {
    const n = Number(v);
    return isNaN(n) ? 'N/A' : `£${n.toFixed(2)}`;
};
const formattedCost = computed(() => fmt(currentVariation.value.cost));

const addRefill = ref(false);
const primaryRefill = computed(() => props.product.refills?.[0] ?? null);
const otherRefills = computed(() => props.product.refills?.slice(1) ?? []);
const refillOutOfStock = computed(
    () => (primaryRefill.value?.stock_qty ?? 0) <= 0,
);

const handleAddToCart = (
    quickAddProduct: ProductDetailData | null = null,
    quickAddQuantity = 1,
) => {
    const itemToAdd = quickAddProduct ?? currentVariation.value;
    const qty = quickAddProduct ? quickAddQuantity : quantity.value;
    const name = quickAddProduct ? quickAddProduct.name : props.product.name;
    if (!itemToAdd?.id || qty < 1 || itemToAdd.stock_qty < qty) return;
    const includeRefill =
        !quickAddProduct &&
        addRefill.value &&
        primaryRefill.value &&
        !refillOutOfStock.value;
    router.post(
        '/cart/add',
        {
            product_id: itemToAdd.id,
            quantity: qty,
            refill_id: includeRefill ? primaryRefill.value!.id : undefined,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                const refillSuffix = includeRefill
                    ? ` + ${primaryRefill.value!.name}`
                    : '';
                successToastRef.value?.show(
                    `${qty} × ${name}${refillSuffix} added to cart!`,
                    'cart',
                );
                if (!quickAddProduct) quantity.value = 1;
            },
        },
    );
};

const handleFavourite = async (productArg?: any) => {
    const targetId =
        (productArg && typeof productArg === 'object' ? productArg.id : null) ??
        props.product.id;
    const isRelated = targetId !== props.product.id;
    if (isRelated) {
        if (wishlistedIds.value.includes(targetId)) {
            wishlistedIds.value.splice(
                wishlistedIds.value.indexOf(targetId),
                1,
            );
        } else {
            wishlistedIds.value.push(targetId);
        }
    } else {
        isWishlisted.value = !isWishlisted.value;
    }
    try {
        const { data } = await axios.post(route('wishlist.toggle'), {
            product_id: targetId,
        });
        successToastRef.value?.show(
            data.message,
            data.wishlisted ? 'favourite' : 'trash',
        );
    } catch (err: any) {
        if (isRelated) {
            if (wishlistedIds.value.includes(targetId)) {
                wishlistedIds.value.splice(
                    wishlistedIds.value.indexOf(targetId),
                    1,
                );
            } else {
                wishlistedIds.value.push(targetId);
            }
        } else {
            isWishlisted.value = !isWishlisted.value;
        }
        if (err.response?.status === 401) window.location.href = route('login');
    }
};

const hasHowToUse = computed(() => !!props.product.how_to_use?.trim());
const hasFaqs = computed(() => (props.product.faqs?.length ?? 0) > 0);
const ldSchemas = computed(() => [productSchema.value, breadcrumbSchema.value]);

const isLowStock = computed(
    () => !isOutOfStock.value && currentVariation.value.stock_qty <= 5,
);

const showStickyCta = ref(false);
let cartBtnObserver: IntersectionObserver | null = null;

onMounted(() => {
    const cartBtn = document.querySelector<HTMLElement>('.pd-cart-btn');
    if (cartBtn && 'IntersectionObserver' in window) {
        cartBtnObserver = new IntersectionObserver(
            ([entry]) => {
                showStickyCta.value = !entry.isIntersecting;
            },
            { threshold: 0 },
        );
        cartBtnObserver.observe(cartBtn);
    }
});

onUnmounted(() => {
    cartBtnObserver?.disconnect();
});
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />
    <JsonLdSchema :schema="ldSchemas" />

    <main class="pd coy-storefront">
        <div class="pd-wrap">
            <div class="pd-grid">
                <div class="pd-mobile-intro">
                    <nav class="pd-breadcrumb" aria-label="Breadcrumb">
                        <a href="/products" class="pd-crumb">Products</a>
                        <span
                            v-if="product.categories.length"
                            class="pd-crumb-sep"
                            >/</span
                        >
                        <span
                            v-if="product.categories.length"
                            class="pd-crumb"
                            >{{ product.categories[0].name }}</span
                        >
                    </nav>
                    <h1 class="pd-title">{{ displayTitle }}</h1>
                    <p class="pd-price">{{ formattedCost }}</p>
                </div>
                <ProductGallery
                    :images="product.images"
                    :name="product.name"
                    :popular="product.total_unique_views > 100"
                    v-model:selected-index="selectedImageIndex"
                    @open="openImageModal"
                />

                <div class="pd-info">
                    <nav class="pd-breadcrumb" aria-label="Breadcrumb">
                        <a href="/products" class="pd-crumb">Products</a>
                        <template
                            v-for="cat in product.categories"
                            :key="cat.id"
                        >
                            <span class="pd-crumb-sep" aria-hidden="true"
                                >/</span
                            >
                            <a
                                :href="
                                    cat.slug
                                        ? `/category/${cat.slug}`
                                        : `/products?categories=${cat.id}`
                                "
                                class="pd-crumb"
                                >{{ cat.name }}</a
                            >
                        </template>
                    </nav>

                    <h1 class="pd-title">{{ displayTitle }}</h1>

                    <div
                        v-if="product.approved_reviews_count > 0"
                        class="pd-rating-row"
                    >
                        <StarRating
                            :rating="product.average_rating"
                            :size="18"
                        />
                        <span class="pd-rating-label"
                            >{{ product.average_rating.toFixed(1) }} ({{
                                product.approved_reviews_count
                            }}
                            review{{
                                product.approved_reviews_count !== 1 ? 's' : ''
                            }})</span
                        >
                    </div>

                    <div class="pd-stock-row">
                        <span
                            v-if="isOutOfStock"
                            class="pd-stock-badge pd-stock--out"
                            >Out of stock</span
                        >
                        <span
                            v-else-if="isLowStock"
                            class="pd-stock-badge pd-stock--low"
                        >
                            <svg
                                width="11"
                                height="11"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                                />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                            Only {{ currentVariation.stock_qty }} left
                        </span>
                        <span v-else class="pd-stock-badge pd-stock--in"
                            >In Stock</span
                        >
                    </div>

                    <p class="pd-price">{{ formattedCost }}</p>

                    <div
                        v-if="product.children?.length > 0"
                        class="pd-variations"
                    >
                        <h2 class="pd-variations-label">Choose an option</h2>
                        <div class="pd-variation-btns">
                            <button
                                v-for="v in product.children"
                                :key="v.id"
                                @click="selectedVariationId = v.id"
                                :disabled="v.stock_qty <= 0"
                                class="pd-variation-btn"
                                :class="{
                                    'pd-variation-btn--active':
                                        v.id === selectedVariationId,
                                    'pd-variation-btn--disabled':
                                        v.stock_qty <= 0,
                                }"
                            >
                                {{ v.name }}
                            </button>
                        </div>
                    </div>

                    <div v-if="primaryRefill" class="pd-refill">
                        <label
                            class="pd-refill-label"
                            :class="{
                                'pd-refill-label--disabled': refillOutOfStock,
                            }"
                        >
                            <input
                                type="checkbox"
                                v-model="addRefill"
                                :disabled="refillOutOfStock"
                                class="pd-refill-checkbox"
                            />
                            <span class="pd-refill-text">
                                Add {{ primaryRefill.name }} refill
                                <span class="pd-refill-price"
                                    >(+{{ fmt(primaryRefill.cost) }})</span
                                >
                            </span>
                        </label>
                        <p v-if="refillOutOfStock" class="pd-refill-oos">
                            Currently out of stock
                        </p>
                        <p v-if="otherRefills.length" class="pd-refill-other">
                            Other refill scents available:
                            <template
                                v-for="(r, i) in otherRefills"
                                :key="r.id"
                            >
                                <a
                                    :href="`/product/${r.id}`"
                                    class="pd-refill-other-link"
                                    >{{ r.name }}</a
                                ><span v-if="i < otherRefills.length - 1"
                                    >,
                                </span>
                            </template>
                        </p>
                    </div>

                    <div class="pd-actions">
                        <div class="pd-qty-group">
                            <span class="pd-action-label">Quantity</span>
                            <div class="pd-qty">
                                <button
                                    @click="decreaseQuantity"
                                    :disabled="quantity <= 1"
                                    class="pd-qty-btn"
                                    aria-label="Decrease quantity"
                                >
                                    <svg
                                        width="12"
                                        height="12"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    >
                                        <path d="M5 12h14" />
                                    </svg>
                                </button>
                                <input
                                    type="number"
                                    v-model.number="quantity"
                                    min="1"
                                    :max="currentVariation.stock_qty"
                                    @change="
                                        quantity = Math.max(
                                            1,
                                            Math.min(
                                                currentVariation.stock_qty,
                                                Number(quantity) || 1,
                                            ),
                                        )
                                    "
                                    class="pd-qty-input"
                                    aria-label="Quantity"
                                />
                                <button
                                    @click="increaseQuantity"
                                    :disabled="
                                        quantity >= currentVariation.stock_qty
                                    "
                                    class="pd-qty-btn"
                                    aria-label="Increase quantity"
                                >
                                    <svg
                                        width="12"
                                        height="12"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    >
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button
                            @click="handleAddToCart()"
                            :disabled="
                                isOutOfStock ||
                                quantity > currentVariation.stock_qty ||
                                quantity < 1
                            "
                            class="pd-cart-btn"
                            :class="{ 'pd-cart-btn--disabled': isOutOfStock }"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"
                                />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <path d="M16 10a4 4 0 0 1-8 0" />
                            </svg>
                            {{
                                isOutOfStock ? 'Out of stock' : 'Add to basket'
                            }}
                        </button>
                        <button
                            @click="handleFavourite()"
                            class="pd-wish-btn"
                            :class="{ 'pd-wish-btn--active': isWishlisted }"
                            :aria-label="
                                isWishlisted
                                    ? 'Remove from wishlist'
                                    : 'Add to wishlist'
                            "
                        >
                            <svg
                                v-if="isWishlisted"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                />
                            </svg>
                            <svg
                                v-else
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                />
                            </svg>
                        </button>
                    </div>

                    <p class="pd-dispatch-note">
                        <svg
                            width="13"
                            height="13"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        Made to order. Typically dispatches in 3 to 5 working
                        days
                    </p>

                    <div class="pd-trust">
                        <div class="pd-trust-item">
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                />
                            </svg>
                            Handmade in the UK
                        </div>
                        <div class="pd-trust-item">
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <rect x="1" y="3" width="15" height="13" />
                                <polygon
                                    points="16 8 20 8 23 11 23 16 16 16 16 8"
                                />
                                <circle cx="5.5" cy="18.5" r="2.5" />
                                <circle cx="18.5" cy="18.5" r="2.5" />
                            </svg>
                            Free UK delivery on orders of £50 or more
                        </div>
                        <div class="pd-trust-item">
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <polyline points="1 4 1 10 7 10" />
                                <path d="M3.51 15a9 9 0 1 0 .49-4.95" />
                            </svg>
                            30-day returns
                        </div>
                        <div class="pd-trust-item">
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="11"
                                    width="18"
                                    height="11"
                                    rx="2"
                                    ry="2"
                                />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            Secure checkout
                        </div>
                    </div>

                    <p class="pd-mpn">
                        Product code {{ currentVariation.mpn }}
                    </p>
                </div>
            </div>

            <section class="pd-product-copy">
                <div class="pd-description">
                    <p class="coy-eyebrow">The fragrance</p>
                    <h2 class="pd-section-title">About this diffuser</h2>
                    <div
                        class="pd-description-body"
                        v-html="product.description"
                    ></div>
                </div>

                <div v-if="product.details" class="pd-description">
                    <p class="coy-eyebrow">Good to know</p>
                    <h2 class="pd-section-title">Product details</h2>
                    <div
                        class="pd-description-body"
                        v-html="product.details"
                    ></div>
                </div>
            </section>

            <section v-if="hasHowToUse" class="pd-content-section">
                <h2 class="pd-section-title">
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4M12 16h.01" />
                    </svg>
                    How to use
                </h2>
                <div class="pd-how-to-use" v-html="product.how_to_use"></div>
            </section>

            <section v-if="hasFaqs" class="pd-content-section">
                <h2 class="pd-section-title">
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <path d="M12 17h.01" />
                    </svg>
                    Frequently asked questions
                </h2>
                <ProductFaqAccordion :faqs="product.faqs ?? []" />
            </section>

            <section class="pd-reviews-section">
                <h2 class="pd-section-title">
                    Customer reviews
                    <span class="pd-reviews-count">{{
                        product.approved_reviews_count
                    }}</span>
                </h2>

                <div
                    v-if="product.approved_reviews_count > 0"
                    class="pd-avg-rating"
                >
                    <StarRating :rating="product.average_rating" :size="22" />
                    <span class="pd-avg-val"
                        >{{ product.average_rating.toFixed(1) }} average</span
                    >
                </div>

                <div v-if="canReview" class="pd-review-form-card">
                    <div class="pd-rf-rating-section">
                        <p class="pd-rf-rating-prompt">
                            How would you rate this product?
                        </p>
                        <div class="pd-rf-stars">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="pd-rf-star"
                                :class="{
                                    'pd-rf-star--filled':
                                        star <= reviewForm.rating,
                                }"
                                @click="reviewForm.rating = star"
                                :aria-label="`Rate ${star} star${star !== 1 ? 's' : ''}`"
                            >
                                <svg
                                    width="28"
                                    height="28"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                                    />
                                </svg>
                            </button>
                        </div>
                        <p
                            class="pd-rf-rating-label"
                            v-if="reviewForm.rating > 0"
                        >
                            {{
                                [
                                    '',
                                    'Poor',
                                    'Fair',
                                    'Good',
                                    'Very Good',
                                    'Excellent',
                                ][reviewForm.rating]
                            }}
                        </p>
                        <p v-if="reviewForm.errors.rating" class="field-error">
                            {{ reviewForm.errors.rating }}
                        </p>
                    </div>
                    <form @submit.prevent="submitReview" class="pd-review-form">
                        <div class="field">
                            <label for="review_message" class="field-label"
                                >Your review</label
                            >
                            <textarea
                                id="review_message"
                                v-model="reviewForm.message"
                                rows="4"
                                placeholder="Share your experience with this product..."
                                class="field-input field-textarea"
                                :class="{
                                    'field-input--error':
                                        reviewForm.errors.message,
                                }"
                            ></textarea>
                            <p
                                v-if="reviewForm.errors.message"
                                class="field-error"
                            >
                                {{ reviewForm.errors.message }}
                            </p>
                        </div>
                        <div class="field">
                            <label for="review_images" class="field-label"
                                >Add photos
                                <span class="field-optional"
                                    >(optional, up to 3)</span
                                ></label
                            >
                            <label for="review_images" class="pd-file-label">
                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"
                                    />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                                {{
                                    reviewForm.images.length > 0
                                        ? `${reviewForm.images.length}
                                photo${
                                    reviewForm.images.length !== 1 ? 's' : ''
                                } selected`
                                        : 'Choose photos'
                                }}
                            </label>
                            <input
                                type="file"
                                id="review_images"
                                multiple
                                accept="image/*"
                                @change="handleImageUpload"
                                class="pd-file-hidden"
                            />
                            <p
                                v-if="
                                    reviewForm.errors['images'] ||
                                    reviewForm.errors['images.0']
                                "
                                class="field-error"
                            >
                                {{
                                    reviewForm.errors['images'] ||
                                    reviewForm.errors['images.0']
                                }}
                            </p>
                        </div>
                        <div class="pd-rf-footer">
                            <button
                                type="submit"
                                :disabled="
                                    reviewForm.processing ||
                                    reviewForm.rating === 0
                                "
                                class="btn-rose"
                                :class="{
                                    'btn-rose--disabled':
                                        reviewForm.rating === 0,
                                }"
                            >
                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M22 2 11 13" />
                                    <path d="M22 2 15 22 11 13 2 9l20-7z" />
                                </svg>
                                {{
                                    reviewForm.processing
                                        ? 'Submitting...'
                                        : 'Submit review'
                                }}
                            </button>
                            <p
                                v-if="reviewForm.rating === 0"
                                class="pd-rf-no-rating-hint"
                            >
                                Please select a star rating above
                            </p>
                        </div>
                    </form>
                </div>

                <div v-else-if="auth.user" class="pd-notice">
                    You must have purchased this product to leave a review.
                </div>
                <div v-else class="pd-notice">
                    <a :href="route('login')" class="pd-notice-link">Log in</a>
                    to see if you're eligible to leave a review.
                </div>

                <div v-if="product.reviews.length > 0" class="pd-review-list">
                    <div
                        v-for="review in product.reviews"
                        :key="review.id"
                        class="pd-review-card"
                    >
                        <div class="pd-review-header">
                            <div>
                                <StarRating
                                    :rating="review.rating"
                                    :size="16"
                                />
                                <p class="pd-reviewer-name">
                                    {{ review.user.name }}
                                </p>
                                <p class="pd-review-date">
                                    {{
                                        new Date(
                                            review.created_at,
                                        ).toLocaleDateString('en-GB', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric',
                                        })
                                    }}
                                </p>
                            </div>
                            <button
                                v-if="
                                    auth.user && auth.user.id === review.user_id
                                "
                                @click="deleteReview(review.id)"
                                class="pd-review-delete"
                                aria-label="Delete review"
                            >
                                <svg
                                    width="13"
                                    height="13"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M3 6h18" />
                                    <path
                                        d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"
                                    />
                                    <path
                                        d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"
                                    />
                                </svg>
                            </button>
                        </div>
                        <p class="pd-review-body">{{ review.message }}</p>
                        <div
                            v-if="review.review_images?.length"
                            class="pd-review-imgs"
                        >
                            <button
                                v-for="(img, idx) in review.review_images"
                                :key="idx"
                                class="pd-review-img"
                                :aria-label="`View photo ${idx + 1} from ${review.user.name}'s review`"
                                @click="
                                    openReviewImage(review.review_images, idx)
                                "
                            >
                                <img
                                    :src="img"
                                    :alt="`${product.name} photo ${idx + 1} from ${review.user.name}'s review`"
                                    loading="lazy"
                                />
                            </button>
                        </div>
                        <div v-if="review.admin_reply" class="pd-admin-reply">
                            <div class="pd-admin-reply-head">
                                <svg
                                    width="12"
                                    height="12"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                                    />
                                </svg>
                                Chapter of You replied
                            </div>
                            <p class="pd-admin-reply-body">
                                {{ review.admin_reply }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="pd-no-reviews">
                    No reviews yet. Be the first!
                </p>
            </section>

            <RecentJournalPosts
                :posts="recentPosts"
                heading="From the Journal"
            />
        </div>

        <section v-if="related.length" class="pd-related">
            <div class="pd-related-wrap">
                <h2 class="pd-related-title">You might also love</h2>
                <ul class="pd-related-grid">
                    <li v-for="rp in related" :key="rp.id">
                        <ProductSpringCard
                            :product="rp"
                            :wishlisted="wishlistedIds.includes(rp.id)"
                            @add-to-cart="handleAddToCart(rp, $event)"
                            @favourite="handleFavourite(rp)"
                        />
                    </li>
                </ul>
            </div>
        </section>
    </main>

    <Transition name="pd-sticky-slide">
        <div v-if="showStickyCta && !isOutOfStock" class="pd-sticky-cta">
            <div class="pd-sticky-inner">
                <div class="pd-sticky-info">
                    <span class="pd-sticky-name">{{ product.name }}</span>
                    <span class="pd-sticky-price">{{ formattedCost }}</span>
                </div>
                <button @click="handleAddToCart()" class="pd-sticky-btn">
                    Add to basket
                </button>
            </div>
        </div>
    </Transition>

    <SuccessToast ref="successToastRef" />
    <ModalImageViewer
        :images="modalImages"
        :label="product.name"
        :initial-index="modalImageIndex"
        :open="isModalOpen"
        @update:open="isModalOpen = $event"
    />

    <Footer />
</template>

<style scoped>
.pd {
    min-height: 100vh;
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
}
.pd-wrap,
.pd-related-wrap {
    width: min(100% - 2 * var(--coy-gutter), var(--coy-container-lg));
    margin-inline: auto;
}
.pd-wrap {
    padding-block: clamp(1.5rem, 4vw, 3rem) var(--coy-section-space);
}
.pd-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(21rem, 0.92fr);
    gap: clamp(2rem, 5vw, 4.5rem);
    align-items: start;
}
.pd-mobile-intro {
    display: none;
}
.pd-info {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: clamp(1.25rem, 3vw, 2rem);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-sm);
}
.pd-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.pd-crumb {
    color: var(--coy-color-text);
    font-size: var(--coy-text-xs);
    text-decoration: none;
}
a.pd-crumb:hover {
    color: var(--coy-color-accent);
    text-decoration: underline;
    text-underline-offset: 0.2rem;
}
.pd-crumb-sep {
    color: var(--coy-color-rose-gold);
}
.pd-title {
    margin: 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(2.25rem, 4.5vw, 3.75rem);
    font-weight: var(--coy-font-weight-medium);
    line-height: 1.03;
    text-wrap: balance;
}
.pd-rating-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.pd-rating-label {
    color: var(--coy-color-text);
    font-size: var(--coy-text-sm);
}
.pd-stock-row {
    display: flex;
}
.pd-stock-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    border: 1px solid;
    border-radius: var(--coy-radius-pill);
    font-size: var(--coy-text-xs);
    font-weight: var(--coy-font-weight-semibold);
}
.pd-stock--in {
    color: var(--coy-color-success);
    background: var(--coy-color-success-soft);
}
.pd-stock--low {
    color: #87500c;
    background: #fff8eb;
    border-color: #d6a45b;
}
.pd-stock--out {
    color: var(--coy-color-error);
    background: var(--coy-color-error-soft);
}
.pd-price {
    margin: 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 2rem;
    font-weight: var(--coy-font-weight-semibold);
}
.pd-variations,
.pd-refill {
    padding-top: 1.25rem;
    border-top: 1px solid var(--coy-color-border-soft);
}
.pd-variations-label {
    margin: 0 0 0.75rem;
    color: var(--coy-color-heading);
    font-size: var(--coy-text-sm);
    font-weight: var(--coy-font-weight-semibold);
}
.pd-variation-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}
.pd-variation-btn {
    min-height: var(--coy-control-height);
    padding: 0.55rem 1rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-pill);
    font: inherit;
    cursor: pointer;
}
.pd-variation-btn:hover:not(:disabled) {
    background: var(--coy-color-surface-soft);
    border-color: var(--coy-color-rose-gold);
}
.pd-variation-btn--active {
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-color: var(--coy-color-accent);
}
.pd-variation-btn--disabled {
    opacity: 0.45;
    cursor: not-allowed;
    text-decoration: line-through;
}
.pd-refill {
    padding: 1rem;
    background: var(--coy-color-surface-soft);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-md);
}
.pd-refill-label {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    color: var(--coy-color-heading);
    cursor: pointer;
}
.pd-refill-label--disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.pd-refill-checkbox {
    width: 1.15rem;
    height: 1.15rem;
    flex: 0 0 auto;
    accent-color: var(--coy-color-accent);
}
.pd-refill-text {
    font-weight: var(--coy-font-weight-semibold);
}
.pd-refill-price,
.pd-refill-other-link {
    color: var(--coy-color-accent);
}
.pd-refill-oos,
.pd-refill-other {
    margin: 0.45rem 0 0 1.85rem;
    font-size: var(--coy-text-xs);
}
.pd-actions {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: 0.75rem;
    align-items: end;
    padding-top: 1.25rem;
    border-top: 1px solid var(--coy-color-border-soft);
}
.pd-qty-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.pd-action-label {
    color: var(--coy-color-heading);
    font-size: var(--coy-text-xs);
    font-weight: var(--coy-font-weight-semibold);
}
.pd-qty {
    height: 3.25rem;
    display: grid;
    grid-template-columns: 2.75rem 2.75rem 2.75rem;
    overflow: hidden;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-pill);
}
.pd-qty-btn {
    display: grid;
    place-items: center;
    padding: 0;
    color: var(--coy-color-heading);
    background: transparent;
    border: 0;
    cursor: pointer;
}
.pd-qty-btn:hover:not(:disabled) {
    background: var(--coy-color-surface-soft);
}
.pd-qty-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
.pd-qty-input {
    width: 100%;
    padding: 0;
    color: var(--coy-color-heading);
    background: transparent;
    border: 0;
    border-inline: 1px solid var(--coy-color-border-soft);
    font: 600 1rem var(--coy-font-body);
    text-align: center;
    appearance: textfield;
}
.pd-qty-input::-webkit-inner-spin-button,
.pd-qty-input::-webkit-outer-spin-button {
    margin: 0;
    appearance: none;
}
.pd-cart-btn,
.pd-sticky-btn,
.btn-rose {
    min-height: 3.25rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.7rem 1.25rem;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border: 1px solid var(--coy-color-accent);
    border-radius: var(--coy-radius-pill);
    font: 700 1rem var(--coy-font-body);
    cursor: pointer;
    transition:
        background var(--coy-duration-base) var(--coy-ease),
        transform var(--coy-duration-base) var(--coy-ease);
}
.pd-cart-btn:hover:not(:disabled),
.pd-sticky-btn:hover,
.btn-rose:hover:not(:disabled) {
    background: var(--coy-color-accent-hover);
    transform: translateY(-1px);
}
.pd-cart-btn:disabled,
.btn-rose:disabled {
    color: var(--coy-color-text);
    background: var(--coy-color-border-soft);
    border-color: var(--coy-color-border);
    cursor: not-allowed;
}
.pd-wish-btn {
    width: 3.25rem;
    height: 3.25rem;
    display: grid;
    place-items: center;
    padding: 0;
    color: var(--coy-color-accent);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: 50%;
    cursor: pointer;
}
.pd-wish-btn:hover,
.pd-wish-btn--active {
    background: var(--coy-color-surface-soft);
    border-color: var(--coy-color-rose-gold);
}
.pd-dispatch-note {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    font-size: var(--coy-text-sm);
}
.pd-trust {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    padding: 1rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-md);
}
.pd-trust-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: var(--coy-text-xs);
}
.pd-trust-item svg {
    flex: 0 0 auto;
    color: var(--coy-color-accent);
}
.pd-mpn {
    margin: 0;
    color: var(--coy-color-text);
    font-size: var(--coy-text-xs);
}
.pd-product-copy {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: clamp(2rem, 6vw, 5rem);
    margin-top: var(--coy-section-space);
    padding-block: clamp(2.5rem, 5vw, 4rem);
    border-block: 1px solid var(--coy-color-border);
}
.pd-description .coy-eyebrow {
    margin-bottom: 0.35rem;
}
.pd-section-title {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    margin: 0 0 1rem;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(1.75rem, 3vw, 2.25rem);
    font-weight: var(--coy-font-weight-semibold);
    line-height: var(--coy-leading-heading);
}
.pd-description-body,
.pd-how-to-use {
    color: var(--coy-color-text);
    font-size: var(--coy-text-body);
    line-height: var(--coy-leading-body);
}
.pd-description-body :deep(p),
.pd-how-to-use :deep(p) {
    margin: 0 0 0.8rem;
}
.pd-description-body :deep(p:last-child),
.pd-how-to-use :deep(p:last-child) {
    margin-bottom: 0;
}
.pd-description-body :deep(ul),
.pd-description-body :deep(ol),
.pd-how-to-use :deep(ul),
.pd-how-to-use :deep(ol) {
    padding-left: 1.25rem;
}
.pd-content-section,
.pd-reviews-section {
    padding-top: clamp(2.5rem, 6vw, 4.5rem);
}
.pd-content-section {
    max-width: var(--coy-container-md);
}
.pd-how-to-use {
    padding: 1.5rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
}
.pd-reviews-count {
    min-width: 2rem;
    display: inline-grid;
    place-items: center;
    padding: 0.1rem 0.55rem;
    color: var(--coy-color-accent);
    background: var(--coy-color-surface-soft);
    border-radius: var(--coy-radius-pill);
    font: 700 var(--coy-text-xs) var(--coy-font-body);
}
.pd-avg-rating {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.pd-avg-val {
    font-weight: var(--coy-font-weight-semibold);
}
.pd-review-form-card,
.pd-review-card,
.pd-notice {
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
}
.pd-review-form-card {
    max-width: var(--coy-container-md);
    margin-bottom: 2rem;
    overflow: hidden;
}
.pd-rf-rating-section {
    padding: 1.5rem;
    background: var(--coy-color-surface-soft);
    border-bottom: 1px solid var(--coy-color-border-soft);
    text-align: center;
}
.pd-rf-rating-prompt {
    margin: 0 0 0.75rem;
    color: var(--coy-color-heading);
    font-weight: var(--coy-font-weight-semibold);
}
.pd-rf-stars {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
}
.pd-rf-star {
    min-width: 2.75rem;
    min-height: 2.75rem;
    padding: 0;
    color: var(--coy-color-border);
    background: transparent;
    border: 0;
    cursor: pointer;
}
.pd-rf-star:hover,
.pd-rf-star--filled {
    color: var(--coy-color-accent);
}
.pd-rf-rating-label {
    min-height: 1.5rem;
    margin: 0.35rem 0 0;
    color: var(--coy-color-accent);
}
.pd-review-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.5rem;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.field-label {
    color: var(--coy-color-heading);
    font-weight: var(--coy-font-weight-semibold);
}
.field-optional {
    color: var(--coy-color-text);
    font-weight: var(--coy-font-weight-regular);
}
.field-input {
    min-height: var(--coy-control-height);
    padding: 0.7rem 1rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    font: inherit;
}
.field-textarea {
    min-height: 7rem;
    resize: vertical;
}
.field-input--error {
    border-color: var(--coy-color-error);
}
.field-error {
    margin: 0;
    color: var(--coy-color-error);
    font-size: var(--coy-text-xs);
}
.pd-file-label {
    width: fit-content;
    min-height: var(--coy-control-height);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1rem;
    color: var(--coy-color-accent);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-pill);
    font-weight: var(--coy-font-weight-semibold);
    cursor: pointer;
}
.pd-file-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip-path: inset(50%);
}
.pd-rf-footer {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.pd-rf-no-rating-hint {
    margin: 0;
    font-size: var(--coy-text-xs);
}
.pd-notice {
    max-width: var(--coy-container-md);
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem;
}
.pd-notice-link {
    color: var(--coy-color-accent);
    font-weight: var(--coy-font-weight-semibold);
}
.pd-review-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}
.pd-review-card {
    padding: 1.25rem;
}
.pd-review-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}
.pd-reviewer-name {
    margin: 0.35rem 0 0;
    color: var(--coy-color-heading);
    font-weight: var(--coy-font-weight-semibold);
}
.pd-review-date {
    margin: 0;
    font-size: var(--coy-text-xs);
}
.pd-review-body {
    margin: 1rem 0 0;
}
.pd-review-delete {
    width: 2.75rem;
    height: 2.75rem;
    display: grid;
    place-items: center;
    color: var(--coy-color-error);
    background: var(--coy-color-error-soft);
    border: 1px solid var(--coy-color-error);
    border-radius: 50%;
    cursor: pointer;
}
.pd-review-imgs {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}
.pd-review-img {
    width: 4rem;
    height: 4rem;
    padding: 0;
    overflow: hidden;
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    cursor: pointer;
}
.pd-review-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.pd-admin-reply {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--coy-color-surface-soft);
    border-left: 3px solid var(--coy-color-accent);
    border-radius: var(--coy-radius-sm);
}
.pd-admin-reply-head {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--coy-color-accent);
    font-size: var(--coy-text-xs);
    font-weight: var(--coy-font-weight-bold);
}
.pd-admin-reply-body {
    margin: 0.5rem 0 0;
}
.pd-no-reviews {
    margin: 1rem 0 0;
}
.pd-related {
    padding-block: clamp(3rem, 6vw, 5rem);
    background: var(--coy-color-surface-soft);
    border-top: 1px solid var(--coy-color-border);
}
.pd-related-title {
    margin: 0 0 1.5rem;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: var(--coy-font-weight-medium);
}
.pd-related-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin: 0;
    padding: 0;
    list-style: none;
}
.pd-sticky-cta {
    position: fixed;
    z-index: 55;
    right: 0;
    bottom: 0;
    left: 0;
    display: none;
    padding: 0.75rem var(--coy-gutter)
        calc(0.75rem + env(safe-area-inset-bottom));
    background: rgb(255 253 251 / 96%);
    border-top: 1px solid var(--coy-color-border);
    box-shadow: 0 -10px 30px rgb(52 42 40 / 14%);
    backdrop-filter: blur(10px);
}
.pd-sticky-inner {
    max-width: 34rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-inline: auto;
}
.pd-sticky-info {
    min-width: 0;
    display: flex;
    flex-direction: column;
}
.pd-sticky-name {
    overflow: hidden;
    color: var(--coy-color-heading);
    font-weight: var(--coy-font-weight-semibold);
    text-overflow: ellipsis;
    white-space: nowrap;
}
.pd-sticky-price {
    color: var(--coy-color-accent);
}
.pd-sticky-btn {
    min-height: var(--coy-control-height);
    flex: 0 0 auto;
}
.pd-sticky-slide-enter-active,
.pd-sticky-slide-leave-active {
    transition:
        opacity 0.2s,
        transform 0.25s var(--coy-ease);
}
.pd-sticky-slide-enter-from,
.pd-sticky-slide-leave-to {
    opacity: 0;
    transform: translateY(100%);
}
@media (max-width: 900px) {
    .pd-grid {
        grid-template-columns: minmax(0, 1fr) minmax(19rem, 0.85fr);
        gap: 2rem;
    }
}
@media (max-width: 760px) {
    .pd {
        padding-bottom: 5.5rem;
    }
    .pd-wrap {
        padding-top: 1.25rem;
    }
    .pd-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .pd-mobile-intro {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }
    .pd-mobile-intro .pd-price {
        font-size: 1.75rem;
    }
    .pd-info > .pd-breadcrumb,
    .pd-info > .pd-title,
    .pd-info > .pd-price {
        display: none;
    }
    .pd-product-copy,
    .pd-review-list {
        grid-template-columns: 1fr;
    }
    .pd-product-copy {
        margin-top: 3rem;
    }
    .pd-related-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .pd-sticky-cta {
        display: block;
    }
}
@media (max-width: 480px) {
    .pd-title {
        font-size: 2.25rem;
    }
    .pd-actions {
        grid-template-columns: auto minmax(0, 1fr) auto;
    }
    .pd-qty {
        grid-template-columns: 2.5rem 2.5rem 2.5rem;
    }
    .pd-cart-btn {
        padding-inline: 0.8rem;
    }
    .pd-cart-btn svg {
        display: none;
    }
    .pd-trust {
        grid-template-columns: 1fr;
    }
}
</style>
