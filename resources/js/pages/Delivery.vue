<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head } from '@inertiajs/vue3';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

const seo = useSeoHead({
    title: 'Delivery Information',
    description: 'Everything you need to know about delivery at Chapter of You. Orders dispatched within 2–3 working days with tracking included.',
    canonical: '/delivery',
});
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="dv">
        <div class="dv-wrap">

            <!-- ── Page header ── -->
            <header class="dv-header">
                <p class="dv-eyebrow">Chapter of You</p>
                <h1 class="dv-title">Delivery Information</h1>
                <div class="dv-title-rule">
                    <span class="dv-rule-line"></span>
                    <span class="dv-rule-petal">✿</span>
                    <span class="dv-rule-line"></span>
                </div>
            </header>

            <!-- ── Content grid ── -->
            <div class="dv-grid">

                <!-- Processing time -->
                <div class="dv-card">
                    <div class="dv-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <h2 class="dv-card-title">Processing Time</h2>
                    <p class="dv-card-body">
                        I aim to prepare and dispatch all orders as quickly as possible. Orders are typically processed
                        within
                        <strong>2–3 working days</strong>. During busier periods, this may take slightly longer — I
                        appreciate your patience.
                    </p>
                </div>

                <!-- Dispatch & tracking -->
                <div class="dv-card">
                    <div class="dv-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2 11 13" />
                            <path d="M22 2 15 22 11 13 2 9l20-7z" />
                        </svg>
                    </div>
                    <h2 class="dv-card-title">Dispatch &amp; Tracking</h2>
                    <p class="dv-card-body">
                        Once your order has been dispatched, you will receive a confirmation email with your
                        <strong>tracking details</strong> so you can follow your package every step of the way.
                    </p>
                </div>

                <!-- Delivery times -->
                <div class="dv-card">
                    <div class="dv-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13" rx="1" />
                            <path d="M16 8h4l3 3v5h-7V8z" />
                            <circle cx="5.5" cy="18.5" r="2.5" />
                            <circle cx="18.5" cy="18.5" r="2.5" />
                        </svg>
                    </div>
                    <h2 class="dv-card-title">Delivery Times</h2>
                    <p class="dv-card-body">
                        Delivery times may vary depending on your location and the courier service used.
                        Please allow additional time during <strong>peak periods</strong> or in the event of any
                        unforeseen delays.
                    </p>
                </div>

                <!-- Free delivery threshold -->
                <div class="dv-card dv-card--highlight">
                    <div class="dv-card-icon dv-card-icon--highlight">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </div>
                    <h2 class="dv-card-title">Free Delivery</h2>
                    <p class="dv-card-body">
                        Enjoy <strong>free UK delivery</strong> on all orders over <strong>£50</strong>. A small treat
                        to say thank you for choosing Chapter of You.
                    </p>
                </div>

            </div>

            <!-- ── Contact banner ── -->
            <div class="dv-contact">
                <div class="dv-contact-inner">
                    <p class="dv-contact-petal" aria-hidden="true">✿</p>
                    <h2 class="dv-contact-title">Questions about your order?</h2>
                    <p class="dv-contact-body">
                        I'm always happy to help. Feel free to get in touch and I'll get back to you as soon as
                        possible.
                    </p>
                    <a href="mailto:contact@chapterofyou.co.uk" class="dv-contact-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        contact@chapterofyou.co.uk
                    </a>
                    <a :href="route('contact')" class="dv-contact-link">
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
.dv {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.dv-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 4rem 1.25rem 6rem;
}

/* ── Header ── */
.dv-header {
    text-align: center;
    margin-bottom: 3.5rem;
}

.dv-eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
}

.dv-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2.2rem, 6vw, 3.2rem);
    font-weight: 400;
    font-style: italic;
    color: #2d1a1a;
    line-height: 1.1;
    margin-bottom: 1.25rem;
}

.dv-title-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.dv-rule-line {
    display: block;
    width: 60px;
    height: 1px;
    background: #e5c9c7;
}

.dv-rule-petal {
    font-size: 0.85rem;
    color: #c9a4a4;
}

/* ── Card grid ── */
.dv-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 3rem;
}

@media (max-width: 620px) {
    .dv-grid {
        grid-template-columns: 1fr;
    }
}

/* ── Cards ── */
.dv-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    padding: 1.75rem 1.5rem;
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.2s, transform 0.2s;
}

.dv-card:hover {
    box-shadow: 0 6px 24px rgba(229, 201, 199, 0.45);
    transform: translateY(-2px);
}

/* Watermark petal */
.dv-card::after {
    content: '✿';
    position: absolute;
    bottom: -8px;
    right: 6px;
    font-size: 4rem;
    color: #e5c9c7;
    opacity: 0.18;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.dv-card--highlight {
    background: linear-gradient(135deg, #fff5f6 0%, #fffafa 100%);
    border-color: #d4a0aa;
}

.dv-card--highlight::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #c47078, #a85058);
    border-radius: 20px 0 0 20px;
}

/* Card icon */
.dv-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(140, 74, 80, 0.08);
    border: 1px solid #e5c9c7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8c4a50;
    margin-bottom: 1rem;
    flex-shrink: 0;
}

.dv-card-icon--highlight {
    background: linear-gradient(135deg, rgba(196, 112, 120, 0.15), rgba(168, 80, 88, 0.1));
    border-color: #d4a0aa;
}

.dv-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-weight: 500;
    color: #2d1a1a;
    margin-bottom: 0.6rem;
    line-height: 1.2;
}

.dv-card-body {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.75;
}

.dv-card-body strong {
    color: #2d1a1a;
    font-weight: 600;
}

/* ── Contact banner ── */
.dv-contact {
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    background: linear-gradient(135deg, #fffafa 0%, #fdf4f3 100%);
    overflow: hidden;
    position: relative;
}

.dv-contact::before {
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

.dv-contact::after {
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

.dv-contact-inner {
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.dv-contact-petal {
    font-size: 1.5rem;
    color: #c9a4a4;
    margin-bottom: -0.25rem;
}

.dv-contact-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
}

.dv-contact-body {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.65;
    max-width: 420px;
}

.dv-contact-btn {
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

.dv-contact-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.dv-contact-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: #8c4a50;
    text-decoration: none;
    font-weight: 600;
    transition: gap 0.2s;
}

.dv-contact-link:hover {
    gap: 0.55rem;
    text-decoration: underline;
}
</style>
