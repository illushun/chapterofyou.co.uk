<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

import NavBar from '@/components/NavBar.vue';

const props = defineProps<{
    id: number;
    request: any;
}>();
</script>

<template>
    <NavBar />

    <Head title="Order Success!" />

    <section class="py-20">

        <div class="min-h-screen bg-background text-copy p-4 md:p-8 lg:p-12">
            <div class="max-w-6xl mx-auto">

                <div class="mb-8">
                    <h1 class="text-5xl font-black text-copy mb-2">Thank you for your order {{ props.id }}</h1>
                    <a href="/products" class="inline-flex items-center text-primary hover:text-primary-dark transition font-semibold">
                        <div v-html="IconArrowLeft" class="size-5 mr-2"></div>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>

    </section>

</template>
