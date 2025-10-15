<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue'; // Import necessary functions

// Icon for the section divider or emphasis (using text-primary)
const IconLeaf = `<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2m0 16v2m-6-8H4m16 0h-2m-2-7L5.5 5.5m11.3 11.3L18.5 18.5m-11.3.1L5.5 18.5M18.5 5.5l-2.1 2.1"/></svg>`;

// --- 1. Content defined as a Structured Array for easy iteration and animation ---
const sections = [
    { type: 'h2', content: 'About us', classes: 'text-4xl md:text-5xl font-black text-copy tracking-tight border-b-2 pb-4 border-copy-light mb-6' },
    { type: 'p', content: 'Welcome to Chapter of You, where my philosophy is simple and personal: "Your chapter, your self-care."', classes: 'text-lg md:text-xl leading-relaxed' },
    { type: 'p', content: 'I believe that cultivating true wellness starts with dedicating moments to yourself. I am founded on the commitment to provide tools and experiences that encourage you to pause, appreciate the present, and intentionally prioritize your personal peace.', classes: 'text-lg leading-relaxed' },
    { type: 'divider', content: IconLeaf, classes: 'flex items-center justify-center py-4' },
    { type: 'h3', content: 'Hand-Made Luxury: My Reed Diffusers', classes: 'text-3xl font-extrabold text-primary pt-2' },
    { type: 'p', content: 'My current collection is driven by the art of creating the perfect, serene atmosphere in your home. I specialize exclusively in luxurious, hand-made reed diffusers. Every scent is meticulously blended and poured by hand, designed to transform your space into a personal sanctuary. I select only the highest quality ingredients, ensuring each diffuser provides a long-lasting, gentle reminder to breathe deeply and prioritize your inner balance.', classes: 'text-lg leading-relaxed' },
    { type: 'h3', content: 'Dedicated Beauty Services: Launching in 2026', classes: 'text-3xl font-extrabold text-primary pt-2' },
    { type: 'p', content: 'I am thrilled to announce that my mission to support your self-care journey will expand significantly in 2026.', classes: 'text-lg leading-relaxed' },
    { type: 'p', content: 'I will be launching a curated range of dedicated beauty services. This expansion is part of my commitment to nurturing your wellbeing by helping you look and feel your absolute best through specialized, high-quality care. Keep an eye on my progress as I prepare to offer these exciting services.', classes: 'text-lg leading-relaxed' },
    { type: 'divider', content: IconLeaf, classes: 'flex items-center justify-center py-4', reverse: true },
    { type: 'h3', content: 'Join My Community', classes: 'text-3xl font-extrabold text-primary pt-2' },
    { type: 'p', content: 'At Chapter of You, you are more than a customer—you are an active participant in a community dedicated to intentional self-care and inner peace. I invite you to explore my exquisite diffusers today and look forward to sharing my next chapter with you in 2026. Thank you for letting me be a small part of your beautiful journey', classes: 'text-lg leading-relaxed' }
];

// --- 2. Intersection Observer Implementation ---

// Array of refs to hold all the section elements
const elementRefs = ref<HTMLElement[]>([]);
const setRef = (el: any) => {
    // Collects all elements in the v-for loop
    if (el) {
        elementRefs.value.push(el);
    }
};

onMounted(async () => {
    // Wait for the DOM to fully render the v-for elements before observing
    await nextTick();

    // Create the Intersection Observer to check when elements enter the viewport
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add 'is-visible' class to trigger the CSS transition
                entry.target.classList.add('is-visible');
                // Stop observing the element once it has been animated
                observer.unobserve(entry.target);
            }
        });
    }, {
        root: null, // relative to the viewport
        rootMargin: '0px',
        threshold: 0.2 // Trigger when 20% of the element is visible
    });

    // Start observing all elements collected via setRef
    elementRefs.value.forEach(element => {
        if (element) {
            observer.observe(element);
        }
    });
});
</script>

<template>
    <Head title="About Us" />

    <!-- Custom CSS for the fade-in effect: initial state and transition -->
    <style scoped>
    /* Initial state: Invisible and slightly shifted up (for fade-up effect) */
    .fade-in-section {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }

    /* Target state: Fully visible and in original position */
    .fade-in-section.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
    </style>

    <section class="py-12 md:py-20 bg-background min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div
                class="border-2 border-copy bg-foreground p-6 sm:p-10 md:p-12 rounded-xl shadow-xl transition duration-500 hover:shadow-2xl space-y-4 text-copy"
            >
                <!-- Iterate through the structured content array -->
                <template v-for="(section, index) in sections" :key="index">

                    <!-- Wrapper div for the fade-in animation, which gets the reference -->
                    <div :ref="setRef" class="fade-in-section">
                        <!-- Render content based on type -->

                        <div v-if="section.type === 'p' || section.type === 'h2' || section.type === 'h3'">
                            <component
                                :is="section.type"
                                :class="section.classes"
                                v-html="section.content"
                            >
                            </component>
                        </div>

                        <!-- Special rendering for the divider component -->
                        <div v-else-if="section.type === 'divider'" :class="section.classes">
                            <hr v-if="!section.reverse" class="flex-grow border-t-2 border-copy-light w-1/4"/>
                            <div v-html="section.content" :class="section.reverse ? 'order-1 mx-4' : 'mx-4'"></div>
                            <hr :class="['flex-grow border-t-2 border-copy-light w-1/4', section.reverse ? 'order-2' : '']"/>
                        </div>

                    </div>
                </template>

            </div>

        </div>
    </section>
</template>

<style>
.rose-text {
}
</style>
