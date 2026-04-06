<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

const seo = useSeoHead({
    title: 'About Me',
    description: 'Meet the founder of Chapter of You — a qualified complementary therapist with a passion for wellbeing, aromatherapy, reflexology and handcrafted luxury products.',
    canonical: '/about',
});

// Scroll-in reveals
const revealEls = ref<Element[]>([]);
const addReveal = (el: any) => { if (el?.$el) revealEls.value.push(el.$el); else if (el) revealEls.value.push(el); };

onMounted(() => {
    const io = new IntersectionObserver(
        entries => entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
        }),
        { threshold: 0.1 }
    );
    revealEls.value.forEach(el => io.observe(el));
});

const qualifications = [
    { label: 'Aromatherapy', icon: '◈' },
    { label: 'Reflexology', icon: '◈' },
    { label: 'Swedish Massage', icon: '◈' },
];
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="ab">

        <!-- ── Masthead ── -->
        <header class="ab-masthead">
            <div class="ab-masthead-rule" aria-hidden="true"></div>
            <div class="ab-masthead-content">
                <p class="ab-kicker">A little about</p>
                <h1 class="ab-heading">The person<br /><em>behind the brand</em></h1>
                <p class="ab-subheading">
                    Chapter of You is not a faceless business.<br />
                    It is one person, one passion, and a genuine belief in the power of self-care.
                </p>
            </div>
            <div class="ab-masthead-ornament" aria-hidden="true">
                <span>✿</span>
                <span class="ab-ornament-line"></span>
                <span>✦</span>
                <span class="ab-ornament-line"></span>
                <span>✿</span>
            </div>
        </header>

        <!-- ── Intro — two-column editorial ── -->
        <section class="ab-intro ab-section">
            <div class="ab-intro-number" aria-hidden="true">01</div>
            <div class="ab-intro-body" :ref="addReveal">
                <h2 class="ab-section-title">Hello, I'm <em>glad you're here</em></h2>
                <p>
                    Welcome to Chapter of You, a small, independent business I built from
                    something deeply personal: the belief that taking care of yourself is not
                    an indulgence. It is a necessity.
                </p>
                <p>
                    I started this brand because I wanted to create products that genuinely
                    support the quiet moments, the ones where scent softly fills your space, you pause, breathe, and
                    give yourself permission to simply <em>be</em>.
                </p>
                <p>
                    Everything I make is handcrafted by me individually with ingredients
                    I have personally chosen for their quality. There is no factory, no
                    shortcuts, and no compromise. Just care, poured into every single bottle.
                </p>
            </div>
        </section>

        <!-- ── Therapist section — the standout ── -->
        <section class="ab-therapist">
            <div class="ab-therapist-inner">
                <div class="ab-therapist-left" :ref="addReveal">
                    <p class="ab-therapist-eyebrow">Qualifications &amp; expertise</p>
                    <h2 class="ab-therapist-title">
                        More than a<br /><em>product maker</em>
                    </h2>
                    <p class="ab-therapist-body">
                        I am a qualified complementary therapist with a deep passion for
                        wellbeing and self-care. My training spans aromatherapy, reflexology
                        and Swedish massage, disciplines that inform every product I create,
                        from the oils I choose to the way I think about scent and its effect
                        on the body and mind.
                    </p>
                    <p class="ab-therapist-body">
                        Through Chapter of You, I am currently focusing on creating luxury
                        aromatherapy and wellness products, while working towards expanding
                        further into the beauty and wellness industry. The products you buy
                        today are the foundation of something much bigger.
                    </p>
                    <!-- Qualification badges -->
                    <div class="ab-quals">
                        <div v-for="q in qualifications" :key="q.label" class="ab-qual-badge">
                            <span class="ab-qual-icon" aria-hidden="true">{{ q.icon }}</span>
                            <span class="ab-qual-label">{{ q.label }}</span>
                        </div>
                    </div>
                </div>
                <div class="ab-therapist-right" :ref="addReveal" aria-hidden="true">
                    <div class="ab-credential-card">
                        <div class="ab-cred-top">
                            <span class="ab-cred-mark">✦</span>
                            <p class="ab-cred-title">Qualified</p>
                            <p class="ab-cred-subtitle">Complementary Therapist</p>
                        </div>
                        <ul class="ab-cred-list">
                            <li>Aromatherapy</li>
                            <li>Reflexology</li>
                            <li>Swedish Massage</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Behind the scenes ── -->
        <section class="ab-section ab-behind">
            <div class="ab-behind-number" aria-hidden="true">02</div>
            <div class="ab-behind-content" :ref="addReveal">
                <h2 class="ab-section-title">Made by hand,<br /><em>made with heart</em></h2>
                <div class="ab-behind-grid">
                    <div class="ab-behind-item">
                        <span class="ab-behind-glyph" aria-hidden="true">◇</span>
                        <h3>Small batch, always</h3>
                        <p>
                            Every diffuser is blended and bottled individually so I can
                            give each one proper attention. Your order is never just a number
                            on a production line.
                        </p>
                    </div>
                    <div class="ab-behind-item">
                        <span class="ab-behind-glyph" aria-hidden="true">◇</span>
                        <h3>Scent as therapy</h3>
                        <p>
                            My aromatherapy training directly shapes how I blend. Fragrance
                            is not just pleasant, it has a genuine effect on mood, memory
                            and wellbeing. I take that seriously.
                        </p>
                    </div>
                    <div class="ab-behind-item">
                        <span class="ab-behind-glyph" aria-hidden="true">◇</span>
                        <h3>Premium ingredients only</h3>
                        <p>
                            I research every ingredient. If I wouldn't feel comfortable
                            using it in a therapeutic context, it doesn't go into a
                            Chapter of You product.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── What's coming ── -->
        <section class="ab-future" :ref="addReveal">
            <div class="ab-future-inner">
                <div class="ab-future-badge">Looking ahead ✦</div>
                <blockquote class="ab-future-quote">
                    "The products you see today are just the beginning. My vision for
                    Chapter of You will extend into a full beauty and wellness
                    experience, with self-care always remaining the main priority."
                </blockquote>
                <p class="ab-future-sig">— Kacey, Chapter of You</p>
            </div>
        </section>

        <!-- ── Personal close ── -->
        <section class="ab-close">
            <div class="ab-close-inner" :ref="addReveal">
                <div class="ab-close-ornament" aria-hidden="true">✿</div>
                <h2 class="ab-close-title">Thank you for<br /><em>being here</em></h2>
                <p class="ab-close-body">
                    Whether you found Chapter of You through a recommendation, a
                    search, or a happy accident, I am genuinely glad you're here.
                    Every order means the world to me, and I hope whatever you choose
                    brings a little calm and beauty into your everyday.
                </p>
                <p class="ab-close-body">
                    If you'd ever like to pop over and say hi, my socials are linked below.
                    If you have any questions about a product or your order please don't hesitate to get
                    in touch.
                </p>
                <a href="mailto:contact@chapterofyou.co.uk" class="ab-close-link">
                    contact@chapterofyou.co.uk
                </a>

                <!-- Social links -->
                <div class="ab-socials">
                    <p class="ab-socials-label">Follow along</p>
                    <div class="ab-socials-row">
                        <a href="https://www.instagram.com/thechapterofyou?igsh=cTM3dDhqdTNuN3Zu&utm_source=qr"
                            target="_blank" rel="noopener noreferrer" class="ab-social-btn"
                            aria-label="Chapter of You on Instagram">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                <circle cx="12" cy="12" r="4" />
                                <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none" />
                            </svg>
                            <span>Instagram</span>
                        </a>
                        <a href="https://www.facebook.com/share/1Ar4e9QxF1/?mibextid=wwXIfr" target="_blank"
                            rel="noopener noreferrer" class="ab-social-btn" aria-label="Chapter of You on Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                            </svg>
                            <span>Facebook</span>
                        </a>
                    </div>
                </div>

                <div class="ab-close-sig" aria-hidden="true">
                    <span class="ab-sig-text">With love,</span>
                    <span class="ab-sig-name">Chapter of You</span>
                </div>
            </div>
        </section>

    </main>

    <Footer />
</template>

<style scoped>
/* ── Base ────────────────────────────────────────────────────────────── */
.ab {
    font-family: 'Nunito', sans-serif;
    color: #2d1a1a;
    background: #fdf4f3;
    overflow-x: hidden;
    padding-top: 64px;
}

/* Scroll reveal */
.ab-intro-body,
.ab-therapist-left,
.ab-therapist-right,
.ab-behind-content,
.ab-future,
.ab-close-inner {
    opacity: 0;
    transform: translateY(32px);
    transition: opacity 0.8s cubic-bezier(.22, .68, 0, 1.1), transform 0.8s cubic-bezier(.22, .68, 0, 1.1);
}

.is-visible {
    opacity: 1 !important;
    transform: none !important;
}

/* ── Masthead ────────────────────────────────────────────────────────── */
.ab-masthead {
    max-width: 860px;
    margin: 0 auto;
    padding: 5rem 1.5rem 3rem;
    text-align: center;
}

.ab-masthead-rule {
    width: 1px;
    height: 60px;
    background: linear-gradient(to bottom, transparent, #e5c9c7);
    margin: 0 auto 2.5rem;
}

.ab-kicker {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 1rem;
}

.ab-heading {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(3rem, 8vw, 5.5rem);
    font-weight: 300;
    line-height: 1.05;
    color: #2d1a1a;
    margin-bottom: 1.5rem;
    letter-spacing: -0.01em;
}

.ab-heading em {
    font-style: italic;
    color: #8c4a50;
}

.ab-subheading {
    font-size: 1rem;
    line-height: 1.8;
    color: #6b4f4f;
    max-width: 520px;
    margin: 0 auto 2.5rem;
}

.ab-masthead-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    color: #c9a4a4;
    font-size: 0.7rem;
}

.ab-ornament-line {
    display: block;
    width: 48px;
    height: 1px;
    background: #e5c9c7;
}

/* ── Shared section styles ─────────────────────────────────────────── */
.ab-section {
    max-width: 1000px;
    margin: 0 auto;
    padding: 5rem 1.5rem;
}

.ab-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    font-weight: 300;
    line-height: 1.2;
    color: #2d1a1a;
    margin-bottom: 1.5rem;
}

.ab-section-title em {
    font-style: italic;
    color: #8c4a50;
}

/* ── Intro ───────────────────────────────────────────────────────────── */
.ab-intro {
    display: grid;
    grid-template-columns: 48px 1fr 180px;
    gap: 3rem;
    align-items: start;
    border-top: 1px solid #f0dcd8;
}

@media (max-width: 760px) {
    .ab-intro {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .ab-intro-number {
        display: none;
    }

    .ab-stamp {
        display: none;
    }
}

.ab-intro-number {
    font-family: 'Cormorant Garamond', serif;
    font-size: 4rem;
    font-weight: 300;
    color: #f0dcd8;
    line-height: 1;
    padding-top: 0.5rem;
    user-select: none;
}

.ab-intro-body p {
    font-size: 1rem;
    line-height: 1.85;
    color: #6b4f4f;
    margin-bottom: 1rem;
}

.ab-intro-body em {
    color: #8c4a50;
    font-style: italic;
}

/* Stamp / seal */
.ab-stamp {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    border: 1.5px solid #e5c9c7;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 0.5rem;
    position: relative;
    flex-shrink: 0;
}

.ab-stamp::before {
    content: '';
    position: absolute;
    inset: 6px;
    border-radius: 50%;
    border: 1px dashed #e5c9c7;
}

.ab-stamp-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #c9a4a4;
    line-height: 1.4;
    text-align: center;
}

.ab-stamp-of {
    font-style: italic;
    font-size: 0.6rem;
    letter-spacing: 0.05em;
}

/* ── Therapist section ───────────────────────────────────────────────── */
.ab-therapist {
    background: #2d1a1a;
    padding: 6rem 1.5rem;
    position: relative;
    overflow: hidden;
}

.ab-therapist::before {
    content: '✿';
    position: absolute;
    top: -20px;
    left: 40px;
    font-size: 12rem;
    color: #3d2424;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.ab-therapist::after {
    content: '✿';
    position: absolute;
    bottom: -20px;
    right: 40px;
    font-size: 10rem;
    color: #3d2424;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.ab-therapist-inner {
    max-width: 1000px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 4rem;
    align-items: center;
    position: relative;
    z-index: 1;
}

@media (max-width: 800px) {
    .ab-therapist-inner {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
}

.ab-therapist-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 1rem;
}

.ab-therapist-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 300;
    line-height: 1.15;
    color: #fffafa;
    margin-bottom: 1.5rem;
}

.ab-therapist-title em {
    font-style: italic;
    color: #e5c9c7;
}

.ab-therapist-body {
    font-size: 0.95rem;
    line-height: 1.85;
    color: #9a7070;
    margin-bottom: 1rem;
}

/* Qualification badges */
.ab-quals {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    margin-top: 1.75rem;
}

.ab-qual-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.9rem;
    border: 1px solid #4a2828;
    border-radius: 999px;
    background: rgba(255, 250, 250, 0.04);
    transition: border-color 0.2s, background 0.2s;
}

.ab-qual-badge:hover {
    border-color: #c9a4a4;
    background: rgba(255, 250, 250, 0.08);
}

.ab-qual-icon {
    font-size: 0.6rem;
    color: #c9a4a4;
}

.ab-qual-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #e5c9c7;
    letter-spacing: 0.03em;
}

/* Credential card */
.ab-credential-card {
    background: rgba(255, 250, 250, 0.04);
    border: 1px solid #4a2828;
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.ab-credential-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #c47078, #a85058, #c47078);
}

.ab-cred-top {
    margin-bottom: 1.5rem;
}

.ab-cred-mark {
    display: block;
    font-size: 1.2rem;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
}

.ab-cred-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.75rem;
    font-style: italic;
    font-weight: 300;
    color: #fffafa;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.ab-cred-subtitle {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #c9a4a4;
}

.ab-cred-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    border-top: 1px solid #3d2424;
    border-bottom: 1px solid #3d2424;
    padding: 1rem 0;
}

.ab-cred-list li {
    font-size: 0.88rem;
    color: #9a7070;
    font-style: italic;
}

/* Seal */
.ab-cred-seal {
    display: flex;
    justify-content: center;
}

.ab-seal-ring {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 1.5px solid #4a2828;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.45rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #5a3838;
    text-align: center;
    padding: 0 0.4rem;
    line-height: 1.3;
}

/* ── Behind the scenes ───────────────────────────────────────────────── */
.ab-behind {
    position: relative;
    border-top: 1px solid #f0dcd8;
    border-bottom: 1px solid #f0dcd8;
    display: grid;
    grid-template-columns: 48px 1fr;
    gap: 3rem;
}

@media (max-width: 760px) {
    .ab-behind {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .ab-behind-number {
        display: none;
    }
}

.ab-behind-number {
    font-family: 'Cormorant Garamond', serif;
    font-size: 4rem;
    font-weight: 300;
    color: #f0dcd8;
    line-height: 1;
    padding-top: 0.5rem;
    user-select: none;
}

.ab-behind-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-top: 2rem;
}

@media (max-width: 640px) {
    .ab-behind-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

.ab-behind-item {
    padding-top: 1rem;
    border-top: 1px solid #f0dcd8;
}

.ab-behind-glyph {
    display: block;
    font-size: 0.6rem;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
    letter-spacing: 0.3em;
}

.ab-behind-item h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #2d1a1a;
    margin-bottom: 0.6rem;
}

.ab-behind-item p {
    font-size: 0.9rem;
    line-height: 1.75;
    color: #6b4f4f;
}

/* ── Future / quote ──────────────────────────────────────────────────── */
.ab-future {
    background: linear-gradient(135deg, #fff5f5, #fdf4f3);
    border-top: 1px solid #f0dcd8;
    border-bottom: 1px solid #f0dcd8;
}

.ab-future-inner {
    max-width: 720px;
    margin: 0 auto;
    padding: 6rem 1.5rem;
    text-align: center;
}

.ab-future-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #8c4a50;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    padding: 0.3rem 1rem;
    margin-bottom: 2.5rem;
    background: #fffafa;
}

.ab-future-quote {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(1.2rem, 3vw, 1.7rem);
    font-style: italic;
    font-weight: 300;
    line-height: 1.65;
    color: #2d1a1a;
    margin: 0 0 1.5rem;
    position: relative;
    padding: 0 1rem;
}

.ab-future-quote::before {
    content: '"';
    position: absolute;
    left: -0.5rem;
    top: -0.5rem;
    font-size: 4rem;
    line-height: 1;
    color: #e5c9c7;
    font-style: normal;
    pointer-events: none;
}

.ab-future-sig {
    font-size: 0.82rem;
    font-style: italic;
    color: #c9a4a4;
    letter-spacing: 0.05em;
}

/* ── Close ───────────────────────────────────────────────────────────── */
.ab-close {
    padding: 6rem 1.5rem 8rem;
}

.ab-close-inner {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.ab-close-ornament {
    font-size: 2rem;
    color: #e5c9c7;
    margin-bottom: 1.5rem;
    display: block;
}

.ab-close-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 300;
    line-height: 1.15;
    color: #2d1a1a;
    margin-bottom: 1.75rem;
}

.ab-close-title em {
    font-style: italic;
    color: #8c4a50;
}

.ab-close-body {
    font-size: 0.97rem;
    line-height: 1.85;
    color: #6b4f4f;
    margin-bottom: 1rem;
}

.ab-close-link {
    display: inline-block;
    margin: 1rem 0 2.5rem;
    font-size: 0.95rem;
    font-weight: 700;
    color: #8c4a50;
    text-decoration: none;
    border-bottom: 1.5px solid #e5c9c7;
    padding-bottom: 0.15rem;
    transition: border-color 0.2s, color 0.2s;
}

.ab-close-link:hover {
    color: #6a3038;
    border-color: #8c4a50;
}

.ab-close-sig {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    margin-top: 0.5rem;
}

.ab-sig-text {
    font-size: 0.82rem;
    font-style: italic;
    color: #c9a4a4;
}

.ab-sig-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.4rem;
    font-style: italic;
    font-weight: 300;
    color: #8c4a50;
    letter-spacing: 0.03em;
}

.ab-socials {
    margin: 0 0 2.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.85rem;
}

.ab-socials-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #c9a4a4;
}

.ab-socials-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ab-social-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1.1rem;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    background: #fffafa;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: border-color 0.2s, background 0.2s, color 0.2s, transform 0.2s;
}

.ab-social-btn:hover {
    border-color: #8c4a50;
    background: #fff5f5;
    color: #8c4a50;
    transform: translateY(-1px);
}
</style>
