<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

const seo = useSeoHead({
    title: 'Terms & Conditions',
    description: 'Terms and conditions for Chapter of You. Read my policies on orders, pricing, delivery, returns, and product information.',
    canonical: '/terms',
});

const lastUpdated = '6 April 2026';
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="tc">
        <div class="tc-wrap">

            <!-- ── Header ── -->
            <header class="tc-header">
                <p class="tc-eyebrow">Chapter of You</p>
                <h1 class="tc-title">Terms &amp; Conditions</h1>
                <div class="tc-title-rule">
                    <span class="tc-rule-line"></span>
                    <span class="tc-rule-petal">✿</span>
                    <span class="tc-rule-line"></span>
                </div>
                <p class="tc-intro">
                    By using this website and placing an order, you agree to the following terms.
                    Please take a moment to read them carefully.
                </p>
                <p class="tc-updated">Last updated: {{ lastUpdated }}</p>
            </header>

            <!-- ── Sections ── -->
            <div class="tc-body">

                <!-- Quick navigation -->
                <nav class="tc-nav" aria-label="Jump to section">
                    <p class="tc-nav-label">Jump to</p>
                    <div class="tc-nav-links">
                        <a href="#orders" class="tc-nav-link">Orders</a>
                        <a href="#pricing" class="tc-nav-link">Pricing</a>
                        <a href="#delivery" class="tc-nav-link">Delivery</a>
                        <a href="#returns" class="tc-nav-link">Returns</a>
                        <a href="#products" class="tc-nav-link">Products</a>
                        <a href="#liability" class="tc-nav-link">Liability</a>
                    </div>
                </nav>

                <!-- Orders -->
                <section id="orders" class="tc-section">
                    <div class="tc-section-num" aria-hidden="true">01</div>
                    <div class="tc-section-content">
                        <h2 class="tc-section-title">Orders</h2>
                        <div class="tc-section-body">
                            <p>
                                All orders are subject to availability. By placing an order on this website,
                                you are making an offer to purchase the selected products at the listed price.
                            </p>
                            <p>
                                Once your order is placed, you will receive a confirmation email. This email
                                confirms receipt of your order, it does not guarantee acceptance until your
                                payment has been processed successfully.
                            </p>
                            <p>
                                I reserve the right to cancel or decline any order, in which case you will
                                receive a full refund.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="tc-divider" aria-hidden="true">✿</div>

                <!-- Pricing -->
                <section id="pricing" class="tc-section">
                    <div class="tc-section-num" aria-hidden="true">02</div>
                    <div class="tc-section-content">
                        <h2 class="tc-section-title">Pricing</h2>
                        <div class="tc-section-body">
                            <p>
                                All prices are listed in <strong>GBP (£)</strong> and are inclusive of
                                applicable VAT where stated. Prices are subject to change without prior notice.
                            </p>
                            <p>
                                The price charged will be the price displayed at the time your order is placed.
                                Subsequent price changes will not affect orders already confirmed.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="tc-divider" aria-hidden="true">✿</div>

                <!-- Dispatch & Delivery -->
                <section id="delivery" class="tc-section">
                    <div class="tc-section-num" aria-hidden="true">03</div>
                    <div class="tc-section-content">
                        <h2 class="tc-section-title">Dispatch &amp; Delivery</h2>
                        <div class="tc-section-body">
                            <p>
                                Orders are typically processed and dispatched within <strong>2–3 working days</strong>.
                                During busier periods, this may take slightly longer.
                            </p>
                            <p>
                                Delivery times may vary depending on your location and the courier service used.
                                Once dispatched, you will receive tracking information via email.
                            </p>
                            <p>
                                I cannot be held responsible for delays caused by third-party couriers,
                                customs, or events outside of my control.
                            </p>
                            <a :href="route('delivery')" class="tc-inline-link">
                                View full delivery information
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </section>

                <div class="tc-divider" aria-hidden="true">✿</div>

                <!-- Returns & Refunds -->
                <section id="returns" class="tc-section">
                    <div class="tc-section-num" aria-hidden="true">04</div>
                    <div class="tc-section-content">
                        <h2 class="tc-section-title">Returns &amp; Refunds</h2>
                        <div class="tc-section-body">
                            <p>
                                Due to the handmade and personal nature of my products, returns are only
                                accepted if items arrive <strong>damaged or faulty</strong>.
                            </p>

                            <div class="tc-callout">
                                <div class="tc-callout-icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 8v4M12 16h.01" />
                                    </svg>
                                </div>
                                <p>
                                    If there is an issue with your order, please contact
                                    <a href="mailto:contact@chapterofyou.co.uk"
                                        class="tc-email-link">contact@chapterofyou.co.uk</a>
                                    within <strong>48 hours of delivery</strong>, including photographs where possible.
                                </p>
                            </div>

                            <p>
                                Refunds or replacements will be considered on a case-by-case basis.
                                I will always aim to find a fair resolution.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="tc-divider" aria-hidden="true">✿</div>

                <!-- Product Information -->
                <section id="products" class="tc-section">
                    <div class="tc-section-num" aria-hidden="true">05</div>
                    <div class="tc-section-content">
                        <h2 class="tc-section-title">Product Information</h2>
                        <div class="tc-section-body">
                            <p>
                                Each product is carefully and lovingly prepared by hand. As a result,
                                <strong>slight variations</strong> in appearance, colour, or scent intensity
                                may occur between batches, this is a natural characteristic of handmade goods.
                            </p>
                            <p>
                                Product descriptions, images, and fragrance notes are provided as accurately
                                as possible. If you have any questions about a specific product before purchasing,
                                please don't hesitate to get in touch.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="tc-divider" aria-hidden="true">✿</div>

                <!-- Liability -->
                <section id="liability" class="tc-section">
                    <div class="tc-section-num" aria-hidden="true">06</div>
                    <div class="tc-section-content">
                        <h2 class="tc-section-title">Liability</h2>
                        <div class="tc-section-body">
                            <p>
                                I am not responsible for any damage, injury, or loss caused by misuse
                                of products. Please follow all care, usage, and safety instructions
                                provided with your order.
                            </p>
                            <p>
                                Reed diffusers contain fragrance oils, keep out of reach of children
                                and pets, and away from polished or painted surfaces.
                            </p>
                            <p>
                                My liability is limited to the value of the products purchased.
                            </p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- ── Footer contact strip ── -->
            <div class="tc-footer-strip">
                <p class="tc-footer-petal" aria-hidden="true">✿</p>
                <p class="tc-footer-text">
                    Questions about these terms?
                </p>
                <a href="mailto:contact@chapterofyou.co.uk" class="tc-footer-link">
                    contact@chapterofyou.co.uk
                </a>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.tc {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.tc-wrap {
    max-width: 760px;
    margin: 0 auto;
    padding: 4rem 1.25rem 6rem;
}

/* ── Header ── */
.tc-header {
    text-align: center;
    margin-bottom: 3rem;
}

.tc-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
}

.tc-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 6vw, 3rem);
    font-weight: 400;
    font-style: italic;
    color: #2d1a1a;
    line-height: 1.1;
    margin-bottom: 1.25rem;
}

.tc-title-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.tc-rule-line {
    display: block;
    width: 60px;
    height: 1px;
    background: #e5c9c7;
}

.tc-rule-petal {
    font-size: 0.85rem;
    color: #c9a4a4;
}

.tc-intro {
    font-size: 0.97rem;
    color: #6b4f4f;
    line-height: 1.7;
    max-width: 520px;
    margin: 0 auto 0.75rem;
}

.tc-updated {
    font-size: 0.75rem;
    color: #c9a4a4;
    font-style: italic;
}

/* ── Jump nav ── */
.tc-nav {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-bottom: 2.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.tc-nav-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #c9a4a4;
    flex-shrink: 0;
}

.tc-nav-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.tc-nav-link {
    font-size: 0.78rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: #fdf4f3;
    transition: background 0.15s, border-color 0.15s;
}

.tc-nav-link:hover {
    background: #faeaea;
    border-color: #c9a4a4;
}

/* ── Body ── */
.tc-body {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 2rem;
}

/* ── Sections ── */
.tc-section {
    display: flex;
    gap: 1.5rem;
    padding: 2rem 2rem 2rem;
    align-items: flex-start;
    scroll-margin-top: 88px;
    /* account for sticky nav */
}

@media (max-width: 540px) {
    .tc-section {
        flex-direction: column;
        gap: 0.5rem;
        padding: 1.5rem;
    }
}

.tc-section-num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 400;
    font-style: italic;
    color: #e5c9c7;
    line-height: 1;
    flex-shrink: 0;
    width: 42px;
    text-align: right;
    padding-top: 0.1rem;
}

@media (max-width: 540px) {
    .tc-section-num {
        width: auto;
        text-align: left;
    }
}

.tc-section-content {
    flex: 1;
    min-width: 0;
}

.tc-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.35rem;
    font-weight: 500;
    color: #2d1a1a;
    margin-bottom: 0.85rem;
    line-height: 1.2;
}

.tc-section-body {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.tc-section-body p {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.75;
}

.tc-section-body strong {
    color: #2d1a1a;
    font-weight: 600;
}

/* Inline link within a section */
.tc-inline-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
    transition: gap 0.2s;
}

.tc-inline-link:hover {
    gap: 0.55rem;
    text-decoration: underline;
}

/* Callout box */
.tc-callout {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.9rem 1rem;
    background: rgba(140, 74, 80, 0.05);
    border: 1px solid #e5c9c7;
    border-left: 3px solid #8c4a50;
    border-radius: 0 10px 10px 0;
}

.tc-callout-icon {
    color: #8c4a50;
    flex-shrink: 0;
    margin-top: 1px;
}

.tc-callout p {
    font-size: 0.88rem;
    color: #6b4f4f;
    line-height: 1.65;
    margin: 0;
}

.tc-email-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
}

.tc-email-link:hover {
    text-decoration: underline;
}

/* Divider between sections */
.tc-divider {
    text-align: center;
    color: #e5c9c7;
    font-size: 0.7rem;
    letter-spacing: 0.4rem;
    padding: 0.15rem 0;
    border-top: 1px solid #f0dcd8;
}

/* ── Footer strip ── */
.tc-footer-strip {
    text-align: center;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.tc-footer-petal {
    font-size: 1.2rem;
    color: #c9a4a4;
}

.tc-footer-text {
    font-size: 0.9rem;
    color: #6b4f4f;
}

.tc-footer-link {
    font-size: 0.9rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
}

.tc-footer-link:hover {
    text-decoration: underline;
}
</style>
