<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';

import NavBar from '@/components/NavBar.vue';
</script>

<template>
    <NavBar />

    <Head title="Contact" />

    <section class="py-20 bg-background min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            Hello world
        </div>
    </section>

</template>
