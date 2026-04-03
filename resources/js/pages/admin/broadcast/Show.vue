<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Broadcast {
    id: number;
    subject: string;
    body: string;
    audience: string;
    recipient_count: number;
    sent_at: string;
    sender: { id: number; name: string } | null;
}

defineProps<{
    broadcast: Broadcast;
    audienceLabel: string;
}>();

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
</script>

<template>
    <AdminLayout>

        <Head :title="`Broadcast #${broadcast.id}`" />

        <!-- Header -->
        <div class="bs-header">
            <div>
                <Link :href="route('admin.broadcasts.index')" class="bs-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                All Broadcasts
                </Link>
                <h1 class="bs-title">Broadcast #{{ broadcast.id }}</h1>
                <p class="bs-sub">Sent {{ fmtDate(broadcast.sent_at) }}</p>
            </div>
        </div>

        <div class="bs-grid">
            <!-- Body preview -->
            <div class="bs-main">
                <div class="bs-card">
                    <h2 class="bs-card-title">Subject</h2>
                    <p class="bs-subject">{{ broadcast.subject }}</p>
                </div>

                <div class="bs-card">
                    <h2 class="bs-card-title">Email Body Preview</h2>
                    <p class="bs-preview-note">This is how your body content appeared in the email template.</p>
                    <div class="bs-preview-wrap">
                        <div class="bs-preview" v-html="broadcast.body"></div>
                    </div>
                </div>
            </div>

            <!-- Meta sidebar -->
            <aside class="bs-aside">
                <div class="bs-card">
                    <h2 class="bs-card-title">Details</h2>
                    <div class="bs-meta">
                        <div class="bs-meta-row">
                            <span class="bs-meta-label">Audience</span>
                            <span class="bs-meta-val">{{ audienceLabel }}</span>
                        </div>
                        <div class="bs-meta-row">
                            <span class="bs-meta-label">Recipients</span>
                            <span class="bs-meta-val bs-meta-count">{{ broadcast.recipient_count.toLocaleString()
                                }}</span>
                        </div>
                        <div class="bs-meta-row">
                            <span class="bs-meta-label">Sent by</span>
                            <span class="bs-meta-val">{{ broadcast.sender?.name ?? '—' }}</span>
                        </div>
                        <div class="bs-meta-row">
                            <span class="bs-meta-label">Sent at</span>
                            <span class="bs-meta-val">{{ fmtDate(broadcast.sent_at) }}</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

    </AdminLayout>
</template>

<style scoped>
.bs-header {
    margin-bottom: 1.75rem;
}

.bs-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--copy-light);
    text-decoration: none;
    margin-bottom: 0.25rem;
    transition: color 0.15s;
}

.bs-back:hover {
    color: var(--copy);
}

.bs-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--copy);
}

.bs-sub {
    font-size: 0.82rem;
    color: var(--copy-light);
    margin-top: 0.1rem;
}

.bs-grid {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 860px) {
    .bs-grid {
        grid-template-columns: 1fr;
    }
}

.bs-main {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.bs-aside {
    position: sticky;
    top: 80px;
}

.bs-card {
    border: 2px solid var(--copy);
    border-radius: 12px;
    background: var(--foreground);
    padding: 1.5rem;
}

.bs-card-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--copy);
    margin-bottom: 0.85rem;
    padding-bottom: 0.6rem;
    border-bottom: 2px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
}

.bs-subject {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--copy);
}

.bs-preview-note {
    font-size: 0.8rem;
    color: var(--copy-lighter);
    font-style: italic;
    margin-bottom: 0.85rem;
}

.bs-preview-wrap {
    border: 1px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
    border-radius: 8px;
    padding: 1.25rem;
    background: var(--background);
    max-height: 500px;
    overflow-y: auto;
}

/* Allow HTML content to render */
.bs-preview {
    font-size: 0.92rem;
    line-height: 1.7;
    color: var(--copy);
}

.bs-preview :deep(p) {
    margin-bottom: 0.85rem;
}

.bs-preview :deep(h2) {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 1rem 0 0.5rem;
}

.bs-preview :deep(ul) {
    padding-left: 1.5rem;
    margin-bottom: 0.85rem;
}

.bs-preview :deep(li) {
    margin-bottom: 0.3rem;
}

.bs-preview :deep(strong) {
    color: var(--copy);
}

.bs-preview :deep(a) {
    color: var(--primary);
}

/* Meta */
.bs-meta {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.bs-meta-row {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.bs-meta-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--copy-lighter);
}

.bs-meta-val {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--copy);
}

.bs-meta-count {
    font-size: 1.1rem;
    font-weight: 800;
}
</style>
