<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useAdmin } from '@/composables/useAdmin';

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

defineProps<{ broadcasts: BroadcastsPaginated; totalOptedIn: number; waitlistCount: number }>();

const { paginate } = useAdmin();

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

// ── Waitlist launch ───────────────────────────────────────────────────────
const waitlistConfirming = ref(false);
const waitlistSending = ref(false);
const waitlistDone = ref<string | null>(null);
const waitlistError = ref<string | null>(null);

async function sendWaitlistLaunch() {
    waitlistSending.value = true;
    waitlistError.value = null;
    try {
        const res = await axios.post(route('admin.broadcasts.waitlist-launch'));
        waitlistDone.value = res.data.message;
        waitlistConfirming.value = false;
    } catch (e: any) {
        waitlistError.value = e?.response?.data?.error ?? 'Something went wrong. Please try again.';
    } finally {
        waitlistSending.value = false;
    }
}
</script>

<template>
    <AdminLayout>

        <Head title="Broadcast Emails — Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Broadcast Emails</h1>
                <p class="adm-sub">Send a message to a segment of your customers.</p>
            </div>
            <Link :href="route('admin.broadcasts.create')" class="adm-btn adm-btn--primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            Compose
            </Link>
        </div>

        <!-- Opted-in stat bar -->
        <div class="bi-stat-bar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <span><strong>{{ totalOptedIn.toLocaleString() }}</strong> opted-in customers in your mailing list</span>
        </div>

        <!-- History table -->
        <div v-if="broadcasts.data.length" class="adm-card adm-card--flush" style="margin-bottom:1.5rem">

            <!-- Desktop table -->
            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">#</th>
                            <th class="adm-th">Subject</th>
                            <th class="adm-th">Audience</th>
                            <th class="adm-th">Recipients</th>
                            <th class="adm-th">Sent by</th>
                            <th class="adm-th">Sent at</th>
                            <th class="adm-th adm-th--right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in broadcasts.data" :key="b.id" class="adm-row">
                            <td class="adm-td adm-td--mono" style="font-size:0.8rem">#{{ b.id }}</td>
                            <td class="adm-td" style="font-weight:600; max-width:240px">{{ b.subject }}</td>
                            <td class="adm-td">
                                <span class="adm-badge adm-badge--lav">
                                    {{ audienceLabel[b.audience] ?? b.audience }}
                                </span>
                            </td>
                            <td class="adm-td" style="font-weight:700">{{ b.recipient_count.toLocaleString() }}</td>
                            <td class="adm-td" style="color:var(--bb-muted)">{{ b.sender?.name ?? '—' }}</td>
                            <td class="adm-td" style="color:var(--bb-muted); white-space:nowrap; font-size:0.8rem">{{
                                fmtDate(b.sent_at) }}</td>
                            <td class="adm-td adm-td--actions">
                                <Link :href="route('admin.broadcasts.show', b.id)" class="adm-action adm-action--edit">
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
            </div>

            <!-- Mobile cards -->
            <div class="adm-mob-list">
                <div v-for="b in broadcasts.data" :key="b.id" class="adm-mob-card">
                    <div class="bi-mob-head">
                        <p style="font-weight:600; font-size:0.95rem; color:var(--bb-text)">{{ b.subject }}</p>
                        <span class="adm-badge adm-badge--lav">{{ audienceLabel[b.audience] ?? b.audience }}</span>
                    </div>
                    <p class="bi-mob-meta">
                        {{ b.recipient_count.toLocaleString() }} recipients
                        · {{ b.sender?.name ?? '—' }}
                        · {{ fmtDate(b.sent_at) }}
                    </p>
                    <div style="display:flex; justify-content:flex-end">
                        <Link :href="route('admin.broadcasts.show', b.id)" class="adm-action adm-action--edit">
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
        <div v-else class="adm-empty" style="margin-bottom:1.5rem">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
            </svg>
            <p class="adm-empty-title">No broadcasts sent yet</p>
            <p class="adm-empty-sub">Compose your first broadcast to get started.</p>
            <Link :href="route('admin.broadcasts.create')" class="adm-btn adm-btn--primary">Compose</Link>
        </div>

        <!-- Pagination -->
        <div v-if="broadcasts.last_page > 1" class="adm-pagination">
            <div class="adm-page-btns">
                <button v-for="link in broadcasts.links" :key="link.label" @click.prevent="paginate(link.url)"
                    :disabled="!link.url" class="adm-page-btn" :class="{ 'adm-page-btn--active': link.active }"
                    v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                </button>
            </div>
        </div>

        <!-- ── Waitlist Launch Email ── -->
        <div class="bi-waitlist-card">
            <div class="bi-waitlist-head">
                <div class="bi-waitlist-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <div>
                    <p class="bi-waitlist-title">Waitlist Launch Email</p>
                    <p class="bi-waitlist-sub">Send the "we're live" email to all waitlist subscribers with their
                        exclusive discount code.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="bi-waitlist-stats">
                <div class="bi-waitlist-stat">
                    <span class="bi-waitlist-val">{{ waitlistCount.toLocaleString() }}</span>
                    <span class="bi-waitlist-label">Waitlist subscribers</span>
                </div>
                <div class="bi-waitlist-stat">
                    <span class="bi-waitlist-val bi-waitlist-val--code">CHAPTERONE</span>
                    <span class="bi-waitlist-label">Discount code included</span>
                </div>
            </div>

            <!-- Alerts — using shared adm-flash classes -->
            <div v-if="waitlistDone" class="adm-flash adm-flash--success">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
                {{ waitlistDone }}
            </div>
            <div v-if="waitlistError" class="adm-flash adm-flash--error">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                {{ waitlistError }}
            </div>

            <!-- Confirm step -->
            <div v-if="waitlistConfirming && !waitlistDone" class="bi-waitlist-confirm">
                <p class="bi-waitlist-confirm-text">
                    This will queue the launch email for
                    <strong>{{ waitlistCount.toLocaleString() }} subscriber{{ waitlistCount !== 1 ? 's' : ''
                        }}</strong>.
                    This cannot be undone.
                </p>
                <div class="bi-waitlist-confirm-btns">
                    <!-- Cancel uses shared ghost button -->
                    <button @click="waitlistConfirming = false"
                        class="adm-btn adm-btn--ghost adm-btn--sm">Cancel</button>
                    <button @click="sendWaitlistLaunch" :disabled="waitlistSending" class="bi-waitlist-send-btn">
                        <svg v-if="waitlistSending" class="adm-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ waitlistSending ? 'Sending…' : `Yes, send to ${waitlistCount.toLocaleString()}` }}
                    </button>
                </div>
            </div>

            <!-- Trigger button -->
            <button v-if="!waitlistConfirming && !waitlistDone" @click="waitlistConfirming = true"
                :disabled="waitlistCount === 0" class="bi-waitlist-trigger-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 2 11 13" />
                    <path d="M22 2 15 22 11 13 2 9l20-7z" />
                </svg>
                Send Launch Email to Waitlist
            </button>
            <p v-if="waitlistCount === 0 && !waitlistDone" class="bi-waitlist-empty-note">No waitlist entries found.</p>
        </div>

    </AdminLayout>
</template>

<style scoped>
/*
 * Only page-specific styles live here.
 * Shared styles (adm-header, adm-title, adm-sub, adm-btn, adm-card, adm-table,
 * adm-badge, adm-action, adm-empty, adm-pagination, adm-flash, adm-spinner)
 * all come from admin-design-system.css.
 */

/* Opted-in stat bar */
.bi-stat-bar {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.7rem 1rem;
    border-radius: var(--bb-radius, 8px);
    background: color-mix(in srgb, var(--bb-lav, #c9b8f0) 12%, transparent);
    border: 1px solid color-mix(in srgb, var(--bb-lav-d, #9b84d4) 30%, transparent);
    color: var(--bb-muted, #7a7a9a);
    font-size: 0.85rem;
    margin-bottom: 1.25rem;
}

.bi-stat-bar svg {
    color: var(--bb-lav-d, #9b84d4);
    flex-shrink: 0;
}

.bi-stat-bar strong {
    color: var(--bb-text, #1a1a2e);
    font-weight: 700;
}

/* Mobile card internals */
.bi-mob-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.bi-mob-meta {
    font-size: 0.78rem;
    color: var(--bb-muted, #7a7a9a);
    line-height: 1.5;
}

/* ── Waitlist card ── */
.bi-waitlist-card {
    margin-top: 2rem;
    background: var(--bb-surface, #ffffff);
    border-radius: var(--bb-radius-lg, 14px);
    border: 1px solid var(--bb-blush, #f2c4ce);
    box-shadow: var(--bb-shadow-card, 0 1px 6px rgba(26, 26, 46, 0.05));
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.bi-waitlist-head {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
}

.bi-waitlist-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #fff5f7;
    border: 1px solid var(--bb-blush, #f2c4ce);
    color: var(--bb-blush-d, #d4899a);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bi-waitlist-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--bb-text, #1a1a2e);
    margin-bottom: 0.2rem;
}

.bi-waitlist-sub {
    font-size: 0.82rem;
    color: var(--bb-muted, #7a7a9a);
    line-height: 1.5;
}

.bi-waitlist-stats {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    padding: 0.85rem 1rem;
    border-radius: var(--bb-radius, 8px);
    background: #fff5f7;
    border: 1px solid var(--bb-blush, #f2c4ce);
}

.bi-waitlist-stat {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.bi-waitlist-val {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--bb-text, #1a1a2e);
    letter-spacing: -0.02em;
}

.bi-waitlist-val--code {
    font-family: var(--bb-mono, monospace);
    font-size: 1rem;
    letter-spacing: 0.1em;
    color: var(--bb-blush-d, #d4899a);
}

.bi-waitlist-label {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bb-muted, #7a7a9a);
}

.bi-waitlist-confirm {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: var(--bb-radius, 8px);
    background: var(--bb-cream, #faf9f7);
    border: 1px solid var(--bb-border, #ece8e2);
}

.bi-waitlist-confirm-text {
    font-size: 0.85rem;
    color: var(--bb-muted, #7a7a9a);
    line-height: 1.5;
}

.bi-waitlist-confirm-text strong {
    color: var(--bb-text, #1a1a2e);
}

.bi-waitlist-confirm-btns {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
}

/* Blush-coloured send button — unique to this page */
.bi-waitlist-send-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.1rem;
    border-radius: var(--bb-radius, 8px);
    border: none;
    background: var(--bb-blush-d, #d4899a);
    color: #fff;
    font-family: var(--bb-font, 'DM Sans', sans-serif);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
}

.bi-waitlist-send-btn:hover:not(:disabled) {
    opacity: 0.88;
    transform: translateY(-1px);
}

.bi-waitlist-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.bi-waitlist-trigger-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    align-self: flex-start;
    padding: 0.6rem 1.1rem;
    border-radius: var(--bb-radius, 8px);
    border: 1px solid var(--bb-blush, #f2c4ce);
    background: #fff5f7;
    color: var(--bb-blush-d, #d4899a);
    font-family: var(--bb-font, 'DM Sans', sans-serif);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s;
}

.bi-waitlist-trigger-btn:hover:not(:disabled) {
    background: var(--bb-blush-d, #d4899a);
    border-color: var(--bb-blush-d, #d4899a);
    color: #fff;
    transform: translateY(-1px);
}

.bi-waitlist-trigger-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.bi-waitlist-empty-note {
    font-size: 0.78rem;
    color: var(--bb-muted, #7a7a9a);
    font-style: italic;
}
</style>
