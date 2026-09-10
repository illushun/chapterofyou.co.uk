<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import CoyBreadcrumbs from '@/components/ui/coy/CoyBreadcrumbs.vue';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link, router } from '@inertiajs/vue3';

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

const seo = useSeoHead({ noIndex: true });

function cardProduct(item: WishlistItem) {
    return {
        ...item.product,
        seo: item.product.seo ?? undefined,
    };
}

function removeItem(wishlistId: number) {
    router.delete(route('wishlist.remove', { id: wishlistId }), {
        preserveScroll: true,
    });
}

function addToCart(productId: number, quantity: number) {
    router.post(
        '/cart/add',
        { product_id: productId, quantity },
        { preserveScroll: true },
    );
}
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <main class="wl coy-storefront">
        <header class="coy-page-header">
            <div class="coy-container coy-page-header__inner">
                <div>
                    <CoyBreadcrumbs
                        :items="[
                            { label: 'Home', href: '/' },
                            { label: 'My account', href: '/account' },
                            { label: 'Wishlist' },
                        ]"
                    />
                    <h1 class="coy-page-header__title">My wishlist</h1>
                    <p class="coy-page-header__meta">
                        {{ items.length }} saved
                        {{ items.length === 1 ? 'fragrance' : 'fragrances' }}
                    </p>
                </div>
                <Link href="/account" class="coy-page-header__action">
                    Back to account
                </Link>
            </div>
        </header>

        <div class="coy-container wl-content">
            <div class="wl-heading">
                <div>
                    <p class="coy-eyebrow">Saved for later</p>
                    <h2>Your fragrances</h2>
                </div>
                <Link href="/products" class="wl-shop-link">
                    Continue shopping <span aria-hidden="true">→</span>
                </Link>
            </div>

            <section v-if="items.length === 0" class="wl-empty">
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path
                        d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1 7.8 7.7 7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8Z"
                    />
                </svg>
                <h2>Your wishlist is ready for a first favourite</h2>
                <p>Save fragrances as you browse and find them waiting here.</p>
                <Link href="/products" class="coy-button coy-button--primary">
                    Browse fragrances
                </Link>
            </section>

            <div v-else class="wl-grid">
                <div
                    v-for="item in items"
                    :key="item.wishlist_id"
                    class="wl-item"
                >
                    <ProductSpringCard
                        :product="cardProduct(item)"
                        wishlisted
                        @add-to-cart="addToCart(item.product.id, $event)"
                        @favourite="removeItem(item.wishlist_id)"
                    />
                    <p class="wl-saved">Saved {{ item.added_at }}</p>
                </div>
            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.wl {
    min-height: 100vh;
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
}

.wl-content {
    padding-block: clamp(2.5rem, 5vw, 4rem) clamp(4rem, 7vw, 6rem);
}

.wl-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: var(--coy-space-5);
    margin-bottom: var(--coy-space-6);
    padding-bottom: var(--coy-space-4);
    border-bottom: 1px solid var(--coy-color-border);
}

.wl-heading h2 {
    margin: var(--coy-space-1) 0 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: var(--coy-font-weight-medium);
    line-height: 1.08;
}

.wl-shop-link {
    display: inline-flex;
    align-items: center;
    gap: var(--coy-space-2);
    color: var(--coy-color-accent);
    font-size: var(--coy-text-sm);
    font-weight: var(--coy-font-weight-semibold);
    text-decoration: none;
}

.wl-shop-link:hover {
    text-decoration: underline;
    text-underline-offset: 0.2rem;
}

.wl-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(1.5rem, 3vw, 2.25rem);
}

.wl-item {
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.wl-item :deep(.product-card) {
    height: auto;
    flex: 1;
}

.wl-saved {
    margin: var(--coy-space-3) 0 0;
    padding-top: var(--coy-space-3);
    color: var(--coy-color-text);
    border-top: 1px solid var(--coy-color-border-soft);
    font-size: 0.875rem;
}

.wl-empty {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: clamp(3rem, 8vw, 6rem) var(--coy-space-5);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-lg);
    text-align: center;
}

.wl-empty svg {
    width: 3rem;
    fill: none;
    stroke: var(--coy-color-rose-gold);
    stroke-width: 1.4;
}

.wl-empty h2 {
    max-width: 22ch;
    margin: var(--coy-space-4) 0 var(--coy-space-2);
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: var(--coy-font-weight-medium);
    line-height: 1.1;
}

.wl-empty p {
    margin: 0 0 var(--coy-space-5);
}

@media (max-width: 860px) {
    .wl-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 560px) {
    .wl-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .wl-grid {
        grid-template-columns: 1fr;
    }

    .coy-page-header__action {
        display: none;
    }
}
</style>
