<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Review {
    id: number;
    user_id: number | null;
    rating: number;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    user: { id: number; name: string; email: string } | null;
    product: { id: number; mpn: string; name: string } | null;
}
interface ReviewsPaginated {
    data: Review[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

const props = defineProps<{
    reviews: ReviewsPaginated;
    counts: { all: number; pending: number; approved: number; rejected: number };
    activeStatus: string;
}>();

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });

const statusClass = (s: string) => {
    if (s === 'approved') return 'badge--green';
    if (s === 'pending') return 'badge--amber';
    if (s === 'rejected') return 'badge--red';
    return 'badge--grey';
};

const starDisplay = (rating: number) => '★'.repeat(rating) + '☆'.repeat(5 - rating);

const setFilter = (status: string) => {
    router.get(route('admin.reviews.index'), status === 'all' ? {} : { status }, {
        preserveState: true, replace: true,
    });
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const tabs = [
    { key: 'all', label: 'All' },
    { key: 'pending', label: 'Pending' },
    { key: 'approved', label: 'Approved' },
    { key: 'rejected', label: 'Rejected' },
];
</script>

<template>
    <AdminLayout>

        <Head title="Reviews" />

        <!-- Header -->
        <div class="ri-header">
            <div>
                <h1 class="ri-title">Reviews</h1>
                <p class="ri-sub">Moderate customer reviews across all products.</p>
            </div>
            <!-- Pending alert badge -->
            <div v-if="counts.pending > 0" class="ri-pending-alert">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                {{ counts.pending }} awaiting moderation
            </div>
        </div>

        <!-- Status tabs -->
        <div class="ri-tabs">
            <button v-for="tab in tabs" :key="tab.key" @click="setFilter(tab.key)" class="ri-tab"
                :class="{ 'ri-tab--active': activeStatus === tab.key }">
                {{ tab.label }}
                <span class="ri-tab-count" :class="{
                    'ri-tab-count--amber': tab.key === 'pending' && counts.pending > 0,
                    'ri-tab-count--active': activeStatus === tab.key,
                }">
                    {{ counts[tab.key as keyof typeof counts] }}
                </span>
            </button>
        </div>

        <!-- Table / empty -->
        <div v-if="reviews.data.length" class="ri-table-wrap">
            <!-- Desktop -->
            <table class="ri-table">
                <thead>
                    <tr>
                        <th>Review</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Rating</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="review in reviews.data" :key="review.id" class="ri-row" style="background-color: white;">
                        <td class="ri-td-id">#{{ review.id }}</td>
                        <td>
                            <template v-if="review.user">
                                <Link :href="route('admin.users.show', review.user.id)" class="ri-link">
                                {{ review.user.name }}
                                </Link>
                                <p class="ri-sub-text">{{ review.user.email }}</p>
                            </template>
                            <span v-else class="ri-muted">Guest</span>
                        </td>
                        <td>
                            <template v-if="review.product">
                                <p class="ri-product-name">{{ review.product.name }}</p>
                                <p class="ri-sub-text">{{ review.product.mpn }}</p>
                            </template>
                            <span v-else class="ri-muted">—</span>
                        </td>
                        <td>
                            <span class="ri-stars">{{ starDisplay(review.rating) }}</span>
                        </td>
                        <td class="ri-muted ri-date">{{ fmtDate(review.created_at) }}</td>
                        <td>
                            <span class="ri-badge" :class="statusClass(review.status)">
                                {{ review.status }}
                            </span>
                        </td>
                        <td class="ri-td-action">
                            <Link :href="route('admin.reviews.show', review.id)" class="ri-view-btn">
                            Review
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Mobile cards -->
            <div class="ri-cards">
                <div v-for="review in reviews.data" :key="review.id" class="ri-card">
                    <div class="ri-card-head">
                        <div>
                            <p class="ri-card-id">#{{ review.id }}</p>
                            <p class="ri-stars">{{ starDisplay(review.rating) }}</p>
                        </div>
                        <span class="ri-badge" :class="statusClass(review.status)">{{ review.status }}</span>
                    </div>
                    <div class="ri-card-body">
                        <p class="ri-card-label">Customer</p>
                        <p v-if="review.user">{{ review.user.name }}</p>
                        <p class="ri-sub-text" v-if="review.user">{{ review.user.email }}</p>
                        <p v-else class="ri-muted">Guest</p>
                    </div>
                    <div class="ri-card-body">
                        <p class="ri-card-label">Product</p>
                        <p v-if="review.product">{{ review.product.name }}</p>
                        <p class="ri-muted" v-else>—</p>
                    </div>
                    <div class="ri-card-foot">
                        <p class="ri-muted ri-date">{{ fmtDate(review.created_at) }}</p>
                        <Link :href="route('admin.reviews.show', review.id)" class="ri-view-btn">
                        View
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else class="ri-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
            <p>No {{ activeStatus === 'all' ? '' : activeStatus }} reviews found.</p>
        </div>

        <!-- Pagination -->
        <div v-if="reviews.last_page > 1" class="ri-pagination">
            <button v-for="link in reviews.links" :key="link.label" @click.prevent="paginate(link.url)"
                :disabled="!link.url" class="ri-page-btn" :class="{ 'ri-page-btn--active': link.active }"
                v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
            </button>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── Header ── */
.ri-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.ri-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--copy);
}

.ri-sub {
    font-size: 0.85rem;
    color: var(--copy-light);
    margin-top: 0.15rem;
}

.ri-pending-alert {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    border-radius: 999px;
    background: #fff8e6;
    border: 1px solid #e0c060;
    color: #8a6000;
    font-size: 0.82rem;
    font-weight: 700;
}

/* ── Tabs ── */
.ri-tabs {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1.25rem;
    border-bottom: 2px solid var(--border, #e5e7eb);
    overflow-x: auto;
}

.ri-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--copy-light);
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    cursor: pointer;
    white-space: nowrap;
    transition: color 0.15s, border-color 0.15s;
}

.ri-tab:hover {
    color: var(--copy);
}

.ri-tab--active {
    color: var(--copy);
    border-bottom-color: var(--primary, #8c4a50);
}

.ri-tab-count {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    background: var(--secondary-light, #f3f4f6);
    color: var(--copy-light);
}

.ri-tab-count--amber {
    background: #fff3cd;
    color: #856404;
}

.ri-tab-count--active {
    background: var(--primary, #8c4a50);
    color: #fff;
}

/* ── Table ── */
.ri-table-wrap {
    border: 2px solid var(--copy);
    border-radius: 12px;
    overflow: hidden;
    background: var(--primary-content);
}

.ri-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    display: none;
}

@media (min-width: 768px) {
    .ri-table {
        display: table;
    }
}

.ri-table thead tr {
    background: var(--secondary-light);
    border-bottom: 2px solid var(--copy);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.ri-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 700;
    color: var(--copy);
}

.ri-row {
    border-bottom: 1px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
}

.ri-row:last-child {
    border-bottom: none;
}

.ri-row:hover {
    background: var(--secondary-light);
}

.ri-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

.ri-td-id {
    font-weight: 700;
    color: var(--copy);
}

.ri-td-action {
    text-align: right;
}

.ri-link {
    font-weight: 600;
    color: var(--copy);
    text-decoration: none;
    transition: color 0.15s;
}

.ri-link:hover {
    color: var(--primary, #8c4a50);
    text-decoration: underline;
}

.ri-sub-text {
    font-size: 0.75rem;
    color: var(--copy-light);
    margin-top: 0.1rem;
}

.ri-product-name {
    font-weight: 500;
    color: var(--copy);
}

.ri-muted {
    color: var(--copy-light);
    font-size: 0.85rem;
}

.ri-date {
    font-size: 0.8rem;
    white-space: nowrap;
}

.ri-stars {
    font-size: 0.85rem;
    color: #c9747a;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

/* ── Status badges ── */
.ri-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    border: 1px solid transparent;
}

.badge--green {
    background: #f0faf0;
    color: #2d7a3a;
    border-color: #a8d8b0;
}

.badge--amber {
    background: #fffbf0;
    color: #8a5a00;
    border-color: #e0c878;
}

.badge--red {
    background: #fff5f5;
    color: #8c2a2a;
    border-color: #e8a8a8;
}

.badge--grey {
    background: #f5f5f5;
    color: #555;
    border-color: #ccc;
}

/* ── View button ── */
.ri-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--copy-light);
    text-decoration: none;
    transition: color 0.15s;
}

.ri-view-btn:hover {
    color: var(--primary, #8c4a50);
}

/* ── Mobile cards ── */
.ri-cards {
    display: flex;
    flex-direction: column;
}

@media (min-width: 768px) {
    .ri-cards {
        display: none;
    }
}

.ri-card {
    padding: 1rem;
    border-bottom: 1px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
    background: var(--foreground);
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.ri-card:last-child {
    border-bottom: none;
}

.ri-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.ri-card-id {
    font-weight: 700;
    font-size: 1rem;
    color: var(--copy);
}

.ri-card-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--copy-lighter);
    margin-bottom: 0.15rem;
}

.ri-card-body {}

.ri-card-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.5rem;
    border-top: 1px solid color-mix(in srgb, var(--copy-light) 20%, transparent);
}

/* ── Empty ── */
.ri-empty {
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
.ri-pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.3rem;
    margin-top: 1.5rem;
}

.ri-page-btn {
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

.ri-page-btn:hover:not(:disabled) {
    background: var(--secondary-light);
}

.ri-page-btn--active {
    background: var(--primary);
    color: var(--primary-content);
}

.ri-page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
