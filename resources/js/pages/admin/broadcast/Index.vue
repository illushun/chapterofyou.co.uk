<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Broadcast {
    id: number;
    subject: string;
    audience: string;
    recipient_count: number;
    sent_at: string;
    sender: { id: number; name: string } | null;
}
interface BroadcastsPaginated {
    data: Broadcast[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

defineProps<{ broadcasts: BroadcastsPaginated }>();

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });

const audienceLabel: Record<string, string> = {
    all: 'All customers',
    ordered_last_90: 'Ordered last 90 days',
    never_ordered: 'Never ordered',
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout>

        <Head title="Broadcast Emails" />

        <!-- Header -->
        <div class="be-header">
            <div>
                <h1 class="be-title">Broadcast Emails</h1>
                <p class="be-sub">Send a message to a segment of your customers.</p>
            </div>
            <Link :href="route('admin.broadcasts.create')" class="be-compose-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            Compose
            </Link>
        </div>

        <!-- History table -->
        <div v-if="broadcasts.data.length" class="be-table-wrap">

            <!-- Desktop -->
            <table class="be-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Audience</th>
                        <th>Recipients</th>
                        <th>Sent by</th>
                        <th>Sent at</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in broadcasts.data" :key="b.id" class="be-row">
                        <td class="be-td-id">#{{ b.id }}</td>
                        <td class="be-td-subject">{{ b.subject }}</td>
                        <td>
                            <span class="be-audience-badge">
                                {{ audienceLabel[b.audience] ?? b.audience }}
                            </span>
                        </td>
                        <td class="be-td-count">
                            <span class="be-count">{{ b.recipient_count.toLocaleString() }}</span>
                        </td>
                        <td class="be-td-sender">{{ b.sender?.name ?? '—' }}</td>
                        <td class="be-td-date">{{ fmtDate(b.sent_at) }}</td>
                        <td class="be-td-action">
                            <Link :href="route('admin.broadcasts.show', b.id)" class="be-view-btn">
                            View
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Mobile cards -->
            <div class="be-cards">
                <div v-for="b in broadcasts.data" :key="b.id" class="be-card">
                    <div class="be-card-head">
                        <p class="be-card-subject">{{ b.subject }}</p>
                        <span class="be-audience-badge">{{ audienceLabel[b.audience] ?? b.audience }}</span>
                    </div>
                    <div class="be-card-meta">
                        <span>{{ b.recipient_count.toLocaleString() }} recipients</span>
                        <span>·</span>
                        <span>{{ b.sender?.name ?? '—' }}</span>
                        <span>·</span>
                        <span>{{ fmtDate(b.sent_at) }}</span>
                    </div>
                    <div class="be-card-foot">
                        <Link :href="route('admin.broadcasts.show', b.id)" class="be-view-btn">
                        View
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else class="be-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
            </svg>
            <p>No broadcasts sent yet.</p>
            <Link :href="route('admin.broadcasts.create')" class="be-compose-btn">
            Send your first broadcast
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="broadcasts.last_page > 1" class="be-pagination">
            <button v-for="link in broadcasts.links" :key="link.label" @click.prevent="paginate(link.url)"
                :disabled="!link.url" class="be-page-btn" :class="{ 'be-page-btn--active': link.active }"
                v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
            </button>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── Header ── */
.be-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.be-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--copy);
}

.be-sub {
    font-size: 0.85rem;
    color: var(--copy-light);
    margin-top: 0.15rem;
}

.be-compose-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1.1rem;
    border-radius: 8px;
    border: 2px solid var(--copy);
    background: var(--primary);
    color: var(--primary-content);
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: opacity 0.15s;
    cursor: pointer;
}

.be-compose-btn:hover {
    opacity: 0.85;
}

/* ── Table ── */
.be-table-wrap {
    border: 2px solid var(--copy);
    border-radius: 12px;
    overflow: hidden;
    background: var(--primary-content);
}

.be-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    display: none;
}

@media (min-width: 768px) {
    .be-table {
        display: table;
    }
}

.be-table thead tr {
    background: var(--secondary-light);
    border-bottom: 2px solid var(--copy);
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 0.05em;
}

.be-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 700;
    color: var(--copy);
}

.be-row {
    border-bottom: 1px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
}

.be-row:last-child {
    border-bottom: none;
}

.be-row:hover {
    background: var(--secondary-light);
}

.be-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

.be-td-id {
    font-weight: 700;
    color: var(--copy-light);
    font-size: 0.8rem;
}

.be-td-subject {
    font-weight: 600;
    color: var(--copy);
    max-width: 240px;
}

.be-td-count {}

.be-td-sender {
    color: var(--copy-light);
    font-size: 0.85rem;
}

.be-td-date {
    color: var(--copy-light);
    font-size: 0.8rem;
    white-space: nowrap;
}

.be-td-action {
    text-align: right;
}

.be-audience-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.18rem 0.6rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent);
    white-space: nowrap;
}

.be-count {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--copy);
}

.be-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--copy-light);
    text-decoration: none;
    transition: color 0.15s;
}

.be-view-btn:hover {
    color: var(--primary);
}

/* ── Mobile cards ── */
.be-cards {
    display: flex;
    flex-direction: column;
}

@media (min-width: 768px) {
    .be-cards {
        display: none;
    }
}

.be-card {
    padding: 1rem;
    border-bottom: 1px solid color-mix(in srgb, var(--copy-light) 25%, transparent);
    background: var(--foreground);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.be-card:last-child {
    border-bottom: none;
}

.be-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.be-card-subject {
    font-weight: 600;
    color: var(--copy);
    font-size: 0.95rem;
}

.be-card-meta {
    font-size: 0.78rem;
    color: var(--copy-light);
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.be-card-foot {
    display: flex;
    justify-content: flex-end;
}

/* ── Empty ── */
.be-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 3.5rem 2rem;
    border: 2px dashed var(--border, #e5e7eb);
    border-radius: 12px;
    color: var(--copy-light);
    text-align: center;
    font-size: 0.95rem;
}

/* ── Pagination ── */
.be-pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.3rem;
    margin-top: 1.5rem;
}

.be-page-btn {
    min-width: 36px;
    height: 36px;
    padding: 0 0.5rem;
    border-radius: 8px;
    border: 2px solid var(--copy);
    background: var(--foreground);
    color: var(--copy-light);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}

.be-page-btn:hover:not(:disabled) {
    background: var(--secondary-light);
}

.be-page-btn--active {
    background: var(--primary);
    color: var(--primary-content);
}

.be-page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
