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
                    <h1 class="text-4xl sm:text-5xl font-black text-copy mb-2">My Account</h1>
                    <p class="text-lg text-copy-lighter">Manage your personal details, security, and shipping addresses.</p>
                </div>

                <div class="space-y-10">

                    <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold text-copy flex items-center gap-2">
                                General Details
                            </h2>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>

</template>
