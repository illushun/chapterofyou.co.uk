<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head } from '@inertiajs/vue3';

interface LandingPageProps {
}

const props = defineProps<LandingPageProps>();
</script>

<template>
    <NavBar />

    <Head title="Chapter of You" />

</template>
