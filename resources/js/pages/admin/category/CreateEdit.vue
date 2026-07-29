<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string | null;
    description: string | null;
    image: string | null;
    meta_title: string | null;
    meta_description: string | null;
    status: 'enabled' | 'disabled';
}

const props = defineProps<{
    category?: Category;
    isEditing: boolean;
}>();

const form = useForm({
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    description: props.category?.description ?? '',
    meta_title: props.category?.meta_title ?? '',
    meta_description: props.category?.meta_description ?? '',
    status: props.category?.status ?? ('enabled' as 'enabled' | 'disabled'),
    new_image: null as File | null,
    remove_image: false,
});

const title = computed(() =>
    props.isEditing ? `Edit: ${props.category?.name}` : 'New Category',
);
const submitLabel = computed(() =>
    props.isEditing ? 'Save Changes' : 'Create Category',
);

// Auto-generate slug from name when creating
const slugEdited = ref(props.isEditing);
watch(
    () => form.name,
    (val) => {
        if (!slugEdited.value) {
            form.slug = val
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    },
);

const showSeo = ref(false);
const metaDescCount = computed(() => form.meta_description.length);
const metaTitleCount = computed(() => form.meta_title.length);

// Image handling
const imagePreview = ref<string | null>(
    props.category?.image
        ? props.category.image.startsWith('http')
            ? props.category.image
            : `/storage/${props.category.image}`
        : null,
);

const handleFileUpload = (e: Event) => {
    const t = e.target as HTMLInputElement;
    if (t.files?.[0]) {
        form.new_image = t.files[0];
        form.remove_image = false;
        imagePreview.value = URL.createObjectURL(t.files[0]);
    }
    t.value = '';
};

const removeNewImage = () => {
    form.new_image = null;
    imagePreview.value = null;
};

const markImageForRemoval = () => {
    form.remove_image = true;
    form.new_image = null;
    imagePreview.value = null;
};

const undoRemoval = () => {
    form.remove_image = false;
    imagePreview.value = props.category?.image
        ? props.category.image.startsWith('http')
            ? props.category.image
            : `/storage/${props.category.image}`
        : null;
};

const fmtSize = (b: number) => {
    if (!b) return '0 B';
    const i = Math.floor(Math.log(b) / Math.log(1024));
    return `${(b / Math.pow(1024, i)).toFixed(1)} ${['B', 'KB', 'MB'][i]}`;
};

const submit = () => {
    if (props.isEditing && props.category) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(
            route('admin.categories.update', props.category.id),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    form.new_image = null;
                },
            },
        );
    } else {
        form.post(route('admin.categories.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.new_image = null;
            },
        });
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`${title} : Admin`" />

        <!-- Header -->
        <div class="cc-header">
            <div>
                <div class="cc-breadcrumb">
                    <Link
                        :href="route('admin.categories.index')"
                        class="cc-breadcrumb-link"
                        >Categories</Link
                    >
                    <span class="cc-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="cc-title">{{ title }}</h1>
                <p class="cc-sub">
                    {{
                        isEditing
                            ? 'Update category details and SEO.'
                            : 'Fill in the details for a new category.'
                    }}
                </p>
            </div>
            <div v-if="form.isDirty" class="cc-unsaved">
                <svg
                    width="13"
                    height="13"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                Unsaved changes
            </div>
        </div>

        <form @submit.prevent="submit" class="cc-grid">
            <!-- ── Left column ── -->
            <div class="cc-left">
                <!-- General info -->
                <section class="cc-card">
                    <h2 class="cc-card-title">General Information</h2>

                    <div class="cc-field">
                        <label class="cc-label" for="name">Category Name</label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            class="cc-input"
                            :class="{ 'cc-input--err': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="cc-err">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Slug -->
                    <div class="cc-field">
                        <label class="cc-label cc-label--sm">URL Slug</label>
                        <div class="cc-slug-wrap">
                            <span class="cc-slug-prefix">/category/</span>
                            <input
                                v-model="form.slug"
                                type="text"
                                class="cc-slug-input"
                                @input="slugEdited = true"
                                placeholder="auto-generated-from-name"
                            />
                        </div>
                        <p v-if="form.errors.slug" class="cc-err">
                            {{ form.errors.slug }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="cc-field">
                        <label class="cc-label cc-label--sm">
                            Description
                            <span class="cc-label-note"
                                >(shown on category landing page, helps
                                SEO)</span
                            >
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="cc-textarea"
                            placeholder="Describe this category. What makes these products special? Who are they for?&#10;&#10;This text appears on the public category page and helps Google understand what the page is about."
                        ></textarea>
                    </div>
                </section>

                <!-- Image -->
                <section class="cc-card">
                    <h2 class="cc-card-title">
                        Category Image
                        <span class="cc-card-title-note">max 2 MB</span>
                    </h2>

                    <!-- Removal notice -->
                    <div v-if="form.remove_image" class="cc-del-notice">
                        <svg
                            width="13"
                            height="13"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                            />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        Current image will be removed on save.
                        <button
                            type="button"
                            @click="undoRemoval"
                            class="cc-del-undo"
                        >
                            Undo
                        </button>
                    </div>

                    <!-- Current image preview -->
                    <div v-else-if="imagePreview" class="cc-existing">
                        <p class="cc-existing-label">
                            {{ form.new_image ? 'New image' : 'Current image' }}
                        </p>
                        <div class="cc-img-card">
                            <img
                                :src="imagePreview"
                                alt="Category image preview"
                                class="cc-img-thumb"
                            />
                            <div class="cc-img-actions">
                                <button
                                    type="button"
                                    @click="
                                        form.new_image
                                            ? removeNewImage()
                                            : markImageForRemoval()
                                    "
                                    class="cc-img-btn cc-img-btn--del"
                                    title="Remove image"
                                >
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
                                        <path d="M3 6h18" />
                                        <path
                                            d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"
                                        />
                                        <path
                                            d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Allow replacing image even when one is already queued/shown -->
                        <label
                            for="file-upload-replace"
                            class="cc-replace-link"
                        >
                            Replace image
                            <input
                                type="file"
                                id="file-upload-replace"
                                accept="image/jpeg,image/png,image/webp"
                                @change="handleFileUpload"
                                class="cc-upload-input"
                            />
                        </label>
                    </div>

                    <!-- Upload zone, shown when no image -->
                    <label v-else for="file-upload" class="cc-upload-zone">
                        <svg
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"
                            />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        <span>Click to upload or drag &amp; drop</span>
                        <span class="cc-upload-note"
                            >JPEG, PNG, WebP (max 2 MB)</span
                        >
                        <input
                            type="file"
                            id="file-upload"
                            accept="image/jpeg,image/png,image/webp"
                            @change="handleFileUpload"
                            class="cc-upload-input"
                        />
                    </label>

                    <p v-if="form.errors.new_image" class="cc-err">
                        {{ form.errors.new_image }}
                    </p>

                    <!-- New file queued info -->
                    <div
                        v-if="form.new_image && !imagePreview"
                        class="cc-queue-item"
                    >
                        <div class="cc-queue-thumb">
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                        </div>
                        <div class="cc-queue-info">
                            <p class="cc-queue-name">
                                {{ form.new_image.name }}
                            </p>
                            <p class="cc-queue-size">
                                {{ fmtSize(form.new_image.size) }}
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="removeNewImage"
                            class="cc-queue-remove"
                            aria-label="Remove"
                        >
                            <svg
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </section>

                <!-- SEO -->
                <section class="cc-card">
                    <button
                        type="button"
                        class="cc-seo-toggle"
                        @click="showSeo = !showSeo"
                    >
                        <span>SEO Settings</span>
                        <svg
                            :style="showSeo ? 'transform:rotate(180deg)' : ''"
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div v-if="showSeo" class="cc-seo-body">
                        <div class="cc-field">
                            <label class="cc-label cc-label--sm">
                                Meta Title
                                <span
                                    class="cc-label-note"
                                    :class="{
                                        'cc-label-note--warn':
                                            metaTitleCount > 55,
                                    }"
                                >
                                    {{ metaTitleCount }}/60
                                </span>
                            </label>
                            <input
                                v-model="form.meta_title"
                                type="text"
                                class="cc-input"
                                maxlength="60"
                                :placeholder="
                                    form.name
                                        ? `${form.name} | Chapter of You`
                                        : 'Auto-filled from name'
                                "
                            />
                            <p v-if="form.errors.meta_title" class="cc-err">
                                {{ form.errors.meta_title }}
                            </p>
                        </div>

                        <div class="cc-field">
                            <label class="cc-label cc-label--sm">
                                Meta Description
                                <span
                                    class="cc-label-note"
                                    :class="{
                                        'cc-label-note--warn':
                                            metaDescCount > 155,
                                    }"
                                >
                                    {{ metaDescCount }}/160
                                </span>
                            </label>
                            <textarea
                                v-model="form.meta_description"
                                rows="3"
                                class="cc-textarea"
                                maxlength="160"
                                :placeholder="
                                    form.description
                                        ? form.description.slice(0, 155) + '…'
                                        : 'Describe this category for search engines…'
                                "
                            ></textarea>
                            <p
                                v-if="form.errors.meta_description"
                                class="cc-err"
                            >
                                {{ form.errors.meta_description }}
                            </p>
                        </div>

                        <!-- Live Google SERP preview -->
                        <div class="cc-serp">
                            <p class="cc-serp-label">Google preview</p>
                            <p class="cc-serp-title">
                                {{
                                    form.meta_title ||
                                    (form.name
                                        ? `${form.name} | Chapter of You`
                                        : 'Category name')
                                }}
                            </p>
                            <p class="cc-serp-url">
                                chapterofyou.co.uk › category ›
                                {{ form.slug || 'category-slug' }}
                            </p>
                            <p class="cc-serp-desc">
                                {{
                                    form.meta_description ||
                                    form.description ||
                                    'Meta description will appear here.'
                                }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ── Right column ── -->
            <div class="cc-right">
                <section class="cc-card cc-card--sticky">
                    <h2 class="cc-card-title">Status &amp; Actions</h2>

                    <div class="cc-field">
                        <label class="cc-label">Category Status</label>
                        <div class="cc-status-btns">
                            <button
                                type="button"
                                @click="form.status = 'enabled'"
                                class="cc-status-btn"
                                :class="{
                                    'cc-status-btn--on':
                                        form.status === 'enabled',
                                }"
                            >
                                <span
                                    class="cc-status-dot cc-status-dot--green"
                                ></span>
                                Active
                            </button>
                            <button
                                type="button"
                                @click="form.status = 'disabled'"
                                class="cc-status-btn"
                                :class="{
                                    'cc-status-btn--off':
                                        form.status === 'disabled',
                                }"
                            >
                                <span
                                    class="cc-status-dot cc-status-dot--grey"
                                ></span>
                                Inactive
                            </button>
                        </div>
                        <p v-if="form.errors.status" class="cc-err">
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="cc-submit-btn"
                    >
                        <svg
                            v-if="form.processing"
                            class="cc-spinner"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="rgba(255,255,255,0.3)"
                                stroke-width="3"
                            />
                            <path
                                d="M12 2a10 10 0 0 1 10 10"
                                stroke="var(--adm-paper-raised)"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                        </svg>
                        {{ form.processing ? 'Saving…' : submitLabel }}
                    </button>

                    <!-- View live page (edit mode, published) -->
                    <a
                        v-if="
                            isEditing &&
                            category?.slug &&
                            category?.status === 'enabled'
                        "
                        :href="`/category/${category.slug}`"
                        target="_blank"
                        class="cc-view-live-btn"
                    >
                        ↗ View Category Page
                    </a>

                    <p v-if="form.isDirty" class="cc-unsaved-inline">
                        Unsaved changes
                    </p>
                </section>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.cc-header,
.cc-card {
    font-family: var(--adm-font);
}

/* Header */
.cc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.cc-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--adm-ink-dim);
    margin-bottom: 0.35rem;
}

.cc-breadcrumb-link {
    color: var(--adm-ink-dim);
    text-decoration: none;
    transition: color 0.15s;
}

.cc-breadcrumb-link:hover {
    color: var(--adm-ink);
}

.cc-breadcrumb-sep {
    opacity: 0.5;
}

.cc-title {
    font-family: var(--adm-display);
    font-style: italic;
    font-size: 2rem;
    font-weight: 400;
    letter-spacing: -0.01em;
    color: var(--adm-ink);
}

.cc-sub {
    font-size: 0.82rem;
    color: var(--adm-ink-dim);
    margin-top: 0.2rem;
}

.cc-unsaved {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    background: var(--adm-warning-bg);
    border: 1px solid var(--adm-warning-line);
    color: var(--adm-warning);
    font-size: 0.78rem;
    font-weight: 600;
}

/* Layout */
.cc-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .cc-grid {
        grid-template-columns: 1fr;
    }
}

.cc-left {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.cc-right {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

@media (min-width: 1024px) {
    .cc-card--sticky {
        position: sticky;
        top: 80px;
    }
}

/* Cards */
.cc-card {
    background: var(--adm-paper-raised);
    border-radius: 14px;
    border: 1px solid var(--adm-line);
    box-shadow: 0 1px 6px rgba(26, 26, 46, 0.05);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.cc-card-title {
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--adm-ink-dim);
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--adm-line);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cc-card-title-note {
    font-size: 0.65rem;
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    font-style: italic;
    color: var(--adm-ink-dim);
    opacity: 0.75;
}

/* Fields */
.cc-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.cc-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--adm-ink);
}

.cc-label--sm {
    font-size: 0.74rem;
    font-weight: 600;
    color: var(--adm-ink);
}

.cc-label-note {
    font-weight: 400;
    font-style: italic;
    color: var(--adm-ink-dim);
    margin-left: 0.25rem;
}

.cc-label-note--warn {
    color: var(--adm-danger) !important;
}

.cc-input {
    width: 100%;
    padding: 0.62rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--adm-line);
    background: var(--adm-paper);
    color: var(--adm-ink);
    font-family: var(--adm-font);
    font-size: 0.9rem;
    outline: none;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
}

.cc-input:focus {
    border-color: var(--adm-stamp-deep);
    box-shadow: 0 0 0 3px rgba(201, 184, 240, 0.2);
}

.cc-input--err {
    border-color: var(--adm-danger);
}

.cc-textarea {
    width: 100%;
    padding: 0.62rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--adm-line);
    background: var(--adm-paper);
    color: var(--adm-ink);
    font-family: var(--adm-font);
    font-size: 0.9rem;
    outline: none;
    resize: vertical;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
}

.cc-textarea:focus {
    border-color: var(--adm-stamp-deep);
    box-shadow: 0 0 0 3px rgba(201, 184, 240, 0.2);
}

.cc-err {
    font-size: 0.75rem;
    color: var(--adm-danger);
}

/* Slug */
.cc-slug-wrap {
    display: flex;
    align-items: center;
    border: 1px solid var(--adm-line);
    border-radius: 8px;
    overflow: hidden;
    background: var(--adm-paper);
}

.cc-slug-prefix {
    padding: 0.62rem 0.75rem;
    background: var(--adm-line);
    font-size: 0.82rem;
    color: var(--adm-ink-dim);
    font-family: monospace;
    white-space: nowrap;
    border-right: 1px solid var(--adm-line);
}

.cc-slug-input {
    flex: 1;
    padding: 0.62rem 0.75rem;
    border: none;
    background: transparent;
    color: var(--adm-ink);
    font-family: monospace;
    font-size: 0.85rem;
    outline: none;
}

/* Upload */
.cc-upload-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 1.75rem 1rem;
    border-radius: 10px;
    border: 2px dashed var(--adm-line);
    background: var(--adm-paper);
    color: var(--adm-ink-dim);
    cursor: pointer;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 500;
    transition:
        border-color 0.15s,
        background 0.15s;
}

.cc-upload-zone:hover {
    border-color: var(--adm-stamp-deep);
    background: var(--adm-stamp-dim);
}

.cc-upload-note {
    font-size: 0.72rem;
    font-weight: 400;
    opacity: 0.7;
}

.cc-upload-input {
    display: none;
}

.cc-replace-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--adm-stamp-deep);
    cursor: pointer;
    margin-top: 0.25rem;
}

.cc-replace-link:hover {
    text-decoration: underline;
}

/* Queue */
.cc-queue-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    background: var(--adm-paper);
    border: 1px solid var(--adm-line);
}

.cc-queue-thumb {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    background: var(--adm-stamp-dim);
    color: var(--adm-stamp-deep);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.cc-queue-info {
    flex: 1;
    min-width: 0;
}

.cc-queue-name {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--adm-ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cc-queue-size {
    font-size: 0.7rem;
    color: var(--adm-ink-dim);
}

.cc-queue-remove {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: none;
    background: none;
    color: var(--adm-ink-dim);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition:
        background 0.15s,
        color 0.15s;
}

.cc-queue-remove:hover {
    background: var(--adm-danger-bg);
    color: var(--adm-danger);
}

/* Existing image */
.cc-existing {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.cc-existing-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--adm-ink-dim);
}

.cc-img-card {
    position: relative;
    display: inline-block;
    border-radius: 10px;
    border: 1px solid var(--adm-line);
    overflow: hidden;
    max-width: 200px;
}

.cc-img-thumb {
    display: block;
    width: 100%;
    height: 130px;
    object-fit: cover;
}

.cc-img-actions {
    position: absolute;
    bottom: 0;
    right: 0;
    display: flex;
    gap: 2px;
    padding: 4px;
    background: rgba(26, 26, 46, 0.55);
    border-top-left-radius: 6px;
}

.cc-img-btn {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--adm-paper-raised);
    transition: opacity 0.15s;
}

.cc-img-btn:hover {
    opacity: 0.8;
}

.cc-img-btn--del {
    background: var(--adm-danger);
}

/* Deletion notice */
.cc-del-notice {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 0.85rem;
    border-radius: 8px;
    background: var(--adm-danger-bg);
    border: 1px solid var(--adm-danger-line);
    color: var(--adm-danger);
    font-size: 0.82rem;
    font-weight: 500;
}

.cc-del-undo {
    margin-left: auto;
    font-size: 0.75rem;
    font-weight: 700;
    background: none;
    border: none;
    color: var(--adm-danger);
    cursor: pointer;
    text-decoration: underline;
    font-family: var(--adm-font);
}

/* SEO */
.cc-seo-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--adm-font);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--adm-ink);
    padding: 0;
}

.cc-seo-toggle svg {
    transition: transform 0.2s;
}

.cc-seo-body {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--adm-line);
}

.cc-serp {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1rem;
    font-family: Arial, sans-serif;
}

.cc-serp-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--adm-ink-dim);
    margin-bottom: 0.5rem;
    font-family: var(--adm-font);
}

.cc-serp-title {
    font-size: 1rem;
    color: #1a0dab;
    margin-bottom: 0.1rem;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.cc-serp-url {
    font-size: 0.78rem;
    color: #006621;
    margin-bottom: 0.25rem;
}

.cc-serp-desc {
    font-size: 0.82rem;
    color: #545454;
    line-height: 1.5;
}

/* Status */
.cc-status-btns {
    display: flex;
    gap: 0.5rem;
}

.cc-status-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.55rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--adm-line);
    background: var(--adm-paper);
    color: var(--adm-ink-dim);
    font-family: var(--adm-font);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.15s,
        border-color 0.15s,
        color 0.15s;
}

.cc-status-btn--on {
    background: var(--adm-success-bg);
    border-color: var(--adm-success);
    color: var(--adm-success);
}

.cc-status-btn--off {
    background: var(--adm-danger-bg);
    border-color: var(--adm-danger);
    color: var(--adm-danger);
}

.cc-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

.cc-status-dot--green {
    background: var(--adm-success);
}

.cc-status-dot--grey {
    background: var(--adm-ink-faint);
}

/* Submit */
.cc-submit-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.72rem 1.25rem;
    border-radius: 8px;
    border: none;
    background: var(--adm-charcoal);
    color: var(--adm-paper-raised);
    font-family: var(--adm-font);
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        opacity 0.15s,
        transform 0.15s;
}

.cc-submit-btn:hover:not(:disabled) {
    opacity: 0.88;
    transform: translateY(-1px);
}

.cc-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.cc-view-live-btn {
    display: block;
    text-align: center;
    padding: 0.55rem 1rem;
    border-radius: 8px;
    border: 1px solid var(--adm-line);
    background: var(--adm-paper);
    color: var(--adm-ink-dim);
    font-family: var(--adm-font);
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    transition:
        border-color 0.15s,
        color 0.15s;
    margin-top: 0.25rem;
}

.cc-view-live-btn:hover {
    border-color: var(--adm-stamp-deep);
    color: var(--adm-ink);
}

.cc-unsaved-inline {
    text-align: center;
    font-size: 0.75rem;
    color: var(--adm-warning);
    font-weight: 500;
}

.cc-spinner {
    width: 15px;
    height: 15px;
    animation: cc-spin 0.8s linear infinite;
}

@keyframes cc-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
