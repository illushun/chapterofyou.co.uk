<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    isEditing: boolean;
    post?: {
        id: number; title: string; slug: string; excerpt: string | null;
        body: string; cover_image: string | null; meta_title: string | null;
        meta_description: string | null; tags: string | null;
        status: 'draft' | 'published'; published_at: string | null;
        author: { name: string } | null;
    };
    suggested?: string[];
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as any) ?? {});

const form = useForm({
    title: props.post?.title ?? '',
    slug: props.post?.slug ?? '',
    excerpt: props.post?.excerpt ?? '',
    body: props.post?.body ?? '',
    meta_title: props.post?.meta_title ?? '',
    meta_description: props.post?.meta_description ?? '',
    tags: props.post?.tags ?? '',
    status: props.post?.status ?? 'draft' as 'draft' | 'published',
    published_at: props.post?.published_at ?? '',
    cover_image: null as File | null,
    remove_cover_image: false,
});

// Auto-generate slug from title (only when creating)
watch(() => form.title, (val) => {
    if (!props.isEditing && !slugManuallyEdited.value) {
        form.slug = val.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
});

const slugManuallyEdited = ref(props.isEditing);
const coverPreview = ref<string | null>(props.post?.cover_image ?? null);
const showSeoPanel = ref(false);
const submitting = ref(false);

function onCoverChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.cover_image = file;
    coverPreview.value = URL.createObjectURL(file);
    form.remove_cover_image = false;
}
function removeCover() {
    form.cover_image = null;
    form.remove_cover_image = true;
    coverPreview.value = null;
}

// Simple rich text toolbar actions
const bodyRef = ref<HTMLTextAreaElement | null>(null);

function insertMarkup(before: string, after = '') {
    const el = bodyRef.value;
    if (!el) return;
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const sel = form.body.substring(start, end);
    form.body = form.body.substring(0, start) + before + sel + after + form.body.substring(end);
    setTimeout(() => {
        el.focus();
        el.setSelectionRange(start + before.length, start + before.length + sel.length);
    }, 0);
}

const toolbarActions = [
    { label: 'H2', action: () => insertMarkup('<h2>', '</h2>') },
    { label: 'H3', action: () => insertMarkup('<h3>', '</h3>') },
    { label: 'Bold', action: () => insertMarkup('<strong>', '</strong>') },
    { label: 'Italic', action: () => insertMarkup('<em>', '</em>') },
    {
        label: 'Link', action: () => {
            const url = prompt('URL:');
            if (url) insertMarkup(`<a href="${url}">`, '</a>');
        }
    },
    { label: '• List', action: () => insertMarkup('<ul>\n  <li>', '</li>\n</ul>') },
    { label: 'Quote', action: () => insertMarkup('<blockquote>', '</blockquote>') },
    { label: 'HR', action: () => { form.body += '\n<hr />\n'; } },
];

function save(status: 'draft' | 'published') {
    form.status = status;
    submitting.value = true;
    if (props.isEditing) {
        form.transform(d => ({ ...d, _method: 'PUT' }))
            .post(route('admin.journal.update', props.post!.id), {
                forceFormData: true,
                onFinish: () => { submitting.value = false; },
            });
    } else {
        form.post(route('admin.journal.store'), {
            forceFormData: true,
            onFinish: () => { submitting.value = false; },
        });
    }
}

const charCount = computed(() => form.meta_description.length);
const titleCharCount = computed(() => form.meta_title.length);
</script>

<template>
    <AdminLayout>

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.journal.index')">Journal</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit Post' : 'New Post' }}</span>
                </div>
                <h1 class="adm-title">{{ isEditing ? 'Edit Post' : 'Write New Post' }}</h1>
            </div>
            <div style="display:flex; gap:0.6rem;">
                <button @click="save('draft')" :disabled="submitting" class="adm-btn adm-btn--ghost adm-btn--sm">
                    Save Draft
                </button>
                <button @click="save('published')" :disabled="submitting" class="adm-btn adm-btn--primary adm-btn--sm">
                    <svg v-if="submitting" class="adm-spinner" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                    </svg>
                    {{ submitting ? 'Saving…' : 'Publish' }}
                </button>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="flash.success" class="adm-flash adm-flash--success">{{ flash.success }}</div>

        <div class="je-layout">

            <!-- ── Left: main content ── -->
            <div class="je-main">

                <!-- Title -->
                <div class="adm-card">
                    <div class="adm-field">
                        <label class="adm-label">Title <span style="color:#e05c6e">*</span></label>
                        <input v-model="form.title" type="text" class="adm-input"
                            placeholder="e.g. How long do reed diffusers last?" />
                        <p v-if="form.errors.title" class="adm-field-error">{{ form.errors.title }}</p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">URL Slug</label>
                        <div class="adm-slug-wrap">
                            <span class="adm-slug-prefix">/journal/</span>
                            <input v-model="form.slug" type="text" class="adm-slug-input"
                                @input="slugManuallyEdited = true" placeholder="auto-generated-from-title" />
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Excerpt <span
                                style="color:var(--bb-muted); font-weight:400;">(shown on listing page)</span></label>
                        <textarea v-model="form.excerpt" rows="2" class="adm-textarea"
                            placeholder="A short, enticing summary of the article (1–2 sentences)…"
                            maxlength="500"></textarea>
                        <p class="adm-field-note">{{ form.excerpt.length }}/500</p>
                    </div>
                </div>

                <!-- Body editor -->
                <div class="adm-card adm-card--flush">
                    <div class="je-toolbar">
                        <button v-for="t in toolbarActions" :key="t.label" type="button" @click="t.action"
                            class="je-toolbar-btn" :title="t.label">
                            {{ t.label }}
                        </button>
                    </div>
                    <textarea ref="bodyRef" v-model="form.body" rows="28" class="je-body-editor"
                        placeholder="Write your article in HTML. Use the toolbar above for quick formatting.&#10;&#10;Tip: use <h2> and <h3> for headings, <p> for paragraphs, <strong> for bold."></textarea>
                    <p v-if="form.errors.body" class="adm-field-error" style="padding: 0.5rem 1.25rem;">{{
                        form.errors.body }}</p>
                </div>

                <!-- SEO panel (collapsible) -->
                <div class="adm-card">
                    <button type="button" class="je-seo-toggle" @click="showSeoPanel = !showSeoPanel">
                        <span>SEO Settings</span>
                        <svg :style="showSeoPanel ? 'transform:rotate(180deg)' : ''" width="14" height="14"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div v-if="showSeoPanel" class="je-seo-body">
                        <div class="adm-field">
                            <label class="adm-label adm-label--sm">
                                Meta Title
                                <span class="adm-field-note">{{ titleCharCount }}/60</span>
                            </label>
                            <input v-model="form.meta_title" type="text" class="adm-input" maxlength="60"
                                :placeholder="form.title || 'Auto-filled from title'" />
                        </div>
                        <div class="adm-field">
                            <label class="adm-label adm-label--sm">
                                Meta Description
                                <span class="adm-field-note" :class="charCount > 155 ? 'adm-field-note--warn' : ''">
                                    {{ charCount }}/160
                                </span>
                            </label>
                            <textarea v-model="form.meta_description" rows="3" class="adm-textarea" maxlength="160"
                                :placeholder="form.excerpt || 'Auto-filled from excerpt'"></textarea>
                        </div>

                        <!-- Google preview -->
                        <div class="je-serp-preview">
                            <p class="je-serp-label">Google preview</p>
                            <p class="je-serp-title">{{ form.meta_title || form.title || 'Post title' }}</p>
                            <p class="je-serp-url">chapterofyou.co.uk › journal › {{ form.slug || 'post-slug' }}</p>
                            <p class="je-serp-desc">{{ form.meta_description || form.excerpt ||
                                'Post excerpt or meta description will appear here.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Suggested topics (only on create) -->
                <div v-if="!isEditing && suggested?.length" class="adm-card">
                    <p class="adm-card-title" style="font-size:0.78rem; margin-bottom:0.75rem;">💡 SEO topic ideas</p>
                    <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                        <button v-for="s in suggested" :key="s" type="button" @click="form.title = s"
                            class="adm-btn adm-btn--ghost adm-btn--sm">
                            {{ s }}
                        </button>
                    </div>
                </div>

            </div>

            <!-- ── Right: sidebar ── -->
            <div class="je-sidebar">

                <!-- Publish settings -->
                <div class="adm-card">
                    <h2 class="adm-card-title">Publishing</h2>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Status</label>
                        <div class="adm-status-btns">
                            <button type="button" @click="form.status = 'draft'" class="adm-status-btn"
                                :class="form.status === 'draft' ? 'adm-status-btn--active' : ''">
                                Draft
                            </button>
                            <button type="button" @click="form.status = 'published'" class="adm-status-btn"
                                :class="form.status === 'published' ? 'adm-status-btn--active' : ''">
                                Published
                            </button>
                        </div>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Publish Date</label>
                        <input v-model="form.published_at" type="datetime-local" class="adm-input" />
                        <p class="adm-field-note">Leave blank to publish immediately.</p>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                        <button @click="save('published')" :disabled="submitting"
                            class="adm-btn adm-btn--primary adm-btn--full">
                            {{ isEditing ? 'Update & Publish' : 'Publish Now' }}
                        </button>
                        <button @click="save('draft')" :disabled="submitting"
                            class="adm-btn adm-btn--ghost adm-btn--full">
                            Save as Draft
                        </button>
                    </div>
                </div>

                <!-- Cover image -->
                <div class="adm-card">
                    <h2 class="adm-card-title">Cover Image</h2>
                    <div v-if="coverPreview" class="je-cover-preview">
                        <img :src="coverPreview" alt="Cover preview" />
                        <button type="button" @click="removeCover" class="je-cover-remove">Remove</button>
                    </div>
                    <label class="adm-upload-zone" v-else>
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                            @change="onCoverChange" />
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        <p class="adm-upload-note">Click to upload cover image<br /><span>JPEG, PNG or WebP · max
                                4MB</span></p>
                    </label>
                </div>

                <!-- Tags -->
                <div class="adm-card">
                    <h2 class="adm-card-title">Tags</h2>
                    <div class="adm-field">
                        <input v-model="form.tags" type="text" class="adm-input"
                            placeholder="aromatherapy, self-care, home fragrance" />
                        <p class="adm-field-note">Comma-separated. Used for related posts.</p>
                    </div>
                </div>

                <!-- View live (edit mode) -->
                <div v-if="isEditing && post?.status === 'published'" class="adm-card">
                    <a :href="`/journal/${post.slug}`" target="_blank" class="adm-btn adm-btn--ghost adm-btn--full">
                        ↗ View Live Post
                    </a>
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

/* Toolbar */
.je-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--bb-border);
    background: var(--bb-cream);
    border-radius: var(--bb-radius) var(--bb-radius) 0 0;
}

.je-toolbar-btn {
    padding: 0.25rem 0.6rem;
    border-radius: var(--bb-radius-sm);
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    font-family: var(--bb-font);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--bb-text);
    cursor: pointer;
    transition: background 0.1s;
}

.je-toolbar-btn:hover {
    background: var(--bb-border);
}

/* Body editor */
.je-body-editor {
    width: 100%;
    min-height: 500px;
    padding: 1.25rem;
    border: none;
    outline: none;
    resize: vertical;
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 0.85rem;
    line-height: 1.7;
    color: var(--bb-text);
    background: var(--bb-surface);
    border-radius: 0 0 var(--bb-radius) var(--bb-radius);
}

/* SEO toggle */
.je-seo-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--bb-font);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--bb-text);
    padding: 0;
}

.je-seo-toggle svg {
    transition: transform 0.2s;
}

.je-seo-body {
    margin-top: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* SERP preview */
.je-serp-preview {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1rem;
    font-family: Arial, sans-serif;
}

.je-serp-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--bb-muted);
    margin-bottom: 0.5rem;
}

.je-serp-title {
    font-size: 1rem;
    color: #1a0dab;
    font-weight: 400;
    margin-bottom: 0.1rem;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.je-serp-url {
    font-size: 0.78rem;
    color: #006621;
    margin-bottom: 0.25rem;
}

.je-serp-desc {
    font-size: 0.82rem;
    color: #545454;
    line-height: 1.5;
}

/* Cover image */
.je-cover-preview {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}

.je-cover-preview img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
}

.je-cover-remove {
    position: absolute;
    top: 6px;
    right: 6px;
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
    border: none;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    font-size: 0.72rem;
    cursor: pointer;
    font-family: var(--bb-font);
}

.adm-field-note--warn {
    color: var(--bb-red) !important;
}
</style>
