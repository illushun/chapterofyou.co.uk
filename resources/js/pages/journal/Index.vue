<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import CoyBreadcrumbs from '@/components/ui/coy/CoyBreadcrumbs.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link } from '@inertiajs/vue3';

defineProps<{
    posts: {
        data: Array<{
            id: number;
            title: string;
            slug: string;
            excerpt: string | null;
            cover_image: string | null;
            tags: string[];
            published_at: string;
            reading_time: number;
            views: number;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: { total?: number };
    };
}>();

const seo = useSeoHead({
    title: 'Journal',
    description:
        'Tips, guides and inspiration for home fragrance, aromatherapy and self-care from Chapter of You.',
    canonical: '/journal',
    ogType: 'website',
});
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />
    <main class="jl coy-storefront">
        <header class="coy-page-header">
            <div class="coy-container coy-page-header__inner">
                <div>
                    <CoyBreadcrumbs
                        :items="[
                            { label: 'Home', href: '/' },
                            { label: 'Journal' },
                        ]"
                    />
                    <h1 class="coy-page-header__title">Journal</h1>
                    <p class="coy-page-header__meta">
                        Notes on fragrance, wellbeing and creating a thoughtful
                        home.
                    </p>
                </div>
            </div>
        </header>

        <div class="coy-container jl-content">
            <div class="jl-heading">
                <div>
                    <p class="coy-eyebrow">Ideas for your space</p>
                    <h2>Latest from the journal</h2>
                </div>
                <p v-if="posts.meta?.total" class="jl-count">
                    {{ posts.meta.total }}
                    {{ posts.meta.total === 1 ? 'article' : 'articles' }}
                </p>
            </div>

            <section v-if="!posts.data.length" class="jl-empty">
                <span aria-hidden="true">✦</span>
                <h2>New stories are on their way</h2>
                <p>
                    Check back soon for fragrance guides and thoughtful ideas.
                </p>
                <Link href="/products" class="jl-empty-link"
                    >Explore fragrances</Link
                >
            </section>

            <div v-else class="jl-grid">
                <article
                    v-for="(post, index) in posts.data"
                    :key="post.id"
                    class="jl-card"
                    :class="{ 'jl-card--featured': index === 0 }"
                >
                    <Link
                        :href="`/journal/${post.slug}`"
                        class="jl-card-image"
                        :aria-label="`Read ${post.title}`"
                    >
                        <img
                            v-if="post.cover_image"
                            :src="post.cover_image"
                            :alt="post.title"
                            class="jl-card-image-content"
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="jl-card-placeholder"
                            aria-hidden="true"
                        >
                            ✦
                        </div>
                    </Link>
                    <div class="jl-card-body">
                        <div class="jl-card-topline">
                            <span v-if="index === 0" class="jl-featured-label"
                                >Featured</span
                            >
                            <div v-if="post.tags.length" class="jl-tags">
                                <span
                                    v-for="tag in post.tags.slice(0, 2)"
                                    :key="tag"
                                    class="jl-tag"
                                    >{{ tag }}</span
                                >
                            </div>
                        </div>
                        <h2 class="jl-card-title">
                            <Link :href="`/journal/${post.slug}`">{{
                                post.title
                            }}</Link>
                        </h2>
                        <p v-if="post.excerpt" class="jl-card-excerpt">
                            {{ post.excerpt }}
                        </p>
                        <div class="jl-card-meta">
                            <span>{{ post.published_at }}</span
                            ><span aria-hidden="true">•</span
                            ><span>{{ post.reading_time }} min read</span>
                        </div>
                        <Link
                            :href="`/journal/${post.slug}`"
                            class="jl-read-more"
                            >Read article
                            <svg aria-hidden="true" viewBox="0 0 24 24">
                                <path d="M5 12h14M12 5l7 7-7 7" /></svg
                        ></Link>
                    </div>
                </article>
            </div>

            <nav
                v-if="posts.links?.length > 3"
                class="jl-pagination"
                aria-label="Journal pagination"
            >
                <template v-for="link in posts.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="jl-page-btn"
                        :class="{ 'jl-page-btn--active': link.active }"
                        ><span v-html="link.label"
                    /></Link>
                    <span
                        v-else
                        class="jl-page-btn jl-page-btn--disabled"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </main>
    <Footer />
</template>

<style scoped>
.jl {
    min-height: 100vh;
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
}
.jl-content {
    padding-block: clamp(2.5rem, 5vw, 4rem) clamp(4rem, 7vw, 6rem);
}
.jl-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: var(--coy-space-5);
    margin-bottom: var(--coy-space-6);
    padding-bottom: var(--coy-space-4);
    border-bottom: 1px solid var(--coy-color-border);
}
.jl-heading h2 {
    margin: var(--coy-space-1) 0 0;
    color: var(--coy-color-heading);
    font: 500 clamp(2rem, 4vw, 3rem) / 1.08 var(--coy-font-display);
}
.jl-count {
    margin: 0 0 var(--coy-space-1);
    font-size: var(--coy-text-sm);
}
.jl-empty {
    display: grid;
    justify-items: center;
    padding: clamp(3rem, 8vw, 6rem) var(--coy-space-5);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-lg);
    text-align: center;
}
.jl-empty > span {
    color: var(--coy-color-rose-gold);
    font-size: 2rem;
}
.jl-empty h2 {
    margin: var(--coy-space-3) 0 var(--coy-space-2);
    color: var(--coy-color-heading);
    font: 500 clamp(1.75rem, 4vw, 2.5rem) / 1.1 var(--coy-font-display);
}
.jl-empty p {
    margin: 0;
}
.jl-empty-link {
    margin-top: var(--coy-space-5);
    padding: 0.7rem 1.2rem;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-radius: var(--coy-radius-pill);
    font-weight: 600;
    text-decoration: none;
}
.jl-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(1.5rem, 3vw, 2.25rem);
}
.jl-card {
    min-width: 0;
    display: flex;
    flex-direction: column;
}
.jl-card--featured {
    grid-column: 1/-1;
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(20rem, 0.85fr);
    overflow: hidden;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
}
.jl-card-image {
    display: block;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border-radius: var(--coy-radius-md);
}
.jl-card--featured .jl-card-image {
    min-height: 24rem;
    aspect-ratio: auto;
    border-radius: 0;
}
.jl-card-image-content {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform 0.45s var(--coy-ease);
}
.jl-card:hover .jl-card-image-content {
    transform: scale(1.035);
}
.jl-card-placeholder {
    width: 100%;
    height: 100%;
    display: grid;
    place-items: center;
    color: var(--coy-color-rose-gold);
    font-size: 2.75rem;
}
.jl-card-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    padding-top: var(--coy-space-4);
}
.jl-card--featured .jl-card-body {
    justify-content: center;
    padding: clamp(2rem, 5vw, 4rem);
}
.jl-card-topline {
    min-height: 1.6rem;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--coy-space-2);
}
.jl-featured-label {
    padding: 0.25rem 0.65rem;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-radius: var(--coy-radius-pill);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    line-height: 1.2;
    text-transform: uppercase;
}
.jl-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--coy-space-2);
}
.jl-tag {
    color: var(--coy-color-accent);
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: var(--coy-tracking-label);
    text-transform: uppercase;
}
.jl-card-title {
    margin: var(--coy-space-3) 0 0;
    color: var(--coy-color-heading);
    font: 500 clamp(1.5rem, 2.5vw, 2rem) / 1.15 var(--coy-font-display);
}
.jl-card--featured .jl-card-title {
    font-size: clamp(2.25rem, 4vw, 3.5rem);
    line-height: 1.05;
}
.jl-card-title a {
    color: inherit;
    text-decoration: none;
}
.jl-card-title a:hover {
    color: var(--coy-color-accent);
}
.jl-card-excerpt {
    display: -webkit-box;
    overflow: hidden;
    margin: var(--coy-space-3) 0 0;
    color: var(--coy-color-text);
    font-size: var(--coy-text-sm);
    line-height: 1.6;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}
.jl-card-meta {
    display: flex;
    align-items: center;
    gap: var(--coy-space-2);
    margin-top: var(--coy-space-4);
    color: var(--coy-color-text);
    font-size: 0.875rem;
}
.jl-read-more {
    display: inline-flex;
    align-items: center;
    gap: var(--coy-space-2);
    margin-top: var(--coy-space-4);
    color: var(--coy-color-accent);
    font-size: var(--coy-text-sm);
    font-weight: 600;
    text-decoration: none;
    transition: gap var(--coy-duration-base) var(--coy-ease);
}
.jl-read-more:hover {
    gap: var(--coy-space-3);
}
.jl-read-more svg {
    width: 0.875rem;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 2.5;
}
.jl-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: var(--coy-space-2);
    margin-top: var(--coy-space-7);
}
.jl-page-btn {
    min-width: 2.5rem;
    height: 2.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 var(--coy-space-3);
    color: var(--coy-color-text);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-pill);
    font-size: 0.875rem;
    text-decoration: none;
    transition: all var(--coy-duration-fast) var(--coy-ease);
}
.jl-page-btn:hover {
    color: var(--coy-color-accent);
    border-color: var(--coy-color-accent);
}
.jl-page-btn--active {
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-color: var(--coy-color-accent);
}
.jl-page-btn--disabled {
    opacity: 0.4;
}
@media (max-width: 860px) {
    .jl-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .jl-card--featured {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 640px) {
    .jl-heading {
        align-items: start;
        flex-direction: column;
    }
    .jl-grid,
    .jl-card--featured {
        grid-template-columns: 1fr;
    }
    .jl-card--featured .jl-card-image {
        min-height: 0;
        aspect-ratio: 4/3;
    }
    .jl-card--featured .jl-card-body {
        padding: var(--coy-space-5);
    }
}
@media (prefers-reduced-motion: reduce) {
    .jl-card-image-content,
    .jl-read-more {
        transition: none;
    }
}
</style>
