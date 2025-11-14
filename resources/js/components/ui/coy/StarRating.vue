<template>
    <div class="flex items-center" :style="{ gap: spacing }">
        <div v-for="i in 5" :key="i" class="relative" @click="handleClick(i)" @mousemove="handleMouseMove(i)"
            @mouseleave="handleMouseLeave" :class="{ 'cursor-pointer': editable }">

            <svg :class="[starClass, 'text-gray-300 dark:text-gray-700']" fill="currentColor" viewBox="0 0 20 20"
                aria-hidden="true" :style="{ width: `${size}px`, height: `${size}px` }">
                <path
                    d="M9.049 2.927c.3-.921 1.691-.921 1.99 0l1.621 4.996a1 1 0 00.95.691h5.253c.97 0 1.371 1.24.588 1.839l-4.253 3.084a1 1 0 00-.364 1.118l1.621 4.996c.3.921-.755 1.688-1.542 1.118l-4.253-3.084a1 1 0 00-1.175 0l-4.253 3.084c-.787.57-1.842-.197-1.542-1.118l1.621-4.996a1 1 0 00-.364-1.118L2.052 8.453c-.783-.599-.382-1.839.588-1.839h5.253a1 1 0 00.95-.691l1.621-4.996z">
                </path>
            </svg>

            <svg v-if="getFillPercentage(i) > 0" class="absolute top-0 left-0" :class="starClass" fill="currentColor"
                viewBox="0 0 20 20" aria-hidden="true"
                :style="{ width: `${size}px`, height: `${size}px`, clipPath: `inset(0 ${100 - getFillPercentage(i)}% 0 0)` }">
                <path
                    d="M9.049 2.927c.3-.921 1.691-.921 1.99 0l1.621 4.996a1 1 0 00.95.691h5.253c.97 0 1.371 1.24.588 1.839l-4.253 3.084a1 1 0 00-.364 1.118l1.621 4.996c.3.921-.755 1.688-1.542 1.118l-4.253-3.084a1 1 0 00-1.175 0l-4.253 3.084c-.787.57-1.842-.197-1.542-1.118l1.621-4.996a1 1 0 00-.364-1.118L2.052 8.453c-.783-.599-.382-1.839.588-1.839h5.253a1 1 0 00.95-.691l1.621-4.996z">
                </path>
            </svg>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    rating: number; // The current rating value (e.g., 3.7 or 4)
    editable: boolean; // If true, allows the user to click/hover to select a rating
    size?: number; // Size in pixels (e.g., 24)
    spacing?: string; // CSS gap value (e.g., '2px' or '0.25rem')
}>();

const emit = defineEmits(['update:rating']);
const hoverRating = ref(0);

// The rating currently being displayed (either actual rating or hover rating)
const displayRating = computed(() => {
    return props.editable && hoverRating.value > 0
        ? hoverRating.value
        : props.rating;
});

// Calculate the fill percentage for a star index
const getFillPercentage = (starIndex: number): number => {
    const value = displayRating.value;

    if (value >= starIndex) {
        return 100; // Fully filled
    }
    if (value > starIndex - 1) {
        // Partially filled star (e.g., rating 3.7 for star 4)
        return (value - (starIndex - 1)) * 100;
    }
    return 0; // Empty
};

// --- Event Handlers for Editable Mode ---

const handleClick = (starIndex: number) => {
    if (!props.editable) return;

    // Toggle logic: If they click the current rating, reset to 0, otherwise set the new rating
    const newRating = starIndex === props.rating ? 0 : starIndex;
    emit('update:rating', newRating);
};

const handleMouseMove = (starIndex: number) => {
    if (!props.editable) return;
    hoverRating.value = starIndex;
};

const handleMouseLeave = () => {
    if (!props.editable) return;
    hoverRating.value = 0;
};

// --- CSS Helpers ---
// Use an empty string for the class if Tailwind utility is passed via the component's `class` attribute.
const starClass = computed(() => 'transition-colors duration-200');

const size = computed(() => props.size ?? 20); // Default size to 20px
const spacing = computed(() => props.spacing ?? '0.1rem'); // Default spacing
</script>
