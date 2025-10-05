<script setup lang="ts">
import { computed } from 'vue';

// Define the component's props with TypeScript
interface Props {
    // Allows the button to render as a button, a link (<a>), or another component (e.g., Link from Inertia)
    as?: 'button' | 'a' | 'Link'; 
    
    // Controls the visual style of the button (default is the primary indigo color)
    variant?: 'primary' | 'secondary' | 'danger';
    
    // Optional prop for disabling the button (useful during form submission)
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
    variant: 'primary',
    disabled: false,
});

// Compute the dynamic Tailwind classes based on the 'variant' prop
const classes = computed(() => {
    switch (props.variant) {
        case 'secondary':
            // White button with indigo text and border (often used for "Book a Service")
            return 'bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-50 active:bg-indigo-100 focus:ring-indigo-500';
        case 'danger':
            // Red button (typically for deleting/canceling actions in admin)
            return 'bg-red-600 text-white hover:bg-red-700 active:bg-red-900 focus:ring-red-500';
        case 'primary':
        default:
            // Default Indigo button (used for major calls to action like "Shop Now")
            return 'bg-indigo-600 text-white hover:bg-indigo-700 active:bg-indigo-900 focus:ring-indigo-500';
    }
});
</script>

<template>
    <component 
        :is="props.as"
        :type="props.as === 'button' ? 'submit' : undefined"
        :disabled="props.disabled"
        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150 shadow-md 
               focus:outline-none focus:ring-2 focus:ring-offset-2
               
               "
        :class="[
            classes, 
            { 'opacity-50 cursor-not-allowed': props.disabled }
        ]"
    >
        <slot />
    </component>
</template>