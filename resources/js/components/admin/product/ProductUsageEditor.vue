<script setup lang="ts">
defineProps<{ error?: string }>();
const instructions = defineModel<string>({ required: true });
</script>

<template>
    <section class="adm-card">
        <h2 class="adm-card-title">
            How to Use
            <span class="adm-card-title-note">shown on product page</span>
        </h2>
        <p class="pe-hint">
            Write instructions for the customer. Supports basic HTML:
            paragraphs, lists, bold text.
        </p>
        <div class="adm-field">
            <textarea
                v-model="instructions"
                rows="6"
                class="adm-textarea pe-mono"
                placeholder="<p>Apply a few drops to your wrists...</p>&#10;<ul>&#10;  <li>Step 1: ...</li>&#10;</ul>"
            ></textarea>
            <p v-if="error" class="adm-err">
                {{ error }}
            </p>
        </div>
        <!-- Live preview -->
        <div v-if="instructions?.trim()" class="pe-preview">
            <p class="pe-preview-label">Preview</p>
            <div class="pe-preview-body" v-html="instructions"></div>
        </div>
    </section>
</template>

<style scoped>
.pe-mono {
    font-family: var(--adm-font, monospace);
    font-size: 0.82rem;
    line-height: 1.65;
}

.pe-preview {
    border: 1px solid var(--adm-line);
    border-radius: var(--adm-radius);
    background: var(--adm-paper);
    overflow: hidden;
}

.pe-preview-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--adm-ink-dim);
    padding: 0.4rem 0.85rem;
    background: var(--adm-paper-raised);
    border-bottom: 1px solid var(--adm-line);
}

.pe-preview-body {
    padding: 0.85rem 1rem;
    font-size: 0.88rem;
    color: var(--adm-ink);
    line-height: 1.7;
}

.pe-preview-body :deep(p) {
    margin-bottom: 0.65rem;
}

.pe-preview-body :deep(ul),
.pe-preview-body :deep(ol) {
    padding-left: 1.25rem;
    margin-bottom: 0.65rem;
}

.pe-preview-body :deep(li) {
    margin-bottom: 0.25rem;
}

.pe-preview-body :deep(strong) {
    font-weight: 700;
}
</style>
