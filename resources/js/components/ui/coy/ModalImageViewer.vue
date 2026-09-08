<script setup lang="ts">
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';

interface ProductImage {
    image: string;
}

const props = defineProps<{
    images: ProductImage[];
    initialIndex: number;
    open: boolean;
    label?: string;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();
const currentModalIndex = ref(props.initialIndex);
const modal = ref<HTMLElement | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);
let returnFocus: HTMLElement | null = null;
let previousOverflow = '';

const imageCount = computed(() => props.images.length);
const currentImageUrl = computed(
    () =>
        props.images[currentModalIndex.value]?.image ||
        '/images/placeholder.jpg',
);
const viewerTitle = computed(() =>
    props.label ? `${props.label} photos` : 'Product photos',
);

watch(
    () => props.initialIndex,
    (newIndex) => {
        currentModalIndex.value = newIndex;
    },
);

function selectImage(index: number) {
    currentModalIndex.value = index;
}

function showPrevious() {
    if (imageCount.value > 0) {
        currentModalIndex.value =
            (currentModalIndex.value - 1 + imageCount.value) % imageCount.value;
    }
}

function showNext() {
    if (imageCount.value > 0) {
        currentModalIndex.value =
            (currentModalIndex.value + 1) % imageCount.value;
    }
}

function closeModal() {
    emit('update:open', false);
}

function handleKeydown(event: KeyboardEvent) {
    if (!props.open) return;
    if (event.key === 'Escape') closeModal();
    if (event.key === 'ArrowLeft') showPrevious();
    if (event.key === 'ArrowRight') showNext();
    if (event.key !== 'Tab' || !modal.value) return;
    const focusable = [
        ...modal.value.querySelectorAll<HTMLElement>('button:not([disabled])'),
    ];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

watch(
    () => props.open,
    async (open) => {
        if (typeof window === 'undefined') return;
        if (open) {
            currentModalIndex.value = props.initialIndex;
            returnFocus = document.activeElement as HTMLElement;
            previousOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', handleKeydown);
            await nextTick();
            closeButton.value?.focus();
            return;
        }
        window.removeEventListener('keydown', handleKeydown);
        document.body.style.overflow = previousOverflow;
        returnFocus?.focus();
        returnFocus = null;
    },
    { immediate: true },
);

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = previousOverflow;
});
</script>

<template>
    <Transition name="miv-fade">
        <div
            v-if="open"
            ref="modal"
            class="miv-backdrop coy-storefront"
            role="dialog"
            aria-modal="true"
            aria-labelledby="image-viewer-title"
            @click.self="closeModal"
        >
            <div class="miv-box">
                <header class="miv-header">
                    <div>
                        <h2 id="image-viewer-title">{{ viewerTitle }}</h2>
                        <p aria-live="polite">
                            Image {{ currentModalIndex + 1 }} of
                            {{ imageCount }}
                        </p>
                    </div>
                    <button
                        ref="closeButton"
                        type="button"
                        class="miv-close"
                        aria-label="Close image viewer"
                        @click="closeModal"
                    >
                        <span>Close</span>
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </header>

                <div class="miv-content">
                    <nav
                        v-if="imageCount > 1"
                        class="miv-thumbs"
                        aria-label="Product images"
                    >
                        <button
                            v-for="(img, index) in images"
                            :key="index"
                            type="button"
                            class="miv-thumb"
                            :class="{
                                'miv-thumb--active':
                                    currentModalIndex === index,
                            }"
                            :aria-label="`View image ${index + 1}`"
                            :aria-current="
                                currentModalIndex === index ? 'true' : undefined
                            "
                            @click="selectImage(index)"
                        >
                            <img
                                :src="img.image"
                                :alt="`${label || 'Product'} thumbnail ${index + 1}`"
                                class="miv-thumb-img"
                            />
                        </button>
                    </nav>

                    <div class="miv-stage">
                        <img
                            :src="currentImageUrl"
                            :alt="`${label || 'Product'} image ${currentModalIndex + 1} of ${imageCount}`"
                            class="miv-img"
                        />
                        <template v-if="imageCount > 1">
                            <button
                                type="button"
                                class="miv-nav miv-nav--prev"
                                aria-label="Previous image"
                                @click="showPrevious"
                            >
                                <svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="miv-nav miv-nav--next"
                                aria-label="Next image"
                                @click="showNext"
                            >
                                <svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.miv-backdrop {
    position: fixed;
    z-index: 80;
    inset: 0;
    display: grid;
    place-items: center;
    padding: clamp(0.75rem, 2vw, 1.5rem);
    background: rgb(52 42 40 / 76%);
    backdrop-filter: blur(8px);
}
.miv-box {
    width: min(100%, 78rem);
    height: min(94vh, 58rem);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: 0 24px 70px rgb(0 0 0 / 28%);
}
.miv-header {
    min-height: 4.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 1rem 0.75rem 1.5rem;
    border-bottom: 1px solid var(--coy-color-border-soft);
}
.miv-header h2 {
    margin: 0;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 1.35rem;
    font-weight: var(--coy-font-weight-semibold);
    line-height: 1.2;
}
.miv-header p {
    margin: 0.15rem 0 0;
    font-size: var(--coy-text-xs);
}
.miv-close {
    min-height: var(--coy-control-height);
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.8rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-pill);
    font: 600 var(--coy-text-sm) var(--coy-font-body);
    cursor: pointer;
}
.miv-close:hover {
    background: var(--coy-color-surface-soft);
}
.miv-close svg,
.miv-nav svg {
    width: 1.1rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.miv-content {
    min-height: 0;
    flex: 1;
    display: grid;
    grid-template-columns: 6rem minmax(0, 1fr);
}
.miv-thumbs {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding: 1rem;
    overflow-y: auto;
    background: var(--coy-color-page);
    border-right: 1px solid var(--coy-color-border-soft);
}
.miv-thumb {
    width: 4rem;
    height: 4rem;
    flex: 0 0 auto;
    padding: 0;
    overflow: hidden;
    background: var(--coy-color-champagne);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    cursor: pointer;
}
.miv-thumb--active {
    border-color: var(--coy-color-accent);
    box-shadow:
        0 0 0 2px var(--coy-color-page),
        0 0 0 4px var(--coy-color-accent);
}
.miv-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.miv-stage {
    position: relative;
    min-width: 0;
    min-height: 0;
    display: grid;
    place-items: center;
    padding: clamp(1rem, 3vw, 2.5rem) 4.5rem;
    overflow: hidden;
    background: var(--coy-color-surface-soft);
}
.miv-img {
    max-width: 100%;
    max-height: 100%;
    display: block;
    object-fit: contain;
}
.miv-nav {
    position: absolute;
    top: 50%;
    width: 3rem;
    height: 3rem;
    display: grid;
    place-items: center;
    padding: 0;
    color: var(--coy-color-heading);
    background: rgb(255 253 251 / 92%);
    border: 1px solid var(--coy-color-border);
    border-radius: 50%;
    box-shadow: var(--coy-shadow-sm);
    cursor: pointer;
    transform: translateY(-50%);
}
.miv-nav:hover {
    background: var(--coy-color-surface);
    border-color: var(--coy-color-rose-gold);
}
.miv-nav--prev {
    left: 1rem;
}
.miv-nav--next {
    right: 1rem;
}
.miv-fade-enter-active,
.miv-fade-leave-active {
    transition: opacity var(--coy-duration-base) var(--coy-ease);
}
.miv-fade-enter-active .miv-box,
.miv-fade-leave-active .miv-box {
    transition: transform var(--coy-duration-base) var(--coy-ease);
}
.miv-fade-enter-from,
.miv-fade-leave-to {
    opacity: 0;
}
.miv-fade-enter-from .miv-box,
.miv-fade-leave-to .miv-box {
    transform: translateY(0.75rem) scale(0.985);
}
@media (max-width: 640px) {
    .miv-backdrop {
        padding: 0;
    }
    .miv-box {
        height: 100dvh;
        border: 0;
        border-radius: 0;
    }
    .miv-header {
        padding-inline: var(--coy-gutter);
    }
    .miv-close span {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip-path: inset(50%);
    }
    .miv-content {
        display: flex;
        flex-direction: column-reverse;
    }
    .miv-thumbs {
        flex-direction: row;
        padding: 0.85rem var(--coy-gutter)
            calc(0.85rem + env(safe-area-inset-bottom));
        overflow-x: auto;
        border-top: 1px solid var(--coy-color-border-soft);
        border-right: 0;
    }
    .miv-stage {
        flex: 1;
        padding: 1rem 3.75rem;
    }
    .miv-nav {
        width: 2.75rem;
        height: 2.75rem;
    }
    .miv-nav--prev {
        left: 0.5rem;
    }
    .miv-nav--next {
        right: 0.5rem;
    }
}
</style>
