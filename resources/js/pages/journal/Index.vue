<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link } from '@inertiajs/vue3';

defineProps<{
    posts: {
        data: Array<{
            id: number; title: string; slug: string;
            excerpt: string | null; cover_image: string | null;
            tags: string[]; published_at: string; reading_time: number;
        }>;
        links: any[];
        meta: any;
    };
}>();

const seo = useSeoHead({
    title: 'Journal',
    description: 'Tips, guides and inspiration for home fragrance, aromatherapy and self-care from Chapter of You.',
    canonical: '/journal',
    ogType: 'website',
});
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="jl">
        <div class="jl-wrap">

            <!-- Header -->
            <header class="jl-header">
                <p class="jl-eyebrow">Chapter of You</p>
                <h1 class="jl-title">The <em>Journal</em></h1>
                <div class="jl-rule">
                    <span></span><span class="jl-petal">✿</span><span></span>
                </div>
                <p class="jl-intro">
                    Tips, guides and inspiration for creating a home that feels like sanctuary.
                </p>
            </header>

            <!-- Empty state -->
            <div v-if="!posts.data.length" class="jl-empty">
                <p class="jl-petal" style="font-size:2rem;">✿</p>
                <p>No articles yet — check back soon.</p>
            </div>

            <!-- Grid -->
            <div v-else class="jl-grid">
                <article v-for="post in posts.data" :key="post.id" class="jl-card">
                    <!-- Cover image -->
                    <Link :href="`/journal/${post.slug}`" class="jl-card-img-wrap">
                    <img v-if="post.cover_image" :src="post.cover_image" :alt="post.title" class="jl-card-img"
                        loading="lazy" />
                    <div v-else class="jl-card-img-placeholder" aria-hidden="true">✿</div>
                    </Link>

                    <div class="jl-card-body">
                        <!-- Tags -->
                        <div v-if="post.tags.length" class="jl-tags">
                            <span v-for="tag in post.tags.slice(0, 3)" :key="tag" class="jl-tag">{{ tag }}</span>
                        </div>

                        <h2 class="jl-card-title">
                            <Link :href="`/journal/${post.slug}`">{{ post.title }}</Link>
                        </h2>

                        <p v-if="post.excerpt" class="jl-card-excerpt">{{ post.excerpt }}</p>

                        <div class="jl-card-meta">
                            <span>{{ post.published_at }}</span>
                            <span class="jl-meta-sep" aria-hidden="true">·</span>
                            <span>{{ post.reading_time }} min read</span>
                        </div>

                        <Link :href="`/journal/${post.slug}`" class="jl-read-more">
                        Read article
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        </Link>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <div v-if="posts.links?.length > 3" class="jl-pagination">
                <template v-for="link in posts.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="jl-page-btn"
                        :class="{ 'jl-page-btn--active': link.active }" v-html="link.label" />
                    <span v-else class="jl-page-btn jl-page-btn--disabled" v-html="link.label" />
                </template>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.jl {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.jl-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 4rem 1.25rem 6rem;
}

/* Header */
.jl-header {
    text-align: center;
    margin-bottom: 3.5rem;
}

.jl-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
}

.jl-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2.2rem, 6vw, 3.5rem);
    font-weight: 300;
    color: #2d1a1a;
    margin-bottom: 1.25rem;
    line-height: 1.1;
}

.jl-title em {
    font-style: italic;
    color: #8c4a50;
}

.jl-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.jl-rule span:not(.jl-petal) {
    display: block;
    width: 60px;
    height: 1px;
    background: #e5c9c7;
}

.jl-petal {
    font-size: 0.85rem;
    color: #c9a4a4;
}

.jl-intro {
    font-size: 0.97rem;
    color: #6b4f4f;
    line-height: 1.7;
    max-width: 500px;
    margin: 0 auto;
}

/* Empty */
.jl-empty {
    text-align: center;
    padding: 4rem 1rem;
    color: #9a7070;
    font-style: italic;
}

/* Grid */
.jl-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

@media (max-width: 860px) {
    .jl-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 540px) {
    .jl-grid {
        grid-template-columns: 1fr;
    }
}

/* Card */
.jl-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s, transform 0.2s;
}

.jl-card:hover {
    box-shadow: 0 8px 28px rgba(229, 201, 199, 0.5);
    transform: translateY(-3px);
}

.jl-card-img-wrap {
    display: block;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #fdf4f3;
}

.jl-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s;
}

.jl-card:hover .jl-card-img {
    transform: scale(1.04);
}

.jl-card-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #e5c9c7;
}

.jl-card-body {
    padding: 1.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.jl-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.jl-tag {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8c4a50;
    background: rgba(140, 74, 80, 0.07);
    border: 1px solid rgba(140, 74, 80, 0.15);
    border-radius: 999px;
    padding: 0.15rem 0.55rem;
}

.jl-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    font-weight: 500;
    color: #2d1a1a;
    line-height: 1.3;
}

.jl-card-title a {
    text-decoration: none;
    color: inherit;
    transition: color 0.15s;
}

.jl-card-title a:hover {
    color: #8c4a50;
}

.jl-card-excerpt {
    font-size: 0.85rem;
    color: #6b4f4f;
    line-height: 1.65;
    flex: 1;
}

.jl-card-meta {
    font-size: 0.75rem;
    color: #9a7070;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.jl-meta-sep {
    color: #e5c9c7;
}

.jl-read-more {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #8c4a50;
    text-decoration: none;
    transition: gap 0.2s;
    margin-top: auto;
}

.jl-read-more:hover {
    gap: 0.55rem;
}

/* Pagination */
.jl-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 3rem;
    flex-wrap: wrap;
}

.jl-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 0.65rem;
    border-radius: 8px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    font-size: 0.82rem;
    color: #6b4f4f;
    text-decoration: none;
    transition: all 0.15s;
    cursor: pointer;
}

.jl-page-btn:hover {
    border-color: #8c4a50;
    color: #8c4a50;
}

.jl-page-btn--active {
    border-color: #8c4a50;
    background: #8c4a50;
    color: #fff;
}

.jl-page-btn--disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>
