<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    posts: Array<{
        title: string;
        slug: string;
        excerpt: string | null;
        cover_image: string | null;
        published_at: string;
        reading_time: number;
    }>;
    heading?: string;
}>();
</script>

<template>
    <section v-if="posts.length" class="rjp">
        <div class="rjp-header">
            <h2 class="rjp-title">{{ heading ?? 'From the Journal' }}</h2>
            <Link href="/journal" class="rjp-all-link">
            View all
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
            </Link>
        </div>

        <div class="rjp-divider" aria-hidden="true">
            <span></span><span class="rjp-petal">✿</span><span></span>
        </div>

        <div class="rjp-list">
            <article v-for="post in posts" :key="post.slug" class="rjp-item">
                <Link :href="`/journal/${post.slug}`" class="rjp-img-wrap" v-if="post.cover_image">
                <img :src="post.cover_image" :alt="post.title" class="rjp-img" loading="lazy" />
                </Link>
                <div class="rjp-body">
                    <p class="rjp-meta">{{ post.published_at }} · {{ post.reading_time }} min read</p>
                    <h3 class="rjp-item-title">
                        <Link :href="`/journal/${post.slug}`">{{ post.title }}</Link>
                    </h3>
                    <p v-if="post.excerpt" class="rjp-excerpt">{{ post.excerpt }}</p>
                </div>
            </article>
        </div>
    </section>
</template>

<style scoped>
.rjp {
    font-family: 'Nunito', sans-serif;
    padding: 2.5rem 0;
    border-top: 1px solid #e5c9c7;
}

.rjp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.rjp-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.4rem;
    font-weight: 400;
    font-style: italic;
    color: #2d1a1a;
}

.rjp-all-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #8c4a50;
    text-decoration: none;
    transition: gap 0.2s;
}

.rjp-all-link:hover {
    gap: 0.5rem;
}

.rjp-divider {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
}

.rjp-divider span:not(.rjp-petal) {
    flex: 1;
    height: 1px;
    background: #e5c9c7;
}

.rjp-petal {
    font-size: 0.75rem;
    color: #c9a4a4;
}

.rjp-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* Horizontal card layout */
.rjp-item {
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
}

.rjp-img-wrap {
    flex-shrink: 0;
    width: 72px;
    height: 72px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5c9c7;
    background: #fdf4f3;
    display: block;
}

.rjp-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.rjp-img-wrap:hover .rjp-img {
    transform: scale(1.06);
}

.rjp-body {
    flex: 1;
    min-width: 0;
}

.rjp-meta {
    font-size: 0.72rem;
    color: #9a7070;
    margin-bottom: 0.2rem;
}

.rjp-item-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.98rem;
    font-weight: 500;
    line-height: 1.3;
    margin-bottom: 0.25rem;
}

.rjp-item-title a {
    text-decoration: none;
    color: #2d1a1a;
    transition: color 0.15s;
}

.rjp-item-title a:hover {
    color: #8c4a50;
}

.rjp-excerpt {
    font-size: 0.8rem;
    color: #6b4f4f;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
