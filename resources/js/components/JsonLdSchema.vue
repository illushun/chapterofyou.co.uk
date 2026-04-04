<script setup lang="ts">
/**
 * JsonLdSchema — injects JSON-LD structured data into the page <head>.
 *
 * Accepts one schema object or an array of schema objects.
 * Each is rendered as a separate <script type="application/ld+json"> tag
 * via Inertia's <Head> component so it appears in the raw HTML that
 * Googlebot fetches on first load.
 *
 * Usage — single schema:
 *
 *   import JsonLdSchema from '@/components/JsonLdSchema.vue';
 *   import { useProductSchema } from '@/composables/useProductSchema';
 *
 *   const schema = useProductSchema({ product, slug: product.seo?.slug });
 *
 *   <JsonLdSchema :schema="schema" />
 *
 * Usage — multiple schemas on the same page (e.g. homepage):
 *
 *   import { useOrganizationSchema, useWebsiteSchema } from '@/composables/useProductSchema';
 *
 *   const schemas = [useOrganizationSchema(), useWebsiteSchema()];
 *
 *   <JsonLdSchema :schema="schemas" />
 *
 * Usage — product page with breadcrumb:
 *
 *   import { useProductSchema, useBreadcrumbSchema } from '@/composables/useProductSchema';
 *
 *   const schemas = [
 *     useProductSchema({ product, slug }),
 *     useBreadcrumbSchema([
 *       { name: 'Home',     url: '/' },
 *       { name: 'Products', url: '/products' },
 *       { name: product.name, url: `/product/${slug}` },
 *     ]),
 *   ];
 *
 *   <JsonLdSchema :schema="schemas" />
 */
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    /** A single schema object or an array of schema objects */
    schema: object | object[];
}>();

// Normalise to array — each item becomes its own <script> tag
const schemaArray = computed(() =>
    Array.isArray(props.schema) ? props.schema : [props.schema]
);
</script>

<template>

    <Head>
        <!--
            One <script type="application/ld+json"> per schema object.
            Inertia serialises these into the <head> on first server render,
            so Googlebot sees them in the raw HTML response.

            v-for on a <script> tag inside <Head> is valid in Inertia —
            each iteration produces a separate <script> element in <head>.
        -->
        <component v-for="(item, index) in schemaArray" :key="index" :is="'script'" type="application/ld+json"
            v-text="JSON.stringify(item)" />
    </Head>
</template>
