<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

// Icon for the section divider or emphasis (using text-primary)
const IconLeaf = `<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.032 2.032m-3.936-2.032L12 5.064M18 17l-2.032-2.032m3.936 2.032L12 18.936M12 21v-4m-2-2h4m5-16l2.032 2.032m-3.936-2.032L12 5.064M18 17l-2.032-2.032m3.936 2.032L12 18.936"/></svg>`;

// The HTML content you provided, injected as a raw string
const aboutContentHtml = `
<div class="about-us-content space-y-6 md:space-y-8 text-copy">
    <h2 class="text-4xl md:text-5xl font-black text-copy mb-6 tracking-tight border-b-2 pb-4 border-copy-light">My Story: Chapter of You</h2>

    <p class="text-lg md:text-xl leading-relaxed">Welcome to <strong>Chapter of You</strong>, where <strong>my</strong> philosophy is simple and personal: <strong>"Your chapter, your self-care."</strong></p>

    <p class="text-lg leading-relaxed"><strong>I</strong> believe that cultivating true wellness starts with dedicating moments to yourself. <strong>I am</strong> founded on the commitment to provide tools and experiences that encourage you to pause, appreciate the present, and intentionally prioritize your personal peace.</p>

    <div class="flex items-center justify-center py-4">
        <div v-html="${IconLeaf}" class="text-primary mx-4"></div>
        <hr class="flex-grow border-t-2 border-copy-light w-1/4"/>
    </div>

    <h3 class="text-3xl font-extrabold text-primary pt-2">Hand-Made Luxury: My Reed Diffusers</h3>

    <p class="text-lg leading-relaxed"><strong>My</strong> current collection is driven by the art of creating the perfect, serene atmosphere in your home. <strong>I</strong> specialize exclusively in <strong>luxurious, hand-made reed diffusers</strong>. Every scent is meticulously blended and poured by hand, designed to transform your space into a personal sanctuary. <strong>I</strong> select only the highest quality ingredients, ensuring each diffuser provides a long-lasting, gentle reminder to breathe deeply and prioritize your inner balance.</p>

    <h3 class="text-3xl font-extrabold text-primary pt-2">Dedicated Beauty Services: Launching in 2026</h3>

    <p class="text-lg leading-relaxed"><strong>I am</strong> thrilled to announce that <strong>my</strong> mission to support your self-care journey will expand significantly in <strong>2026</strong>.</p>

    <p class="text-lg leading-relaxed"><strong>I</strong> will be launching a curated range of <strong>dedicated beauty services</strong>. This expansion is part of <strong>my</strong> commitment to nurturing your wellbeing by helping you look and feel your absolute best through specialized, high-quality care. Keep an eye on <strong>my</strong> progress as <strong>I</strong> prepare to offer these exciting services.</p>

    <div class="flex items-center justify-center py-4">
        <hr class="flex-grow border-t-2 border-copy-light w-1/4"/>
        <div v-html="${IconLeaf}" class="text-primary mx-4"></div>
        <hr class="flex-grow border-t-2 border-copy-light w-1/4"/>
    </div>

    <h3 class="text-3xl font-extrabold text-primary pt-2">Join My Community</h3>

    <p class="text-lg leading-relaxed">At Chapter of You, you are more than a customer—you are an active participant in a community dedicated to <strong>intentional self-care and inner peace</strong>. <strong>I</strong> invite you to explore <strong>my</strong> exquisite diffusers today and look forward to sharing <strong>my</strong> next chapter with you in 2026. Thank you for letting <strong>me</strong> be a small part of your beautiful journey</p>
</div>
`;
</script>

<template>
    <Head title="About Us" />

    <section class="py-12 md:py-20 bg-background min-h-screen">
        <!-- The container width is kept at max-w-4xl for a good article reading experience -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div
                v-html="aboutContentHtml"
                class="border-2 border-copy bg-foreground p-6 sm:p-10 md:p-12 rounded-xl shadow-xl transition duration-500 hover:shadow-2xl"
            ></div>

        </div>
    </section>
</template>
