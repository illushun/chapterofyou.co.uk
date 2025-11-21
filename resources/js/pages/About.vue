<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';

import NavBar from '@/components/NavBar.vue';

const IconLeaf = `<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.66 0-3.18.9-3.9 2.3A5.5 5.5 0 0 0 7 3c-3 0-5 2.2-5 5.5 0 2.3 1.5 4.04 3 5.5L12 20l7-6z"/></svg>`;

const sections = [
    { type: 'h2', content: 'About us', classes: 'text-4xl md:text-5xl font-black text-copy tracking-tight border-b-2 pb-4 border-copy-light mb-6' },
    { type: 'p', content: 'Welcome to Chapter of You, where my philosophy is simple and personal: "Your chapter, your self-care."', classes: 'text-lg md:text-xl leading-relaxed' },
    { type: 'p', content: 'I believe that cultivating true wellness starts with dedicating moments to yourself. I am founded on the commitment to provide tools and experiences that encourage you to pause, appreciate the present, and intentionally prioritise your personal peace.', classes: 'text-lg leading-relaxed' },
    { type: 'divider', content: IconLeaf, classes: 'flex items-center justify-center py-4' },
    { type: 'h3', content: 'My Reed Diffusers', classes: 'text-3xl font-extrabold text-primary pt-2' },
    { type: 'p', content: 'My current collection is driven by the art of creating the perfect, serene atmosphere in your home. I specialise exclusively in luxurious, hand-made reed diffusers. Every scent is meticulously blended and poured by hand, designed to transform your space into a personal sanctuary. I select only the highest quality ingredients, ensuring each diffuser provides a long-lasting, gentle reminder to breathe deeply and prioritise your inner balance.', classes: 'text-lg leading-relaxed' },
    { type: 'h3', content: 'Launching in 2026', classes: 'text-3xl font-extrabold text-primary pt-2' },
    { type: 'p', content: 'I am thrilled to announce that my mission to support your self-care journey will expand significantly in 2026.', classes: 'text-lg leading-relaxed' },
    { type: 'p', content: 'I will be launching a curated range of dedicated beauty services. This expansion is part of my commitment to nurturing your wellbeing by helping you look and feel your absolute best through specialised, high-quality care. Keep an eye on my progress as I prepare to offer these exciting services.', classes: 'text-lg leading-relaxed' },
    { type: 'divider', content: IconLeaf, classes: 'flex items-center justify-center py-4' },
    { type: 'h3', content: 'Join My Community', classes: 'text-3xl font-extrabold text-primary pt-2' },
    { type: 'p', content: 'At Chapter of You, you are more than a customer, you are an active participant in a community dedicated to intentional self-care and inner peace. I invite you to explore my exquisite diffusers today and look forward to sharing my next chapter with you in 2026. Thank you for letting me be a small part of your beautiful journey', classes: 'text-lg leading-relaxed' }
];

const elementRefs = ref<HTMLElement[]>([]);
const setRef = (el: any) => {
    if (el) {
        elementRefs.value.push(el);
    }
};

onMounted(async () => {
    await nextTick();

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        root: null,
        rootMargin: '0px',
        threshold: 0.2
    });

    elementRefs.value.forEach(element => {
        if (element) {
            observer.observe(element);
        }
    });
});
</script>

<template>
    <NavBar />

    <Head title="About Us" />

    <style scoped>
        .fade-in-section {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .fade-in-section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <section class="py-20">
        <div class="mx-auto max-w-screen-xl p-4 md:p-8 lg:p-12">

            <div
                class="border-2 border-copy bg-foreground p-6 sm:p-10 md:p-12 rounded-xl transition duration-500 hover:shadow-2xl space-y-4 text-copy">
                <template v-for="(section, index) in sections" :key="index">

                    <div :ref="setRef" class="fade-in-section">
                        <div v-if="section.type === 'p' || section.type === 'h2' || section.type === 'h3'">
                            <component :is="section.type" :class="section.classes" v-html="section.content">
                            </component>
                        </div>

                        <div v-else-if="section.type === 'divider'" :class="section.classes">
                            <hr v-if="!section.reverse" class="flex-grow border-t-2 border-copy-light w-1/4" />
                            <div v-html="section.content" :class="section.reverse ? 'order-1 mx-4' : 'mx-4'"></div>
                            <hr
                                :class="['flex-grow border-t-2 border-copy-light w-1/4', section.reverse ? 'order-2' : '']" />
                        </div>

                    </div>
                </template>

            </div>

        </div>
    </section>
</template>
