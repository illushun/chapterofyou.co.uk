<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link } from '@inertiajs/vue3';

const seo = useSeoHead({
    title: 'About Chapter of You',
    description:
        'Meet the founder of Chapter of You, a VTCT Level 3 qualified complementary therapist creating handmade home fragrance from individual oils.',
    canonical: '/about',
});

const principles = [
    [
        'Blended from individual oils',
        'Every fragrance is built from individual oils rather than bought as a ready-made blend.',
    ],
    [
        'Made in small batches',
        'Products are prepared with close attention to the blend, finish and quality of every bottle.',
    ],
    [
        'Created for your space',
        'Fragrance notes, room and mood guidance help you choose a scent that suits your home.',
    ],
];
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />
    <main class="about coy-storefront">
        <header class="hero">
            <div class="hero-grid container">
                <div>
                    <p class="coy-eyebrow">About Chapter of You</p>
                    <h1>Home fragrance, made with care and knowledge</h1>
                    <p class="lead">
                        Chapter of You is an independent home-fragrance brand
                        founded by Kacey, a qualified complementary therapist
                        with a practical understanding of aromatherapy and
                        wellbeing.
                    </p>
                    <div class="actions">
                        <Link :href="route('products')" class="button"
                            >Shop fragrances</Link
                        >
                        <Link
                            :href="route('scent-finder.index')"
                            class="text-link"
                            >Find your scent
                            <span aria-hidden="true">→</span></Link
                        >
                    </div>
                </div>
                <aside
                    class="credential"
                    aria-label="Professional qualification"
                >
                    <p class="label">Professional training</p>
                    <h2>VTCT Level 3 Diploma</h2>
                    <p>Complementary therapies</p>
                    <ul>
                        <li>Aromatherapy</li>
                        <li>Reflexology</li>
                        <li>Swedish massage</li>
                    </ul>
                </aside>
            </div>
        </header>

        <section class="section">
            <div class="split container">
                <div>
                    <p class="coy-eyebrow">The story</p>
                    <h2>A personal approach to home fragrance</h2>
                </div>
                <div class="copy">
                    <p>
                        I created Chapter of You from a belief that self-care
                        should feel personal and easy to make part of everyday
                        life. Scent can change how a room feels, bring back a
                        memory, or help mark a moment to pause.
                    </p>
                    <p>
                        My training in complementary therapies includes
                        aromatherapy, reflexology and Swedish massage. That
                        knowledge shapes how I think about fragrance, mood and
                        the experience a product creates in your home.
                    </p>
                    <p>
                        Every product is made with ingredients I have selected
                        for their quality. Chapter of You blends each fragrance
                        from individual oils, giving every scent its own
                        carefully considered character.
                    </p>
                </div>
            </div>
        </section>

        <section class="section principles-section">
            <div class="container">
                <div class="section-heading">
                    <p class="coy-eyebrow">What makes it different</p>
                    <h2>Thoughtful from blend to bottle</h2>
                </div>
                <div class="principles">
                    <article
                        v-for="(principle, index) in principles"
                        :key="principle[0]"
                    >
                        <span>0{{ index + 1 }}</span>
                        <h3>{{ principle[0] }}</h3>
                        <p>{{ principle[1] }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section future">
            <div class="split container">
                <div>
                    <p class="coy-eyebrow">Looking ahead</p>
                    <h2>More ways to make time for yourself</h2>
                </div>
                <div class="copy">
                    <p>
                        Home fragrance is the first chapter. In time, the brand
                        will grow to include beauty and nail treatments, while
                        keeping the same focus on personal choice, quality and
                        self-care.
                    </p>
                    <p>
                        For now, each fragrance is made to help you shape the
                        atmosphere of your own space, in your own way.
                    </p>
                </div>
            </div>
        </section>

        <section class="cta">
            <div class="cta-inner container">
                <div>
                    <p class="coy-eyebrow">Choose your next fragrance</p>
                    <h2>Find a scent for your home</h2>
                    <p>
                        Browse every fragrance or answer three quick questions
                        for tailored suggestions.
                    </p>
                </div>
                <div class="actions">
                    <Link :href="route('products')" class="button"
                        >Shop all products</Link
                    ><Link :href="route('scent-finder.index')" class="text-link"
                        >Use the scent finder
                        <span aria-hidden="true">→</span></Link
                    >
                </div>
            </div>
        </section>
    </main>
    <Footer />
</template>

<style scoped>
.about {
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
}
.container {
    width: min(calc(100% - var(--coy-gutter) * 2), var(--coy-container-lg));
    margin-inline: auto;
}
.hero {
    padding: clamp(3rem, 7vw, 6rem) 0;
    border-bottom: 1px solid var(--coy-color-border);
}
.hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(17rem, 0.65fr);
    gap: clamp(2rem, 7vw, 7rem);
    align-items: end;
}
.hero-grid > div {
    max-width: 47rem;
}
.about h1,
.about h2,
.about h3 {
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-weight: 500;
}
.about h1 {
    margin: 0.5rem 0 1.25rem;
    font-size: clamp(3rem, 7vw, 5.5rem);
    line-height: 0.98;
    letter-spacing: -0.04em;
}
.about h2 {
    max-width: 12ch;
    margin-top: 0.55rem;
    font-size: clamp(2.25rem, 4.5vw, 3.75rem);
    line-height: 1.04;
    letter-spacing: -0.03em;
}
.lead {
    max-width: 42rem;
    font-size: clamp(1.1rem, 1.5vw, 1.25rem);
    line-height: 1.65;
}
.actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1.25rem;
    margin-top: 2rem;
}
.button,
.text-link {
    min-height: var(--coy-control-height);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    text-decoration: none;
}
.button {
    padding: 0.75rem 1.4rem;
    border: 1px solid var(--coy-color-heading);
    border-radius: var(--coy-radius-sm);
    background: var(--coy-color-heading);
    color: var(--coy-color-on-accent);
}
.text-link {
    border-bottom: 1px solid currentColor;
    color: var(--coy-color-heading);
}
.text-link span {
    transition: transform var(--coy-duration-fast) var(--coy-ease);
}
.text-link:hover span {
    transform: translateX(0.2rem);
}
.credential {
    padding: clamp(1.5rem, 3vw, 2rem);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    background: var(--coy-color-surface);
}
.credential .label {
    margin-bottom: 1rem;
    color: var(--coy-color-accent);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: var(--coy-tracking-label);
    text-transform: uppercase;
}
.credential h2 {
    max-width: none;
    margin: 0;
    font-size: 1.65rem;
    letter-spacing: 0;
}
.credential > p:not(.label) {
    margin-top: 0.25rem;
}
.credential ul {
    display: grid;
    gap: 0.6rem;
    margin-top: 1.5rem;
    padding: 1.25rem 0 0;
    border-top: 1px solid var(--coy-color-border);
    list-style: none;
}
.credential li {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 1rem;
}
.credential li:before {
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 50%;
    background: var(--coy-color-accent);
    content: '';
}
.section {
    padding-block: var(--coy-section-space);
}
.split {
    display: grid;
    grid-template-columns: minmax(16rem, 0.8fr) minmax(0, 1fr);
    gap: clamp(2rem, 8vw, 8rem);
}
.copy {
    max-width: 42rem;
}
.copy p {
    font-size: 1.075rem;
    line-height: 1.75;
}
.copy p + p {
    margin-top: 1.25rem;
}
.principles-section {
    border-block: 1px solid var(--coy-color-border);
    background: var(--coy-color-surface-soft);
}
.section-heading {
    margin-bottom: clamp(2rem, 5vw, 3.5rem);
}
.section-heading h2 {
    max-width: 16ch;
}
.principles {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.principles article {
    padding: clamp(1.25rem, 3vw, 2rem);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-md);
    background: var(--coy-color-surface);
}
.principles article > span {
    color: var(--coy-color-accent);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.1em;
}
.principles h3 {
    margin: 1.5rem 0 0.65rem;
    font-size: 1.55rem;
    line-height: 1.15;
}
.principles p {
    font-size: 1rem;
    line-height: 1.65;
}
.future {
    background: var(--coy-color-surface);
}
.cta {
    padding-block: clamp(3rem, 7vw, 5rem);
    background: var(--coy-color-heading);
}
.cta-inner {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 2rem;
}
.cta h2 {
    max-width: none;
    margin-bottom: 0.65rem;
}
.cta p,
.cta h2,
.cta .coy-eyebrow,
.cta .text-link {
    color: var(--coy-color-on-accent);
}
.cta p {
    max-width: 38rem;
    font-size: 1.05rem;
    line-height: 1.65;
    opacity: 0.82;
}
.cta .actions {
    flex-shrink: 0;
    margin: 0;
}
.cta .button {
    border-color: var(--coy-color-on-accent);
    background: var(--coy-color-on-accent);
    color: var(--coy-color-heading);
}
@media (max-width: 800px) {
    .hero-grid,
    .split {
        grid-template-columns: 1fr;
    }
    .credential {
        max-width: 30rem;
    }
    .principles {
        grid-template-columns: 1fr;
    }
    .cta-inner {
        align-items: flex-start;
        flex-direction: column;
    }
    .cta .actions {
        margin-top: 0.5rem;
    }
}
@media (max-width: 520px) {
    .hero {
        padding-top: 2rem;
    }
    .actions {
        align-items: stretch;
        flex-direction: column;
    }
    .text-link {
        align-self: flex-start;
    }
    .cta .actions,
    .cta .button {
        width: 100%;
    }
}
@media (prefers-reduced-motion: reduce) {
    .text-link span {
        transition: none;
    }
}
</style>
