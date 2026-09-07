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
@media (min-width: 860px) {
    .pd-images {
        position: sticky;
        top: 88px;
    }
}

.pd-main-img-btn {
    display: block;
    width: 100%;
    border: none;
    background: none;
    padding: 0;
    cursor: zoom-in;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 0.85rem;
}

.pd-main-img-wrap {
    position: relative;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    overflow: hidden;
    background: #fdf4f3;
    aspect-ratio: 1 / 1;
}

.pd-popular-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 10;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    background: #8c4a50;
    color: #fff;
    border-radius: 999px;
    padding: 0.2rem 0.65rem;
}

.pd-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.pd-main-img-btn:hover .pd-main-img {
    transform: scale(1.04);
}

.pd-img-zoom-hint {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 250, 250, 0.88);
    border: 1px solid #e5c9c7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8c4a50;
    opacity: 0;
    transition: opacity 0.2s;
}

.pd-main-img-btn:hover .pd-img-zoom-hint {
    opacity: 1;
}

.pd-thumbs {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
}

.pd-thumb {
    border-radius: 12px;
    border: 1px solid #e5c9c7;
    overflow: hidden;
    background: #fdf4f3;
    aspect-ratio: 1/1;
    cursor: pointer;
    padding: 0;
    transition:
        border-color 0.2s,
        box-shadow 0.2s;
}

.pd-thumb--active {
    border-color: #8c4a50;
    box-shadow: 0 0 0 2px rgba(140, 74, 80, 0.15);
}

.pd-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
