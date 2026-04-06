<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import { Head } from '@inertiajs/vue3';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import JsonLdSchema from '@/components/JsonLdSchema.vue';
import { useOrganizationSchema, useWebsiteSchema } from '@/composables/useProductSchema';

interface FeaturedProduct {
    id: number;
    name: string;
    mpn: string;
    cost: number;
    image: string | null;
    slug: string | null;
    views: number;
}

defineProps<{
    featuredProducts?: FeaturedProduct[];
}>();

const seo = useSeoHead({
    // No title — homepage should just be "Chapter of You"
    description: 'Chapter of You. Luxurious hand-crafted reed diffusers made with care for your home. Your chapter, your self-care.',
    canonical: '/',  // resolves to https://www.chapterofyou.co.uk
});

const siteSchemas = [useOrganizationSchema(), useWebsiteSchema()];
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />
    <JsonLdSchema :schema="siteSchemas" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="lp">


        <!-- ── Hero ── -->
        <section class="lp-hero">
            <!-- Decorative blobs -->
            <div class="lp-blob lp-blob--1" aria-hidden="true"></div>
            <div class="lp-blob lp-blob--2" aria-hidden="true"></div>

            <!-- Scattered petal marks -->
            <svg class="lp-petals" viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <ellipse cx="80" cy="100" rx="22" ry="8" fill="#c9a4a4" opacity=".13" transform="rotate(-30 80 100)" />
                <ellipse cx="820" cy="140" rx="16" ry="6" fill="#c9a4a4" opacity=".10" transform="rotate(40 820 140)" />
                <ellipse cx="160" cy="440" rx="20" ry="7" fill="#c9a4a4" opacity=".09" transform="rotate(15 160 440)" />
                <ellipse cx="740" cy="400" rx="18" ry="6" fill="#c9a4a4" opacity=".11"
                    transform="rotate(-20 740 400)" />
                <ellipse cx="450" cy="560" rx="14" ry="5" fill="#c9a4a4" opacity=".08" transform="rotate(5 450 560)" />
                <ellipse cx="50" cy="320" rx="12" ry="4" fill="#c9a4a4" opacity=".09" transform="rotate(25 50 320)" />
                <ellipse cx="860" cy="480" rx="20" ry="7" fill="#c9a4a4" opacity=".07"
                    transform="rotate(-45 860 480)" />
                <text x="200" y="180" font-size="18" fill="#c9a4a4" opacity=".15"
                    transform="rotate(-20 200 180)">✿</text>
                <text x="650" y="100" font-size="14" fill="#c9a4a4" opacity=".12"
                    transform="rotate(15 650 100)">✿</text>
                <text x="750" y="550" font-size="22" fill="#c9a4a4" opacity=".10"
                    transform="rotate(-10 750 550)">✿</text>
                <text x="100" y="550" font-size="16" fill="#c9a4a4" opacity=".11"
                    transform="rotate(20 100 550)">✿</text>
            </svg>

            <div class="lp-hero-content">
                <p class="lp-hero-eyebrow">Handcrafted with love</p>
                <h1 class="lp-hero-title">
                    <em>Chapter</em><br>of You
                </h1>
                <p class="lp-hero-sub">
                    Your space, your scent, your self-care.<br>
                    Premium reed diffusers poured by hand, made to order, just for you.
                </p>
                <div class="lp-hero-divider" aria-hidden="true">
                    <span></span><span class="lp-hero-divider-dot">✦</span><span></span>
                </div>
                <div class="lp-hero-actions">
                    <a href="/products" class="btn-rose btn-rose--lg">
                        Shop the Collection
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="/about" class="btn-ghost">
                        My Story
                    </a>
                </div>
            </div>
        </section>

        <!-- ── Trust bar ── -->
        <section class="lp-trust" aria-label="Why Chapter of You">
            <div class="lp-trust-inner">
                <div class="lp-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                    <span>Made with love</span>
                </div>
                <div class="lp-trust-sep" aria-hidden="true">✦</div>
                <div class="lp-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    <span>Premium ingredients only</span>
                </div>
                <div class="lp-trust-sep" aria-hidden="true">✦</div>
                <div class="lp-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="1" y="3" width="15" height="13" rx="2" />
                        <path d="M16 8h4l3 5v3h-7V8z" />
                        <circle cx="5.5" cy="18.5" r="2.5" />
                        <circle cx="18.5" cy="18.5" r="2.5" />
                    </svg>
                    <span>Free shipping over £50</span>
                </div>
                <div class="lp-trust-sep" aria-hidden="true">✦</div>
                <div class="lp-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path
                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                    <span>Made to order</span>
                </div>
            </div>
        </section>


        <!-- ── Story strip ── -->
        <section class="lp-story">
            <div class="lp-story-inner">
                <div class="lp-story-text">
                    <p class="lp-section-eyebrow lp-section-eyebrow--light">The story</p>
                    <h2 class="lp-story-title">A small business<br><em>with a big heart</em></h2>
                    <p class="lp-story-body">
                        Welcome to Chapter of You, a one-woman business built on a single belief: that proper self-care
                        starts with the small, quiet moments you carve out for yourself.
                    </p>
                    <p class="lp-story-body">
                        Every diffuser is poured, blended and finished by hand, because your space deserves something
                        made with genuine care, not a factory line.
                    </p>
                    <a href="/about" class="lp-story-link">
                        Read more about me
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="lp-story-stats">
                    <div class="lp-stat-card">
                        <p class="lp-stat-val">100%</p>
                        <p class="lp-stat-label">Hand-poured &amp; blended</p>
                    </div>
                    <div class="lp-stat-card">
                        <p class="lp-stat-val">Premium</p>
                        <p class="lp-stat-label">Fragrance oils only</p>
                    </div>
                    <div class="lp-stat-card">
                        <p class="lp-stat-val">Made</p>
                        <p class="lp-stat-label">To order, just for you</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Why choose us ── -->
        <section class="lp-section">
            <div class="lp-section-inner">
                <div class="lp-section-header">
                    <p class="lp-section-eyebrow">Our craft</p>
                    <h2 class="lp-section-title">Reed diffusers <em>designed for your sanctuary</em></h2>
                </div>
                <div class="lp-features">
                    <div class="lp-feature-card">
                        <div class="lp-feature-icon" aria-hidden="true">✦</div>
                        <h3 class="lp-feature-title">Meticulously blended</h3>
                        <p class="lp-feature-body">Every scent is carefully crafted from premium fragrance oils,
                            balanced by hand to fill your home with a gentle, lasting aroma.</p>
                    </div>
                    <div class="lp-feature-card">
                        <div class="lp-feature-icon" aria-hidden="true">✦</div>
                        <h3 class="lp-feature-title">Poured with intention</h3>
                        <p class="lp-feature-body">Each diffuser is hand-finished individually, never rushed, never
                            mass-produced. Your order is made to order, just for you.</p>
                    </div>
                    <div class="lp-feature-card">
                        <div class="lp-feature-icon" aria-hidden="true">✦</div>
                        <h3 class="lp-feature-title">Quality ingredients</h3>
                        <p class="lp-feature-body">Only the highest quality reed diffuser base and fragrance oils make
                            it into my products. Nothing unnecessary, nothing cheap.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Hottest products ── -->
        <section v-if="featuredProducts?.length" class="lp-hot">
            <div class="lp-hot-inner">
                <div class="lp-hot-header">
                    <div>
                        <p class="lp-section-eyebrow">Most loved</p>
                        <h2 class="lp-section-title">Our <em>bestsellers</em></h2>
                    </div>
                    <a href="/products" class="lp-hot-see-all">
                        View all
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="lp-hot-grid">
                    <a v-for="product in featuredProducts" :key="product.id"
                        :href="product.slug ? `/product/${product.slug}` : `/product/${product.id}`"
                        class="lp-hot-card">

                        <!-- Badge -->
                        <span v-if="product.views > 100" class="lp-hot-badge">Popular</span>

                        <!-- Image -->
                        <div class="lp-hot-img-wrap">
                            <img :src="product.image ?? '/images/placeholder.jpg'" :alt="product.name"
                                class="lp-hot-img" />
                        </div>

                        <!-- Body -->
                        <div class="lp-hot-body">
                            <p class="lp-hot-mpn">{{ product.mpn }}</p>
                            <p class="lp-hot-name">{{ product.name }}</p>
                            <div class="lp-hot-footer">
                                <span class="lp-hot-price">£{{ Number(product.cost).toFixed(2) }}</span>
                                <span class="lp-hot-cta">
                                    View
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                    </a>
                </div>
            </div>
        </section>

        <!-- ── CTA ── -->
        <section class="lp-cta-section">
            <div class="lp-cta-card">
                <div class="lp-cta-petals" aria-hidden="true">
                    <span>✿</span><span>✦</span><span>✿</span><span>✦</span><span>✿</span>
                </div>
                <h2 class="lp-cta-title">Join my little community</h2>
                <p class="lp-cta-body">
                    At Chapter of You you're more than a customer, you're part of a community dedicated to intentional
                    self-care. Explore the collection and let me bring a little calm to your corner of the world.
                </p>
                <a href="/products" class="btn-rose btn-rose--lg">
                    Shop the Collection
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </section>

    </main>

    <Footer />
</template>

<style scoped>
/* ── Base ── */
.lp {
    font-family: 'Nunito', sans-serif;
    color: #2d1a1a;
    background: #fdf4f3;
    overflow-x: hidden;
    padding-top: 64px;
}

/* ── Promo banner ── */
.lp-promo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    padding: 0.55rem 1rem;
    text-align: center;
}

.lp-promo svg {
    opacity: 0.7;
    flex-shrink: 0;
}

/* ── Hero ── */
.lp-hero {
    position: relative;
    min-height: 88vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
    background: #fdf4f3;
    padding: 4rem 1.5rem 5rem;
}

/* Blobs */
.lp-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
}

.lp-blob--1 {
    width: 480px;
    height: 480px;
    background: #e5c9c7;
    top: -80px;
    left: -120px;
    opacity: 0.45;
}

.lp-blob--2 {
    width: 380px;
    height: 380px;
    background: #f0dcd8;
    bottom: -60px;
    right: -80px;
    opacity: 0.4;
}

/* Scattered petals SVG */
.lp-petals {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.lp-hero-content {
    position: relative;
    z-index: 1;
    max-width: 680px;
}

.lp-hero-eyebrow {
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #8c4a50;
    margin-bottom: 1.25rem;
    animation: lp-fadeUp 0.9s ease both;
}

.lp-hero-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(4.5rem, 14vw, 9.5rem);
    font-weight: 300;
    line-height: 0.9;
    color: #2d1a1a;
    margin-bottom: 1.5rem;
    animation: lp-fadeUp 0.9s 0.12s ease both;
}

.lp-hero-title em {
    font-style: italic;
    color: #8c4a50;
}

.lp-hero-sub {
    font-size: clamp(0.95rem, 2vw, 1.1rem);
    color: #6b4f4f;
    line-height: 1.75;
    max-width: 480px;
    margin: 0 auto 2rem;
    font-style: italic;
    animation: lp-fadeUp 0.9s 0.24s ease both;
}

.lp-hero-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2.25rem;
    animation: lp-fadeUp 0.9s 0.35s ease both;
}

.lp-hero-divider span:not(.lp-hero-divider-dot) {
    display: block;
    width: 70px;
    height: 1px;
    background: #e5c9c7;
}

.lp-hero-divider-dot {
    color: #c9a4a4;
    font-size: 0.9rem;
}

.lp-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    justify-content: center;
    animation: lp-fadeUp 0.9s 0.45s ease both;
}

@keyframes lp-fadeUp {
    from {
        opacity: 0;
        transform: translateY(22px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

/* ── Trust bar ── */
.lp-trust {
    background: #fffafa;
    border-top: 1px solid #e5c9c7;
    border-bottom: 1px solid #e5c9c7;
    padding: 1.1rem 1.5rem;
}

.lp-trust-inner {
    max-width: 860px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 1.25rem 2rem;
}

.lp-trust-item {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #6b4f4f;
    white-space: nowrap;
}

.lp-trust-item svg {
    color: #c9a4a4;
    flex-shrink: 0;
}

.lp-trust-sep {
    font-size: 0.6rem;
    color: #e5c9c7;
}

/* ── Shared section layout ── */
.lp-section {
    padding: 5rem 1.5rem;
}

.lp-section-inner {
    max-width: 1100px;
    margin: 0 auto;
}

.lp-section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.lp-section-eyebrow {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: #8c4a50;
    margin-bottom: 0.75rem;
    display: block;
}

.lp-section-eyebrow--light {
    color: rgba(255, 255, 255, 0.75);
}

.lp-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 4.5vw, 2.9rem);
    font-weight: 400;
    line-height: 1.2;
    color: #2d1a1a;
}

.lp-section-title em {
    font-style: italic;
    color: #8c4a50;
}


/* ── Story strip ── */
.lp-story {
    background: #8c4a50;
    padding: 5rem 1.5rem;
}

.lp-story-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

@media (max-width: 768px) {
    .lp-story-inner {
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }
}

.lp-story-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 400;
    line-height: 1.2;
    color: #fff;
    margin-bottom: 1.25rem;
}

.lp-story-title em {
    font-style: italic;
    opacity: 0.85;
}

.lp-story-body {
    font-size: 0.97rem;
    line-height: 1.8;
    color: rgba(255, 255, 255, 0.82);
    margin-bottom: 1rem;
}

.lp-story-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.4);
    padding-bottom: 0.1rem;
    transition: border-color 0.2s;
    margin-top: 0.5rem;
}

.lp-story-link:hover {
    border-color: #fff;
}

.lp-story-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.lp-stat-card:first-child {
    grid-column: 1 / -1;
}

.lp-stat-card {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 1.5rem 1.25rem;
    text-align: center;
    backdrop-filter: blur(4px);
    transition: background 0.2s;
}

.lp-stat-card:hover {
    background: rgba(255, 255, 255, 0.2);
}

.lp-stat-val {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 400;
    color: #fff;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.lp-stat-label {
    font-size: 0.82rem;
    font-style: italic;
    color: rgba(255, 255, 255, 0.75);
    line-height: 1.4;
}

/* ── Features ── */
.lp-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

@media (max-width: 768px) {
    .lp-features {
        grid-template-columns: 1fr;
    }
}

.lp-feature-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 2rem 1.5rem;
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.25s, transform 0.25s;
}

.lp-feature-card:hover {
    box-shadow: 0 8px 28px rgba(229, 201, 199, 0.5);
    transform: translateY(-3px);
}

.lp-feature-card::before {
    content: '✿';
    position: absolute;
    bottom: -5px;
    right: 7px;
    font-size: 3rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.lp-feature-icon {
    font-size: 0.9rem;
    color: #c9a4a4;
    letter-spacing: 0.3em;
    margin-bottom: 1rem;
    display: block;
}

.lp-feature-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.75rem;
}

.lp-feature-body {
    font-size: 0.9rem;
    line-height: 1.75;
    color: #6b4f4f;
}

/* ── Hottest products ── */
.lp-hot {
    background: #fffafa;
    border-top: 1px solid #e5c9c7;
    border-bottom: 1px solid #e5c9c7;
    padding: 5rem 1.5rem;
}

.lp-hot-inner {
    max-width: 1100px;
    margin: 0 auto;
}

.lp-hot-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 2.5rem;
    flex-wrap: wrap;
}

.lp-hot-see-all {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #8c4a50;
    text-decoration: none;
    border-bottom: 1px solid #e5c9c7;
    padding-bottom: 0.1rem;
    transition: border-color 0.2s, color 0.2s;
    white-space: nowrap;
}

.lp-hot-see-all:hover {
    color: #6a3038;
    border-color: #8c4a50;
}

.lp-hot-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.1rem;
}

@media (max-width: 1000px) {
    .lp-hot-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .lp-hot-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.lp-hot-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    position: relative;
    transition: box-shadow 0.25s, transform 0.25s;
}

.lp-hot-card:hover {
    box-shadow: 0 8px 32px rgba(201, 164, 164, 0.45);
    transform: translateY(-4px);
}

.lp-hot-card::before {
    content: '✿';
    position: absolute;
    bottom: -5px;
    right: 7px;
    font-size: 3rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

.lp-hot-card::after {
    content: '✿';
    position: absolute;
    top: 5px;
    left: 9px;
    font-size: 0.8rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

.lp-hot-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    background: #8c4a50;
    color: #fff;
    border-radius: 999px;
    padding: 0.18rem 0.6rem;
}

.lp-hot-img-wrap {
    height: 185px;
    overflow: hidden;
    background: #fdf4f3;
    border-bottom: 1px solid #f0dcd8;
    flex-shrink: 0;
}

.lp-hot-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.45s ease;
}

.lp-hot-card:hover .lp-hot-img {
    transform: scale(1.05);
}

.lp-hot-body {
    padding: 0.85rem 1rem 0.95rem;
    background: #fffafa;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    flex: 1;
}

.lp-hot-mpn {
    font-size: 0.68rem;
    font-family: monospace;
    letter-spacing: 0.05em;
    color: #a08080;
}

.lp-hot-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.05rem;
    font-weight: 500;
    color: #2d1a1a;
    line-height: 1.25;
    margin-bottom: 0.35rem;
}

.lp-hot-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.6rem;
    border-top: 1px solid #f0dcd8;
    margin-top: auto;
}

.lp-hot-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.35rem;
    font-weight: 500;
    color: #8c4a50;
}

.lp-hot-cta {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #8c4a50;
    transition: gap 0.2s;
}

.lp-hot-card:hover .lp-hot-cta {
    gap: 0.45rem;
}

/* ── CTA section ── */
.lp-cta-section {
    padding: 4rem 1.5rem 6rem;
}

.lp-cta-card {
    max-width: 760px;
    margin: 0 auto;
    border: 1px solid #e5c9c7;
    border-radius: 28px;
    background: #fffafa;
    box-shadow: 0 4px 32px rgba(229, 201, 199, 0.45);
    padding: 3.5rem 2.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.lp-cta-card::before {
    content: '✿';
    position: absolute;
    bottom: -10px;
    right: 12px;
    font-size: 6rem;
    color: #c9a4a4;
    opacity: 0.1;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.lp-cta-petals {
    display: flex;
    justify-content: center;
    gap: 1.25rem;
    font-size: 1.25rem;
    color: #e5c9c7;
    margin-bottom: 1.5rem;
    letter-spacing: 0.4rem;
}

.lp-cta-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1rem;
}

.lp-cta-body {
    font-size: 0.97rem;
    line-height: 1.8;
    color: #6b4f4f;
    max-width: 520px;
    margin: 0 auto 2rem;
}

/* ── Buttons ── */
.btn-rose {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.72rem 1.5rem;
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

.btn-rose:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.btn-rose--lg {
    padding: 0.85rem 2rem;
    font-size: 0.97rem;
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.72rem 1.5rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: rgba(255, 250, 250, 0.7);
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    backdrop-filter: blur(4px);
    transition: background 0.2s, border-color 0.2s, color 0.2s;
}

.btn-ghost:hover {
    background: #faeaea;
    border-color: #c9a4a4;
    color: #2d1a1a;
}
</style>
