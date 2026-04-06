<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

const seo = useSeoHead({
    title: 'Returns & Refunds',
    description: 'Returns and refunds policy for Chapter of You. Information on damaged or faulty items, how to contact me, and how refunds are processed.',
    canonical: '/returns',
});

const lastUpdated = '6 April 2026';
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="rr">
        <div class="rr-wrap">

            <!-- ── Header ── -->
            <header class="rr-header">
                <p class="rr-eyebrow">Chapter of You</p>
                <h1 class="rr-title">Returns &amp; Refunds</h1>
                <div class="rr-title-rule">
                    <span class="rr-rule-line"></span>
                    <span class="rr-rule-petal">✿</span>
                    <span class="rr-rule-line"></span>
                </div>
                <p class="rr-intro">
                    I want you to love your order. If something isn't right, please don't
                    hesitate to get in touch, I'll always do my best to help. 🤍
                </p>
                <p class="rr-updated">Last updated: {{ lastUpdated }}</p>
            </header>

            <!-- ── Policy overview cards ── -->
            <div class="rr-overview">
                <div class="rr-overview-card rr-overview-card--no">
                    <div class="rr-overview-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M15 9l-6 6M9 9l6 6" />
                        </svg>
                    </div>
                    <p class="rr-overview-label">Change of Mind</p>
                    <p class="rr-overview-value">Not accepted</p>
                </div>
                <div class="rr-overview-card rr-overview-card--yes">
                    <div class="rr-overview-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M8 12l3 3 5-5" />
                        </svg>
                    </div>
                    <p class="rr-overview-label">Damaged or Faulty</p>
                    <p class="rr-overview-value">Accepted</p>
                </div>
                <div class="rr-overview-card rr-overview-card--time">
                    <div class="rr-overview-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <p class="rr-overview-label">Report Window</p>
                    <p class="rr-overview-value">Within 48 hours</p>
                </div>
            </div>

            <!-- ── Body ── -->
            <div class="rr-body">

                <!-- No change of mind -->
                <section class="rr-section">
                    <div class="rr-section-num" aria-hidden="true">01</div>
                    <div class="rr-section-content">
                        <h2 class="rr-section-title">Change of Mind Returns</h2>
                        <div class="rr-section-body">
                            <p>
                                Due to the handmade and personal nature of my products, I am unable
                                to accept returns for change of mind. Please review your order carefully
                                before completing your purchase.
                            </p>
                            <p>
                                If you have any questions about a product before you buy, please feel
                                free to <a :href="route('contact')" class="rr-inline-link">get in touch</a> —
                                I'm always happy to help.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="rr-divider" aria-hidden="true">✿</div>

                <!-- Damaged or faulty -->
                <section class="rr-section">
                    <div class="rr-section-num" aria-hidden="true">02</div>
                    <div class="rr-section-content">
                        <h2 class="rr-section-title">Damaged or Faulty Items</h2>
                        <div class="rr-section-body">
                            <p>
                                If your order arrives damaged or faulty, please contact me within
                                <strong>48 hours of delivery</strong> so I can resolve this for you
                                as quickly as possible.
                            </p>

                            <!-- How to report -->
                            <div class="rr-how-to">
                                <p class="rr-how-to-title">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                    Email <a href="mailto:contact@chapterofyou.co.uk"
                                        class="rr-email-link">contact@chapterofyou.co.uk</a> with:
                                </p>
                                <ul class="rr-checklist">
                                    <li>
                                        <span class="rr-check-icon">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5" />
                                            </svg>
                                        </span>
                                        Your order number
                                    </li>
                                    <li>
                                        <span class="rr-check-icon">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5" />
                                            </svg>
                                        </span>
                                        A description of the issue
                                    </li>
                                    <li>
                                        <span class="rr-check-icon">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5" />
                                            </svg>
                                        </span>
                                        Clear photos of the product and packaging
                                    </li>
                                </ul>
                            </div>

                            <p>
                                Once reviewed, I'll resolve this for you as fairly and quickly as possible.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="rr-divider" aria-hidden="true">✿</div>

                <!-- Refunds -->
                <section class="rr-section">
                    <div class="rr-section-num" aria-hidden="true">03</div>
                    <div class="rr-section-content">
                        <h2 class="rr-section-title">Refunds</h2>
                        <div class="rr-section-body">
                            <p>
                                Where a refund is approved, it will be processed back to your
                                <strong>original payment method</strong>. Please allow a few
                                working days for the refund to appear depending on your bank or
                                card provider.
                            </p>
                            <div class="rr-callout">
                                <div class="rr-callout-icon">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 8v4M12 16h.01" />
                                    </svg>
                                </div>
                                <p>
                                    Please note that original payment processing fees charged by
                                    Stripe are non-refundable and cannot be returned as part of any refund.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="rr-divider" aria-hidden="true">✿</div>

                <!-- Additional information -->
                <section class="rr-section">
                    <div class="rr-section-num" aria-hidden="true">04</div>
                    <div class="rr-section-content">
                        <h2 class="rr-section-title">Additional Information</h2>
                        <div class="rr-section-body">
                            <p>
                                To be considered for a return or refund, items must not have been
                                used and should be kept in their <strong>original packaging</strong>
                                where possible.
                            </p>
                            <p>
                                I am not responsible for damage caused by misuse, improper handling,
                                or failure to follow the care and usage instructions provided with
                                your order. Reed diffusers contain fragrance oils — please handle
                                with care and keep upright during storage and transit.
                            </p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- ── Contact banner ── -->
            <div class="rr-contact">
                <div class="rr-contact-inner">
                    <p class="rr-contact-petal" aria-hidden="true">✿</p>
                    <h2 class="rr-contact-title">Got an issue with your order?</h2>
                    <p class="rr-contact-body">
                        I want every Chapter of You order to be something you love. If anything
                        isn't right, please reach out — I'll always do my best to make it right.
                    </p>
                    <a href="mailto:contact@chapterofyou.co.uk" class="rr-contact-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        contact@chapterofyou.co.uk
                    </a>
                    <a :href="route('contact')" class="rr-contact-link">
                        Or use our contact form
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </main>
</template>

<style scoped>
.rr {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.rr-wrap {
    max-width: 760px;
    margin: 0 auto;
    padding: 4rem 1.25rem 6rem;
}

/* ── Header ── */
.rr-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.rr-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
}

.rr-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 6vw, 3rem);
    font-weight: 400;
    font-style: italic;
    color: #2d1a1a;
    line-height: 1.1;
    margin-bottom: 1.25rem;
}

.rr-title-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.rr-rule-line {
    display: block;
    width: 60px;
    height: 1px;
    background: #e5c9c7;
}

.rr-rule-petal {
    font-size: 0.85rem;
    color: #c9a4a4;
}

.rr-intro {
    font-size: 0.97rem;
    color: #6b4f4f;
    line-height: 1.7;
    max-width: 500px;
    margin: 0 auto 0.75rem;
}

.rr-updated {
    font-size: 0.75rem;
    color: #c9a4a4;
    font-style: italic;
}

/* ── Overview cards ── */
.rr-overview {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 2.5rem;
}

@media (max-width: 560px) {
    .rr-overview {
        grid-template-columns: 1fr;
    }
}

.rr-overview-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 16px;
    padding: 1.25rem 1rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.rr-overview-card--no {
    background: #fff5f5;
    border-color: #e8a8a8;
}

.rr-overview-card--yes {
    background: #f2faf2;
    border-color: #a8d8b0;
}

.rr-overview-card--time {
    background: #fff8f0;
    border-color: #f0c898;
}

.rr-overview-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.rr-overview-card--no .rr-overview-icon {
    background: rgba(140, 42, 42, 0.08);
    color: #8c2a2a;
}

.rr-overview-card--yes .rr-overview-icon {
    background: rgba(45, 122, 58, 0.08);
    color: #2d7a3a;
}

.rr-overview-card--time .rr-overview-icon {
    background: rgba(168, 100, 20, 0.08);
    color: #a86414;
}

.rr-overview-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.rr-overview-card--no .rr-overview-label {
    color: #8c2a2a;
}

.rr-overview-card--yes .rr-overview-label {
    color: #2d7a3a;
}

.rr-overview-card--time .rr-overview-label {
    color: #a86414;
}

.rr-overview-value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #2d1a1a;
}

/* ── Body card ── */
.rr-body {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 2rem;
}

/* ── Sections ── */
.rr-section {
    display: flex;
    gap: 1.5rem;
    padding: 2rem;
    align-items: flex-start;
    scroll-margin-top: 88px;
}

@media (max-width: 540px) {
    .rr-section {
        flex-direction: column;
        gap: 0.5rem;
        padding: 1.5rem;
    }
}

.rr-section-num {
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
    .rr-section-num {
        width: auto;
        text-align: left;
    }
}

.rr-section-content {
    flex: 1;
    min-width: 0;
}

.rr-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.35rem;
    font-weight: 500;
    color: #2d1a1a;
    margin-bottom: 0.85rem;
    line-height: 1.2;
}

.rr-section-body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.rr-section-body p {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.75;
}

.rr-section-body strong {
    color: #2d1a1a;
    font-weight: 600;
}

/* Inline link */
.rr-inline-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
}

.rr-inline-link:hover {
    text-decoration: underline;
}

/* How-to box */
.rr-how-to {
    background: #fdf4f3;
    border: 1px solid #e5c9c7;
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.rr-how-to-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: #2d1a1a;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.rr-email-link {
    color: #8c4a50;
    font-weight: 700;
    text-decoration: none;
}

.rr-email-link:hover {
    text-decoration: underline;
}

/* Checklist */
.rr-checklist {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.rr-checklist li {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    line-height: 1.5;
}

.rr-check-icon {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    flex-shrink: 0;
    background: rgba(45, 122, 58, 0.1);
    color: #2d7a3a;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
}

/* Callout */
.rr-callout {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.9rem 1rem;
    background: rgba(140, 74, 80, 0.05);
    border: 1px solid #e5c9c7;
    border-left: 3px solid #8c4a50;
    border-radius: 0 10px 10px 0;
}

.rr-callout-icon {
    color: #8c4a50;
    flex-shrink: 0;
    margin-top: 1px;
}

.rr-callout p {
    font-size: 0.88rem;
    color: #6b4f4f;
    line-height: 1.65;
    margin: 0;
}

/* Divider */
.rr-divider {
    text-align: center;
    color: #e5c9c7;
    font-size: 0.7rem;
    letter-spacing: 0.4rem;
    padding: 0.15rem 0;
    border-top: 1px solid #f0dcd8;
}

/* ── Contact banner ── */
.rr-contact {
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    background: linear-gradient(135deg, #fffafa 0%, #fdf4f3 100%);
    overflow: hidden;
    position: relative;
}

.rr-contact::before {
    content: '✿';
    position: absolute;
    top: -10px;
    left: 20px;
    font-size: 5rem;
    color: #e5c9c7;
    opacity: 0.15;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.rr-contact::after {
    content: '✿';
    position: absolute;
    bottom: -12px;
    right: 20px;
    font-size: 5rem;
    color: #e5c9c7;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.rr-contact-inner {
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.rr-contact-petal {
    font-size: 1.5rem;
    color: #c9a4a4;
    margin-bottom: -0.25rem;
}

.rr-contact-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
}

.rr-contact-body {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.65;
    max-width: 420px;
}

.rr-contact-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.75rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
    margin-top: 0.25rem;
}

.rr-contact-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.rr-contact-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: #8c4a50;
    text-decoration: none;
    font-weight: 600;
    transition: gap 0.2s;
}

.rr-contact-link:hover {
    gap: 0.55rem;
    text-decoration: underline;
}
</style>
