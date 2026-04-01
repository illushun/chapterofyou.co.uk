<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import ModalImageViewer from '@/components/ui/coy/ModalImageViewer.vue';
import StarRating from '@/components/ui/coy/StarRating.vue';

interface ProductImage { image: string; }
interface ProductReview {
    id: number; product_id: number; user_id: number;
    message: string; rating: number; review_images: string[];
    created_at: string; user: { id: number; name: string };
}
interface ProductVariation {
    id: number; mpn: string; name: string;
    cost: number; stock_qty: number; parent_product_id: number;
}
interface ProductDetailData {
    id: number; name: string; mpn: string; description: string;
    cost: number; stock_qty: number; total_unique_views: number;
    average_rating: number; approved_reviews_count: number;
    images: ProductImage[]; reviews: ProductReview[];
    categories: { id: number; name: string }[];
    children: ProductVariation[];
    seo?: { meta_title: string; meta_description: string };
}
interface ProductProps {
    product: ProductDetailData;
    parent?: ProductDetailData | null;
    related: ProductDetailData[];
    canReview: boolean;
    wishlisted: boolean;
    wishlistedIds: number[];
}

const props = defineProps<ProductProps>();
const page = usePage();
const auth = computed(() => page.props.auth as any);
const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);
const isModalOpen = ref(false);
const isWishlisted = ref(props.wishlisted ?? false);
const wishlistedIds = ref<number[]>(props.wishlistedIds ?? []);

const quantity = ref(1);
const increaseQuantity = () => { if (currentVariation.value.stock_qty > quantity.value) quantity.value++; };
const decreaseQuantity = () => { if (quantity.value > 1) quantity.value--; };

const selectedImageIndex = ref(0);
const mainImageUrl = computed(() => props.product.images[selectedImageIndex.value]?.image || '/images/placeholder.jpg');
const openImageModal = () => { if ((props.product.images || []).length > 0) isModalOpen.value = true; };

const reviewForm = useForm({ rating: 0, message: '', images: [] as File[] });

const submitReview = () => {
    reviewForm.post(route('products.review.store', props.product.id), {
        onSuccess: () => {
            successToastRef.value?.show('Review submitted! Awaiting approval.', 'star');
            reviewForm.reset('rating', 'message', 'images');
        },
    });
};

const deleteReview = (reviewId: number) => {
    router.delete(route('products.review.destroy', reviewId), {
        preserveScroll: true,
        onSuccess: () => successToastRef.value?.show('Review deleted.', 'trash'),
    });
};

const handleImageUpload = (event: Event) => {
    const t = event.target as HTMLInputElement;
    if (t.files) reviewForm.images = Array.from(t.files).slice(0, 3);
};

const getInitialVariationId = (): number | null => {
    if (props.product.children?.length > 0)
        return props.product.children.find(v => v.stock_qty > 0)?.id ?? null;
    return null;
};

const selectedVariationId = ref<number | null>(getInitialVariationId());
const currentVariation = computed(() => {
    if (selectedVariationId.value) {
        const v = props.product.children.find(v => v.id === selectedVariationId.value);
        if (v) return v;
    }
    return { id: props.product.id, cost: props.product.cost, stock_qty: props.product.stock_qty || 0, mpn: props.product.mpn };
});

const isOutOfStock = computed(() => currentVariation.value.stock_qty <= 0);
const isPopular = computed(() => props.product.total_unique_views > 100);

const fmt = (v: number | string) => {
    const n = Number(v);
    return isNaN(n) ? 'N/A' : `£${n.toFixed(2)}`;
};
const formattedCost = computed(() => fmt(currentVariation.value.cost));

const handleAddToCart = (quickAddProduct: ProductDetailData | null = null) => {
    const itemToAdd = quickAddProduct ?? currentVariation.value;
    const qty = quickAddProduct ? 1 : quantity.value;
    const name = quickAddProduct ? quickAddProduct.name : props.product.name;

    if (!itemToAdd?.id || qty < 1 || itemToAdd.stock_qty < qty) return;

    router.post('/cart/add', { product_id: itemToAdd.id, quantity: qty }, {
        preserveScroll: true,
        onSuccess: () => {
            successToastRef.value?.show(`${qty} × ${name} added to cart!`, 'cart');
            if (!quickAddProduct) quantity.value = 1;
        },
    });
};

const handleFavourite = async (productArg?: any) => {
    const targetId = (productArg && typeof productArg === 'object' ? productArg.id : null) ?? props.product.id;
    const isRelated = targetId !== props.product.id;

    isRelated
        ? (wishlistedIds.value.includes(targetId)
            ? wishlistedIds.value.splice(wishlistedIds.value.indexOf(targetId), 1)
            : wishlistedIds.value.push(targetId))
        : (isWishlisted.value = !isWishlisted.value);

    try {
        const { data } = await axios.post(route('wishlist.toggle'), { product_id: targetId });
        successToastRef.value?.show(data.message, data.wishlisted ? 'favourite' : 'trash');
    } catch (err: any) {
        isRelated
            ? (wishlistedIds.value.includes(targetId)
                ? wishlistedIds.value.splice(wishlistedIds.value.indexOf(targetId), 1)
                : wishlistedIds.value.push(targetId))
            : (isWishlisted.value = !isWishlisted.value);
        if (err.response?.status === 401) window.location.href = route('login');
    }
};

const pageTitle = computed(() => props.product.seo?.meta_title || `${props.product.name} | Chapter of You`);
</script>

<template>
    <NavBar />

    <Head :title="pageTitle" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="pd">
        <div class="pd-wrap">

            <!-- ── Product main section ── -->
            <div class="pd-grid">

                <!-- Image column -->
                <div class="pd-images">
                    <!-- Main image -->
                    <button @click="openImageModal" class="pd-main-img-btn" aria-label="View full image">
                        <div class="pd-main-img-wrap">
                            <span v-if="isPopular" class="pd-popular-badge">Popular</span>
                            <img :src="mainImageUrl" :alt="product.name" class="pd-main-img" />
                            <!-- Zoom hint -->
                            <div class="pd-img-zoom-hint" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3M11 8v6M8 11h6" />
                                </svg>
                            </div>
                        </div>
                    </button>

                    <!-- Thumbnails -->
                    <div v-if="product.images.length > 1" class="pd-thumbs">
                        <button v-for="(img, i) in product.images" :key="i" @click="selectedImageIndex = i"
                            class="pd-thumb" :class="{ 'pd-thumb--active': selectedImageIndex === i }">
                            <img :src="img.image" :alt="`View ${i + 1}`" class="pd-thumb-img" />
                        </button>
                    </div>
                </div>

                <!-- Info column -->
                <div class="pd-info">

                    <!-- Breadcrumb -->
                    <nav class="pd-breadcrumb" aria-label="Breadcrumb">
                        <a href="/products" class="pd-crumb">Products</a>
                        <template v-for="cat in product.categories" :key="cat.id">
                            <span class="pd-crumb-sep" aria-hidden="true">/</span>
                            <a :href="`/products?categories=${cat.id}`" class="pd-crumb">{{ cat.name }}</a>
                        </template>
                    </nav>

                    <h1 class="pd-title">{{ product.name }}</h1>
                    <p class="pd-mpn">{{ currentVariation.mpn }}</p>

                    <!-- Rating summary -->
                    <div v-if="product.approved_reviews_count > 0" class="pd-rating-row">
                        <StarRating :rating="product.average_rating" :size="18" class="text-secondary-content" />
                        <span class="pd-rating-label">{{ product.average_rating.toFixed(1) }} ({{
                            product.approved_reviews_count }} review{{
                                product.approved_reviews_count !== 1 ? 's' : '' }})</span>
                    </div>

                    <!-- Stock badge -->
                    <div class="pd-stock-row">
                        <span class="pd-stock-badge" :class="isOutOfStock ? 'pd-stock--out' : 'pd-stock--in'">
                            {{ isOutOfStock ? 'Out of Stock' : 'In Stock' }}
                        </span>
                    </div>

                    <!-- Price -->
                    <p class="pd-price">{{ formattedCost }}</p>

                    <!-- Variations -->
                    <div v-if="product.children?.length > 0" class="pd-variations">
                        <h3 class="pd-variations-label">Choose Option</h3>
                        <div class="pd-variation-btns">
                            <button v-for="v in product.children" :key="v.id" @click="selectedVariationId = v.id"
                                :disabled="v.stock_qty <= 0" class="pd-variation-btn" :class="{
                                    'pd-variation-btn--active': v.id === selectedVariationId,
                                    'pd-variation-btn--disabled': v.stock_qty <= 0,
                                }">
                                {{ v.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Add to cart row -->
                    <div class="pd-actions">
                        <!-- Qty stepper -->
                        <div class="pd-qty">
                            <button @click="decreaseQuantity" :disabled="quantity <= 1" class="pd-qty-btn"
                                aria-label="Decrease quantity">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round">
                                    <path d="M5 12h14" />
                                </svg>
                            </button>
                            <input type="number" v-model.number="quantity" min="1" :max="currentVariation.stock_qty"
                                @change="quantity = Math.max(1, Math.min(currentVariation.stock_qty, Number(quantity) || 1))"
                                class="pd-qty-input" aria-label="Quantity" />
                            <button @click="increaseQuantity" :disabled="quantity >= currentVariation.stock_qty"
                                class="pd-qty-btn" aria-label="Increase quantity">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round">
                                    <path d="M5 12h14" />
                                    <path d="M12 5v14" />
                                </svg>
                            </button>
                        </div>

                        <!-- Add to cart -->
                        <button @click="handleAddToCart()"
                            :disabled="isOutOfStock || quantity > currentVariation.stock_qty || quantity < 1"
                            class="pd-cart-btn" :class="{ 'pd-cart-btn--disabled': isOutOfStock }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <path d="M16 10a4 4 0 0 1-8 0" />
                            </svg>
                            {{ isOutOfStock ? 'Out of Stock' : 'Add to Cart' }}
                        </button>

                        <!-- Wishlist -->
                        <button @click="handleFavourite()" class="pd-wish-btn"
                            :class="{ 'pd-wish-btn--active': isWishlisted }"
                            :aria-label="isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'">
                            <svg v-if="isWishlisted" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Description -->
                    <div class="pd-description">
                        <h2 class="pd-section-title">Details</h2>
                        <div class="pd-description-body" v-html="product.description"></div>
                    </div>

                </div>
            </div>

            <!-- ── Reviews ── -->
            <section class="pd-reviews-section">
                <h2 class="pd-section-title">
                    Customer Reviews
                    <span class="pd-reviews-count">{{ product.approved_reviews_count }}</span>
                </h2>

                <!-- Average rating -->
                <div v-if="product.approved_reviews_count > 0" class="pd-avg-rating">
                    <StarRating :rating="product.average_rating" :size="22" class="text-secondary-content" />
                    <span class="pd-avg-val">{{ product.average_rating.toFixed(1) }} average</span>
                </div>

                <!-- Write a review -->
                <div v-if="canReview" class="pd-review-form-card">
                    <h3 class="pd-review-form-title">Write a Review</h3>
                    <form @submit.prevent="submitReview" class="pd-review-form">
                        <div class="field">
                            <label class="field-label">Your Rating</label>
                            <StarRating :rating="reviewForm.rating" :editable="true"
                                @update:rating="reviewForm.rating = $event" :size="28" class="text-secondary-content" />
                            <p v-if="reviewForm.errors.rating" class="field-error">{{ reviewForm.errors.rating }}</p>
                        </div>
                        <div class="field">
                            <label for="review_message" class="field-label">Your Review</label>
                            <textarea id="review_message" v-model="reviewForm.message" rows="4"
                                class="field-input field-textarea"
                                :class="{ 'field-input--error': reviewForm.errors.message }"></textarea>
                            <p v-if="reviewForm.errors.message" class="field-error">{{ reviewForm.errors.message }}</p>
                        </div>
                        <div class="field">
                            <label for="review_images" class="field-label">
                                Photos <span class="field-optional">(up to 3)</span>
                            </label>
                            <input type="file" id="review_images" multiple accept="image/*" @change="handleImageUpload"
                                class="pd-file-input" />
                            <p v-if="reviewForm.errors['images'] || reviewForm.errors['images.0']" class="field-error">
                                {{ reviewForm.errors['images'] || reviewForm.errors['images.0'] }}
                            </p>
                        </div>
                        <button type="submit" :disabled="reviewForm.processing || reviewForm.rating === 0"
                            class="btn-rose" :class="{ 'btn-rose--disabled': reviewForm.rating === 0 }">
                            Submit Review
                        </button>
                    </form>
                </div>

                <!-- Not eligible notices -->
                <div v-else-if="auth.user" class="pd-notice">
                    You must have purchased this product to leave a review.
                </div>
                <div v-else class="pd-notice">
                    <a :href="route('login')" class="pd-notice-link">Log in</a> to see if you're eligible to leave a
                    review.
                </div>

                <!-- Review list -->
                <div v-if="product.reviews.length > 0" class="pd-review-list">
                    <div v-for="review in product.reviews" :key="review.id" class="pd-review-card">
                        <div class="pd-review-header">
                            <div>
                                <StarRating :rating="review.rating" :size="16" class="text-secondary-content" />
                                <p class="pd-reviewer-name">{{ review.user.name }}</p>
                                <p class="pd-review-date">{{ new Date(review.created_at).toLocaleDateString('en-GB', {
                                    day:
                                    'numeric', month: 'long', year: 'numeric' }) }}</p>
                            </div>
                            <button v-if="auth.user && auth.user.id === review.user_id" @click="deleteReview(review.id)"
                                class="pd-review-delete" aria-label="Delete review">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                </svg>
                            </button>
                        </div>
                        <p class="pd-review-body">{{ review.message }}</p>
                        <div v-if="review.review_images?.length" class="pd-review-imgs">
                            <img v-for="(img, idx) in review.review_images" :key="idx" :src="img"
                                :alt="`Review photo ${idx + 1}`" class="pd-review-img" @click="openImageModal" />
                        </div>
                    </div>
                </div>
                <p v-else class="pd-no-reviews">No reviews yet. Be the first!</p>
            </section>

        </div>

        <!-- ── Related products ── -->
        <section v-if="related.length" class="pd-related">
            <div class="pd-related-wrap">
                <h2 class="pd-related-title">You might also love</h2>
                <ul class="pd-related-grid">
                    <li v-for="rp in related" :key="rp.id">
                        <ProductSpringCard :product="rp" :wishlisted="wishlistedIds.includes(rp.id)"
                            @add-to-cart="handleAddToCart(rp)" @favourite="handleFavourite(rp)" />
                    </li>
                </ul>
            </div>
        </section>
    </main>

    <SuccessToast ref="successToastRef" />
    <ModalImageViewer :images="product.images || []" :initial-index="selectedImageIndex" :open="isModalOpen"
        @update:open="isModalOpen = $event" />
</template>

<style scoped>
.pd {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.pd-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 3rem 1.25rem 4rem;
}

/* ── Product grid ── */
.pd-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3.5rem;
    align-items: start;
    margin-bottom: 4rem;
}

@media (max-width: 860px) {
    .pd-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* ── Images ── */
.pd-images {
    position: sticky;
    top: 88px;
}

.pd-main-img-btn {
    display: block;
    width: 100%;
    border: none;
    background: none;
    padding: 0;
    cursor: zoom-in;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 0.85rem;
}

.pd-main-img-wrap {
    position: relative;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    overflow: hidden;
    background: #fdf4f3;
    aspect-ratio: 1 / 1;
}

.pd-popular-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 10;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    background: #8c4a50;
    color: #fff;
    border-radius: 999px;
    padding: 0.2rem 0.65rem;
}

.pd-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.pd-main-img-btn:hover .pd-main-img {
    transform: scale(1.04);
}

.pd-img-zoom-hint {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 250, 250, 0.88);
    border: 1px solid #e5c9c7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8c4a50;
    opacity: 0;
    transition: opacity 0.2s;
}

.pd-main-img-btn:hover .pd-img-zoom-hint {
    opacity: 1;
}

.pd-thumbs {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
}

.pd-thumb {
    border-radius: 12px;
    border: 1px solid #e5c9c7;
    overflow: hidden;
    background: #fdf4f3;
    aspect-ratio: 1/1;
    cursor: pointer;
    padding: 0;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.pd-thumb--active {
    border-color: #8c4a50;
    box-shadow: 0 0 0 2px rgba(140, 74, 80, 0.15);
}

.pd-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ── Info column ── */
.pd-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.pd-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.pd-crumb {
    font-size: 0.8rem;
    color: #6b4f4f;
    text-decoration: none;
    transition: color 0.2s;
}

.pd-crumb:hover {
    color: #8c4a50;
}

.pd-crumb-sep {
    font-size: 0.8rem;
    color: #c9a4a4;
}

.pd-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 400;
    color: #2d1a1a;
    line-height: 1.15;
    margin: 0;
}

.pd-mpn {
    font-size: 0.78rem;
    font-family: monospace;
    letter-spacing: 0.05em;
    color: #a08080;
}

.pd-rating-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.pd-rating-label {
    font-size: 0.85rem;
    color: #6b4f4f;
}

.pd-stock-row {
    display: flex;
}

.pd-stock-badge {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    border-radius: 999px;
    padding: 0.2rem 0.75rem;
    border: 1px solid transparent;
}

.pd-stock--in {
    background: #f0faf0;
    color: #2d7a3a;
    border-color: #a8d8b0;
}

.pd-stock--out {
    background: #fff5f5;
    color: #8c2a2a;
    border-color: #e8a8a8;
}

.pd-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.4rem;
    font-weight: 400;
    color: #8c4a50;
    letter-spacing: -0.02em;
    margin: 0;
}

/* ── Variations ── */
.pd-variations {
    border-top: 1px solid #e5c9c7;
    padding-top: 1rem;
}

.pd-variations-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #6b4f4f;
    margin-bottom: 0.65rem;
}

.pd-variation-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.pd-variation-btn {
    padding: 0.45rem 1rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s, color 0.2s;
}

.pd-variation-btn:hover {
    border-color: #c9a4a4;
    background: #faeaea;
}

.pd-variation-btn--active {
    border-color: #8c4a50;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-weight: 600;
}

.pd-variation-btn--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    text-decoration: line-through;
}

/* ── Actions row ── */
.pd-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding-top: 0.5rem;
}

/* Qty stepper */
.pd-qty {
    display: flex;
    align-items: center;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    overflow: hidden;
    background: #fffafa;
    flex-shrink: 0;
}

.pd-qty-btn {
    width: 36px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    cursor: pointer;
    color: #8c4a50;
    transition: background 0.15s;
}

.pd-qty-btn:hover:not(:disabled) {
    background: #faeaea;
}

.pd-qty-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.pd-qty-input {
    width: 40px;
    text-align: center;
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d1a1a;
    background: transparent;
    border: none;
    border-left: 1px solid #e5c9c7;
    border-right: 1px solid #e5c9c7;
    outline: none;
    padding: 0;
    height: 40px;
    line-height: 40px;
    /* hide number arrows */
    -moz-appearance: textfield;
}

.pd-qty-input::-webkit-outer-spin-button,
.pd-qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Cart button */
.pd-cart-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.7rem 1.5rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.pd-cart-btn:hover:not(:disabled):not(.pd-cart-btn--disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.pd-cart-btn--disabled,
.pd-cart-btn:disabled {
    background: #f0dcd8;
    border-color: #e5c9c7;
    color: #9a7070;
    cursor: not-allowed;
    box-shadow: none;
}

/* Wishlist button */
.pd-wish-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    color: #c9a4a4;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.pd-wish-btn:hover {
    background: #faeaea;
    color: #8c4a50;
    border-color: #c9a4a4;
}

.pd-wish-btn--active {
    background: #faeaea;
    color: #8c4a50;
    border-color: #c9a4a4;
}

/* ── Description ── */
.pd-description {
    border-top: 1px solid #e5c9c7;
    padding-top: 1.25rem;
}

.pd-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.25rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.85rem;
}

.pd-description-body {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.75;
}

/* ── Reviews section ── */
.pd-reviews-section {
    border-top: 1px solid #e5c9c7;
    padding-top: 2.5rem;
}

.pd-reviews-count {
    font-family: 'Nunito', sans-serif;
    font-style: normal;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: rgba(140, 74, 80, 0.1);
    color: #8c4a50;
    border: 1px solid rgba(140, 74, 80, 0.2);
    border-radius: 999px;
    padding: 0.15rem 0.55rem;
}

.pd-avg-rating {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.5rem;
}

.pd-avg-val {
    font-size: 0.9rem;
    color: #6b4f4f;
    font-style: italic;
}

/* Review form card */
.pd-review-form-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.3);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.pd-review-form-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1.1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5c9c7;
}

.pd-review-form {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

/* Shared field styles */
.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b4f4f;
}

.field-optional {
    font-weight: 400;
    text-transform: none;
    font-style: italic;
}

.field-input {
    padding: 0.65rem 0.9rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.field-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.field-input--error {
    border-color: #c84040;
}

.field-textarea {
    resize: vertical;
    min-height: 110px;
}

.field-error {
    font-size: 0.78rem;
    color: #b54040;
}

.pd-file-input {
    font-size: 0.85rem;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
}

.pd-file-input::file-selector-button {
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: #fdf4f3;
    color: #8c4a50;
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    margin-right: 0.75rem;
    transition: background 0.2s;
}

.pd-file-input::file-selector-button:hover {
    background: #faeaea;
}

/* Notices */
.pd-notice {
    border: 1px solid #e5c9c7;
    border-radius: 12px;
    background: #fffafa;
    padding: 0.85rem 1.1rem;
    font-size: 0.9rem;
    color: #6b4f4f;
    font-style: italic;
    margin-bottom: 1.5rem;
}

.pd-notice-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
}

.pd-notice-link:hover {
    text-decoration: underline;
}

/* Review list */
.pd-review-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1.5rem;
}

.pd-review-card {
    border: 1px solid #e5c9c7;
    border-radius: 16px;
    background: #fffafa;
    padding: 1.25rem;
    position: relative;
    overflow: hidden;
}

.pd-review-card::before {
    content: '✿';
    position: absolute;
    bottom: -5px;
    right: 7px;
    font-size: 2.5rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.pd-review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.pd-reviewer-name {
    font-size: 0.92rem;
    font-weight: 600;
    color: #2d1a1a;
    margin-top: 0.2rem;
}

.pd-review-date {
    font-size: 0.78rem;
    color: #6b4f4f;
    font-style: italic;
}

.pd-review-body {
    font-size: 0.9rem;
    color: #6b4f4f;
    line-height: 1.65;
}

.pd-review-delete {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid #e8a8a8;
    background: #fff5f5;
    color: #8c2a2a;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s;
}

.pd-review-delete:hover {
    background: #fee2e2;
}

.pd-review-imgs {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.pd-review-img {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e5c9c7;
    cursor: pointer;
}

.pd-no-reviews {
    font-size: 0.9rem;
    color: #6b4f4f;
    font-style: italic;
    margin-top: 1rem;
}

/* ── Related products ── */
.pd-related {
    background: #f5ece9;
    padding: 3rem 0 4rem;
    border-top: 1px solid #e5c9c7;
}

.pd-related-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1.25rem;
}

.pd-related-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1.75rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5c9c7;
}

.pd-related-grid {
    list-style: none;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.1rem;
}

@media (max-width: 860px) {
    .pd-related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .pd-related-grid {
        grid-template-columns: 1fr 1fr;
    }
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
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-rose:hover:not(:disabled):not(.btn-rose--disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.btn-rose--disabled,
.btn-rose:disabled {
    background: #f0dcd8;
    border-color: #e5c9c7;
    color: #9a7070;
    cursor: not-allowed;
    box-shadow: none;
}
</style>
