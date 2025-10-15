<script setup lang="ts">
import { computed, watch, ref } from 'vue';

interface ProductImage {
    image: string;
}

const props = defineProps<{
    images: ProductImage[];
    initialIndex: number;
    open: boolean;
}>();

const emit = defineEmits(['update:open']);

// Internal state for the currently viewed image in the modal
const currentModalIndex = ref(props.initialIndex);

// Sync internal index when the modal is opened
watch(() => props.open, (newOpen) => {
    if (newOpen) {
        currentModalIndex.value = props.initialIndex;
    }
});

// Sync internal index when the initialIndex prop changes (from clicking a thumbnail outside)
watch(() => props.initialIndex, (newIndex) => {
    currentModalIndex.value = newIndex;
});

const currentImageUrl = computed(() => {
    return props.images[currentModalIndex.value]?.image || 'https://via.placeholder.com/1200?text=Image+Not+Found';
});

const imageCount = computed(() => props.images.length);

const showPrevious = () => {
    if (imageCount.value > 0) {
        currentModalIndex.value = (currentModalIndex.value - 1 + imageCount.value) % imageCount.value;
    }
};

const showNext = () => {
    if (imageCount.value > 0) {
        currentModalIndex.value = (currentModalIndex.value + 1) % imageCount.value;
    }
};

const closeModal = () => {
    emit('update:open', false);
};

// Handle keyboard navigation (Escape to close, arrows for next/prev)
const handleKeydown = (event: KeyboardEvent) => {
    if (!props.open) return;

    if (event.key === 'Escape') {
        closeModal();
    } else if (event.key === 'ArrowLeft') {
        showPrevious();
    } else if (event.key === 'ArrowRight') {
        showNext();
    }
};

// Add/Remove keyboard listener when component is mounted/unmounted or modal state changes
watch(() => props.open, (newOpen) => {
    if (typeof window !== 'undefined') {
        if (newOpen) {
            window.addEventListener('keydown', handleKeydown);
        } else {
            window.removeEventListener('keydown', handleKeydown);
        }
    }
}, { immediate: true });
</script>

<template>
    <Transition name="fade">
        <div
            v-if="open"
            @click.self="closeModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-90 transition-opacity"
        >
            <div
                class="relative w-full max-w-4xl h-full max-h-[90vh] rounded-xl"
            >
                <div class="relative -m-0.5 w-full h-full rounded-lg border-2 border-copy bg-foreground p-4 md:p-8 flex flex-col items-center justify-center">

                    <button
                        @click="closeModal"
                        class="absolute top-4 right-4 z-10 p-2 rounded-lg border-2 border-copy bg-error text-error-content transition hover:bg-error-dark"
                        aria-label="Close image viewer"
                    >
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>

                    <div class="relative flex-1 w-full flex items-center justify-center overflow-hidden">
                        <img
                            :src="currentImageUrl"
                            alt="Large product view"
                            class="max-w-full max-h-full object-contain transition-transform duration-300"
                        />
                    </div>

                    <div v-if="imageCount > 1" class="absolute inset-y-0 w-full flex items-center justify-between pointer-events-none p-4 md:p-8">
                        <button
                            @click.stop="showPrevious"
                            class="pointer-events-auto p-3 rounded-full border-2 border-copy bg-primary text-primary-content transition hover:bg-primary-dark"
                            aria-label="Previous image"
                        >
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button
                            @click.stop="showNext"
                            class="pointer-events-auto p-3 rounded-full border-2 border-copy bg-primary text-primary-content transition hover:bg-primary-dark"
                            aria-label="Next image"
                        >
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>

                    <div v-if="imageCount > 1" class="mt-4 text-copy font-medium">
                        {{ currentModalIndex + 1 }} / {{ imageCount }}
                    </div>
                </div>
            </div>
        </div>
    </Transition>
    <Teleport to="body">
        <style scoped>
            .fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
            .fade-enter-from, .fade-leave-to { opacity: 0; }
        </style>
    </Teleport>
</template>
