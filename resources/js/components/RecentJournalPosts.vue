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
            <div>
                <p class="rjp-eyebrow">Ideas for your home</p>
                <h2 class="rjp-title">{{ heading ?? 'From the Journal' }}</h2>
            </div>
            <Link href="/journal" class="rjp-all-link">
                View all articles
                <svg
                    width="12"
                    height="12"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
            </Link>
        </div>

        <div class="rjp-list">
            <article v-for="post in posts" :key="post.slug" class="rjp-item">
                <Link
                    :href="`/journal/${post.slug}`"
                    class="rjp-img-wrap"
                    v-if="post.cover_image"
                >
                    <img
                        :src="post.cover_image"
                        :alt="post.title"
                        class="rjp-img"
                        loading="lazy"
                    />
                </Link>
                <div class="rjp-body">
                    <p class="rjp-meta">
                        {{ post.published_at }} · {{ post.reading_time }} min
                        read
                    </p>
                    <h3 class="rjp-item-title">
                        <Link :href="`/journal/${post.slug}`">{{
                            post.title
                        }}</Link>
                    </h3>
                    <p v-if="post.excerpt" class="rjp-excerpt">
                        {{ post.excerpt }}
                    </p>
                    <Link :href="`/journal/${post.slug}`" class="rjp-read-link">
                        Read article
                        <span aria-hidden="true">→</span>
                    </Link>
                </div>
            </article>
        </div>
    </section>
</template>

<style scoped>
.rjp {
    padding: clamp(3rem, 6vw, 4.5rem) 0 0;
    border-top: 1px solid var(--coy-color-border);
}

.rjp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--coy-space-5);
    margin-bottom: var(--coy-space-6);
}

.rjp-eyebrow {
    margin: 0 0 var(--coy-space-1);
    color: var(--coy-color-accent);
    font-size: var(--coy-text-xs);
    font-weight: var(--coy-font-weight-bold);
    letter-spacing: var(--coy-tracking-label);
    text-transform: uppercase;
}

.rjp-title {
    margin: 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: var(--coy-font-weight-medium);
    line-height: var(--coy-leading-heading);
}

.rjp-all-link {
    display: inline-flex;
    align-items: center;
    gap: var(--coy-space-2);
    color: var(--coy-color-accent);
    font-size: var(--coy-text-sm);
    font-weight: var(--coy-font-weight-bold);
    text-decoration: none;
    transition: gap var(--coy-duration-fast) var(--coy-ease);
}

.rjp-all-link:hover {
    gap: var(--coy-space-3);
}

.rjp-list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--coy-space-5);
}

.rjp-item {
    min-width: 0;
}

.rjp-img-wrap {
    aspect-ratio: 4 / 3;
    display: block;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-md);
}

.rjp-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--coy-duration-base) var(--coy-ease);
}

.rjp-img-wrap:hover .rjp-img {
    transform: scale(1.06);
}

.rjp-body {
    min-width: 0;
    padding-top: var(--coy-space-4);
}

.rjp-meta {
    margin: 0 0 var(--coy-space-2);
    color: var(--coy-color-text);
    font-size: var(--coy-text-xs);
}

.rjp-item-title {
    margin: 0;
    font-family: var(--coy-font-display);
    font-size: clamp(1.4rem, 2.5vw, 1.75rem);
    font-weight: var(--coy-font-weight-semibold);
    line-height: 1.2;
}

.rjp-item-title a {
    color: var(--coy-color-heading);
    text-decoration: none;
    transition: color var(--coy-duration-fast) var(--coy-ease);
}

.rjp-item-title a:hover {
    color: var(--coy-color-accent);
}

.rjp-excerpt {
    display: -webkit-box;
    margin: var(--coy-space-3) 0 0;
    overflow: hidden;
    color: var(--coy-color-text);
    font-size: var(--coy-text-sm);
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: var(--coy-leading-body);
}

.rjp-read-link {
    display: inline-flex;
    align-items: center;
    gap: var(--coy-space-2);
    margin-top: var(--coy-space-4);
    color: var(--coy-color-accent);
    font-size: var(--coy-text-sm);
    font-weight: var(--coy-font-weight-bold);
    text-decoration: underline;
    text-underline-offset: 0.2rem;
}

.rjp-read-link span {
    transition: transform var(--coy-duration-fast) var(--coy-ease);
}

.rjp-read-link:hover span {
    transform: translateX(0.2rem);
}

@media (max-width: 760px) {
    .rjp-header {
        align-items: flex-start;
        flex-direction: column;
        gap: var(--coy-space-3);
    }

    .rjp-list {
        grid-template-columns: 1fr;
        gap: var(--coy-space-7);
    }
}

@media (prefers-reduced-motion: reduce) {
    .rjp-img,
    .rjp-all-link,
    .rjp-read-link span {
        transition: none;
    }
}
</style>
