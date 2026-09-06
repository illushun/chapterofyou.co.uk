<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    /** A single schema object or an array of schema objects */
    schema: object | object[];
}>();

// Normalise to array - each item becomes its own <script> tag
const schemaArray = computed(() =>
    Array.isArray(props.schema) ? props.schema : [props.schema],
);
</script>

<template>
    <Head>
        <component
            v-for="(item, index) in schemaArray"
            :key="index"
            :is="'script'"
            type="application/ld+json"
            >{{ JSON.stringify(item) }}</component
        >
    </Head>
</template>
