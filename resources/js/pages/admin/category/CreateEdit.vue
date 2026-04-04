<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Category {
    id: number;
    name: string;
    image: string;
    status: 'enabled' | 'disabled';
}

const props = defineProps<{
    category?: Category;
    isEditing: boolean;
    errors: Record<string, string>;
}>();

const form = useForm({
    name: props.category?.name || '',
    status: props.category?.status || 'enabled',
    new_image: null as File | null,
    images_to_delete: [] as string[],
    images_to_toggle: [] as number[],
});

const title = computed(() => props.isEditing ? `Edit: ${props.category?.name}` : 'New Category');
const submitLabel = computed(() => props.isEditing ? 'Save Changes' : 'Create Category');

const handleFileUpload = (e: Event) => {
    const t = e.target as HTMLInputElement;
    if (t.files?.[0]) { form.new_image = t.files[0]; }
    t.value = '';
};

const removeNewImage = () => { form.new_image = null; };

const deleteExistingImage = (path: string) => {
    if (!form.images_to_delete.includes(path)) form.images_to_delete.push(path);
};

const fmtSize = (b: number) => {
    if (!b) return '0 B';
    const i = Math.floor(Math.log(b) / Math.log(1024));
    return `${(b / Math.pow(1024, i)).toFixed(1)} ${['B', 'KB', 'MB'][i]}`;
};

const submit = () => {
    if (props.isEditing && props.category) {
        form.transform(d => ({ ...d, _method: 'put' }))
            .post(route('admin.categories.update', props.category.id), {
                preserveScroll: true,
                onSuccess: () => { form.new_image = null; form.images_to_delete = []; },
            });
    } else {
        form.post(route('admin.categories.store'), {
            preserveScroll: true,
            onSuccess: () => { form.new_image = null; form.images_to_delete = []; },
        });
    }
};
</script>

<template>
    <AdminLayout>

        <Head :title="`${title} — Admin`" />

        <!-- Header -->
        <div class="cc-header">
            <div>
                <div class="cc-breadcrumb">
                    <Link :href="route('admin.categories.index')" class="cc-breadcrumb-link">Categories</Link>
                    <span class="cc-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="cc-title">{{ title }}</h1>
                <p class="cc-sub">{{ isEditing ? 'Update category details and image.' : 'Fill in the details for a new
                    category.' }}</p>
            </div>
            <div v-if="form.isDirty" class="cc-unsaved">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
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
                        <input id="name" type="text" v-model="form.name" required class="cc-input"
                            :class="{ 'cc-input--err': form.errors.name }" />
                        <p v-if="form.errors.name" class="cc-err">{{ form.errors.name }}</p>
                    </div>
                </section>

                <!-- Image -->
                <section class="cc-card">
                    <h2 class="cc-card-title">
                        Category Image
                        <span class="cc-card-title-note">max 2 MB</span>
                    </h2>

                    <!-- Upload zone -->
                    <label for="file-upload" class="cc-upload-zone">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        <span>Click to upload or drag &amp; drop</span>
                        <span class="cc-upload-note">JPEG, PNG, WebP — max 2 MB</span>
                        <input type="file" id="file-upload" accept="image/jpeg,image/png,image/webp"
                            @change="handleFileUpload" class="cc-upload-input" />
                    </label>
                    <p v-if="form.errors['new_image']" class="cc-err">{{ form.errors['new_image'] }}</p>

                    <!-- Queued new image -->
                    <div v-if="form.new_image" class="cc-queue-item">
                        <div class="cc-queue-thumb">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                        </div>
                        <div class="cc-queue-info">
                            <p class="cc-queue-name">{{ form.new_image.name }}</p>
                            <p class="cc-queue-size">{{ fmtSize(form.new_image.size) }}</p>
                        </div>
                        <button type="button" @click="removeNewImage" class="cc-queue-remove" aria-label="Remove">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Existing image -->
                    <div v-if="category?.image && !form.images_to_delete.includes(category.image)" class="cc-existing">
                        <p class="cc-existing-label">Current image</p>
                        <div class="cc-img-card">
                            <img :src="category.image" alt="Category image" class="cc-img-thumb"
                                onerror="this.src='https://placehold.co/200x120/f0f0f0/999?text=No+Image'" />
                            <div class="cc-img-actions">
                                <button type="button" @click="deleteExistingImage(category.image)"
                                    class="cc-img-btn cc-img-btn--del" title="Remove image">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Marked for deletion notice -->
                    <div v-if="category?.image && form.images_to_delete.includes(category.image)" class="cc-del-notice">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        Current image will be removed on save.
                        <button type="button"
                            @click="form.images_to_delete = form.images_to_delete.filter(p => p !== category?.image)"
                            class="cc-del-undo">Undo</button>
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
                            <button type="button" @click="form.status = 'enabled'" class="cc-status-btn"
                                :class="{ 'cc-status-btn--on': form.status === 'enabled' }">
                                <span class="cc-status-dot cc-status-dot--green"></span>
                                Active
                            </button>
                            <button type="button" @click="form.status = 'disabled'" class="cc-status-btn"
                                :class="{ 'cc-status-btn--off': form.status === 'disabled' }">
                                <span class="cc-status-dot cc-status-dot--grey"></span>
                                Inactive
                            </button>
                        </div>
                        <p v-if="form.errors.status" class="cc-err">{{ form.errors.status }}</p>
                    </div>

                    <button type="submit" :disabled="form.processing" class="cc-submit-btn">
                        <svg v-if="form.processing" class="cc-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ form.processing ? 'Saving...' : submitLabel }}
                    </button>

                    <p v-if="form.isDirty" class="cc-unsaved-inline">Unsaved changes</p>
                </section>
            </div>

        </form>
    </AdminLayout>
</template>

<style scoped>
.cc-header,
.cc-card {
    --bb-navy: #1a1a2e;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;
    --bb-red-bg: #fdeef0;
    --bb-green: #4caf7d;
    --bb-green-bg: #eef7f2;
    --bb-lav-d: #9b84d4;
    font-family: 'DM Sans', sans-serif;
}

/* ── Header ── */
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
    color: var(--bb-muted);
    margin-bottom: 0.35rem;
}

.cc-breadcrumb-link {
    color: var(--bb-muted);
    text-decoration: none;
    transition: color 0.15s;
}

.cc-breadcrumb-link:hover {
    color: var(--bb-text);
}

.cc-breadcrumb-sep {
    opacity: 0.5;
}

.cc-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--bb-text);
}

.cc-sub {
    font-size: 0.82rem;
    color: var(--bb-muted);
    margin-top: 0.2rem;
}

.cc-unsaved {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    background: #fff8e6;
    border: 1px solid #e0c060;
    color: #8a6000;
    font-size: 0.78rem;
    font-weight: 600;
}

/* ── Layout ── */
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

/* ── Cards ── */
.cc-card {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
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
    color: var(--bb-muted);
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--bb-border);
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
    color: var(--bb-muted);
    opacity: 0.75;
}

/* ── Fields ── */
.cc-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.cc-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--bb-text);
}

.cc-input {
    width: 100%;
    padding: 0.62rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.cc-input:focus {
    border-color: var(--bb-lav-d);
    box-shadow: 0 0 0 3px rgba(201, 184, 240, 0.2);
}

.cc-input--err {
    border-color: var(--bb-red);
}

.cc-err {
    font-size: 0.75rem;
    color: var(--bb-red);
}

/* ── Upload zone ── */
.cc-upload-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 1.75rem 1rem;
    border-radius: 10px;
    border: 2px dashed var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-muted);
    cursor: pointer;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 500;
    transition: border-color 0.15s, background 0.15s;
}

.cc-upload-zone:hover {
    border-color: var(--bb-lav-d);
    background: #faf8ff;
}

.cc-upload-note {
    font-size: 0.72rem;
    font-weight: 400;
    opacity: 0.7;
}

.cc-upload-input {
    display: none;
}

/* ── Queued file ── */
.cc-queue-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    background: var(--bb-cream);
    border: 1px solid var(--bb-border);
}

.cc-queue-thumb {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    background: #e8e4f0;
    color: var(--bb-lav-d);
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
    color: var(--bb-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cc-queue-size {
    font-size: 0.7rem;
    color: var(--bb-muted);
}

.cc-queue-remove {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: none;
    background: none;
    color: var(--bb-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, color 0.15s;
}

.cc-queue-remove:hover {
    background: var(--bb-red-bg);
    color: var(--bb-red);
}

/* ── Existing image ── */
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
    color: var(--bb-muted);
}

.cc-img-card {
    position: relative;
    display: inline-block;
    border-radius: 10px;
    border: 1px solid var(--bb-border);
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
    color: #fff;
    transition: opacity 0.15s;
}

.cc-img-btn:hover {
    opacity: 0.8;
}

.cc-img-btn--del {
    background: var(--bb-red);
}

/* ── Deletion notice ── */
.cc-del-notice {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 0.85rem;
    border-radius: 8px;
    background: var(--bb-red-bg);
    border: 1px solid #f5b8c0;
    color: var(--bb-red);
    font-size: 0.82rem;
    font-weight: 500;
}

.cc-del-undo {
    margin-left: auto;
    font-size: 0.75rem;
    font-weight: 700;
    background: none;
    border: none;
    color: var(--bb-red);
    cursor: pointer;
    text-decoration: underline;
    font-family: 'DM Sans', sans-serif;
}

/* ── Status buttons ── */
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
    border: 1px solid var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}

.cc-status-btn--on {
    background: var(--bb-green-bg);
    border-color: var(--bb-green);
    color: #2a7a50;
}

.cc-status-btn--off {
    background: var(--bb-red-bg);
    border-color: var(--bb-red);
    color: var(--bb-red);
}

.cc-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

.cc-status-dot--green {
    background: var(--bb-green);
}

.cc-status-dot--grey {
    background: #b0b0c0;
}

/* ── Submit ── */
.cc-submit-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.72rem 1.25rem;
    border-radius: 8px;
    border: none;
    background: var(--bb-navy);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
}

.cc-submit-btn:hover:not(:disabled) {
    opacity: 0.88;
    transform: translateY(-1px);
}

.cc-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.cc-unsaved-inline {
    text-align: center;
    font-size: 0.75rem;
    color: #8a6000;
    font-weight: 500;
}

/* ── Spinner ── */
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
