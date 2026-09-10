<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    items: Array<{ label: string; href?: string }>;
}>();
</script>

<template>
    <nav class="coy-breadcrumbs" aria-label="Breadcrumb">
        <ol class="coy-breadcrumbs__list">
            <li v-for="(item, index) in items" :key="item.label">
                <Link
                    v-if="item.href && index < items.length - 1"
                    :href="item.href"
                    class="coy-breadcrumbs__link"
                >
                    {{ item.label }}
                </Link>
                <span v-else aria-current="page">{{ item.label }}</span>
                <svg
                    v-if="index < items.length - 1"
                    class="coy-breadcrumbs__separator"
                    aria-hidden="true"
                    viewBox="0 0 16 16"
                >
                    <path d="m6 3 5 5-5 5" />
                </svg>
            </li>
        </ol>
    </nav>
</template>
