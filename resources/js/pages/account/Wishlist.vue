<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import NavBar from '@/components/NavBar.vue';

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

const formatCurrency = (v: number | string) => {
    const n = Number(v);
    return isNaN(n) ? '£0.00' : `£${n.toFixed(2)}`;
};

const productLink = (item: WishlistItem) =>
    item.product.seo?.slug
        ? `/product/${item.product.seo.slug}`
        : `/product/${item.product.id}`;

const removeItem = (wishlistId: number) => {
    router.delete(route('wishlist.remove', { id: wishlistId }), {
        preserveScroll: true,
    });
};

const addToCart = (productId: number) => {
    router.post('/cart/add', { product_id: productId, quantity: 1 }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <NavBar />

    <Head title="My Wishlist" />

    <section class="py-20">
        <div class="mx-auto max-w-screen-lg p-4 md:p-8 lg:p-12">

            <div class="mb-8 border-b-2 border-copy pb-4">
                <h1 class="text-4xl font-black text-copy">My Wishlist</h1>
                <p class="text-copy-light mt-1">{{ items.length }} saved item{{ items.length !== 1 ? 's' : '' }}</p>
            </div>

            <!-- Empty state -->
            <div v-if="items.length === 0" class="text-center py-20 border-2 border-dashed border-copy rounded-xl">
                <p class="text-xl font-semibold text-copy-light mb-4">Your wishlist is empty.</p>
                <a href="/products" class="inline-block rounded-lg border-2 border-copy px-6 py-3 font-bold transition"
                    style="background-color: var(--primary); color: var(--primary-content);">
                    Browse Products
                </a>
            </div>

            <!-- Wishlist grid -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="item in items" :key="item.wishlist_id"
                    class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div
                        class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden flex flex-col">

                        <!-- Image -->
                        <a :href="productLink(item)" class="block h-48 overflow-hidden">
                            <img :src="item.product.images?.[0]?.image ?? 'https://via.placeholder.com/300?text=No+Image'"
                                :alt="item.product.name"
                                class="w-full h-full object-cover transition duration-300 hover:scale-105" />
                        </a>

                        <!-- Info -->
                        <div class="p-4 flex flex-col flex-1">
                            <p class="text-xs text-copy-light font-mono mb-1">{{ item.product.mpn }}</p>
                            <a :href="productLink(item)"
                                class="text-base font-bold text-copy hover:text-primary transition leading-tight mb-2">
                                {{ item.product.name }}
                            </a>

                            <div class="flex items-center justify-between mt-auto pt-3 border-t border-copy-light">
                                <span class="text-2xl font-black text-copy">{{ formatCurrency(item.product.cost)
                                    }}</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" :class="item.product.stock_qty > 0
                                    ? 'bg-success text-success-content'
                                    : 'bg-error text-error-content'">
                                    {{ item.product.stock_qty > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </div>

                            <p class="text-xs text-copy-light mt-1">Saved {{ item.added_at }}</p>

                            <!-- Actions -->
                            <div class="flex gap-2 mt-4">
                                <button @click="addToCart(item.product.id)" :disabled="item.product.stock_qty <= 0"
                                    class="flex-1 rounded-lg border-2 border-copy py-2 text-sm font-bold transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    style="background-color: var(--primary); color: var(--primary-content);">
                                    Add to Cart
                                </button>
                                <button @click="removeItem(item.wishlist_id)"
                                    class="rounded-lg border-2 border-copy px-3 py-2 text-sm font-medium text-error hover:bg-red-50 transition"
                                    title="Remove from wishlist">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>
</template>
