<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import NavBar from '@/components/NavBar.vue';
</script>

<template>

    <NavBar />

    <Head title="About Us" />

    <section class="py-20">

        <div class="mx-auto max-w-screen-xl p-4 md:p-8 lg:p-12">

            <div class="max-w-4xl mx-auto">

                <div class="mb-8 border-b-2 border-copy-light pb-4">
                    <h1 class="text-4xl sm:text-5xl font-black text-copy mb-2">About us</h1>
                </div>

                <div class="space-y-10">

                    <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold text-copy flex items-center gap-2">
                                <div class="about-us-content">
<h2>My Story: Chapter of You</h2>

<p>Welcome to <strong>Chapter of You</strong>, where <strong>my</strong> philosophy is simple and personal: <strong>"Your chapter, your self-care."</strong></p>

<p><strong>I</strong> believe that cultivating true wellness starts with dedicating moments to yourself. <strong>I am</strong> founded on the commitment to provide tools and experiences that encourage you to pause, appreciate the present, and intentionally prioritize your personal peace.</p>

<h3>Hand-Made Luxury: My Reed Diffusers</h3>

<p><strong>My</strong> current collection is driven by the art of creating the perfect, serene atmosphere in your home. <strong>I</strong> specialize exclusively in <strong>luxurious, hand-made reed diffusers</strong>. Every scent is meticulously blended and poured by hand, designed to transform your space into a personal sanctuary. <strong>I</strong> select only the highest quality ingredients, ensuring each diffuser provides a long-lasting, gentle reminder to breathe deeply and prioritize your inner balance.</p>

<h3>Dedicated Beauty Services: Launching in 2026</h3>

<p><strong>I am</strong> thrilled to announce that <strong>my</strong> mission to support your self-care journey will expand significantly in <strong>2026</strong>.</p>

<p><strong>I</strong> will be launching a curated range of <strong>dedicated beauty services</strong>. This expansion is part of <strong>my</strong> commitment to nurturing your wellbeing by helping you look and feel your absolute best through specialized, high-quality care. Keep an eye on <strong>my</strong> progress as <strong>I</strong> prepare to offer these exciting services.</p>

<h3>Join My Community</h3>

<p>At Chapter of You, you are more than a customer—you are an active participant in a community dedicated to <strong>intentional self-care and inner peace</strong>. <strong>I</strong> invite you to explore <strong>my</strong> exquisite diffusers today and look forward to sharing <strong>my</strong> next chapter with you in 2026. Thank you for letting <strong>me</strong> be a small part of your beautiful journey</p>


</div>
                            </h2>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>

</template>
