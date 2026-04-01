<script setup lang="ts">
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="auth-layout">
        <slot />
    </div>
</template>

<style scoped>
.auth-layout {
    min-height: 100svh;
    background: #fdf4f3;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
</style>
