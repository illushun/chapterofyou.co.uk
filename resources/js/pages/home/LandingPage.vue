<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import { Head } from '@inertiajs/vue3';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import JsonLdSchema from '@/components/JsonLdSchema.vue';
import { useOrganizationSchema, useWebsiteSchema } from '@/composables/useProductSchema';
import StarRating from '@/components/ui/coy/StarRating.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

interface FeaturedProduct {
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
    featuredProducts?: FeaturedProduct[];
    testimonials?: Testimonial[];
    season?: Season;
}>();

// Spotlight cycles through featured products automatically
const spotlightIndex = ref(0);
let spotlightTimer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if ((props.featuredProducts?.length ?? 0) > 1) {
        spotlightTimer = setInterval(() => {
            spotlightIndex.value = (spotlightIndex.value + 1) % props.featuredProducts!.length;
        }, 2800);
    }
});

onUnmounted(() => {
    if (spotlightTimer) clearInterval(spotlightTimer);
});

// Email capture
const ctaEmail = ref('');
const ctaSubmitting = ref(false);
const ctaSubmitted = ref(false);
const ctaError = ref('');

async function submitCtaEmail() {
    if (!ctaEmail.value.trim() || ctaSubmitting.value) return;
    ctaSubmitting.value = true;
    ctaError.value = '';
    try {
        await axios.post(route('waitlist.store'), { email: ctaEmail.value.trim() });
        ctaSubmitted.value = true;
        ctaEmail.value = '';
    } catch (err: any) {
        ctaError.value = err?.response?.data?.errors?.email?.[0]
            ?? err?.response?.data?.message
            ?? 'Something went wrong. Please try again.';
    } finally {
        ctaSubmitting.value = false;
    }
}

const seo = useSeoHead({
    description: 'Luxury handmade reed diffusers crafted to order in the UK. Shop premium home fragrance gifts from Chapter of You — hand-poured with care, made just for you.',
    canonical: '/',
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

        <!-- ── Seasonal banner ── -->
        <div v-if="season" class="lp-season-banner" :class="`lp-season-banner--${season.id}`">
            <span class="lp-season-banner-motif" aria-hidden="true">{{ season.motif }}</span>
            <span class="lp-season-banner-text">{{ season.banner }}</span>
            <a href="/products" class="lp-season-banner-cta">
                Shop now
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>

        <!-- ── Hero ── -->
        <section class="lp-hero" :class="season?.id ? `lp-hero--${season.id}` : ''">
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
                    transform="rotate(-20 200 180)">{{ season?.motif ?? '✿' }}</text>
                <text x="650" y="100" font-size="14" fill="#c9a4a4" opacity=".12"
                    transform="rotate(15 650 100)">{{ season?.motif ?? '✿' }}</text>
                <text x="750" y="550" font-size="22" fill="#c9a4a4" opacity=".10"
                    transform="rotate(-10 750 550)">{{ season?.motif ?? '✿' }}</text>
                <text x="100" y="550" font-size="16" fill="#c9a4a4" opacity=".11"
                    transform="rotate(20 100 550)">{{ season?.motif ?? '✿' }}</text>
            </svg>

            <!-- Centred brand text -->
            <div class="lp-hero-content">
                <p class="lp-hero-eyebrow">{{ season?.eyebrow ?? 'Handcrafted with love' }}</p>
                <h1 class="lp-hero-title">
                    <em>Chapter</em><br>of You
                </h1>
                <p class="lp-hero-sub">
                    {{ season?.sub ?? 'Your space, your scent, your self-care. Premium reed diffusers poured by hand, made to order, just for you.' }}
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

            <!-- Interactive product strip -->
            <div v-if="featuredProducts?.length" class="lp-hero-strip" aria-label="Featured products">
                <a
                    v-for="(p, i) in featuredProducts"
                    :key="p.id"
                    :href="p.slug ? `/product/${p.slug}` : `/product/${p.id}`"
                    class="lp-hero-pcard"
                    :class="{ 'lp-hero-pcard--lit': spotlightIndex === i }"
                    @mouseenter="spotlightIndex = i"
                >
                    <div class="lp-hero-pcard-img">
                        <img :src="p.image ?? '/images/placeholder.jpg'" :alt="p.name" loading="lazy" />
                    </div>
                    <div class="lp-hero-pcard-body">
                        <p class="lp-hero-pcard-name">{{ p.name }}</p>
                        <p class="lp-hero-pcard-price">£{{ Number(p.cost).toFixed(2) }}</p>
                    </div>
                </a>
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
                    <p class="lp-section-eyebrow">My craft</p>
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

        <!-- ── Testimonials ── -->
        <section v-if="testimonials?.length" class="lp-testimonials">
            <div class="lp-testimonials-inner">
                <div class="lp-section-header">
                    <p class="lp-section-eyebrow">Customer love</p>
                    <h2 class="lp-section-title">What people are <em>saying</em></h2>
                </div>
                <div class="lp-testimonials-grid">
                    <div v-for="t in testimonials" :key="t.id" class="lp-testimonial-card">
                        <div class="lp-testimonial-stars">
                            <StarRating :rating="t.rating" :size="16" />
                        </div>
                        <p class="lp-testimonial-body">"{{ t.message }}"</p>
                        <p class="lp-testimonial-author">— {{ t.user.name }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Hottest products ── -->
        <section v-if="featuredProducts?.length" class="lp-hot">
            <div class="lp-hot-inner">
                <div class="lp-hot-header">
                    <div>
                        <p class="lp-section-eyebrow">{{ season?.sectionLabel ?? 'Most loved' }}</p>
                        <h2 class="lp-section-title">My <em>bestsellers</em></h2>
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

        <!-- ── CTA / Email capture ── -->
        <section class="lp-cta-section">
            <div class="lp-cta-card">
                <div class="lp-cta-petals" aria-hidden="true">
                    <span>✿</span><span>✦</span><span>✿</span><span>✦</span><span>✿</span>
                </div>
                <h2 class="lp-cta-title">Get 10% off your first order</h2>
                <p class="lp-cta-body">
                    Join my little community and receive an exclusive discount on your first order,
                    plus early access to new scents and behind-the-scenes updates.
                </p>

                <template v-if="!ctaSubmitted">
                    <form class="lp-cta-form" @submit.prevent="submitCtaEmail">
                        <input
                            v-model="ctaEmail"
                            type="email"
                            placeholder="Your email address"
                            class="lp-cta-input"
                            required
                            autocomplete="email"
                        />
                        <button type="submit" class="btn-rose btn-rose--lg" :disabled="ctaSubmitting">
                            {{ ctaSubmitting ? 'Joining…' : 'Claim my 10% off' }}
                        </button>
                    </form>
                    <p v-if="ctaError" class="lp-cta-error">{{ ctaError }}</p>
                    <p class="lp-cta-disclaimer">No spam, ever. Unsubscribe any time.</p>
                </template>

                <div v-else class="lp-cta-success">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    You're in! Check your inbox for your discount code.
                </div>

                <a href="/products" class="lp-cta-shop-link">
                    Browse the collection
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
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

/* ── Seasonal banner ── */
.lp-season-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    padding: 0.55rem 1.25rem;
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: #fff;
    text-align: center;
    background: linear-gradient(135deg, #c47078, #a85058);
}

.lp-season-banner--summer  { background: linear-gradient(135deg, #d4961e, #b87418); }
.lp-season-banner--autumn  { background: linear-gradient(135deg, #b06030, #8c4418); }
.lp-season-banner--winter  { background: linear-gradient(135deg, #5a4880, #3e3060); }
.lp-season-banner--spring  { background: linear-gradient(135deg, #9858b8, #7840a0); }

.lp-season-banner-motif {
    font-size: 1rem;
    opacity: 0.9;
}

.lp-season-banner-text {
    opacity: 0.95;
}

.lp-season-banner-cta {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    opacity: 0.9;
    border-bottom: 1px solid rgba(255,255,255,0.5);
    padding-bottom: 1px;
    white-space: nowrap;
    transition: opacity 0.2s;
}

.lp-season-banner-cta:hover {
    opacity: 1;
    border-color: #fff;
}

/* ── Hero ── */
.lp-hero {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
    background: #fdf4f3;
    padding: 5rem 1.5rem 4rem;
    /* seasonal blob colour variables */
    --blob1: #e5c9c7;
    --blob2: #f0dcd8;
}

.lp-hero--summer { --blob1: #f5d4a8; --blob2: #f0e0b8; }
.lp-hero--autumn { --blob1: #e8c090; --blob2: #e0b870; }
.lp-hero--winter { --blob1: #ccc0dc; --blob2: #dcd4e8; }
.lp-hero--spring { --blob1: #d8c0ec; --blob2: #e8d4f4; }

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
    background: var(--blob1);
    top: -80px;
    left: -120px;
    opacity: 0.45;
}

.lp-blob--2 {
    width: 380px;
    height: 380px;
    background: var(--blob2);
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

/* ── Hero product strip ── */
.lp-hero-strip {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    max-width: 1000px;
    width: 100%;
    margin-top: 3.5rem;
    padding: 0 0 1rem;
}

@media (max-width: 860px) {
    .lp-hero-strip {
        grid-template-columns: repeat(2, 1fr);
        max-width: 480px;
        margin-top: 2.5rem;
    }
}

.lp-hero-pcard {
    --base-y: 0px;
    display: flex;
    flex-direction: column;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    text-decoration: none;
    transform: translateY(var(--base-y));
    transition: transform 0.4s cubic-bezier(.34,1.56,.64,1), box-shadow 0.35s ease, border-color 0.25s ease;
    box-shadow: 0 2px 12px rgba(229, 201, 199, 0.3);
}

.lp-hero-pcard:nth-child(1) { --base-y: 0px; }
.lp-hero-pcard:nth-child(2) { --base-y: 18px; }
.lp-hero-pcard:nth-child(3) { --base-y: -8px; }
.lp-hero-pcard:nth-child(4) { --base-y: 10px; }

@media (max-width: 860px) {
    .lp-hero-pcard:nth-child(n) { --base-y: 0px; }
}

.lp-hero-pcard:hover,
.lp-hero-pcard--lit {
    transform: translateY(calc(var(--base-y) - 14px));
    border-color: #c9a4a4;
    box-shadow: 0 18px 40px rgba(140, 74, 80, 0.18);
}

.lp-hero-pcard-img {
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: #fdf4f3;
    border-bottom: 1px solid #f0dcd8;
}

.lp-hero-pcard-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.45s ease;
    display: block;
}

.lp-hero-pcard:hover .lp-hero-pcard-img img,
.lp-hero-pcard--lit .lp-hero-pcard-img img {
    transform: scale(1.05);
}

.lp-hero-pcard-body {
    padding: 0.7rem 0.85rem 0.85rem;
}

.lp-hero-pcard-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 0.95rem;
    font-weight: 500;
    color: #2d1a1a;
    line-height: 1.3;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.lp-hero-pcard-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #8c4a50;
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

/* ── Testimonials ── */
.lp-testimonials {
    padding: 5rem 1.5rem;
    background: #fdf4f3;
}

.lp-testimonials-inner {
    max-width: 1100px;
    margin: 0 auto;
}

.lp-testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 340px));
    gap: 1.25rem;
    justify-content: center;
}

@media (max-width: 600px) {
    .lp-testimonials-grid {
        grid-template-columns: 1fr;
    }
}

.lp-testimonial-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    padding: 1.75rem 1.5rem;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.3);
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    position: relative;
    overflow: hidden;
}

.lp-testimonial-card::before {
    content: '"';
    position: absolute;
    top: -8px;
    left: 12px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 5rem;
    color: #e5c9c7;
    line-height: 1;
    pointer-events: none;
    user-select: none;
}

.lp-testimonial-stars {
    position: relative;
    z-index: 1;
    color: #c9747a; /* filled star colour — StarRating uses currentColor */
}

.lp-testimonial-body {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.05rem;
    font-style: italic;
    color: #2d1a1a;
    line-height: 1.7;
    flex: 1;
    position: relative;
    z-index: 1;
}

.lp-testimonial-author {
    font-size: 0.82rem;
    font-weight: 700;
    color: #8c4a50;
    letter-spacing: 0.03em;
}

/* ── CTA email form ── */
.lp-cta-form {
    display: flex;
    gap: 0.65rem;
    max-width: 480px;
    margin: 0 auto 0.75rem;
    flex-wrap: wrap;
    justify-content: center;
}

.lp-cta-input {
    flex: 1;
    min-width: 200px;
    padding: 0.72rem 1.1rem;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.lp-cta-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.lp-cta-input::placeholder {
    color: #a08080;
}

.lp-cta-error {
    font-size: 0.82rem;
    color: #b54040;
    margin-bottom: 0.5rem;
}

.lp-cta-disclaimer {
    font-size: 0.78rem;
    color: #a08080;
    font-style: italic;
    margin-bottom: 1.25rem;
}

.lp-cta-success {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #2d7a3a;
    font-weight: 600;
    font-size: 0.95rem;
    background: #f0faf0;
    border: 1px solid #a8d8b0;
    border-radius: 999px;
    padding: 0.6rem 1.2rem;
    margin-bottom: 1.25rem;
}

.lp-cta-shop-link {
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
    margin-top: 1rem;
    display: flex;
    justify-content: center;
}

.lp-cta-shop-link:hover {
    color: #6a3038;
    border-color: #8c4a50;
}
</style>
