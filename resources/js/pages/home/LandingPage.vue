<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import JsonLdSchema from '@/components/JsonLdSchema.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import StarRating from '@/components/ui/coy/StarRating.vue';
import {
    useItemListSchema,
    useOrganizationSchema,
    useWebsiteSchema,
} from '@/composables/useProductSchema';
import { useSeoHead } from '@/composables/useSeoHead';
import axios from 'axios';
import { computed, ref } from 'vue';

interface Product {
    id: number;
    name: string;
    mpn: string;
    cost: number;
    image: string | null;
    slug: string | null;
    views: number;
}
interface Testimonial {
    id: number;
    rating: number;
    message: string;
    user: { name: string };
}
interface Season {
    id: 'spring' | 'summer' | 'autumn' | 'winter';
    banner: string;
    eyebrow: string;
    sub: string;
    motif: string;
    sectionLabel: string;
}

const props = defineProps<{
    featuredProducts?: Product[];
    testimonials?: Testimonial[];
    season?: Season;
}>();
const heroProduct = computed(() => props.featuredProducts?.[0]);
const reviews = computed(() => props.testimonials?.slice(0, 3) ?? []);
const productUrl = (product: Product) =>
    product.slug ? `/product/${product.slug}` : `/product/${product.id}`;
const ctaEmail = ref('');
const ctaSubmitting = ref(false);
const ctaSubmitted = ref(false);
const ctaError = ref('');

async function submitCtaEmail() {
    if (!ctaEmail.value.trim() || ctaSubmitting.value) return;
    ctaSubmitting.value = true;
    ctaError.value = '';
    try {
        await axios.post(route('waitlist.store'), {
            email: ctaEmail.value.trim(),
        });
        ctaSubmitted.value = true;
        ctaEmail.value = '';
    } catch (err: any) {
        ctaError.value =
            err?.response?.data?.errors?.email?.[0] ??
            err?.response?.data?.message ??
            'Something went wrong. Please try again.';
    } finally {
        ctaSubmitting.value = false;
    }
}

const seo = useSeoHead({
    description:
        'Luxury handmade reed diffusers crafted to order in the UK. Discover long-lasting home fragrance, thoughtful gifts and your perfect scent.',
    canonical: '/',
});
const siteSchemas = computed(() => {
    const schemas: object[] = [useOrganizationSchema(), useWebsiteSchema()];
    if (props.featuredProducts?.length)
        schemas.push(
            useItemListSchema(
                props.featuredProducts.map((product) => ({
                    name: product.name,
                    url: productUrl(product),
                    image: product.image,
                })),
            ),
        );
    return schemas;
});
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />
    <JsonLdSchema :schema="siteSchemas" />

    <main class="home coy-storefront">
        <aside v-if="season" class="announcement" aria-label="Seasonal news">
            <span>{{ season.banner }}</span>
            <a href="/products">Shop now</a>
        </aside>

        <section class="hero" aria-labelledby="home-title">
            <div class="hero-inner coy-container">
                <div class="hero-copy">
                    <p class="coy-eyebrow">Handmade home fragrance</p>
                    <h1 id="home-title" class="coy-heading">
                        Make your space feel more like <em>you</em>
                    </h1>
                    <p>
                        Long-lasting reed diffusers, hand-poured in the UK with
                        premium fragrance oils and made especially for your
                        home.
                    </p>
                    <div class="hero-actions">
                        <a
                            href="/products"
                            class="coy-button coy-button--primary"
                            >Shop all diffusers</a
                        >
                        <a
                            href="/scent-finder"
                            class="coy-button coy-button--secondary"
                            >Find my scent</a
                        >
                    </div>
                    <p class="hero-delivery">
                        Free UK delivery on orders of £50 or more
                    </p>
                </div>
                <a
                    v-if="heroProduct"
                    :href="productUrl(heroProduct)"
                    class="hero-product"
                >
                    <div class="hero-image">
                        <img
                            v-if="heroProduct.image"
                            :src="heroProduct.image"
                            :alt="heroProduct.name"
                        />
                        <span v-else>Hand-poured for you</span>
                    </div>
                    <div class="hero-product-info">
                        <span
                            ><strong>{{ heroProduct.name }}</strong
                            ><small>Featured fragrance</small></span
                        ><b>£{{ heroProduct.cost.toFixed(2) }}</b>
                    </div>
                </a>
                <div v-else class="hero-product hero-placeholder">
                    Hand-poured for you
                </div>
            </div>
        </section>

        <section class="benefits" aria-label="Why shop with Chapter of You">
            <div class="coy-container benefits-grid">
                <div>
                    <span aria-hidden="true">♡</span>
                    <p>
                        <strong>Made by hand</strong
                        ><small>Poured and finished with care</small>
                    </p>
                </div>
                <div>
                    <span aria-hidden="true">◇</span>
                    <p>
                        <strong>Premium quality</strong
                        ><small>Beautiful, lasting fragrance</small>
                    </p>
                </div>
                <div>
                    <span aria-hidden="true">♢</span>
                    <p>
                        <strong>Made to order</strong
                        ><small>Prepared especially for you</small>
                    </p>
                </div>
                <div>
                    <span aria-hidden="true">☆</span>
                    <p>
                        <strong>Loved by customers</strong
                        ><small>Real reviews, real homes</small>
                    </p>
                </div>
            </div>
        </section>

        <section
            v-if="featuredProducts?.length"
            class="bestsellers coy-section"
            aria-labelledby="bestsellers-title"
        >
            <div class="coy-container">
                <header class="section-header">
                    <div>
                        <p class="coy-eyebrow">
                            {{ season?.sectionLabel ?? 'Customer favourites' }}
                        </p>
                        <h2 id="bestsellers-title" class="coy-heading">
                            Meet the bestsellers
                        </h2>
                    </div>
                    <a href="/products" class="simple-link"
                        >Shop all fragrances
                        <span aria-hidden="true">→</span></a
                    >
                </header>
                <div class="product-grid">
                    <a
                        v-for="product in featuredProducts"
                        :key="product.id"
                        :href="productUrl(product)"
                        class="product-card"
                    >
                        <div class="product-image">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                loading="lazy"
                            />
                            <span v-else>Chapter of You</span>
                            <b v-if="product.views > 100">Popular</b>
                        </div>
                        <div class="product-copy">
                            <span
                                ><strong>{{ product.name }}</strong
                                ><small>Handmade reed diffuser</small></span
                            ><b>£{{ product.cost.toFixed(2) }}</b>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="finder coy-section" aria-labelledby="finder-title">
            <div class="coy-container finder-card">
                <div class="finder-copy">
                    <p class="coy-eyebrow">Your perfect match</p>
                    <h2 id="finder-title" class="coy-heading">
                        Not sure which scent to choose?
                    </h2>
                    <p>
                        Tell us how you want your room to feel and our
                        two-minute Scent Finder will point you towards
                        fragrances chosen for you.
                    </p>
                    <a
                        href="/scent-finder"
                        class="coy-button coy-button--primary"
                        >Take the Scent Finder</a
                    >
                </div>
                <div class="finder-options" aria-hidden="true">
                    <span>Fresh</span><span>Calming</span><span>Floral</span
                    ><span>Warm</span><span>Fruity</span><span>Clean</span>
                </div>
            </div>
        </section>

        <section class="story coy-section" aria-labelledby="story-title">
            <div class="coy-container story-grid">
                <div class="story-image">
                    <img
                        v-if="featuredProducts?.[1]?.image"
                        :src="featuredProducts[1].image!"
                        alt="A Chapter of You diffuser made with care"
                        loading="lazy"
                    />
                    <span v-else>Made with care</span>
                </div>
                <div class="story-copy">
                    <p class="coy-eyebrow">A personal touch</p>
                    <h2 id="story-title" class="coy-heading">
                        A small business with a big heart
                    </h2>
                    <p>
                        Chapter of You began with a simple idea: everyday
                        self-care should feel personal, thoughtful and easy to
                        enjoy.
                    </p>
                    <p>
                        Every diffuser is blended, poured and finished by hand.
                        When you order from Chapter of You, your fragrance is
                        made by a person who genuinely cares about what arrives
                        at your door.
                    </p>
                    <a href="/about" class="simple-link"
                        >Read my story <span aria-hidden="true">→</span></a
                    >
                </div>
            </div>
        </section>

        <section
            v-if="reviews.length"
            class="reviews coy-section"
            aria-labelledby="reviews-title"
        >
            <div class="coy-container">
                <header class="reviews-header">
                    <p class="coy-eyebrow">Customer love</p>
                    <h2 id="reviews-title" class="coy-heading">
                        Why customers come back
                    </h2>
                    <p>
                        Kind words from people who have made Chapter of You part
                        of their homes.
                    </p>
                </header>
                <div class="review-grid">
                    <blockquote v-for="review in reviews" :key="review.id">
                        <StarRating :rating="review.rating" :size="18" />
                        <p>“{{ review.message }}”</p>
                        <footer>{{ review.user.name }}</footer>
                    </blockquote>
                </div>
            </div>
        </section>

        <section class="gift coy-section" aria-labelledby="gift-title">
            <div class="coy-container gift-card">
                <div>
                    <p class="coy-eyebrow">A thoughtful gift</p>
                    <h2 id="gift-title" class="coy-heading">
                        Let them choose their perfect scent
                    </h2>
                    <p>
                        Chapter of You gift vouchers make giving something
                        personal beautifully simple.
                    </p>
                </div>
                <a
                    href="/gift-vouchers"
                    class="coy-button coy-button--secondary"
                    >Shop gift vouchers</a
                >
            </div>
        </section>

        <section
            class="newsletter coy-section"
            aria-labelledby="newsletter-title"
        >
            <div class="newsletter-inner">
                <p class="coy-eyebrow">Stay in the know</p>
                <h2 id="newsletter-title" class="coy-heading">
                    Enjoy 10% off your first order
                </h2>
                <p>
                    Join for new fragrances, thoughtful inspiration and
                    occasional treats.
                </p>
                <form v-if="!ctaSubmitted" @submit.prevent="submitCtaEmail">
                    <label for="home-email">Email address</label>
                    <div>
                        <input
                            id="home-email"
                            v-model="ctaEmail"
                            class="coy-field"
                            type="email"
                            autocomplete="email"
                            placeholder="you@example.com"
                            required
                        /><button
                            class="coy-button coy-button--primary"
                            type="submit"
                            :disabled="ctaSubmitting"
                        >
                            {{ ctaSubmitting ? 'Joining…' : 'Get my 10% off' }}
                        </button>
                    </div>
                    <p v-if="ctaError" class="error" role="alert">
                        {{ ctaError }}
                    </p>
                    <small>No spam. Unsubscribe at any time.</small>
                </form>
                <p v-else class="success" role="status">
                    You are in. Check your inbox for your discount code.
                </p>
            </div>
        </section>
    </main>
    <Footer />
</template>

<style scoped>
.home {
    overflow: hidden;
    padding-top: var(--coy-nav-height);
    font-size: 1.125rem;
}
.announcement {
    min-height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 0.65rem var(--coy-gutter);
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    font-size: 1rem;
    line-height: 1.4;
    text-align: center;
}
.announcement a {
    color: inherit;
    font-weight: 700;
    text-underline-offset: 0.25rem;
    white-space: nowrap;
}
.hero {
    padding: clamp(2rem, 5vw, 4.5rem) 0;
    background: linear-gradient(
        100deg,
        var(--coy-color-page) 0 58%,
        var(--coy-color-blush) 58%
    );
}
.hero-inner {
    display: grid;
    grid-template-columns: minmax(0, 0.92fr) minmax(26rem, 1.08fr);
    gap: clamp(2rem, 6vw, 6rem);
    align-items: center;
}
.hero-copy {
    padding-block: 2rem;
}
.hero-copy h1 {
    max-width: 12ch;
    margin: 1rem 0 0;
    font-size: clamp(3rem, 5.2vw, 5rem);
    font-weight: 500;
    letter-spacing: -0.035em;
    line-height: 1;
}
.hero-copy h1 em {
    color: var(--coy-color-accent);
    font-weight: 400;
}
.hero-copy > p:not(.coy-eyebrow, .hero-delivery) {
    max-width: 35rem;
    margin: 1.5rem 0 0;
    line-height: 1.6;
}
.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 2rem;
}
.hero-delivery {
    margin: 1rem 0 0;
    font-size: 1rem;
    font-weight: 600;
}
.hero-product {
    display: block;
    color: var(--coy-color-heading);
    background: var(--coy-color-surface);
    border-radius: var(--coy-radius-md);
    box-shadow: var(--coy-shadow-md);
    text-decoration: none;
    overflow: hidden;
}
.hero-image {
    aspect-ratio: 5/4;
    overflow: hidden;
    background: var(--coy-color-champagne);
}
.hero-image img,
.product-image img,
.story-image img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform 0.45s var(--coy-ease);
}
.hero-product:hover img,
.product-card:hover img {
    transform: scale(1.025);
}
.hero-image > span,
.product-image > span,
.story-image > span,
.hero-placeholder {
    height: 100%;
    display: grid;
    place-items: center;
    font-family: var(--coy-font-display);
    font-size: 1.5rem;
}
.hero-product-info,
.product-copy {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.1rem 1.25rem;
}
.hero-product-info > span,
.product-copy > span {
    display: flex;
    flex-direction: column;
}
.hero-product-info strong,
.product-copy strong {
    font-family: var(--coy-font-display);
    font-size: 1.35rem;
    font-weight: 600;
}
.hero-product-info small,
.product-copy small {
    margin-top: 0.15rem;
    color: var(--coy-color-text);
    font-size: 1rem;
}
.hero-product-info > b,
.product-copy > b {
    color: var(--coy-color-accent);
    font-size: 1.125rem;
    white-space: nowrap;
}
.benefits {
    padding: 1.5rem 0;
    background: var(--coy-color-surface);
    border-block: 1px solid var(--coy-color-border-soft);
}
.benefits-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}
.benefits-grid > div {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}
.benefits-grid > div > span {
    color: var(--coy-color-accent);
    font-size: 1.5rem;
}
.benefits-grid p {
    display: flex;
    flex-direction: column;
    margin: 0;
}
.benefits-grid strong {
    color: var(--coy-color-heading);
    font-size: 1rem;
}
.benefits-grid small {
    font-size: 1rem;
    line-height: 1.4;
}
.section-header {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 2rem;
    margin-bottom: 2.5rem;
}
.section-header h2,
.finder h2,
.story h2,
.reviews h2,
.gift h2,
.newsletter h2 {
    margin: 0.65rem 0 0;
    font-size: clamp(2.25rem, 3.7vw, 3.25rem);
    font-weight: 500;
    line-height: 1.1;
    letter-spacing: -0.02em;
}
.simple-link {
    color: var(--coy-color-heading);
    font-size: 1.0625rem;
    font-weight: 700;
    text-decoration-color: var(--coy-color-rose-gold);
    text-underline-offset: 0.3rem;
    white-space: nowrap;
}
.simple-link:hover {
    color: var(--coy-color-accent);
}
.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}
.product-card {
    color: var(--coy-color-heading);
    text-decoration: none;
}
.product-image {
    position: relative;
    aspect-ratio: 1/1.12;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border-radius: var(--coy-radius-md);
}
.product-image > b {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.4rem 0.7rem;
    color: white;
    background: var(--coy-color-accent);
    border-radius: var(--coy-radius-pill);
    font-size: 1rem;
}
.product-copy {
    align-items: start;
    padding: 1rem 0.1rem;
}
.product-copy strong {
    font-size: 1.25rem;
}
.product-copy > b {
    font-size: 1rem;
}
.finder {
    background: var(--coy-color-surface);
}
.finder-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(22rem, 0.8fr);
    gap: clamp(2rem, 7vw, 7rem);
    align-items: center;
    padding: clamp(2.5rem, 5vw, 5rem);
    background: var(--coy-color-blush);
    border-radius: var(--coy-radius-xl);
}
.finder-copy > p:not(.coy-eyebrow) {
    max-width: 38rem;
    margin: 1.25rem 0 1.75rem;
    line-height: 1.6;
}
.finder-options {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem;
}
.finder-options span {
    min-width: 7rem;
    padding: 1rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-pill);
    font-family: var(--coy-font-display);
    font-size: 1.25rem;
    text-align: center;
}
.story-grid {
    display: grid;
    grid-template-columns: minmax(22rem, 0.9fr) minmax(25rem, 1.1fr);
    gap: clamp(3rem, 8vw, 7rem);
    align-items: center;
}
.story-image {
    aspect-ratio: 5/4;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border-radius: var(--coy-radius-lg);
}
.story-copy > p:not(.coy-eyebrow) {
    max-width: 39rem;
    margin: 1.25rem 0 0;
    line-height: 1.65;
}
.story-copy .simple-link {
    display: inline-block;
    margin-top: 1.75rem;
}
.reviews {
    background: var(--coy-color-page);
}
.reviews-header {
    max-width: 42rem;
    margin: 0 auto 2.5rem;
    text-align: center;
}
.reviews-header > p:last-child {
    margin: 1rem 0 0;
}
.review-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
.review-grid blockquote {
    margin: 0;
    padding: 2rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-sm);
}
.review-grid blockquote > p {
    margin: 1.25rem 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 1.35rem;
    line-height: 1.5;
}
.review-grid footer {
    color: var(--coy-color-accent);
    font-size: 1rem;
    font-weight: 700;
}
.gift {
    background: var(--coy-color-surface);
}
.gift-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 3rem;
    padding: clamp(2rem, 5vw, 4rem);
    background: var(--coy-color-champagne);
    border-radius: var(--coy-radius-lg);
}
.gift-card > div {
    max-width: 45rem;
}
.gift-card p:not(.coy-eyebrow) {
    margin: 1rem 0 0;
}
.newsletter {
    text-align: center;
}
.newsletter-inner {
    width: min(100% - 2 * var(--coy-gutter), 43rem);
    margin: auto;
}
.newsletter-inner > p:not(.coy-eyebrow, .success) {
    margin: 1rem 0 2rem;
}
.newsletter form {
    text-align: left;
}
.newsletter label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--coy-color-heading);
    font-size: 1rem;
    font-weight: 700;
}
.newsletter form > div {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.65rem;
}
.newsletter small {
    display: block;
    margin-top: 0.7rem;
    font-size: 1rem;
    text-align: center;
}
.error {
    color: var(--coy-color-error);
    font-size: 1rem;
    font-weight: 700;
}
.success {
    padding: 1rem;
    color: var(--coy-color-success);
    background: var(--coy-color-success-soft);
    border: 1px solid;
    border-radius: var(--coy-radius-md);
}
@media (max-width: 950px) {
    .hero {
        background: var(--coy-color-page);
    }
    .hero-inner {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    .benefits-grid {
        grid-template-columns: 1fr 1fr;
    }
    .product-grid {
        grid-template-columns: 1fr 1fr;
    }
    .finder-card,
    .story-grid {
        grid-template-columns: 1fr 1fr;
        gap: 2.5rem;
    }
    .review-grid {
        grid-template-columns: 1fr 1fr;
    }
    .review-grid blockquote:last-child {
        grid-column: 1/-1;
    }
}
@media (max-width: 700px) {
    .home {
        font-size: 1.0625rem;
    }
    .announcement {
        align-items: flex-start;
        flex-direction: column;
        gap: 0.2rem;
        text-align: left;
    }
    .hero {
        padding: 2.5rem 0;
    }
    .hero-inner,
    .finder-card,
    .story-grid {
        grid-template-columns: 1fr;
    }
    .hero-copy {
        padding: 0;
    }
    .hero-copy h1 {
        font-size: clamp(2.75rem, 13vw, 4rem);
    }
    .hero-product {
        margin-top: 0.5rem;
    }
    .hero-image {
        aspect-ratio: 1/1;
    }
    .benefits-grid {
        grid-template-columns: 1fr;
    }
    .benefits-grid > div {
        padding: 0.25rem 0;
    }
    .section-header {
        align-items: start;
        flex-direction: column;
        gap: 1rem;
    }
    .section-header h2,
    .finder h2,
    .story h2,
    .reviews h2,
    .gift h2,
    .newsletter h2 {
        font-size: clamp(2rem, 9vw, 2.75rem);
    }
    .product-grid {
        gap: 0.75rem;
    }
    .product-copy {
        align-items: start;
        flex-direction: column;
        gap: 0.35rem;
    }
    .product-copy small {
        display: none;
    }
    .finder-card {
        padding: 2rem 1.25rem;
    }
    .finder-options {
        justify-content: flex-start;
    }
    .finder-options span {
        min-width: calc(50% - 0.375rem);
    }
    .story-grid {
        display: flex;
        flex-direction: column;
    }
    .story-image {
        width: 100%;
    }
    .review-grid {
        grid-template-columns: 1fr;
    }
    .review-grid blockquote:last-child {
        grid-column: auto;
    }
    .gift-card {
        align-items: flex-start;
        flex-direction: column;
        gap: 1.5rem;
    }
    .newsletter form > div {
        grid-template-columns: 1fr;
    }
    .newsletter form button {
        width: 100%;
    }
}
@media (max-width: 380px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
    .hero-actions .coy-button {
        width: 100%;
    }
}
</style>
