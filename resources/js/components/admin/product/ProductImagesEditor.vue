<script setup lang="ts">
import { useAdmin } from '@/composables/useAdmin';
import type { EditableProductImage } from '@/types/product';
import { computed } from 'vue';

const props = defineProps<{ images: EditableProductImage[]; error?: string }>();
const newImages = defineModel<File[]>('newImages', { required: true });
const deletedIds = defineModel<number[]>('deletedIds', { required: true });
const toggledIds = defineModel<number[]>('toggledIds', { required: true });
const { fmtSize } = useAdmin();

const filteredExistingImages = computed(() =>
    props.images
        .filter((image) => !deletedIds.value.includes(image.id))
        .map((image) =>
            toggledIds.value.includes(image.id)
                ? { ...image, is_enabled: !image.is_enabled }
                : image,
        ),
);

function handleFileUpload(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;
    newImages.value = [...newImages.value, ...Array.from(input.files)].slice(
        0,
        5,
    );
    input.value = '';
}

function removeNewImage(index: number) {
    newImages.value = newImages.value.filter((_, i) => i !== index);
}

function toggleImageStatus(id: number) {
    toggledIds.value = toggledIds.value.includes(id)
        ? toggledIds.value.filter((value) => value !== id)
        : [...toggledIds.value, id];
}

function deleteExistingImage(id: number) {
    if (!deletedIds.value.includes(id))
        deletedIds.value = [...deletedIds.value, id];
    toggledIds.value = toggledIds.value.filter((value) => value !== id);
}
</script>

<template>
    <section class="adm-card">
        <h2 class="adm-card-title">
            Product Images
            <span class="adm-card-title-note">max 5 total</span>
        </h2>

        <label for="file-upload" class="adm-upload-zone">
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
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" y1="3" x2="12" y2="15" />
            </svg>
            <span>Click to upload or drag &amp; drop</span>
            <span class="adm-upload-note">JPEG, PNG, WebP (max 2 MB each)</span>
            <input
                type="file"
                id="file-upload"
                multiple
                accept="image/jpeg,image/png,image/webp"
                @change="handleFileUpload"
                style="display: none"
            />
        </label>
        <p v-if="error" class="adm-err">
            {{ error }}
        </p>

        <!-- Queued new images -->
        <div v-if="newImages.length" class="pe-queue">
            <p class="pe-sub-label">
                Queued for upload ({{ newImages.length }})
            </p>
            <div class="pe-queue-list">
                <div
                    v-for="(file, i) in newImages"
                    :key="i"
                    class="pe-queue-item"
                >
                    <div class="pe-queue-thumb">
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
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                    </div>
                    <div class="pe-queue-info">
                        <p class="pe-queue-name">{{ file.name }}</p>
                        <p class="pe-queue-size">
                            {{ fmtSize(file.size) }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="removeNewImage(i)"
                        class="pe-icon-remove"
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
            </div>
        </div>

        <!-- Existing images -->
        <div v-if="filteredExistingImages.length">
            <p class="pe-sub-label">
                Existing images ({{ filteredExistingImages.length }})
            </p>
            <div class="pe-img-grid">
                <div
                    v-for="img in filteredExistingImages"
                    :key="img.id"
                    class="pe-img-card"
                    :class="{
                        'pe-img-card--disabled': !img.is_enabled,
                    }"
                >
                    <img
                        :src="img.file_path"
                        :alt="`Image ${img.id}`"
                        class="pe-img-thumb"
                    />
                    <div v-if="!img.is_enabled" class="pe-img-hidden-tag">
                        Hidden
                    </div>
                    <div class="pe-img-actions">
                        <button
                            type="button"
                            @click="toggleImageStatus(img.id)"
                            class="pe-img-btn"
                            :class="
                                img.is_enabled
                                    ? 'pe-img-btn--hide'
                                    : 'pe-img-btn--show'
                            "
                            :title="img.is_enabled ? 'Hide' : 'Show'"
                        >
                            <svg
                                v-if="img.is_enabled"
                                width="12"
                                height="12"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"
                                />
                                <path
                                    d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"
                                />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                            <svg
                                v-else
                                width="12"
                                height="12"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="deleteExistingImage(img.id)"
                            class="pe-img-btn pe-img-btn--del"
                            title="Delete"
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
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.pe-queue {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.pe-sub-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--adm-ink-dim);
    margin-bottom: 0.35rem;
}

.pe-queue-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.pe-queue-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: var(--adm-radius);
    background: var(--adm-paper);
    border: 1px solid var(--adm-line);
}

.pe-queue-thumb {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--adm-stamp-dim);
    color: var(--adm-stamp-deep);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pe-queue-info {
    flex: 1;
    min-width: 0;
}

.pe-queue-name {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--adm-ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pe-queue-size {
    font-size: 0.7rem;
    color: var(--adm-ink-dim);
}

.pe-img-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
    margin-top: 0.5rem;
}

@media (max-width: 640px) {
    .pe-img-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.pe-img-card {
    position: relative;
    border-radius: var(--adm-radius);
    border: 1px solid var(--adm-line);
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--adm-paper);
    transition: opacity 0.15s;
}

.pe-img-card--disabled {
    opacity: 0.5;
}

.pe-img-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.pe-img-hidden-tag {
    position: absolute;
    top: 0;
    left: 0;
    background: rgba(178, 58, 38, 0.85);
    color: var(--adm-paper-raised);
    font-size: 0.55rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 2px 5px;
}

.pe-img-actions {
    position: absolute;
    bottom: 0;
    right: 0;
    display: flex;
    gap: 2px;
    padding: 3px;
    background: rgba(42, 39, 35, 0.55);
    border-top-left-radius: 6px;
}

.pe-img-btn {
    width: 22px;
    height: 22px;
    border-radius: 4px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--adm-paper-raised);
    transition: opacity 0.15s;
}

.pe-img-btn:hover {
    opacity: 0.8;
}

.pe-img-btn--hide {
    background: var(--adm-warning);
}

.pe-img-btn--show {
    background: var(--adm-success);
}

.pe-img-btn--del {
    background: var(--adm-danger);
}
</style>
