<script setup lang="ts">
/**
 * SeoHead — drop-in SEO <Head> block for every page.
 *
 * Usage in any page:
 *
 *   import SeoHead from '@/components/SeoHead.vue';
 *
 *   // In <script setup>:
 *   const seo = useSeoHead({ title: 'Products', description: '...', canonical: '/products' });
 *
 *   // In <template>:
 *   <SeoHead v-bind="seo" />
 *
 * That's it. No need to write out all 12 meta tags on every page.
 */
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
    description: string;
    canonical: string;
    ogImage: string;
    ogType: string;
    robots: string;
    siteName: string;
}>();
</script>

<template>

    <Head>
        <title>{{ title }}</title>
        <meta name="description" :content="description" />
        <link rel="canonical" :href="canonical" />

        <!-- Open Graph -->
        <meta property="og:site_name" :content="siteName" />
        <meta property="og:type" :content="ogType" />
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description" />
        <meta property="og:url" :content="canonical" />
        <meta property="og:image" :content="ogImage" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="title" />
        <meta name="twitter:description" :content="description" />
        <meta name="twitter:image" :content="ogImage" />

        <!-- Robots -->
        <meta name="robots" :content="robots" />
    </Head>
</template>
