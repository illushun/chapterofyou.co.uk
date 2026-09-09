<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import ProductSpringCard from '@/components/ui/coy/ProductSpringCard.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { moodTagLabel, roomLabel, scentFamilyLabel } from '@/lib/scentTaxonomy';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface ProductCardData {
    id: number;
    name: string;
    mpn: string;
    cost: number;
    stock_qty: number;
    images?: { image: string }[];
    total_unique_views?: number;
    seo?: { slug: string };
}

const props = defineProps<{
    products: ProductCardData[];
    answers: {
        scent_families: string[];
        mood_tags: string[];
        room_tags: string[];
    };
    wishlistedIds: number[];
}>();

const seo = useSeoHead({
    title: 'Your Scent Matches',
    description: 'Your personalised Chapter of You fragrance recommendations.',
    canonical: '/scent-finder',
});

const wishlistedIds = ref<number[]>(props.wishlistedIds ?? []);
const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);

const handleAddToCart = (product: ProductCardData, quantity = 1) => {
    router.post(
        '/cart/add',
        { product_id: product.id, quantity },
        {
            preserveScroll: true,
            onSuccess: () =>
                successToastRef.value?.show(
                    `${product.name} added to cart!`,
                    'cart',
                ),
        },
    );
};

const handleFavourite = async (product: ProductCardData) => {
    const idx = wishlistedIds.value.indexOf(product.id);
    if (idx === -1) {
        wishlistedIds.value.push(product.id);
    } else {
        wishlistedIds.value.splice(idx, 1);
    }
    try {
        const { data } = await axios.post(route('wishlist.toggle'), {
            product_id: product.id,
        });
        successToastRef.value?.show(
            data.message,
            data.wishlisted ? 'favourite' : 'trash',
        );
    } catch (err: any) {
        if (idx === -1) {
            wishlistedIds.value.splice(
                wishlistedIds.value.indexOf(product.id),
                1,
            );
        } else {
            wishlistedIds.value.splice(idx, 0, product.id);
        }
        if (err.response?.status === 401) window.location.href = route('login');
    }
};
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <main class="sfr coy-storefront">
        <div class="sfr-wrap">
            <header class="sfr-header">
                <p class="coy-eyebrow">Scent finder</p>
                <h1 class="sfr-title">Your scent matches</h1>
                <p class="sfr-summary">
                    Based on your choices:
                    <strong>{{
                        answers.scent_families.map(scentFamilyLabel).join(' & ')
                    }}</strong>
                    scents for
                    <strong>{{
                        answers.mood_tags.map(moodTagLabel).join(', ')
                    }}</strong>
                    moments in your
                    <strong>{{
                        answers.room_tags.map(roomLabel).join(' & ')
                    }}</strong
                    >.
                </p>
            </header>

            <div v-if="products.length === 0" class="sfr-empty">
                <h2>No close matches yet</h2>
                <p>Try the finder again with a different combination.</p>
                <Link
                    :href="route('scent-finder.index')"
                    class="sfr-btn sfr-btn--primary"
                    >Retake the quiz</Link
                >
            </div>

            <ul v-else class="sfr-grid">
                <li v-for="product in products" :key="product.id">
                    <ProductSpringCard
                        :product="product"
                        :wishlisted="wishlistedIds.includes(product.id)"
                        @add-to-cart="handleAddToCart(product, $event)"
                        @favourite="handleFavourite(product)"
                    />
                </li>
            </ul>

            <div v-if="products.length" class="sfr-retake">
                <Link
                    :href="route('scent-finder.index')"
                    class="sfr-btn sfr-btn--ghost"
                    >Retake the quiz</Link
                >
            </div>
        </div>
    </main>

    <SuccessToast ref="successToastRef" />
    <Footer />
</template>

<style scoped>
.sfr {
    min-height: 70vh;
    background: #fdf4f3;
    padding: 4rem 1.5rem 5rem;
}

.sfr-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

.sfr-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.sfr-eyebrow {
    font-family: 'Nunito', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #a85058;
    margin-bottom: 0.5rem;
}

.sfr-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.5rem;
    font-weight: 400;
    color: #2d1a1a;
}

.sfr-title em {
    font-style: italic;
    color: #a85058;
}

.sfr-summary {
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    color: #6b4f4f;
    margin-top: 0.75rem;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.sfr-summary strong {
    color: #a85058;
}

.sfr-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
}

.sfr-petal {
    color: #c9a4a4;
    font-size: 2rem;
}

.sfr-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    list-style: none;
    padding: 0;
}

.sfr-retake {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
}

.sfr-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.75rem;
    border-radius: 999px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition:
        transform 0.2s,
        box-shadow 0.2s,
        background 0.2s;
    border: 1px solid transparent;
}

.sfr-btn--primary {
    background: linear-gradient(135deg, #c47078, #a85058);
    border-color: #a85058;
    color: #fff;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
}

.sfr-btn--ghost {
    background: rgba(255, 250, 250, 0.7);
    border-color: #e5c9c7;
    color: #6b4f4f;
}

.sfr-btn--ghost:hover {
    background: #faeaea;
    border-color: #c9a4a4;
}

.sfr {
    min-height: 70vh;
    padding: var(--coy-nav-height) 0 0;
    background: var(--coy-color-page);
}

.sfr-wrap {
    width: min(calc(100% - (var(--coy-gutter) * 2)), var(--coy-container-lg));
    max-width: none;
    margin-inline: auto;
    padding: clamp(2.5rem, 6vw, 5rem) 0 clamp(4rem, 8vw, 7rem);
}

.sfr-header {
    max-width: 48rem;
    margin-bottom: clamp(2rem, 5vw, 3.5rem);
    text-align: left;
}

.sfr-title {
    margin: 0.45rem 0 0.8rem;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(2.75rem, 7vw, 4.75rem);
    font-weight: 500;
    line-height: 0.98;
    letter-spacing: -0.035em;
}

.sfr-summary {
    max-width: 44rem;
    margin: 0;
    color: var(--coy-color-text);
    font-family: var(--coy-font-body);
    font-size: 1.05rem;
    line-height: 1.7;
}

.sfr-summary strong {
    color: var(--coy-color-heading);
    font-weight: 600;
}

.sfr-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(1.25rem, 3vw, 2rem);
}

.sfr-empty {
    max-width: 40rem;
    padding: clamp(2rem, 5vw, 3rem);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    background: var(--coy-color-surface);
    color: var(--coy-color-text);
    font-family: var(--coy-font-body);
    text-align: left;
}

.sfr-empty h2 {
    margin-bottom: 0.5rem;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 2rem;
    font-weight: 500;
}

.sfr-empty p {
    margin-bottom: 1.5rem;
}

.sfr-retake {
    justify-content: flex-start;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid var(--coy-color-border);
}

.sfr-btn {
    min-height: var(--coy-control-height);
    padding: 0.75rem 1.4rem;
    border-radius: var(--coy-radius-sm);
    font-family: var(--coy-font-body);
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    transition: opacity var(--coy-duration-fast) var(--coy-ease);
}

.sfr-btn--primary {
    border-color: var(--coy-color-heading);
    background: var(--coy-color-heading);
    box-shadow: none;
    color: var(--coy-color-on-accent);
}

.sfr-btn--ghost {
    border-color: var(--coy-color-border);
    background: var(--coy-color-surface);
    color: var(--coy-color-heading);
}

.sfr-btn--ghost:hover {
    border-color: var(--coy-color-heading);
    background: var(--coy-color-surface-soft);
}

@media (max-width: 900px) {
    .sfr-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .sfr-wrap {
        padding-top: 2rem;
    }

    .sfr-grid {
        grid-template-columns: 1fr;
    }
}
</style>
