<script setup lang="ts">
import { computed, watch, ref } from 'vue';

interface ProductImage { image: string; }

const props = defineProps<{
    images: ProductImage[];
    initialIndex: number;
    open: boolean;
}>();

const emit = defineEmits(['update:open']);

const currentModalIndex = ref(props.initialIndex);

watch(() => props.open, (newOpen) => {
    if (newOpen) currentModalIndex.value = props.initialIndex;
});

watch(() => props.initialIndex, (newIndex) => {
    currentModalIndex.value = newIndex;
});

const currentImageUrl = computed(() =>
    props.images[currentModalIndex.value]?.image || '/images/placeholder.jpg'
);
const imageCount = computed(() => props.images.length);

const showPrevious = () => {
    if (imageCount.value > 0)
        currentModalIndex.value = (currentModalIndex.value - 1 + imageCount.value) % imageCount.value;
};
const showNext = () => {
    if (imageCount.value > 0)
        currentModalIndex.value = (currentModalIndex.value + 1) % imageCount.value;
};
const closeModal = () => emit('update:open', false);

const handleKeydown = (event: KeyboardEvent) => {
    if (!props.open) return;
    if (event.key === 'Escape') closeModal();
    if (event.key === 'ArrowLeft') showPrevious();
    if (event.key === 'ArrowRight') showNext();
};

watch(() => props.open, (newOpen) => {
    if (typeof window === 'undefined') return;
    newOpen
        ? window.addEventListener('keydown', handleKeydown)
        : window.removeEventListener('keydown', handleKeydown);
}, { immediate: true });
</script>

<template>
    <Transition name="miv-fade">
        <div v-if="open" class="miv-backdrop" @click.self="closeModal" role="dialog" aria-modal="true"
            aria-label="Image viewer">

            <div class="miv-box">

                <!-- Close -->
                <button @click="closeModal" class="miv-close" aria-label="Close image viewer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>

                <!-- Image -->
                <div class="miv-img-wrap">
                    <img :src="currentImageUrl" alt="Product image" class="miv-img" />
                </div>

                <!-- Nav arrows -->
                <template v-if="imageCount > 1">
                    <button @click.stop="showPrevious" class="miv-nav miv-nav--prev" aria-label="Previous image">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                    </button>
                    <button @click.stop="showNext" class="miv-nav miv-nav--next" aria-label="Next image">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </template>

                <!-- Counter + thumbnails -->
                <div v-if="imageCount > 1" class="miv-footer">
                    <div class="miv-thumbs">
                        <button v-for="(img, i) in images" :key="i" @click="currentModalIndex = i" class="miv-thumb"
                            :class="{ 'miv-thumb--active': currentModalIndex === i }"
                            :aria-label="`View image ${i + 1}`">
                            <img :src="img.image" :alt="`Thumbnail ${i + 1}`" class="miv-thumb-img" />
                        </button>
                    </div>
                    <p class="miv-counter">{{ currentModalIndex + 1 }} / {{ imageCount }}</p>
                </div>

            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* ── Backdrop ── */
.miv-backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(45, 26, 26, 0.85);
    backdrop-filter: blur(6px);
}

/* ── Modal box ── */
.miv-box {
    position: relative;
    width: 100%;
    max-width: 820px;
    max-height: 90vh;
    border-radius: 24px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    box-shadow: 0 24px 64px rgba(45, 26, 26, 0.35);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* ── Close button ── */
.miv-close {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 20;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 1px solid #e5c9c7;
    background: rgba(255, 250, 250, 0.92);
    color: #6b4f4f;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.miv-close:hover {
    background: #faeaea;
    color: #8c4a50;
    border-color: #c9a4a4;
}

/* ── Image area ── */
.miv-img-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #fdf4f3;
    min-height: 300px;
    max-height: 65vh;
}

.miv-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: opacity 0.25s ease;
}

/* ── Nav arrows ── */
.miv-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid #e5c9c7;
    background: rgba(255, 250, 250, 0.9);
    color: #8c4a50;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition: background 0.2s, border-color 0.2s, transform 0.2s;
}

.miv-nav:hover {
    background: #faeaea;
    border-color: #c9a4a4;
}

.miv-nav--prev {
    left: 12px;
}

.miv-nav--prev:hover {
    transform: translateY(-50%) translateX(-2px);
}

.miv-nav--next {
    right: 12px;
}

.miv-nav--next:hover {
    transform: translateY(-50%) translateX(2px);
}

/* ── Footer: thumbs + counter ── */
.miv-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e5c9c7;
    background: #fffafa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.miv-thumbs {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    flex: 1;
}

.miv-thumbs::-webkit-scrollbar {
    height: 3px;
}

.miv-thumbs::-webkit-scrollbar-track {
    background: transparent;
}

.miv-thumbs::-webkit-scrollbar-thumb {
    background: #e5c9c7;
    border-radius: 999px;
}

.miv-thumb {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 8px;
    border: 1px solid #e5c9c7;
    overflow: hidden;
    background: #fdf4f3;
    padding: 0;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.miv-thumb--active {
    border-color: #8c4a50;
    box-shadow: 0 0 0 2px rgba(140, 74, 80, 0.15);
}

.miv-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.miv-counter {
    font-size: 0.78rem;
    color: #6b4f4f;
    font-style: italic;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Transition ── */
.miv-fade-enter-active,
.miv-fade-leave-active {
    transition: opacity 0.25s ease;
}

.miv-fade-enter-from,
.miv-fade-leave-to {
    opacity: 0;
}

.miv-fade-enter-active .miv-box,
.miv-fade-leave-active .miv-box {
    transition: transform 0.25s ease;
}

.miv-fade-enter-from .miv-box {
    transform: scale(0.96);
}

.miv-fade-leave-to .miv-box {
    transform: scale(0.98);
}
</style>
