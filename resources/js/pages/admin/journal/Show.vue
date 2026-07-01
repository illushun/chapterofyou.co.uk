<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface JournalPostDetail {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    body: string;
    cover_image: string | null;
    meta_title: string | null;
    meta_description: string | null;
    tags: string | null;
    tags_array: string[];
    reading_time: number;
    status: 'draft' | 'published';
    published_at: string | null;
    views: number;
    is_ai_generated: boolean;
    author: { id: number; name: string } | null;
    created_at: string;
}

const props = defineProps<{
    post: JournalPostDetail;
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as any) ?? {});

const fmtDate = (d: string | null) =>
    d
        ? new Date(d).toLocaleDateString('en-GB', {
              day: 'numeric', month: 'long', year: 'numeric',
              hour: '2-digit', minute: '2-digit',
          })
        : '—';

function deletePost() {
    if (!confirm(`Delete "${props.post.title}"? This cannot be undone.`)) return;
    router.delete(route('admin.journal.destroy', props.post.id));
}
</script>

<template>
    <AdminLayout>

        <Head :title="post.title" />

        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.journal.index')">Journal</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ post.title }}</span>
                </div>
                <h1 class="adm-title">{{ post.title }}</h1>
                <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.35rem;">
                    <span class="adm-badge" :class="post.status === 'published' ? 'adm-badge--on' : 'adm-badge--warn'">
                        {{ post.status }}
                    </span>
                    <span v-if="post.is_ai_generated" class="adm-badge adm-badge--lav">AI generated</span>
                </div>
            </div>
            <div style="display:flex; gap:0.6rem;">
                <a v-if="post.status === 'published'" :href="`/journal/${post.slug}`" target="_blank"
                    class="adm-btn adm-btn--ghost">↗ View Live</a>
                <Link :href="route('admin.journal.edit', post.id)" class="adm-btn adm-btn--ghost">Edit</Link>
                <button @click="deletePost" class="adm-btn adm-btn--danger">Delete</button>
            </div>
        </div>

        <div v-if="flash.success" class="adm-flash adm-flash--success">{{ flash.success }}</div>
        <div v-if="flash.error" class="adm-flash adm-flash--error">{{ flash.error }}</div>

        <div class="je-layout">
            <div class="je-main">
                <div v-if="post.cover_image" class="adm-card adm-card--flush">
                    <img :src="post.cover_image" :alt="post.title" class="js-cover" />
                </div>

                <div v-if="post.excerpt" class="adm-card">
                    <h2 class="adm-card-title">Excerpt</h2>
                    <p>{{ post.excerpt }}</p>
                </div>

                <div class="adm-card">
                    <h2 class="adm-card-title">Body</h2>
                    <article class="js-body" v-html="post.body"></article>
                </div>
            </div>

            <div class="je-sidebar">
                <div class="adm-card">
                    <h2 class="adm-card-title">Details</h2>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Slug</label>
                        <p style="font-family: var(--bb-mono); font-size:0.82rem;">/journal/{{ post.slug }}</p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Published</label>
                        <p>{{ fmtDate(post.published_at) }}</p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Author</label>
                        <p>{{ post.author?.name ?? '—' }}</p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Views</label>
                        <p>{{ post.views.toLocaleString() }}</p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Reading time</label>
                        <p>{{ post.reading_time }} min</p>
                    </div>
                </div>

                <div v-if="post.tags_array.length" class="adm-card">
                    <h2 class="adm-card-title">Tags</h2>
                    <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                        <span v-for="tag in post.tags_array" :key="tag" class="adm-badge adm-badge--off">{{ tag }}</span>
                    </div>
                </div>

                <div class="adm-card">
                    <h2 class="adm-card-title">SEO</h2>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Meta Title</label>
                        <p>{{ post.meta_title || post.title }}</p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Meta Description</label>
                        <p>{{ post.meta_description || post.excerpt || '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.je-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .je-layout {
        grid-template-columns: 1fr;
    }
}

.je-main {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.je-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

@media (min-width: 900px) {
    .je-sidebar {
        position: sticky;
        top: 84px;
    }
}

.js-cover {
    width: 100%;
    max-height: 360px;
    object-fit: cover;
    display: block;
}

.js-body {
    font-size: 0.92rem;
    line-height: 1.7;
    color: var(--bb-text);
}

.js-body :deep(h2) {
    font-size: 1.15rem;
    font-weight: 700;
    margin: 1.25rem 0 0.6rem;
}

.js-body :deep(h3) {
    font-size: 1rem;
    font-weight: 700;
    margin: 1rem 0 0.5rem;
}

.js-body :deep(p) {
    margin-bottom: 0.85rem;
}

.js-body :deep(ul),
.js-body :deep(ol) {
    padding-left: 1.5rem;
    margin-bottom: 0.85rem;
}

.js-body :deep(li) {
    margin-bottom: 0.3rem;
}

.js-body :deep(a) {
    color: var(--bb-lav-d);
}

.js-body :deep(blockquote) {
    border-left: 3px solid var(--bb-border);
    padding-left: 1rem;
    color: var(--bb-muted);
    font-style: italic;
    margin: 1rem 0;
}

.js-body :deep(img) {
    max-width: 100%;
    border-radius: var(--bb-radius);
}
</style>
