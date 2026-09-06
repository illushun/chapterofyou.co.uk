<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';

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

defineProps<{
    reviews: ReviewsPaginated;
    counts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
    };
    activeStatus: string;
}>();

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const statusClass = (s: string) => {
    if (s === 'approved') return 'badge--green';
    if (s === 'pending') return 'badge--amber';
    if (s === 'rejected') return 'badge--red';
    return 'badge--grey';
};

const setFilter = (status: string) => {
    router.get(
        route('admin.reviews.index'),
        status === 'all' ? {} : { status },
        {
            preserveState: true,
            replace: true,
        },
    );
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
        <Head title="Reviews : Admin" />

        <!-- Header -->
        <div class="ri-header">
            <div>
                <h1 class="ri-title">Reviews</h1>
                <p class="ri-sub">
                    Moderate customer reviews across all products.
                </p>
            </div>
            <!-- Pending alert badge -->
            <div v-if="counts.pending > 0" class="ri-pending-alert">
                <svg
                    width="14"
                    height="14"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                {{ counts.pending }} awaiting moderation
            </div>
        </div>

        <!-- Status tabs -->
        <div class="ri-tabs">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                @click="setFilter(tab.key)"
                class="ri-tab"
                :class="{ 'ri-tab--active': activeStatus === tab.key }"
            >
                {{ tab.label }}
                <span
                    class="ri-tab-count"
                    :class="{
                        'ri-tab-count--amber':
                            tab.key === 'pending' && counts.pending > 0,
                        'ri-tab-count--active': activeStatus === tab.key,
                    }"
                >
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
                    <tr
                        v-for="review in reviews.data"
                        :key="review.id"
                        class="ri-row"
                    >
                        <td class="ri-td-id">#{{ review.id }}</td>
                        <td>
                            <template v-if="review.user">
                                <Link
                                    :href="
                                        route(
                                            'admin.users.show',
                                            review.user.id,
                                        )
                                    "
                                    class="ri-link"
                                >
                                    {{ review.user.name }}
                                </Link>
                                <p class="ri-sub-text">
                                    {{ review.user.email }}
                                </p>
                            </template>
                            <span v-else class="ri-muted">Guest</span>
                        </td>
                        <td>
                            <template v-if="review.product">
                                <p class="ri-product-name">
                                    {{ review.product.name }}
                                </p>
                                <p class="ri-sub-text">
                                    {{ review.product.mpn }}
                                </p>
                            </template>
                            <span v-else class="ri-muted">-</span>
                        </td>
                        <td>
                            <span class="ri-stars">
                                <Star
                                    v-for="i in 5"
                                    :key="i"
                                    :size="12"
                                    :stroke-width="1.5"
                                    :fill="
                                        i <= review.rating
                                            ? 'currentColor'
                                            : 'none'
                                    "
                                />
                            </span>
                        </td>
                        <td class="ri-muted ri-date">
                            {{ fmtDate(review.created_at) }}
                        </td>
                        <td>
                            <span
                                class="ri-badge"
                                :class="statusClass(review.status)"
                            >
                                {{ review.status }}
                            </span>
                        </td>
                        <td class="ri-td-action">
                            <Link
                                :href="route('admin.reviews.show', review.id)"
                                class="ri-view-btn"
                            >
                                Review
                                <svg
                                    width="12"
                                    height="12"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Mobile cards -->
            <div class="ri-cards">
                <div
                    v-for="review in reviews.data"
                    :key="review.id"
                    class="ri-card"
                >
                    <div class="ri-card-head">
                        <div>
                            <p class="ri-card-id">#{{ review.id }}</p>
                            <p class="ri-stars">
                                <Star
                                    v-for="i in 5"
                                    :key="i"
                                    :size="12"
                                    :stroke-width="1.5"
                                    :fill="
                                        i <= review.rating
                                            ? 'currentColor'
                                            : 'none'
                                    "
                                />
                            </p>
                        </div>
                        <span
                            class="ri-badge"
                            :class="statusClass(review.status)"
                            >{{ review.status }}</span
                        >
                    </div>
                    <div class="ri-card-body">
                        <p class="ri-card-label">Customer</p>
                        <p v-if="review.user">{{ review.user.name }}</p>
                        <p class="ri-sub-text" v-if="review.user">
                            {{ review.user.email }}
                        </p>
                        <p v-else class="ri-muted">Guest</p>
                    </div>
                    <div class="ri-card-body">
                        <p class="ri-card-label">Product</p>
                        <p v-if="review.product">{{ review.product.name }}</p>
                        <p class="ri-muted" v-else>-</p>
                    </div>
                    <div class="ri-card-foot">
                        <p class="ri-muted ri-date">
                            {{ fmtDate(review.created_at) }}
                        </p>
                        <Link
                            :href="route('admin.reviews.show', review.id)"
                            class="ri-view-btn"
                        >
                            View
                            <svg
                                width="12"
                                height="12"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else class="ri-empty">
            <svg
                width="36"
                height="36"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path
                    d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                />
            </svg>
            <p>
                No {{ activeStatus === 'all' ? '' : activeStatus }} reviews
                found.
            </p>
        </div>

        <!-- Pagination -->
        <div v-if="reviews.last_page > 1" class="ri-pagination">
            <button
                v-for="link in reviews.links"
                :key="link.label"
                @click.prevent="paginate(link.url)"
                :disabled="!link.url"
                class="ri-page-btn"
                :class="{ 'ri-page-btn--active': link.active }"
                v-html="
                    link.label
                        .replace('&laquo; Previous', '←')
                        .replace('Next &raquo;', '→')
                "
            ></button>
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
    font-family: var(--adm-display);
    font-style: italic;
    font-size: 2rem;
    font-weight: 400;
    color: var(--adm-ink);
}

.ri-sub {
    font-size: 0.85rem;
    color: var(--adm-ink-dim);
    margin-top: 0.15rem;
}

.ri-pending-alert {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    border-radius: 999px;
    background: var(--adm-warning-bg);
    border: 1px dashed var(--adm-warning-line);
    color: var(--adm-warning);
    font-size: 0.82rem;
    font-weight: 700;
}

/* ── Tabs ── */
.ri-tabs {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid var(--adm-line);
    overflow-x: auto;
}

.ri-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--adm-ink-dim);
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    cursor: pointer;
    white-space: nowrap;
    transition:
        color 0.15s,
        border-color 0.15s;
}

.ri-tab:hover {
    color: var(--adm-ink);
}

.ri-tab--active {
    color: var(--adm-ink);
    border-bottom-color: var(--adm-stamp);
}

.ri-tab-count {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    background: var(--adm-paper);
    color: var(--adm-ink-dim);
}

.ri-tab-count--amber {
    background: var(--adm-warning-bg);
    color: var(--adm-warning);
}

.ri-tab-count--active {
    background: var(--adm-stamp);
    color: var(--adm-paper-raised);
}

/* ── Table ── */
.ri-table-wrap {
    border: 1px solid var(--adm-line);
    border-radius: 12px;
    overflow: hidden;
    background: var(--adm-paper-raised);
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
    background: var(--adm-paper);
    border-bottom: 1px solid var(--adm-line);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.ri-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 700;
    color: var(--adm-ink);
}

.ri-row {
    border-bottom: 1px solid
        color-mix(in srgb, var(--adm-ink-dim) 30%, transparent);
}

.ri-row:last-child {
    border-bottom: none;
}

.ri-row:hover {
    background: var(--adm-paper);
}

.ri-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

.ri-td-id {
    font-weight: 700;
    color: var(--adm-ink);
}

.ri-td-action {
    text-align: right;
}

.ri-link {
    font-weight: 600;
    color: var(--adm-ink);
    text-decoration: none;
    transition: color 0.15s;
}

.ri-link:hover {
    color: var(--adm-stamp);
    text-decoration: underline;
}

.ri-sub-text {
    font-size: 0.75rem;
    color: var(--adm-ink-dim);
    margin-top: 0.1rem;
}

.ri-product-name {
    font-weight: 500;
    color: var(--adm-ink);
}

.ri-muted {
    color: var(--adm-ink-dim);
    font-size: 0.85rem;
}

.ri-date {
    font-size: 0.8rem;
    white-space: nowrap;
}

.ri-stars {
    display: inline-flex;
    align-items: center;
    gap: 0.1rem;
    color: var(--adm-warning);
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
    background: var(--adm-success-bg);
    color: var(--adm-success);
    border-color: var(--adm-success-line);
}

.badge--amber {
    background: var(--adm-warning-bg);
    color: var(--adm-warning);
    border-color: var(--adm-warning-line);
}

.badge--red {
    background: var(--adm-danger-bg);
    color: var(--adm-danger);
    border-color: var(--adm-danger-line);
}

.badge--grey {
    background: var(--adm-paper-sunk);
    color: var(--adm-ink-dim);
    border-color: var(--adm-line);
}

/* ── View button ── */
.ri-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--adm-ink-dim);
    text-decoration: none;
    transition: color 0.15s;
}

.ri-view-btn:hover {
    color: var(--adm-stamp);
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
    border-bottom: 1px solid
        color-mix(in srgb, var(--adm-ink-dim) 30%, transparent);
    background: var(--adm-paper-raised);
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
    color: var(--adm-ink);
}

.ri-card-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--adm-ink-faint);
    margin-bottom: 0.15rem;
}

.ri-card-body {
}

.ri-card-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.5rem;
    border-top: 1px solid
        color-mix(in srgb, var(--adm-ink-dim) 20%, transparent);
}

/* ── Empty ── */
.ri-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 3.5rem 2rem;
    border: 2px dashed var(--adm-line);
    border-radius: 12px;
    color: var(--adm-ink-dim);
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
    border: 1px solid var(--adm-line);
    background: var(--adm-paper-raised);
    color: var(--adm-ink-dim);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}

.ri-page-btn:hover:not(:disabled) {
    background: var(--adm-paper);
}

.ri-page-btn--active {
    background: var(--adm-charcoal);
    color: var(--adm-paper-raised);
}

.ri-page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
