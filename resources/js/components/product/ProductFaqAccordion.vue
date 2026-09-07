<script setup lang="ts">
import type { ProductFaq } from '@/types/product';
import { ref } from 'vue';

defineProps<{ faqs: ProductFaq[] }>();
const openFaqIndex = ref<number | null>(null);

function toggleFaq(index: number) {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
}
</script>

<template>
    <div class="pd-faq-list">
        <div v-for="(faq, i) in faqs" :key="i" class="pd-faq-item">
            <button
                class="pd-faq-trigger"
                @click="toggleFaq(i)"
                :aria-expanded="openFaqIndex === i"
                :aria-controls="`faq-body-${i}`"
            >
                <span class="pd-faq-q">{{ faq.question }}</span>
                <svg
                    class="pd-faq-chevron"
                    :class="{
                        'pd-faq-chevron--open': openFaqIndex === i,
                    }"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>
            <div
                :id="`faq-body-${i}`"
                class="pd-faq-body"
                :class="{ 'pd-faq-body--open': openFaqIndex === i }"
            >
                <p class="pd-faq-a">{{ faq.answer }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* FAQ accordion */
.pd-faq-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.pd-faq-item {
    border: 1px solid #e5c9c7;
    border-radius: 14px;
    background: #fffafa;
    overflow: hidden;
    transition: box-shadow 0.2s;
}

.pd-faq-item:hover {
    box-shadow: 0 2px 12px rgba(229, 201, 199, 0.4);
}

.pd-faq-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background 0.15s;
}

.pd-faq-trigger:hover {
    background: rgba(229, 201, 199, 0.12);
}

.pd-faq-q {
    font-family: 'Nunito', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    color: #2d1a1a;
    line-height: 1.4;
}

.pd-faq-chevron {
    color: #8c4a50;
    flex-shrink: 0;
    transition: transform 0.25s ease;
}

.pd-faq-chevron--open {
    transform: rotate(180deg);
}

/* Collapse/expand via max-height transition */
.pd-faq-body {
    max-height: 0;
    overflow: hidden;
    transition:
        max-height 0.3s ease,
        padding 0.3s ease;
    padding: 0 1.25rem;
}

.pd-faq-body--open {
    max-height: 800px;
    padding: 0 1.25rem 1.1rem;
}

.pd-faq-a {
    font-size: 1rem;
    color: #6b4f4f;
    line-height: 1.7;
    padding-top: 0.6rem;
    border-top: 1px solid #f0dcd8;
}
</style>
