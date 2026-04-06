<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

interface WishlistItem {
    wishlist_id: number;
    added_at: string;
    product: {
        id: number;
        name: string;
        mpn: string;
        cost: number;
        stock_qty: number;
        status: string;
        images: { image: string }[];
        seo: { slug: string } | null;
    };
}

defineProps<{ items: WishlistItem[] }>();

const fmt = (v: number | string) => {
    const n = Number(v);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};

const productLink = (item: WishlistItem) =>
    item.product.seo?.slug ? `/product/${item.product.seo.slug}` : `/product/${item.product.id}`;

const removeItem = (wishlistId: number) => {
    router.delete(route('wishlist.remove', { id: wishlistId }), { preserveScroll: true });
};

const addToCart = (productId: number) => {
    router.post('/cart/add', { product_id: productId, quantity: 1 }, { preserveScroll: true });
};

const seo = useSeoHead({ noIndex: true });
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="wl">
        <div class="wl-wrap">

            <!-- Header -->
            <header class="wl-header">
                <a href="/account" class="wl-back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Back to Account
                </a>
                <h1 class="wl-title">My Wishlist</h1>
                <p class="wl-sub">{{ items.length }} saved item{{ items.length !== 1 ? 's' : '' }}</p>
            </header>

            <!-- Empty state -->
            <div v-if="items.length === 0" class="wl-empty">
                <svg class="wl-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                </svg>
                <h2>Your wishlist is empty</h2>
                <p>Save items you love and come back to them anytime.</p>
                <a href="/products" class="btn-rose">Browse the collection</a>
            </div>

            <!-- Wishlist grid -->
            <div v-else class="wl-grid">
                <div v-for="item in items" :key="item.wishlist_id" class="wl-card">

                    <!-- Image -->
                    <a :href="productLink(item)" class="wl-img-wrap">
                        <img :src="item.product.images?.[0]?.image ?? '/images/placeholder.jpg'"
                            :alt="item.product.name" class="wl-img" />
                        <div v-if="item.product.stock_qty <= 0" class="wl-oos-overlay">
                            <span>Out of Stock</span>
                        </div>
                    </a>

                    <!-- Body -->
                    <div class="wl-body">
                        <p class="wl-mpn">{{ item.product.mpn }}</p>
                        <a :href="productLink(item)" class="wl-name">{{ item.product.name }}</a>

                        <div class="wl-meta">
                            <span class="wl-price">{{ fmt(item.product.cost) }}</span>
                            <span class="wl-stock"
                                :class="item.product.stock_qty > 0 ? 'wl-stock--in' : 'wl-stock--out'">
                                {{ item.product.stock_qty > 0 ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </div>

                        <p class="wl-saved">Saved {{ item.added_at }}</p>

                        <div class="wl-actions">
                            <button @click="addToCart(item.product.id)" :disabled="item.product.stock_qty <= 0"
                                class="btn-rose btn-rose--sm wl-add">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                                    <line x1="3" y1="6" x2="21" y2="6" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                                Add to Cart
                            </button>
                            <button @click="removeItem(item.wishlist_id)" class="wl-remove" title="Remove from wishlist"
                                aria-label="Remove from wishlist">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.wl {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.wl-wrap {
    max-width: 1040px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

/* ── Header ── */
.wl-header {
    margin-bottom: 2.5rem;
}

.wl-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    text-decoration: none;
    margin-bottom: 1rem;
    transition: color 0.2s;
}

.wl-back:hover {
    color: #8c4a50;
}

.wl-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.25rem;
}

.wl-sub {
    font-size: 0.92rem;
    color: #6b4f4f;
    font-style: italic;
}

/* ── Empty ── */
.wl-empty {
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

.wl-empty-icon {
    width: 48px;
    height: 48px;
    color: #c9a4a4;
    margin-bottom: 0.25rem;
}

.wl-empty h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
}

.wl-empty p {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.6;
}

/* ── Grid ── */
.wl-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

@media (max-width: 860px) {
    .wl-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 520px) {
    .wl-grid {
        grid-template-columns: 1fr;
    }
}

/* ── Card ── */
.wl-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: box-shadow 0.25s, transform 0.25s;
}

.wl-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.2rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

.wl-card::after {
    content: '✿';
    position: absolute;
    top: 6px;
    left: 10px;
    font-size: 0.85rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

.wl-card:hover {
    box-shadow: 0 6px 24px rgba(229, 201, 199, 0.55);
    transform: translateY(-2px);
}

/* ── Image ── */
.wl-img-wrap {
    display: block;
    position: relative;
    height: 200px;
    overflow: hidden;
    background: #fdf4f3;
    border-bottom: 1px solid #e5c9c7;
}

.wl-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.wl-card:hover .wl-img {
    transform: scale(1.04);
}

.wl-oos-overlay {
    position: absolute;
    inset: 0;
    background: rgba(253, 244, 243, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
}

.wl-oos-overlay span {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #8c4a50;
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    padding: 0.3rem 0.85rem;
}

/* ── Body ── */
.wl-body {
    padding: 1rem 1.1rem 1.1rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    position: relative;
    z-index: 1;
}

.wl-mpn {
    font-size: 0.72rem;
    font-family: monospace;
    color: #6b4f4f;
    margin-bottom: 0.25rem;
    letter-spacing: 0.04em;
}

.wl-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-weight: 500;
    color: #2d1a1a;
    text-decoration: none;
    line-height: 1.3;
    margin-bottom: 0.75rem;
    transition: color 0.2s;
    display: block;
}

.wl-name:hover {
    color: #8c4a50;
}

.wl-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.65rem;
    border-top: 1px solid #e5c9c7;
    margin-bottom: 0.4rem;
}

.wl-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    font-weight: 500;
    color: #8c4a50;
}

.wl-stock {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    border: 1px solid transparent;
}

.wl-stock--in {
    background: #f0faf0;
    color: #2d7a3a;
    border-color: #a8d8b0;
}

.wl-stock--out {
    background: #fff5f5;
    color: #8c2a2a;
    border-color: #e8a8a8;
}

.wl-saved {
    font-size: 0.75rem;
    color: #6b4f4f;
    font-style: italic;
    margin-bottom: 0.85rem;
}

/* ── Actions ── */
.wl-actions {
    display: flex;
    gap: 0.6rem;
    margin-top: auto;
}

.wl-add {
    flex: 1;
    justify-content: center;
}

.wl-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: transparent;
    color: #c9a4a4;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.wl-remove:hover {
    background: #faeaea;
    color: #8c4a50;
    border-color: #c9a4a4;
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

.btn-rose:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.btn-rose:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.btn-rose--sm {
    padding: 0.55rem 1rem;
    font-size: 0.85rem;
}
</style>
