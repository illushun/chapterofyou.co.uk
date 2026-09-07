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
const primaryProduct = computed(() => props.featuredProducts?.[0]);
const secondaryProduct = computed(() => props.featuredProducts?.[1]);
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
        'Thoughtful handmade home fragrance, created in the UK to make everyday moments feel special. Discover reed diffusers, gifts and your perfect scent.',
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
            <span>{{ season.banner }}</span
            ><a href="/products">Explore the collection</a>
        </aside>

        <section class="hero" aria-labelledby="home-heading">
            <div class="hero-copy">
                <p class="coy-eyebrow">A moment that is yours</p>
                <h1 id="home-heading" class="coy-heading">
                    A little time,<br /><em>just for you.</em>
                </h1>
                <p class="hero-intro">
                    Thoughtful home fragrance and everyday rituals, created to
                    make ordinary moments feel special.
                </p>
                <div class="hero-actions">
                    <a href="/products" class="coy-button coy-button--primary"
                        >Shop home fragrance
                        <span aria-hidden="true">↗</span></a
                    >
                    <a href="/about" class="text-link">Discover our story</a>
                </div>
                <dl class="hero-notes">
                    <div>
                        <dt>Made</dt>
                        <dd>By hand in the UK</dd>
                    </div>
                    <div>
                        <dt>Chosen</dt>
                        <dd>For your kind of calm</dd>
                    </div>
                </dl>
            </div>
            <div class="hero-gallery" aria-label="Featured home fragrance">
                <a
                    v-if="primaryProduct"
                    :href="productUrl(primaryProduct)"
                    class="hero-main"
                >
                    <img
                        v-if="primaryProduct.image"
                        :src="primaryProduct.image"
                        :alt="primaryProduct.name"
                    />
                    <span v-else class="placeholder">Made for your space</span>
                    <span class="hero-caption"
                        ><span>{{ primaryProduct.name }}</span
                        ><strong
                            >£{{ primaryProduct.cost.toFixed(2) }}</strong
                        ></span
                    >
                </a>
                <div v-else class="hero-main">
                    <span class="placeholder">Made for your space</span>
                </div>
                <a
                    v-if="secondaryProduct"
                    :href="productUrl(secondaryProduct)"
                    class="hero-detail"
                >
                    <img
                        v-if="secondaryProduct.image"
                        :src="secondaryProduct.image"
                        :alt="secondaryProduct.name"
                    />
                    <span v-else class="placeholder">Made with care</span>
                </a>
                <p class="handnote" aria-hidden="true">
                    Small rituals,<br />beautifully made
                </p>
                <span class="chapter" aria-hidden="true">Chapter 01</span>
            </div>
        </section>

        <section class="trust" aria-label="Our promises">
            <p>Hand-poured with care</p>
            <i></i>
            <p>Premium fragrance oils</p>
            <i></i>
            <p>Made to order</p>
            <i></i>
            <p>Free UK delivery over £50</p>
        </section>

        <section class="discovery coy-section" aria-labelledby="discover-title">
            <div class="coy-container">
                <header class="split-heading">
                    <div>
                        <p class="coy-eyebrow">Begin where you are</p>
                        <h2 id="discover-title" class="coy-heading">
                            Find what feels like <em>you</em>
                        </h2>
                    </div>
                    <p>
                        Whether you know your favourite notes or only how you
                        want a room to feel, there is an easy place to begin.
                    </p>
                </header>
                <div class="discovery-grid">
                    <a
                        href="/scent-finder"
                        class="discovery-card discovery-card--main"
                        ><b>01</b>
                        <div>
                            <small>Guided discovery</small>
                            <h3>Find your scent</h3>
                            <span>Take the two-minute quiz ↗</span>
                        </div></a
                    >
                    <a
                        href="/products"
                        class="discovery-card discovery-card--rose"
                        ><b>02</b>
                        <div>
                            <small>Customer favourites</small>
                            <h3>Shop the most loved</h3>
                            <span>Discover bestsellers ↗</span>
                        </div></a
                    >
                    <a
                        href="/gift-vouchers"
                        class="discovery-card discovery-card--light"
                        ><b>03</b>
                        <div>
                            <small>Something thoughtful</small>
                            <h3>Give them the choice</h3>
                            <span>Explore gift vouchers ↗</span>
                        </div></a
                    >
                </div>
            </div>
        </section>

        <section
            v-if="featuredProducts?.length"
            class="collection coy-section"
            aria-labelledby="collection-title"
        >
            <div class="coy-container">
                <header class="collection-heading">
                    <div>
                        <p class="coy-eyebrow">
                            {{ season?.sectionLabel ?? 'Most loved' }}
                        </p>
                        <h2 id="collection-title" class="coy-heading">
                            Scents worth <em>staying home for</em>
                        </h2>
                    </div>
                    <a href="/products" class="text-link"
                        >View all fragrances</a
                    >
                </header>
                <div class="products">
                    <a
                        v-for="(product, index) in featuredProducts"
                        :key="product.id"
                        :href="productUrl(product)"
                        class="product"
                        :class="`product--${index + 1}`"
                    >
                        <div class="product-image">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                loading="lazy"
                            />
                            <span v-else class="placeholder"
                                >Chapter of You</span
                            >
                            <span v-if="index === 0" class="product-label"
                                >Signature scent</span
                            >
                        </div>
                        <div class="product-info">
                            <div>
                                <h3>{{ product.name }}</h3>
                                <p>Handmade reed diffuser</p>
                            </div>
                            <strong>£{{ product.cost.toFixed(2) }}</strong>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="manifesto" aria-labelledby="manifesto-title">
            <span class="manifesto-chapter" aria-hidden="true">Chapter 02</span>
            <div>
                <p class="coy-eyebrow">A gentler pace</p>
                <h2 id="manifesto-title" class="coy-heading">
                    Self-care does not need to be elaborate.
                    <em>Sometimes it begins with the room around you.</em>
                </h2>
                <p>
                    A familiar fragrance. A slower morning. Five quiet minutes
                    at the end of the day. Chapter of You is made for those
                    small, meaningful rituals.
                </p>
            </div>
        </section>

        <section class="story coy-section" aria-labelledby="story-title">
            <div class="coy-container story-grid">
                <div class="story-visual">
                    <div>
                        <img
                            v-if="secondaryProduct?.image"
                            :src="secondaryProduct.image"
                            alt="A Chapter of You fragrance made with care"
                            loading="lazy"
                        /><span v-else class="placeholder"
                            >Made personally</span
                        >
                    </div>
                    <p>Created in small batches<br />and finished by hand</p>
                </div>
                <div class="story-copy">
                    <p class="coy-eyebrow">Behind the brand</p>
                    <h2 id="story-title" class="coy-heading">
                        Made personally.<br /><em>Chosen personally.</em>
                    </h2>
                    <p>
                        Chapter of You is an independent business built around
                        one simple belief: the smallest moments of care can
                        change how a day feels.
                    </p>
                    <p>
                        Every diffuser is poured, blended and finished by hand,
                        with the kind of attention that mass production cannot
                        recreate.
                    </p>
                    <a href="/about" class="text-link">Meet the maker</a>
                    <dl>
                        <div>
                            <dt>100%</dt>
                            <dd>Hand-poured</dd>
                        </div>
                        <div>
                            <dt>Made</dt>
                            <dd>To order</dd>
                        </div>
                        <div>
                            <dt>Yours</dt>
                            <dd>To enjoy</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <section
            v-if="reviews.length"
            class="reviews coy-section"
            aria-labelledby="reviews-title"
        >
            <div class="coy-container">
                <header>
                    <p class="coy-eyebrow">Kind words</p>
                    <h2 id="reviews-title" class="coy-heading">
                        Notes from your <em>chapters</em>
                    </h2>
                </header>
                <div class="review-grid">
                    <blockquote class="review-main">
                        <StarRating :rating="reviews[0].rating" :size="18" />
                        <p>“{{ reviews[0].message }}”</p>
                        <footer>{{ reviews[0].user.name }}</footer>
                    </blockquote>
                    <div v-if="reviews.length > 1" class="review-side">
                        <blockquote
                            v-for="review in reviews.slice(1)"
                            :key="review.id"
                        >
                            <StarRating :rating="review.rating" :size="15" />
                            <p>“{{ review.message }}”</p>
                            <footer>{{ review.user.name }}</footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="newsletter coy-section"
            aria-labelledby="newsletter-title"
        >
            <div>
                <p class="coy-eyebrow">A quieter kind of inbox</p>
                <h2 id="newsletter-title" class="coy-heading">
                    New scents, thoughtful rituals<br />and an occasional treat.
                </h2>
                <p>
                    Join the Chapter of You community and receive 10% off your
                    first order.
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
                            {{
                                ctaSubmitting
                                    ? 'Joining…'
                                    : 'Join the community'
                            }}
                        </button>
                    </div>
                    <p v-if="ctaError" class="error" role="alert">
                        {{ ctaError }}
                    </p>
                    <small
                        >No noise. Just the lovely things. Unsubscribe any
                        time.</small
                    >
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
}
.announcement {
    min-height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 0.55rem var(--coy-gutter);
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    font-size: var(--coy-text-xs);
    line-height: 1.35;
    text-align: center;
}
.announcement a {
    color: inherit;
    font-weight: 600;
    text-underline-offset: 0.25rem;
    white-space: nowrap;
}
.hero {
    min-height: min(53rem, calc(100svh - var(--coy-nav-height)));
    display: grid;
    grid-template-columns: minmax(0, 0.85fr) minmax(28rem, 1.15fr);
}
.hero-copy {
    max-width: 41rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: clamp(4rem, 7vw, 8rem) var(--coy-gutter) clamp(3rem, 6vw, 6rem)
        max(var(--coy-gutter), calc((100vw - var(--coy-container-xl)) / 2));
}
.hero-copy h1 {
    max-width: 10ch;
    margin: 1.5rem 0 0;
    font-size: clamp(3.7rem, 6.6vw, 7.8rem);
    font-weight: 400;
    letter-spacing: -0.045em;
    line-height: 0.92;
}
.hero em,
.split-heading em,
.collection-heading em,
.story-copy em,
.reviews em {
    color: var(--coy-color-accent);
    font-weight: 400;
}
.hero-intro {
    max-width: 31rem;
    margin: 2rem 0 0;
    font-size: var(--coy-text-lead);
    line-height: 1.55;
}
.hero-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-top: 2rem;
}
.text-link {
    width: fit-content;
    color: var(--coy-color-heading);
    font-weight: 600;
    text-decoration-color: var(--coy-color-rose-gold);
    text-underline-offset: 0.35rem;
}
.text-link:hover {
    color: var(--coy-color-accent);
}
.hero-notes {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin: clamp(3rem, 7vw, 6rem) 0 0;
    padding-top: 1rem;
    border-top: 1px solid var(--coy-color-border);
}
.hero-notes div,
.story-copy dl div {
    display: flex;
    flex-direction: column;
}
.hero-notes dt,
.story-copy dt {
    color: var(--coy-color-accent);
    font-family: var(--coy-font-display);
    font-size: 1.35rem;
}
.hero-notes dd,
.story-copy dd {
    margin: 0;
    font-size: var(--coy-text-xs);
}
.hero-gallery {
    position: relative;
    min-height: 43rem;
    margin: clamp(1.5rem, 3vw, 3rem) clamp(1.5rem, 3vw, 3rem)
        clamp(1.5rem, 3vw, 3rem) 0;
    background: var(--coy-color-blush);
    border-radius: 0 0 0 clamp(3rem, 8vw, 9rem);
}
.hero-main,
.hero-detail {
    position: absolute;
    display: block;
    overflow: hidden;
    color: var(--coy-color-heading);
    background: var(--coy-color-champagne);
    text-decoration: none;
}
.hero-main {
    inset: clamp(2rem, 5vw, 5rem) clamp(2rem, 5vw, 5rem) clamp(5rem, 8vw, 8rem)
        clamp(2rem, 4vw, 4rem);
    border-radius: var(--coy-radius-sm) var(--coy-radius-sm)
        clamp(2rem, 4vw, 4rem);
}
.hero-detail {
    width: clamp(8.5rem, 16vw, 14rem);
    aspect-ratio: 4/5;
    right: clamp(1rem, 2vw, 2rem);
    bottom: clamp(1.5rem, 3vw, 3rem);
    border: 0.5rem solid var(--coy-color-page);
    box-shadow: var(--coy-shadow-md);
}
.hero-main img,
.hero-detail img,
.product-image img,
.story-visual img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform 700ms var(--coy-ease);
}
.hero-main:hover img,
.product:hover img {
    transform: scale(1.025);
}
.hero-caption {
    position: absolute;
    inset: auto 0 0;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem;
    color: white;
    background: linear-gradient(transparent, rgb(52 42 40/76%));
}
.handnote {
    position: absolute;
    left: -1.5rem;
    bottom: 3rem;
    margin: 0;
    padding: 0.75rem 1rem;
    color: var(--coy-color-accent);
    background: var(--coy-color-page);
    font-family: var(--coy-font-display);
    font-size: 1.25rem;
    font-style: italic;
    line-height: 1.15;
    transform: rotate(-4deg);
}
.chapter,
.manifesto-chapter {
    position: absolute;
    color: var(--coy-color-accent);
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    writing-mode: vertical-rl;
}
.chapter {
    top: 2rem;
    right: 0.85rem;
}
.placeholder {
    width: 100%;
    height: 100%;
    display: grid;
    place-items: center;
    padding: 2rem;
    font-family: var(--coy-font-display);
    font-size: 1.5rem;
    font-style: italic;
    text-align: center;
}
.trust {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(0.75rem, 2vw, 2rem);
    padding: 1rem var(--coy-gutter);
    background: var(--coy-color-surface);
    border-block: 1px solid var(--coy-color-border-soft);
    font-size: var(--coy-text-xs);
    text-align: center;
}
.trust p {
    margin: 0;
}
.trust i {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: var(--coy-color-rose-gold);
}
.split-heading,
.collection-heading {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(18rem, 0.65fr);
    align-items: end;
    gap: clamp(2rem, 7vw, 7rem);
    margin-bottom: clamp(2.5rem, 5vw, 4.5rem);
}
.split-heading h2,
.collection-heading h2,
.story-copy h2,
.reviews h2,
.newsletter h2 {
    margin: 0.75rem 0 0;
    font-size: var(--coy-text-h2);
    font-weight: 400;
    letter-spacing: -0.025em;
}
.split-heading > p {
    max-width: 34rem;
    margin: 0;
}
.discovery-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    grid-template-rows: 1fr 1fr;
    gap: 1rem;
}
.discovery-card {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 13rem;
    padding: clamp(1.5rem, 3vw, 2.5rem);
    color: var(--coy-color-heading);
    background: var(--coy-color-champagne);
    border: 1px solid transparent;
    border-radius: var(--coy-radius-sm);
    text-decoration: none;
    transition: transform 0.2s;
}
.discovery-card:hover {
    transform: translateY(-3px);
}
.discovery-card--main {
    grid-row: 1/-1;
    justify-content: flex-end;
    min-height: 28rem;
    padding-right: 25%;
    color: white;
    background: var(--coy-color-accent);
    border-radius: var(--coy-radius-sm) var(--coy-radius-sm) 5rem;
}
.discovery-card--rose {
    background: var(--coy-color-blush);
}
.discovery-card--light {
    background: var(--coy-color-surface);
    border-color: var(--coy-color-border);
}
.discovery-card > b {
    position: absolute;
    top: 1.25rem;
    right: 1.5rem;
    font-size: 0.75rem;
    letter-spacing: 0.14em;
}
.discovery-card small {
    font-size: var(--coy-text-xs);
}
.discovery-card h3 {
    max-width: 13ch;
    margin: 0.4rem 0 1rem;
    font-family: var(--coy-font-display);
    font-size: var(--coy-text-h3);
    font-weight: 400;
    line-height: 1.1;
}
.discovery-card span {
    font-weight: 600;
}
.collection {
    background: var(--coy-color-surface);
}
.collection-heading {
    grid-template-columns: 1fr auto;
}
.products {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr 0.8fr;
    gap: clamp(1rem, 2vw, 1.5rem);
    align-items: start;
}
.product {
    color: var(--coy-color-heading);
    text-decoration: none;
}
.product--1 {
    grid-row: span 2;
}
.product--4 {
    grid-column: 2/-1;
    width: calc(50% - 0.75rem);
    justify-self: end;
}
.product-image {
    position: relative;
    aspect-ratio: 4/5;
    overflow: hidden;
    background: var(--coy-color-page);
    border-radius: var(--coy-radius-sm);
}
.product--1 .product-image {
    aspect-ratio: 4/5.5;
    border-radius: var(--coy-radius-sm) var(--coy-radius-sm) 4rem;
}
.product-label {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.4rem 0.75rem;
    color: white;
    background: var(--coy-color-accent);
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}
.product-info {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0 1.5rem;
}
.product-info h3 {
    margin: 0;
    font-family: var(--coy-font-display);
    font-size: 1.35rem;
    font-weight: 500;
    line-height: 1.2;
}
.product-info p {
    margin: 0.2rem 0 0;
    color: var(--coy-color-text);
    font-size: var(--coy-text-xs);
}
.product-info strong {
    color: var(--coy-color-accent);
    font-weight: 600;
    white-space: nowrap;
}
.manifesto {
    position: relative;
    display: grid;
    place-items: center;
    min-height: clamp(34rem, 60vw, 46rem);
    padding: var(--coy-section-space) var(--coy-gutter);
    color: white;
    background: var(--coy-color-heading);
    text-align: center;
}
.manifesto > div {
    max-width: 58rem;
}
.manifesto .coy-eyebrow {
    color: var(--coy-color-champagne);
}
.manifesto h2 {
    margin: 1rem 0 2rem;
    color: inherit;
    font-size: clamp(2.5rem, 5vw, 4.8rem);
    font-weight: 400;
    line-height: 1.08;
    letter-spacing: -0.03em;
}
.manifesto h2 em {
    display: block;
    color: var(--coy-color-blush);
}
.manifesto div > p:last-child {
    max-width: 39rem;
    margin: auto;
    color: var(--coy-color-champagne);
    font-size: var(--coy-text-lead);
}
.manifesto-chapter {
    left: var(--coy-gutter);
    top: 50%;
    color: var(--coy-color-champagne);
    transform: translateY(-50%);
}
.story-grid {
    display: grid;
    grid-template-columns: minmax(20rem, 0.9fr) minmax(25rem, 1.1fr);
    gap: clamp(3rem, 9vw, 8rem);
    align-items: center;
}
.story-visual {
    position: relative;
    padding: 0 0 3rem 3rem;
}
.story-visual > div {
    aspect-ratio: 4/5;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border-radius: 45% 45% var(--coy-radius-sm) var(--coy-radius-sm);
}
.story-visual > p {
    position: absolute;
    left: 0;
    bottom: 0;
    margin: 0;
    padding: 1rem 1.25rem;
    color: white;
    background: var(--coy-color-accent);
    font-family: var(--coy-font-display);
    font-size: 1.2rem;
    font-style: italic;
    line-height: 1.2;
}
.story-copy > p:not(.coy-eyebrow) {
    max-width: 35rem;
    margin: 1.25rem 0 0;
}
.story-copy .text-link {
    display: block;
    margin-top: 2rem;
}
.story-copy dl {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin: 3rem 0 0;
    padding-top: 1.5rem;
    border-top: 1px solid var(--coy-color-border);
}
.reviews {
    background: var(--coy-color-blush);
}
.reviews header {
    max-width: 42rem;
    margin-bottom: clamp(2.5rem, 5vw, 4rem);
}
.review-grid {
    display: grid;
    grid-template-columns: 1.25fr 0.75fr;
    gap: 1rem;
}
.reviews blockquote {
    margin: 0;
    background: var(--coy-color-surface);
    border-radius: var(--coy-radius-sm);
}
.review-main {
    min-height: 23rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: clamp(2rem, 5vw, 4rem);
}
.review-main > p {
    max-width: 25ch;
    margin: 1.5rem 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(1.7rem, 3vw, 2.7rem);
    line-height: 1.25;
}
.reviews footer {
    color: var(--coy-color-accent);
    font-weight: 600;
}
.review-side {
    display: grid;
    gap: 1rem;
}
.review-side blockquote {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 2rem;
}
.review-side p {
    margin: 1rem 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 1.25rem;
    line-height: 1.4;
}
.newsletter {
    text-align: center;
}
.newsletter > div {
    width: min(100% - 2 * var(--coy-gutter), 48rem);
    margin: auto;
}
.newsletter h2 {
    font-size: clamp(2.3rem, 4.5vw, 4rem);
}
.newsletter > div > p:not(.coy-eyebrow) {
    margin: 1.5rem auto 2rem;
    font-size: var(--coy-text-lead);
}
.newsletter form {
    max-width: 39rem;
    margin: auto;
    text-align: left;
}
.newsletter label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--coy-color-heading);
    font-weight: 600;
}
.newsletter form > div {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.65rem;
}
.newsletter small {
    display: block;
    margin-top: 0.65rem;
    text-align: center;
}
.error {
    color: var(--coy-color-error);
    font-weight: 600;
}
.success {
    width: fit-content;
    padding: 0.75rem 1rem;
    color: var(--coy-color-success);
    background: var(--coy-color-success-soft);
    border: 1px solid;
    border-radius: var(--coy-radius-sm);
}
@media (max-width: 900px) {
    .hero {
        grid-template-columns: 1fr;
    }
    .hero-copy {
        max-width: none;
        padding-inline: var(--coy-gutter);
    }
    .hero-gallery {
        min-height: min(42rem, 105vw);
        margin: 0 var(--coy-gutter) var(--coy-gutter);
        border-radius: var(--coy-radius-sm) var(--coy-radius-sm) 5rem;
    }
    .handnote {
        left: 1rem;
    }
    .products {
        grid-template-columns: 1fr 1fr;
    }
    .product--1 {
        grid-row: auto;
    }
    .product--4 {
        grid-column: auto;
        width: auto;
    }
    .product--1 .product-image {
        aspect-ratio: 4/5;
        border-radius: var(--coy-radius-sm);
    }
}
@media (max-width: 680px) {
    .announcement {
        align-items: flex-start;
        flex-direction: column;
        gap: 0.2rem;
        text-align: left;
    }
    .hero-copy {
        padding-block: 3.5rem 2.5rem;
    }
    .hero-copy h1 {
        font-size: clamp(3.2rem, 16vw, 5rem);
    }
    .hero-actions {
        align-items: flex-start;
        flex-direction: column;
        gap: 1.25rem;
    }
    .hero-gallery {
        min-height: 31rem;
    }
    .hero-main {
        inset: 1.25rem 1.25rem 4.5rem;
    }
    .hero-detail {
        width: 7.5rem;
        right: 0.75rem;
        bottom: 1rem;
        border-width: 0.35rem;
    }
    .handnote,
    .chapter,
    .manifesto-chapter {
        display: none;
    }
    .trust {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem 1rem;
        text-align: left;
    }
    .trust i {
        display: none;
    }
    .split-heading,
    .collection-heading {
        grid-template-columns: 1fr;
        align-items: start;
        gap: 1.25rem;
    }
    .discovery-grid {
        grid-template-columns: 1fr;
        grid-template-rows: auto;
    }
    .discovery-card--main {
        grid-row: auto;
        min-height: 19rem;
    }
    .product-info {
        flex-direction: column;
        gap: 0.35rem;
    }
    .product-info h3 {
        font-size: 1.15rem;
    }
    .product-info p {
        display: none;
    }
    .manifesto h2 {
        font-size: clamp(2.4rem, 11vw, 3.6rem);
    }
    .story-grid,
    .review-grid {
        grid-template-columns: 1fr;
    }
    .story-visual {
        padding-left: 1.5rem;
    }
    .story-copy dl {
        gap: 0.5rem;
    }
    .review-main {
        min-height: auto;
        padding: 2rem;
    }
    .newsletter h2 br {
        display: none;
    }
    .newsletter form > div {
        grid-template-columns: 1fr;
    }
    .newsletter button {
        width: 100%;
    }
}
@media (max-width: 370px) {
    .hero-notes,
    .products,
    .story-copy dl,
    .trust {
        grid-template-columns: 1fr;
    }
}
</style>
