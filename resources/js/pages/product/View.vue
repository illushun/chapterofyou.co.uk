<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3'; // For setting page head elements

// --- TypeScript Interface Definitions for Type Safety ---

interface ProductProps {
    products: any;
}

// Define props using the defined interface, ensuring type checking at compile time
const props = defineProps<ProductProps>();
</script>

<template>
    <div v-if="props.products" class="mb-6">
        <div v-for="product in props.products">
            {{  product['mpn'] }} {{ product['cost'] }}
        </div>
    </div>

</template>
