<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import JsonLdSchema from '@/components/JsonLdSchema.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    post: {
        id: number; title: string; slug: string;
        excerpt: string | null; body: string;
        cover_image: string | null; tags: string[];
        published_at: string; reading_time: number;
        meta_title: string | null; meta_description: string | null;
        author: string | null;
        views: number;
    };
    related: Array<{
        id: number; title: string; slug: string;
        excerpt: string | null; cover_image: string | null;
        published_at: string; reading_time: number;
    }>;
}>();

const seo = useSeoHead({
    title: props.post.meta_title || props.post.title,
    description: props.post.meta_description || props.post.excerpt || undefined,
    canonical: `/journal/${props.post.slug}`,
    ogImage: props.post.cover_image || undefined,
    ogType: 'article',
});

// Article JSON-LD schema — helps Google show article rich results
const articleSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Article',
    'headline': props.post.title,
    'description': props.post.meta_description || props.post.excerpt,
    'url': `https://www.chapterofyou.co.uk/journal/${props.post.slug}`,
    'datePublished': props.post.published_at,
    'author': {
        '@type': 'Person',
        'name': props.post.author || 'Chapter of You',
    },
    'publisher': {
        '@type': 'Organization',
        'name': 'Chapter of You',
        'url': 'https://www.chapterofyou.co.uk',
        'logo': {
            '@type': 'ImageObject',
            'url': 'https://www.chapterofyou.co.uk/storage/images/large_image.png',
        },
    },
    ...(props.post.cover_image ? { 'image': props.post.cover_image } : {}),
}));
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />
    <JsonLdSchema :schema="articleSchema" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="js">

        <!-- Cover image -->
        <div v-if="post.cover_image" class="js-cover">
            <img :src="post.cover_image" :alt="post.title" class="js-cover-img" />
            <div class="js-cover-overlay" aria-hidden="true"></div>
        </div>

        <div class="js-wrap">

            <!-- Article header -->
            <header class="js-header">
                <nav class="js-breadcrumb" aria-label="Breadcrumb">
                    <Link href="/journal" class="js-breadcrumb-link">Journal</Link>
                    <span aria-hidden="true">›</span>
                    <span>{{ post.title }}</span>
                </nav>

                <div v-if="post.tags.length" class="js-tags">
                    <span v-for="tag in post.tags" :key="tag" class="js-tag">{{ tag }}</span>
                </div>

                <h1 class="js-title">{{ post.title }}</h1>

                <div class="js-meta">
                    <span>{{ post.published_at }}</span>
                    <span class="js-meta-sep" aria-hidden="true">·</span>
                    <span>{{ post.reading_time }} min read</span>
                    <span v-if="post.author" class="js-meta-sep" aria-hidden="true">·</span>
                    <span v-if="post.author">By {{ post.author }}</span>
                    <span class="js-meta-sep" aria-hidden="true">·</span>
                    <span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            style="display:inline; vertical-align:-1px;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        {{ post.views.toLocaleString() }}
                    </span>
                </div>

                <p v-if="post.excerpt" class="js-excerpt">{{ post.excerpt }}</p>

                <div class="js-rule" aria-hidden="true">
                    <span></span><span>✿</span><span></span>
                </div>
            </header>

            <!-- Article body -->
            <article class="js-body" v-html="post.body"></article>

            <!-- Tags footer -->
            <div v-if="post.tags.length" class="js-tags-footer">
                <span class="js-tags-label">Tagged:</span>
                <span v-for="tag in post.tags" :key="tag" class="js-tag">{{ tag }}</span>
            </div>

            <!-- Share nudge -->
            <div class="js-share">
                <p class="js-share-text">Enjoyed this article? Share it with someone who'd love it.</p>
                <div class="js-share-links">
                    <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent('https://www.chapterofyou.co.uk/journal/' + post.slug)}`"
                        target="_blank" rel="noopener noreferrer" class="js-share-btn">
                        Facebook
                    </a>
                    <a :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent('https://www.chapterofyou.co.uk/journal/' + post.slug)}&text=${encodeURIComponent(post.title)}`"
                        target="_blank" rel="noopener noreferrer" class="js-share-btn">
                        Twitter / X
                    </a>
                </div>
            </div>

            <!-- Related posts -->
            <section v-if="related.length" class="js-related">
                <h2 class="js-related-title">More from the Journal</h2>
                <div class="js-related-grid">
                    <article v-for="r in related" :key="r.id" class="js-related-card">
                        <Link :href="`/journal/${r.slug}`" class="js-related-img-wrap">
                        <img v-if="r.cover_image" :src="r.cover_image" :alt="r.title" class="js-related-img"
                            loading="lazy" />
                        <div v-else class="js-related-img-placeholder" aria-hidden="true">✿</div>
                        </Link>
                        <div class="js-related-body">
                            <p class="js-related-meta">{{ r.published_at }} · {{ r.reading_time }} min</p>
                            <h3 class="js-related-heading">
                                <Link :href="`/journal/${r.slug}`">{{ r.title }}</Link>
                            </h3>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Back link -->
            <div class="js-back">
                <Link href="/journal" class="js-back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7M19 12H5" />
                </svg>
                Back to Journal
                </Link>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.js {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

/* Cover */
.js-cover {
    position: relative;
    height: 380px;
    overflow: hidden;
}

@media (max-width: 640px) {
    .js-cover {
        height: 220px;
    }
}

.js-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.js-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 40%, rgba(253, 244, 243, 0.9) 100%);
}

/* Wrap */
.js-wrap {
    max-width: 720px;
    margin: 0 auto;
    padding: 2.5rem 1.25rem 6rem;
}

/* Breadcrumb */
.js-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #9a7070;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.js-breadcrumb-link {
    color: #8c4a50;
    text-decoration: none;
}

.js-breadcrumb-link:hover {
    text-decoration: underline;
}

/* Tags */
.js-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 1rem;
}

.js-tag {
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

/* Header */
.js-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(1.8rem, 5vw, 2.8rem);
    font-weight: 400;
    line-height: 1.2;
    color: #2d1a1a;
    margin-bottom: 0.85rem;
}

.js-meta {
    font-size: 0.82rem;
    color: #9a7070;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.js-meta-sep {
    color: #e5c9c7;
}

.js-excerpt {
    font-size: 1.05rem;
    line-height: 1.7;
    color: #6b4f4f;
    font-style: italic;
    margin-bottom: 1.5rem;
}

.js-rule {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1.75rem 0;
}

.js-rule span:not(:nth-child(2)) {
    flex: 1;
    height: 1px;
    background: #e5c9c7;
}

.js-rule span:nth-child(2) {
    font-size: 0.8rem;
    color: #c9a4a4;
}

/* Body — rich text styles */
.js-body {
    font-size: 1.05rem;
    line-height: 1.85;
    color: #3d2b2b;
    margin-bottom: 2.5rem;
}

:deep(.js-body h2) {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem;
    font-weight: 500;
    color: #2d1a1a;
    margin: 2rem 0 0.75rem;
}

:deep(.js-body h3) {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.25rem;
    font-weight: 500;
    color: #2d1a1a;
    margin: 1.5rem 0 0.5rem;
}

:deep(.js-body p) {
    margin-bottom: 1.25rem;
}

:deep(.js-body strong) {
    color: #2d1a1a;
    font-weight: 700;
}

:deep(.js-body em) {
    font-style: italic;
    color: #8c4a50;
}

:deep(.js-body a) {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 2px;
}

:deep(.js-body a:hover) {
    color: #6a3038;
}

:deep(.js-body ul),
:deep(.js-body ol) {
    padding-left: 1.5rem;
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

:deep(.js-body li) {
    line-height: 1.65;
}

:deep(.js-body blockquote) {
    border-left: 3px solid #e5c9c7;
    padding: 0.5rem 0 0.5rem 1.25rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #6b4f4f;
    font-size: 1.1rem;
    line-height: 1.65;
}

:deep(.js-body hr) {
    border: none;
    border-top: 1px solid #f0dcd8;
    margin: 2rem 0;
}

:deep(.js-body img) {
    max-width: 100%;
    border-radius: 12px;
    margin: 1rem 0;
}

/* Tags footer */
.js-tags-footer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding-top: 1.5rem;
    border-top: 1px solid #f0dcd8;
    margin-bottom: 2rem;
}

.js-tags-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #c9a4a4;
}

/* Share */
.js-share {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    text-align: center;
    margin-bottom: 3rem;
}

.js-share-text {
    font-size: 0.9rem;
    font-style: italic;
    color: #6b4f4f;
    margin-bottom: 0.85rem;
}

.js-share-links {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
}

.js-share-btn {
    display: inline-flex;
    padding: 0.45rem 1rem;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #6b4f4f;
    text-decoration: none;
    background: #fdf4f3;
    transition: border-color 0.15s, color 0.15s;
}

.js-share-btn:hover {
    border-color: #8c4a50;
    color: #8c4a50;
}

/* Related */
.js-related {
    margin-bottom: 3rem;
}

.js-related-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.4rem;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 1.25rem;
}

.js-related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .js-related-grid {
        grid-template-columns: 1fr;
    }
}

.js-related-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 14px;
    overflow: hidden;
}

.js-related-img-wrap {
    display: block;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #fdf4f3;
}

.js-related-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.js-related-card:hover .js-related-img {
    transform: scale(1.04);
}

.js-related-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #e5c9c7;
}

.js-related-body {
    padding: 0.85rem;
}

.js-related-meta {
    font-size: 0.72rem;
    color: #9a7070;
    margin-bottom: 0.3rem;
}

.js-related-heading {
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.95rem;
    font-weight: 500;
    line-height: 1.3;
}

.js-related-heading a {
    text-decoration: none;
    color: #2d1a1a;
    transition: color 0.15s;
}

.js-related-heading a:hover {
    color: #8c4a50;
}

/* Back */
.js-back {
    text-align: center;
}

.js-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
}

.js-back-link:hover {
    text-decoration: underline;
}
</style>
