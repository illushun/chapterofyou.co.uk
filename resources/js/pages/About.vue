<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';

// Intersection observer for scroll-in reveals
const reveals = ref<HTMLElement[]>([]);
const addReveal = (el: any) => { if (el) reveals.value.push(el); };

onMounted(() => {
    const io = new IntersectionObserver(
        (entries) => entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('revealed');
                io.unobserve(e.target);
            }
        }),
        { threshold: 0.12 }
    );
    reveals.value.forEach(el => io.observe(el));
});
</script>

<template>
    <NavBar />

    <Head title="About Me" />

    <!-- Google Fonts -->
    <component :is="'link'" rel="preconnect" href="https://fonts.googleapis.com" />
    <component :is="'link'" rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,700&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="about-page">

        <!-- ── Hero ── -->
        <section class="hero">
            <div class="hero-bg" aria-hidden="true">
                <!-- Decorative blobs -->
                <div class="blob blob-1"></div>
                <div class="blob blob-2"></div>
                <div class="blob blob-3"></div>
                <!-- Scattered petal marks -->
                <svg class="petal-scatter" viewBox="0 0 800 500" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <ellipse cx="120" cy="80" rx="18" ry="7" fill="currentColor" opacity=".12"
                        transform="rotate(-30 120 80)" />
                    <ellipse cx="680" cy="120" rx="14" ry="5" fill="currentColor" opacity=".10"
                        transform="rotate(40 680 120)" />
                    <ellipse cx="200" cy="380" rx="22" ry="8" fill="currentColor" opacity=".09"
                        transform="rotate(15 200 380)" />
                    <ellipse cx="620" cy="350" rx="16" ry="6" fill="currentColor" opacity=".11"
                        transform="rotate(-20 620 350)" />
                    <ellipse cx="400" cy="460" rx="12" ry="4" fill="currentColor" opacity=".08"
                        transform="rotate(5 400 460)" />
                    <ellipse cx="740" cy="400" rx="20" ry="7" fill="currentColor" opacity=".07"
                        transform="rotate(-45 740 400)" />
                    <ellipse cx="60" cy="300" rx="15" ry="5" fill="currentColor" opacity=".09"
                        transform="rotate(25 60 300)" />
                </svg>
            </div>
            <div class="hero-content">
                <p class="hero-eyebrow">Handcrafted with love</p>
                <h1 class="hero-title">
                    <em>Chapter</em><br />of You
                </h1>
                <p class="hero-tagline">Your chapter, your self-care.</p>
                <div class="hero-divider" aria-hidden="true">
                    <span></span><span class="dot">✦</span><span></span>
                </div>
            </div>
        </section>

        <!-- ── Intro pull-quote ── -->
        <section class="container">
            <div class="pullquote reveal" :ref="addReveal">
                <span class="quote-mark" aria-hidden="true">"</span>
                <blockquote>
                    Every diffuser is poured, blended and finished by hand — because
                    your space deserves something made with genuine care, not a factory line.
                </blockquote>
            </div>
        </section>

        <!-- ── Story section ── -->
        <section class="container story-grid">
            <div class="story-text reveal" :ref="addReveal">
                <span class="section-label">The story</span>
                <h2>A small business<br /><em>with a big heart</em></h2>
                <p>
                    Welcome to Chapter of You — a one-woman business built on
                    a single belief: that proper self-care starts with the small, quiet
                    moments you carve out for yourself.
                </p>
                <p>
                    I founded Chapter of You on the commitment to give you beautiful,
                    considered tools that encourage you to pause, breathe deeply, and
                    intentionally put yourself first. No shortcuts, no compromise — just
                    craftsmanship you can feel.
                </p>
            </div>
            <div class="story-aside reveal" :ref="addReveal">
                <div class="aside-card">
                    <div class="aside-icon" aria-hidden="true"></div>
                    <p class="aside-stat">100%</p>
                    <p class="aside-label">Hand-poured &amp;<br />blended by me</p>
                </div>
                <div class="aside-card">
                    <div class="aside-icon" aria-hidden="true"></div>
                    <p class="aside-stat">Premium</p>
                    <p class="aside-label">Ingredients<br />only</p>
                </div>
                <div class="aside-card">
                    <div class="aside-icon" aria-hidden="true"></div>
                    <p class="aside-stat">Made</p>
                    <p class="aside-label">To order,<br />just for you</p>
                </div>
            </div>
        </section>

        <!-- ── Wavy divider ── -->
        <div class="wave-divider" aria-hidden="true">
            <svg viewBox="0 0 1200 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,30 C150,60 350,0 600,30 C850,60 1050,0 1200,30 L1200,60 L0,60 Z" fill="currentColor" />
            </svg>
        </div>

        <!-- ── Diffusers feature ── -->
        <section class="feature-section">
            <div class="container">
                <div class="feature-header reveal" :ref="addReveal">
                    <span class="section-label light">My collection</span>
                    <h2 class="light">Reed Diffusers<br /><em>designed for your sanctuary</em></h2>
                </div>
                <div class="feature-cards">
                    <div v-for="(card, i) in featureCards" :key="i" class="feature-card reveal" :ref="addReveal"
                        :style="{ animationDelay: `${i * 0.1}s` }">
                        <div class="feature-card-accent" aria-hidden="true">✦</div>
                        <h3>{{ card.title }}</h3>
                        <p>{{ card.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Second wavy divider (flipped) ── -->
        <div class="wave-divider wave-divider--flip" aria-hidden="true">
            <svg viewBox="0 0 1200 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,30 C150,60 350,0 600,30 C850,60 1050,0 1200,30 L1200,60 L0,60 Z" fill="currentColor" />
            </svg>
        </div>

        <!-- ── 2026 teaser ── -->
        <section class="container teaser-section">
            <div class="teaser reveal" :ref="addReveal">
                <span class="teaser-badge">Coming 2026</span>
                <h2>Something beautiful<br /><em>is on its way</em></h2>
                <p>
                    My mission to support your self-care journey is growing.
                    In 2026 I'll be launching a curated range of dedicated beauty services —
                    an expansion built on the same love and attention to detail you already know.
                </p>
                <p>
                    Keep an eye on this space. The next chapter is going to be wonderful.
                </p>
            </div>
        </section>

        <!-- ── Community CTA ── -->
        <section class="container cta-section">
            <div class="cta-card reveal" :ref="addReveal">
                <div class="cta-petals" aria-hidden="true">
                    <span>✿</span><span>✦</span><span>✿</span><span>✦</span><span>✿</span>
                </div>
                <h2>Join my little community</h2>
                <p>
                    At Chapter of You you're more than a customer — you're part of a community
                    dedicated to intentional self-care. Explore my collection and let me
                    bring a little calm to your corner of the world.
                </p>
                <a href="/products" class="cta-btn">
                    Explore the collection
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </section>

    </main>

    <Footer />
</template>

<script lang="ts">
export default {
    data() {
        return {
            featureCards: [
                {
                    icon: '✿',
                    title: 'Meticulously blended',
                    body: 'Every scent is carefully crafted from premium fragrance oils, balanced by hand to fill your home with a gentle, lasting aroma.',
                },
                {
                    icon: '✦',
                    title: 'Poured with intention',
                    body: 'Each diffuser is hand-finished individually — never rushed, never mass-produced. Your order is made to order, just for you.',
                },
                {
                    icon: '◇',
                    title: 'Quality ingredients',
                    body: 'Only the highest quality reed diffuser base and fragrance oils make it into my products. Nothing unnecessary, nothing cheap.',
                },
            ],
        };
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,700&family=Nunito:wght@300;400;500;600&display=swap');

/* ── Base ── */
.about-page {
    font-family: 'Nunito', sans-serif;
    color: #2d1a1a;
    overflow-x: hidden;
    padding-top: 64px;
    /* navbar height */
}

.container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ── Scroll reveal ── */
.reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.75s cubic-bezier(.22, .68, 0, 1.2), transform 0.75s cubic-bezier(.22, .68, 0, 1.2);
}

.reveal.revealed {
    opacity: 1;
    transform: none;
}

/* ── Hero ── */
.hero {
    position: relative;
    min-height: 92vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
    background: #fdf4f3;
}

.hero-bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
    color: #8c4a50;
}

.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.55;
}

.blob-1 {
    width: 500px;
    height: 500px;
    background: #e5c9c7;
    top: -100px;
    left: -120px;
}

.blob-2 {
    width: 400px;
    height: 400px;
    background: #e5c9c7;
    bottom: -80px;
    right: -100px;
}

.blob-3 {
    width: 300px;
    height: 300px;
    background: #e5c9c7;
    top: 50%;
    left: 55%;
    transform: translate(-50%, -50%);
    opacity: 0.25;
}

.petal-scatter {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.hero-content {
    position: relative;
    z-index: 1;
    padding: 3rem 1.5rem;
    max-width: 700px;
}

.hero-eyebrow {
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    font-style: italic;
    letter-spacing: 0.12em;
    color: #8c4a50;
    margin-bottom: 1.2rem;
    animation: fadeUp 0.9s ease both;
}

.hero-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(4rem, 12vw, 9rem);
    font-weight: 900;
    line-height: 0.92;
    color: #2d1a1a;
    margin-bottom: 1.5rem;
    animation: fadeUp 0.9s 0.15s ease both;
}

.hero-title em {
    font-style: italic;
    color: #8c4a50;
}

.hero-tagline {
    font-family: 'Nunito', sans-serif;
    font-style: italic;
    font-size: 1.25rem;
    color: #6b4f4f;
    margin-bottom: 2.5rem;
    animation: fadeUp 0.9s 0.3s ease both;
}

.hero-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    animation: fadeUp 0.9s 0.45s ease both;
}

.hero-divider span:not(.dot) {
    display: block;
    width: 80px;
    height: 1.5px;
    background: #e5c9c7;
    opacity: 0.4;
}

.hero-divider .dot {
    color: #8c4a50;
    font-size: 1rem;
}

/* ── Pull-quote ── */
.pullquote {
    max-width: 760px;
    margin: 5rem auto;
    text-align: center;
    position: relative;
    padding: 2.5rem 2rem;
}

.pullquote::before,
.pullquote::after {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 2px;
    background: #e5c9c7;
    opacity: 0.3;
}

.pullquote::before {
    top: 0;
}

.pullquote::after {
    bottom: 0;
}

.quote-mark {
    display: block;
    font-family: 'Cormorant Garamond', serif;
    font-size: 6rem;
    line-height: 0.6;
    color: #8c4a50;
    opacity: 0.25;
    margin-bottom: 0.5rem;
    user-select: none;
}

blockquote {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(1.3rem, 3vw, 1.75rem);
    font-style: italic;
    font-weight: 400;
    line-height: 1.55;
    color: #2d1a1a;
    margin: 0;
}

/* ── Story grid ── */
.story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    padding-top: 2rem;
    padding-bottom: 5rem;
}

@media (max-width: 768px) {
    .story-grid {
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }
}

.section-label {
    display: inline-block;
    font-family: 'Nunito', sans-serif;
    font-size: 0.78rem;
    font-style: italic;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #8c4a50;
    margin-bottom: 1rem;
}

.section-label.light {
    color: rgba(255, 255, 255, 0.7);
}

.story-text h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 700;
    line-height: 1.2;
    color: #2d1a1a;
    margin-bottom: 1.5rem;
}

.story-text h2 em {
    font-style: italic;
    color: #8c4a50;
}

.story-text p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #6b4f4f;
    margin-bottom: 1rem;
}

.story-aside {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.aside-card:first-child {
    grid-column: 1 / -1;
}

.aside-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 16px;
    padding: 1.75rem 1.25rem;
    text-align: center;
    position: relative;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    transition: transform 0.2s, box-shadow 0.2s;
}

.aside-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 24px rgba(229, 201, 199, 0.5);
}

.aside-stat {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 900;
    color: #8c4a50;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.aside-label {
    font-size: 0.85rem;
    font-style: italic;
    color: #6b4f4f;
    line-height: 1.4;
}

/* ── Wave divider ── */
.wave-divider {
    color: #8c4a50;
    opacity: 0.12;
    line-height: 0;
    margin-bottom: -1px;
}

.wave-divider svg {
    width: 100%;
    height: 50px;
    display: block;
}

.wave-divider--flip {
    transform: scaleY(-1);
    margin-top: -1px;
    margin-bottom: 0;
}

/* ── Feature section ── */
.feature-section {
    background: #e5c9c7;
    padding: 5rem 0;
    margin: 0;
}

.feature-header {
    text-align: center;
    margin-bottom: 3.5rem;
}

.feature-header h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}

.feature-header h2 em {
    font-style: italic;
    opacity: 0.85;
}

.feature-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .feature-cards {
        grid-template-columns: 1fr;
    }
}

.feature-card {
    background: rgba(255, 255, 255, 0.12);
    border: 1.5px solid rgba(255, 255, 255, 0.25);
    border-radius: 16px;
    padding: 2rem 1.5rem;
    backdrop-filter: blur(4px);
    transition: background 0.25s, transform 0.25s;
}

.feature-card:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-4px);
}

.feature-card h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.75rem;
}

.feature-card p {
    font-size: 0.95rem;
    line-height: 1.75;
    color: rgba(255, 255, 255, 0.82);
}

/* ── Teaser ── */
.teaser-section {
    padding: 6rem 1.5rem;
    max-width: 720px;
    text-align: center;
}

.teaser-badge {
    display: inline-block;
    background: #e5c9c7;
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-style: italic;
    font-size: 0.9rem;
    padding: 0.35rem 1.1rem;
    border-radius: 999px;
    margin-bottom: 1.5rem;
    letter-spacing: 0.04em;
}

.teaser h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 700;
    line-height: 1.2;
    color: #2d1a1a;
    margin-bottom: 1.5rem;
}

.teaser h2 em {
    font-style: italic;
    color: #8c4a50;
}

.teaser p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #6b4f4f;
    margin-bottom: 1rem;
}

/* ── CTA ── */
.cta-section {
    padding-bottom: 7rem;
}

.cta-card {
    text-align: center;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    padding: 4rem 2.5rem;
    position: relative;
    background: #fffafa;
    box-shadow: 0 6px 24px rgba(229, 201, 199, 0.5);
    overflow: hidden;
}

.cta-petals {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    font-size: 1.5rem;
    color: #8c4a50;
    opacity: 0.3;
    margin-bottom: 1.5rem;
    letter-spacing: 0.5rem;
}

.cta-card h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.8rem, 3.5vw, 2.5rem);
    font-weight: 700;
    color: #2d1a1a;
    margin-bottom: 1rem;
}

.cta-card p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #6b4f4f;
    max-width: 560px;
    margin: 0 auto 2rem;
}

.cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    background: #e5c9c7;
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.85rem 2.25rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    box-shadow: 3px 3px 0 var(--copy);
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}

.cta-btn:hover {
    transform: translateY(-3px);
    box-shadow: 5px 5px 0 var(--copy);
}

/* ── Keyframes ── */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}
</style>
