<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import JsonLdSchema from '@/components/JsonLdSchema.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    category: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        image_url: string | null;
        meta_title: string;
        meta_description: string;
    };
    products: {
        data: Array<{
            id: number;
            name: string;
            cost: number;
            stock_qty: number;
            slug: string | number;
            image: string | null;
        }>;
        links: any[];
        meta: any;
    };
}>();

const seo = useSeoHead({
    title: props.category.meta_title,
    description: props.category.meta_description,
    canonical: `/category/${props.category.slug}`,
    ogImage: props.category.image_url || undefined,
});

const schemas = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'CollectionPage',
        'name': props.category.name,
        'description': props.category.meta_description,
        'url': `https://www.chapterofyou.co.uk/category/${props.category.slug}`,
        ...(props.category.image_url ? { 'image': props.category.image_url } : {}),
    },
    {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        'itemListElement': [
            { '@type': 'ListItem', 'position': 1, 'name': 'Home', 'item': 'https://www.chapterofyou.co.uk' },
            { '@type': 'ListItem', 'position': 2, 'name': 'Products', 'item': 'https://www.chapterofyou.co.uk/products' },
            { '@type': 'ListItem', 'position': 3, 'name': props.category.name, 'item': `https://www.chapterofyou.co.uk/category/${props.category.slug}` },
        ],
    },
]);

const fmt = (v: number) => `£${Number(v).toFixed(2)}`;
const imgSrc = (img: string | null) => !img ? null : img.startsWith('http') ? img : `${img}`;
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />
    <JsonLdSchema :schema="schemas" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="cl">

        <!-- Hero banner -->
        <header class="cl-hero" :style="category.image_url ? `background-image:url('${category.image_url}')` : ''">
            <div class="cl-hero-overlay" aria-hidden="true"></div>
            <div class="cl-hero-content">
                <nav class="cl-breadcrumb" aria-label="Breadcrumb">
                    <Link href="/" class="cl-bc-link">Home</Link>
                    <span aria-hidden="true">›</span>
                    <Link href="/products" class="cl-bc-link">Products</Link>
                    <span aria-hidden="true">›</span>
                    <span>{{ category.name }}</span>
                </nav>
                <h1 class="cl-hero-title">{{ category.name }}</h1>
                <p v-if="category.description" class="cl-hero-desc">{{ category.description }}</p>
                <p class="cl-hero-count">
                    {{ products.meta?.total ?? products.data.length }}
                    {{ (products.meta?.total ?? products.data.length) === 1 ? 'product' : 'products' }}
                </p>
            </div>
        </header>

        <div class="cl-wrap">

            <!-- Product grid -->
            <div v-if="products.data.length" class="cl-grid">
                <article v-for="product in products.data" :key="product.id" class="cl-card">
                    <Link :href="`/product/${product.slug}`" class="cl-card-img-wrap">
                    <img v-if="imgSrc(product.image)" :src="imgSrc(product.image)!" :alt="product.name"
                        class="cl-card-img" loading="lazy" />
                    <div v-else class="cl-card-img-ph" aria-hidden="true">✿</div>
                    <div v-if="product.stock_qty === 0" class="cl-oos">Out of stock</div>
                    </Link>
                    <div class="cl-card-body">
                        <h2 class="cl-card-name">
                            <Link :href="`/product/${product.slug}`">{{ product.name }}</Link>
                        </h2>
                        <p class="cl-card-price">{{ fmt(product.cost) }}</p>
                        <Link :href="`/product/${product.slug}`" class="cl-card-btn"
                            :class="{ 'cl-card-btn--disabled': product.stock_qty === 0 }">
                        {{ product.stock_qty > 0 ? 'View Product' : 'Out of Stock' }}
                        </Link>
                    </div>
                </article>
            </div>

            <div v-else class="cl-empty">
                <p class="cl-empty-petal" aria-hidden="true">✿</p>
                <p class="cl-empty-text">No products in this category yet.</p>
                <Link href="/products" class="cl-back-link">Browse all products →</Link>
            </div>

            <!-- Pagination -->
            <div v-if="products.links?.length > 3" class="cl-pagination">
                <template v-for="link in products.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="cl-page-btn"
                        :class="{ 'cl-page-btn--active': link.active }" v-html="link.label" />
                    <span v-else class="cl-page-btn cl-page-btn--disabled" v-html="link.label" />
                </template>
            </div>

            <div class="cl-footer-nav">
                <Link href="/products" class="cl-back-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7M19 12H5" />
                </svg>
                All Products
                </Link>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.cl {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

/* Hero */
.cl-hero {
    position: relative;
    min-height: 280px;
    display: flex;
    align-items: flex-end;
    background: #2d1a1a;
    background-size: cover;
    background-position: center;
}

@media (max-width: 640px) {
    .cl-hero {
        min-height: 200px;
    }
}

.cl-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(45, 26, 26, 0.3) 0%, rgba(45, 26, 26, 0.78) 100%);
}

.cl-hero-content {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1060px;
    margin: 0 auto;
    padding: 2rem 1.25rem 2.5rem;
}

.cl-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: rgba(255, 250, 250, 0.55);
    margin-bottom: 0.85rem;
    flex-wrap: wrap;
}

.cl-bc-link {
    color: rgba(255, 250, 250, 0.65);
    text-decoration: none;
    transition: color 0.15s;
}

.cl-bc-link:hover {
    color: #fffafa;
}

.cl-hero-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 6vw, 3rem);
    font-weight: 400;
    font-style: italic;
    color: #fffafa;
    line-height: 1.1;
    margin-bottom: 0.75rem;
}

.cl-hero-desc {
    font-size: 0.95rem;
    color: rgba(255, 250, 250, 0.78);
    line-height: 1.65;
    max-width: 560px;
    margin-bottom: 0.75rem;
}

.cl-hero-count {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 250, 250, 0.45);
}

/* Wrap */
.cl-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 3rem 1.25rem 5rem;
}

/* Grid */
.cl-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

@media (max-width: 900px) {
    .cl-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 640px) {
    .cl-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 380px) {
    .cl-grid {
        grid-template-columns: 1fr;
    }
}

/* Cards */
.cl-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s, transform 0.2s;
}

.cl-card:hover {
    box-shadow: 0 6px 22px rgba(229, 201, 199, 0.45);
    transform: translateY(-2px);
}

.cl-card-img-wrap {
    display: block;
    aspect-ratio: 1/1;
    overflow: hidden;
    background: #fdf4f3;
    position: relative;
}

.cl-card-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
    transition: transform 0.3s;
}

.cl-card:hover .cl-card-img {
    transform: scale(1.04);
}

.cl-card-img-ph {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #e5c9c7;
}

.cl-oos {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #2d1a1a;
    color: #fffafa;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
}

.cl-card-body {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.cl-card-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1rem;
    font-weight: 500;
    color: #2d1a1a;
    line-height: 1.3;
}

.cl-card-name a {
    text-decoration: none;
    color: inherit;
    transition: color 0.15s;
}

.cl-card-name a:hover {
    color: #8c4a50;
}

.cl-card-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    font-weight: 500;
    color: #8c4a50;
}

.cl-card-btn {
    display: block;
    text-align: center;
    padding: 0.55rem 0.75rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    margin-top: auto;
    transition: transform 0.15s, box-shadow 0.15s;
}

.cl-card-btn:hover:not(.cl-card-btn--disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(168, 80, 88, 0.25);
}

.cl-card-btn--disabled {
    background: #f0dcd8;
    border-color: #e5c9c7;
    color: #9a7070;
    cursor: not-allowed;
}

/* Empty */
.cl-empty {
    text-align: center;
    padding: 5rem 1rem;
}

.cl-empty-petal {
    font-size: 2rem;
    color: #e5c9c7;
    margin-bottom: 0.75rem;
}

.cl-empty-text {
    font-size: 0.95rem;
    color: #6b4f4f;
    font-style: italic;
    margin-bottom: 1rem;
}

/* Pagination */
.cl-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 3rem;
    flex-wrap: wrap;
}

.cl-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 0.65rem;
    border-radius: 8px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    font-size: 0.82rem;
    color: #6b4f4f;
    text-decoration: none;
    transition: all 0.15s;
}

.cl-page-btn:hover {
    border-color: #8c4a50;
    color: #8c4a50;
}

.cl-page-btn--active {
    border-color: #8c4a50;
    background: #8c4a50;
    color: #fff;
}

.cl-page-btn--disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

/* Footer */
.cl-footer-nav {
    margin-top: 2.5rem;
    text-align: center;
}

.cl-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
}

.cl-back-link:hover {
    text-decoration: underline;
}
</style>
