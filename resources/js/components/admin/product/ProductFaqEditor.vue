<script setup lang="ts">
import type { ProductFaq } from '@/types/product';

const faqs = defineModel<ProductFaq[]>({ required: true });

function addFaq() {
    faqs.value = [...faqs.value, { question: '', answer: '' }];
}

function removeFaq(index: number) {
    faqs.value = faqs.value.filter((_, i) => i !== index);
}
</script>

<template>
    <section class="adm-card">
        <h2 class="adm-card-title">
            FAQs
            <span class="adm-card-title-note">shown on product page</span>
        </h2>
        <p class="pe-hint">
            Add product-specific questions. These display as a collapsible
            accordion on the product page.
        </p>

        <div class="pe-faq-list">
            <div v-for="(faq, i) in faqs" :key="i" class="pe-faq-item">
                <div class="pe-faq-num">{{ i + 1 }}</div>
                <div class="pe-faq-fields">
                    <div class="adm-field">
                        <label
                            class="adm-label"
                            style="
                                font-size: 0.7rem;
                                letter-spacing: 0.07em;
                                text-transform: uppercase;
                                color: var(--adm-ink-dim);
                            "
                            >Question</label
                        >
                        <input
                            type="text"
                            v-model="faq.question"
                            class="adm-input adm-input--sm"
                            placeholder="e.g. How long does the scent last?"
                        />
                    </div>
                    <div class="adm-field">
                        <label
                            class="adm-label"
                            style="
                                font-size: 0.7rem;
                                letter-spacing: 0.07em;
                                text-transform: uppercase;
                                color: var(--adm-ink-dim);
                            "
                            >Answer</label
                        >
                        <textarea
                            v-model="faq.answer"
                            rows="3"
                            class="adm-textarea adm-input--sm"
                            placeholder="e.g. My fragrances typically last 6–8 hours on skin..."
                        ></textarea>
                    </div>
                </div>
                <button
                    type="button"
                    @click="removeFaq(i)"
                    class="pe-icon-remove"
                    aria-label="Remove FAQ"
                >
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p v-if="faqs.length === 0" class="pe-empty-note">
                No FAQs added yet.
            </p>
        </div>

        <button type="button" @click="addFaq" class="pe-dashed-btn">
            <svg
                width="13"
                height="13"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
            >
                <path d="M12 5v14M5 12h14" />
            </svg>
            Add FAQ
        </button>
    </section>
</template>

<style scoped>
.pe-faq-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.pe-faq-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem;
    border-radius: var(--adm-radius);
    background: var(--adm-paper);
    border: 1px solid var(--adm-line);
}

.pe-faq-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    flex-shrink: 0;
    background: var(--adm-stamp-dim);
    color: var(--adm-stamp-deep);
    font-size: 0.68rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
}

.pe-faq-fields {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
</style>
