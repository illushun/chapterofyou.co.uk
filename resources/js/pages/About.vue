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

My Story: Chapter of You

Welcome to Chapter of You, where my philosophy is simple and personal: "Your chapter, your self-care."

I believe that cultivating true wellness starts with dedicating moments to yourself. I am founded on the commitment to provide tools and experiences that encourage you to pause, appreciate the present, and intentionally prioritize your personal peace.

Hand-Made Luxury: My Reed Diffusers

My current collection is driven by the art of creating the perfect, serene atmosphere in your home. I specialize exclusively in luxurious, hand-made reed diffusers. Every scent is meticulously blended and poured by hand, designed to transform your space into a personal sanctuary. I select only the highest quality ingredients, ensuring each diffuser provides a long-lasting, gentle reminder to breathe deeply and prioritize your inner balance.

Dedicated Beauty Services: Launching in 2026

I am thrilled to announce that my mission to support your self-care journey will expand significantly in 2026.

I will be launching a curated range of dedicated beauty services. This expansion is part of my commitment to nurturing your wellbeing by helping you look and feel your absolute best through specialized, high-quality care. Keep an eye on my progress as I prepare to offer these exciting services.

Join My Community

At Chapter of You, you are more than a customer—you are an active participant in a community dedicated to intentional self-care and inner peace. I invite you to explore my exquisite diffusers today and look forward to sharing my next chapter with you in 2026. Thank you for letting me be a small part of your beautiful journey.
                            </h2>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>

</template>
