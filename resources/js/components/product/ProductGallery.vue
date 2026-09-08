<script setup lang="ts">
import type { ProductImage } from '@/types/product';
import { computed } from 'vue';

const props = defineProps<{
    images: ProductImage[];
    name: string;
    popular: boolean;
}>();
const selectedImageIndex = defineModel<number>('selectedIndex', {
    required: true,
});
const emit = defineEmits<{ open: [] }>();
const mainImageUrl = computed(
    () =>
        props.images[selectedImageIndex.value]?.image ||
        '/images/placeholder.jpg',
);

function openImageModal() {
    if (props.images.length) emit('open');
}
</script>

<template>
    <div class="pd-images">
        <button
            @click="openImageModal"
            class="pd-main-img-btn"
            aria-label="View full image"
        >
            <div class="pd-main-img-wrap">
                <span v-if="popular" class="pd-popular-badge">Popular</span>
                <img :src="mainImageUrl" :alt="name" class="pd-main-img" />
                <div class="pd-img-zoom-hint" aria-hidden="true">
                    <svg
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3M11 8v6M8 11h6" />
                    </svg>
                </div>
            </div>
        </button>
        <div v-if="images.length > 1" class="pd-thumbs">
            <button
                v-for="(img, i) in images"
                :key="i"
                @click="selectedImageIndex = i"
                class="pd-thumb"
                :class="{
                    'pd-thumb--active': selectedImageIndex === i,
                }"
                :aria-label="`View ${name} image ${i + 1}`"
            >
                <img
                    :src="img.image"
                    :alt="`${name} - view ${i + 1}`"
                    class="pd-thumb-img"
                    loading="lazy"
                />
            </button>
        </div>
    </div>
</template>

<style scoped>
.pd-images {
    min-width: 0;
}
.pd-main-img-btn {
    width: 100%;
    display: block;
    margin: 0 0 0.75rem;
    padding: 0;
    overflow: hidden;
    background: transparent;
    border: 0;
    border-radius: var(--coy-radius-lg);
    cursor: zoom-in;
}
.pd-main-img-wrap {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-lg);
}
.pd-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.45s var(--coy-ease);
}
.pd-main-img-btn:hover .pd-main-img {
    transform: scale(1.025);
}
.pd-popular-badge {
    position: absolute;
    z-index: 1;
    top: 1rem;
    left: 1rem;
    padding: 0.3rem 0.7rem;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-radius: var(--coy-radius-pill);
    font-size: var(--coy-text-xs);
    font-weight: var(--coy-font-weight-bold);
}
.pd-img-zoom-hint {
    position: absolute;
    right: 1rem;
    bottom: 1rem;
    width: 2.75rem;
    height: 2.75rem;
    display: grid;
    place-items: center;
    color: var(--coy-color-heading);
    background: rgb(255 253 251 / 90%);
    border: 1px solid var(--coy-color-border);
    border-radius: 50%;
    opacity: 0;
    transition: opacity var(--coy-duration-base) var(--coy-ease);
}
.pd-main-img-btn:hover .pd-img-zoom-hint,
.pd-main-img-btn:focus-visible .pd-img-zoom-hint {
    opacity: 1;
}
.pd-thumbs {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.65rem;
}
.pd-thumb {
    aspect-ratio: 1;
    padding: 0;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-sm);
    cursor: pointer;
}
.pd-thumb--active {
    border-color: var(--coy-color-accent);
    box-shadow:
        0 0 0 2px var(--coy-color-page),
        0 0 0 4px var(--coy-color-accent);
}
.pd-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
@media (min-width: 761px) {
    .pd-images {
        position: sticky;
        top: calc(var(--coy-nav-height) + 1.5rem);
    }
}
@media (max-width: 760px) {
    .pd-thumbs {
        display: flex;
        gap: 0.65rem;
        padding: 0.2rem 0.15rem 0.35rem;
        overflow-x: auto;
        scroll-snap-type: x proximity;
    }
    .pd-thumb {
        width: 4.5rem;
        flex: 0 0 4.5rem;
        scroll-snap-align: start;
    }
}
</style>
