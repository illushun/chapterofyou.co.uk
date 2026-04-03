<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    audiences: Record<string, string>;
    audienceCounts: Record<string, number>;
}>();

const form = useForm({
    subject: '',
    body: '',
    audience: 'all',
});

// Simple confirmation before sending
const confirmed = ref(false);
const confirmAndSend = () => { confirmed.value = true; };
const cancelConfirm = () => { confirmed.value = false; };

const submit = () => {
    form.post(route('admin.broadcasts.store'), {
        onError: () => { confirmed.value = false; },
    });
};

const selectedCount = computed(() => props.audienceCounts[form.audience] ?? 0);

// Live character counts
const subjectMax = 255;
const bodyMax = 20000;

// Minimal toolbar actions for the body textarea
// (We keep it as plain textarea — the HTML is passed raw to the blade template)
const insertTag = (open: string, close: string) => {
    const el = document.getElementById('body-input') as HTMLTextAreaElement;
    if (!el) return;
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const sel = form.body.substring(start, end);
    form.body = form.body.substring(0, start) + open + sel + close + form.body.substring(end);
    // Reposition cursor
    setTimeout(() => {
        el.selectionStart = start + open.length;
        el.selectionEnd = start + open.length + sel.length;
        el.focus();
    }, 0);
};
</script>

<template>
    <AdminLayout>

        <Head title="Compose Broadcast" />

        <!-- Header -->
        <div class="bc-header">
            <div class="bc-header-left">
                <Link :href="route('admin.broadcasts.index')" class="bc-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Broadcasts
                </Link>
                <h1 class="bc-title">Compose Broadcast</h1>
                <p class="bc-sub">Write and send an email to a segment of your customer list.</p>
            </div>
        </div>

        <!-- Confirmation overlay -->
        <div v-if="confirmed" class="bc-confirm-overlay">
            <div class="bc-confirm-box">
                <div class="bc-confirm-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>
                <h2 class="bc-confirm-title">Ready to send?</h2>
                <p class="bc-confirm-body">
                    You're about to send <strong>"{{ form.subject }}"</strong> to
                    <strong>{{ selectedCount.toLocaleString() }} recipient{{ selectedCount !== 1 ? 's' : '' }}</strong>
                    ({{ audiences[form.audience] }}).
                </p>
                <p class="bc-confirm-note">This cannot be undone. Emails will be queued immediately.</p>
                <div class="bc-confirm-actions">
                    <button @click="cancelConfirm" class="bc-btn bc-btn--ghost" type="button">
                        Go back
                    </button>
                    <button @click="submit" :disabled="form.processing" class="bc-btn bc-btn--send" type="button">
                        <svg v-if="form.processing" class="bc-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ form.processing ? 'Sending...' : `Send to ${selectedCount.toLocaleString()}` }}
                    </button>
                </div>
            </div>
        </div>

        <div class="bc-grid">

            <!-- ── Compose form ── -->
            <div class="bc-left">

                <!-- Subject -->
                <div class="bc-card">
                    <h2 class="bc-card-title">Subject Line</h2>
                    <div class="bc-field">
                        <input id="subject-input" v-model="form.subject" type="text" class="bc-input"
                            :class="{ 'bc-input--error': form.errors.subject }"
                            placeholder="e.g. Something special just for you..." :maxlength="subjectMax" />
                        <div class="bc-field-foot">
                            <p v-if="form.errors.subject" class="bc-error">{{ form.errors.subject }}</p>
                            <span class="bc-char-count" :class="{ 'bc-char-count--warn': form.subject.length > 200 }">
                                {{ form.subject.length }} / {{ subjectMax }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="bc-card">
                    <h2 class="bc-card-title">Email Body</h2>
                    <p class="bc-body-hint">
                        Write your message below. Basic HTML is supported — use the toolbar for quick formatting, or
                        write tags manually. The email template will wrap your content automatically.
                    </p>

                    <!-- Mini toolbar -->
                    <div class="bc-toolbar" role="toolbar" aria-label="Formatting">
                        <button type="button" @click="insertTag('<strong>', '</strong>')" class="bc-tool"
                            title="Bold"><strong>B</strong></button>
                        <button type="button" @click="insertTag('<em>', '</em>')" class="bc-tool bc-tool--italic"
                            title="Italic"><em>I</em></button>
                        <button type="button" @click="insertTag('<p>', '</p>')" class="bc-tool"
                            title="Paragraph">¶</button>
                        <button type="button" @click="insertTag('<h2>', '</h2>')" class="bc-tool"
                            title="Heading">H2</button>
                        <button type="button" @click="insertTag('<ul>\n  <li>', '</li>\n</ul>')" class="bc-tool"
                            title="List">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="8" y1="6" x2="21" y2="6" />
                                <line x1="8" y1="12" x2="21" y2="12" />
                                <line x1="8" y1="18" x2="21" y2="18" />
                                <line x1="3" y1="6" x2="3.01" y2="6" />
                                <line x1="3" y1="12" x2="3.01" y2="12" />
                                <line x1="3" y1="18" x2="3.01" y2="18" />
                            </svg>
                        </button>
                        <div class="bc-toolbar-sep"></div>
                        <button type="button" @click="insertTag('<a href=\'\'>', '</a>')" class="bc-tool" title="Link">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                            </svg>
                        </button>
                    </div>

                    <div class="bc-field">
                        <textarea id="body-input" v-model="form.body" rows="16" class="bc-textarea"
                            :class="{ 'bc-input--error': form.errors.body }"
                            placeholder="Write your email content here. You can use HTML tags for formatting.&#10;&#10;Example:&#10;&lt;p&gt;Hi there,&lt;/p&gt;&#10;&lt;p&gt;We have some exciting news to share...&lt;/p&gt;"></textarea>
                        <div class="bc-field-foot">
                            <p v-if="form.errors.body" class="bc-error">{{ form.errors.body }}</p>
                            <span class="bc-char-count">{{ form.body.length }} / {{ bodyMax.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Sidebar ── -->
            <aside class="bc-right">

                <!-- Audience selector -->
                <div class="bc-card">
                    <h2 class="bc-card-title">Audience</h2>
                    <p class="bc-audience-hint">Who should receive this email?</p>

                    <div class="bc-audience-options">
                        <label v-for="(label, key) in audiences" :key="key" class="bc-audience-option"
                            :class="{ 'bc-audience-option--selected': form.audience === key }">
                            <div class="bc-audience-radio-wrap">
                                <input type="radio" :value="key" v-model="form.audience" class="bc-radio" />
                            </div>
                            <div class="bc-audience-info">
                                <p class="bc-audience-label">{{ label }}</p>
                                <p class="bc-audience-count">
                                    {{ (audienceCounts[key] ?? 0).toLocaleString() }}
                                    recipient{{ (audienceCounts[key] ?? 0) !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </label>
                    </div>

                    <p v-if="form.errors.audience" class="bc-error">{{ form.errors.audience }}</p>
                </div>

                <!-- Send summary -->
                <div class="bc-card bc-send-card">
                    <h2 class="bc-card-title">Ready to Send?</h2>

                    <div class="bc-send-summary">
                        <div class="bc-send-row">
                            <span class="bc-send-label">Recipients</span>
                            <span class="bc-send-val">{{ selectedCount.toLocaleString() }}</span>
                        </div>
                        <div class="bc-send-row">
                            <span class="bc-send-label">Audience</span>
                            <span class="bc-send-val">{{ audiences[form.audience] }}</span>
                        </div>
                        <div class="bc-send-row">
                            <span class="bc-send-label">Subject</span>
                            <span class="bc-send-val bc-send-subject" :class="{ 'bc-send-placeholder': !form.subject }">
                                {{ form.subject || 'Not yet written' }}
                            </span>
                        </div>
                    </div>

                    <!-- Warnings -->
                    <div v-if="selectedCount === 0" class="bc-warning">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        No recipients in this segment.
                    </div>

                    <button type="button" @click="confirmAndSend"
                        :disabled="!form.subject.trim() || !form.body.trim() || selectedCount === 0"
                        class="bc-btn bc-btn--send bc-btn--full">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2 11 13" />
                            <path d="M22 2 15 22 11 13 2 9l20-7z" />
                        </svg>
                        Review &amp; Send
                    </button>

                    <p class="bc-send-note">
                        You'll see a confirmation before anything is sent.
                    </p>
                </div>

            </aside>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── Header ── */
.bc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.bc-header-left {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.bc-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--copy-light);
    text-decoration: none;
    transition: color 0.15s;
    margin-bottom: 0.2rem;
}

.bc-back:hover {
    color: var(--copy);
}

.bc-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--copy);
}

.bc-sub {
    font-size: 0.85rem;
    color: var(--copy-light);
}

/* ── Grid ── */
.bc-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .bc-grid {
        grid-template-columns: 1fr;
    }
}

.bc-left {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.bc-right {
    position: sticky;
    top: 80px;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ── Cards ── */
.bc-card {
    border: 2px solid var(--copy);
    border-radius: 12px;
    background: var(--foreground);
    padding: 1.5rem;
}

.bc-card-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--copy);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
}

/* ── Fields ── */
.bc-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.bc-field-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 1.25rem;
}

.bc-input {
    width: 100%;
    padding: 0.65rem 0.9rem;
    border: 2px solid color-mix(in srgb, var(--copy-light) 40%, transparent);
    border-radius: 8px;
    background: var(--background);
    color: var(--copy);
    font-family: inherit;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s;
}

.bc-input:focus {
    border-color: var(--primary);
}

.bc-input--error {
    border-color: #c84040;
}

.bc-error {
    font-size: 0.78rem;
    color: #b54040;
}

.bc-char-count {
    font-size: 0.72rem;
    color: var(--copy-lighter);
    margin-left: auto;
}

.bc-char-count--warn {
    color: #c84040;
}

/* ── Body textarea ── */
.bc-body-hint {
    font-size: 0.82rem;
    color: var(--copy-light);
    line-height: 1.5;
    margin-bottom: 0.85rem;
    font-style: italic;
}

.bc-toolbar {
    display: flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.4rem 0.5rem;
    background: var(--secondary-light);
    border: 2px solid color-mix(in srgb, var(--copy-light) 40%, transparent);
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    flex-wrap: wrap;
}

.bc-tool {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--copy-light);
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.bc-tool:hover {
    background: var(--foreground);
    color: var(--copy);
    border-color: color-mix(in srgb, var(--copy-light) 40%, transparent);
}

.bc-tool--italic {
    font-style: italic;
}

.bc-toolbar-sep {
    width: 1px;
    height: 18px;
    background: color-mix(in srgb, var(--copy-light) 35%, transparent);
    margin: 0 0.2rem;
}

.bc-textarea {
    width: 100%;
    padding: 0.75rem 0.9rem;
    border: 2px solid color-mix(in srgb, var(--copy-light) 40%, transparent);
    border-top: none;
    border-radius: 0 0 8px 8px;
    background: var(--background);
    color: var(--copy);
    font-family: 'Courier New', monospace;
    font-size: 0.88rem;
    line-height: 1.65;
    resize: vertical;
    outline: none;
    transition: border-color 0.2s;
    min-height: 320px;
}

.bc-textarea:focus {
    border-color: var(--primary);
}

/* ── Audience selector ── */
.bc-audience-hint {
    font-size: 0.82rem;
    color: var(--copy-light);
    margin-bottom: 0.85rem;
    font-style: italic;
}

.bc-audience-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.bc-audience-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    border: 2px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    background: var(--background);
}

.bc-audience-option:hover {
    border-color: color-mix(in srgb, var(--copy-light) 60%, transparent);
}

.bc-audience-option--selected {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 6%, transparent);
}

.bc-radio {
    accent-color: var(--primary);
    width: 15px;
    height: 15px;
    cursor: pointer;
}

.bc-audience-label {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--copy);
}

.bc-audience-count {
    font-size: 0.75rem;
    color: var(--copy-light);
    margin-top: 0.1rem;
}

/* ── Send card ── */
.bc-send-card {}

.bc-send-summary {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    margin-bottom: 1.1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid color-mix(in srgb, var(--copy-light) 25%, transparent);
}

.bc-send-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.75rem;
}

.bc-send-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--copy-lighter);
    flex-shrink: 0;
}

.bc-send-val {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--copy);
    text-align: right;
}

.bc-send-subject {
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bc-send-placeholder {
    color: var(--copy-lighter);
    font-style: italic;
    font-weight: 400;
}

.bc-warning {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #8a5a00;
    background: #fffbf0;
    border: 1px solid #e0c878;
    border-radius: 8px;
    padding: 0.55rem 0.75rem;
    margin-bottom: 0.85rem;
}

.bc-send-note {
    font-size: 0.75rem;
    color: var(--copy-lighter);
    font-style: italic;
    margin-top: 0.6rem;
    text-align: center;
}

/* ── Buttons ── */
.bc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    border: 2px solid var(--copy);
    font-family: inherit;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
}

.bc-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.bc-btn:not(:disabled):hover {
    transform: translateY(-1px);
}

.bc-btn--full {
    width: 100%;
}

.bc-btn--send {
    background: var(--primary);
    color: var(--primary-content);
    border-color: var(--primary);
}

.bc-btn--ghost {
    background: var(--foreground);
    color: var(--copy);
    border-color: color-mix(in srgb, var(--copy-light) 50%, transparent);
}

/* ── Confirm overlay ── */
.bc-confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    backdrop-filter: blur(4px);
}

.bc-confirm-box {
    width: 100%;
    max-width: 440px;
    background: var(--foreground);
    border: 2px solid var(--copy);
    border-radius: 16px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25);
}

.bc-confirm-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: 2px solid var(--copy);
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.bc-confirm-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--copy);
    text-align: center;
}

.bc-confirm-body {
    font-size: 0.92rem;
    color: var(--copy-light);
    text-align: center;
    line-height: 1.6;
}

.bc-confirm-body strong {
    color: var(--copy);
}

.bc-confirm-note {
    font-size: 0.8rem;
    color: var(--copy-lighter);
    text-align: center;
    font-style: italic;
}

.bc-confirm-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.5rem;
}

.bc-confirm-actions .bc-btn {
    flex: 1;
}

/* ── Spinner ── */
.bc-spinner {
    width: 15px;
    height: 15px;
    animation: bc-spin 0.8s linear infinite;
}

@keyframes bc-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
