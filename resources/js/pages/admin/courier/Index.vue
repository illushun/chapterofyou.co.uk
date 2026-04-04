<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// Interfaces for data structure
interface Courier {
    id: number;
    name: string;
    type: 'Royal Mail' | 'FedEx' | 'Evri' | 'DPD';
    status: 'enabled' | 'disabled';
    cost: number;
    created_at: string;
}

interface CouriersPaginated {
    data: Courier[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

const props = defineProps<{
    couriers: CouriersPaginated;
}>();

const confirmDelete = (courier: Courier) => {
    if (confirm(`Are you sure you want to delete the courier: ${courier.name}? This action cannot be undone.`)) {
        router.delete(route('admin.couriers.destroy', courier.id), {
            preserveScroll: true,
        });
    }
};

const formatCurrency = (amount: number | string | null | undefined): string => {
    const numericAmount = Number(amount) || 0;
    return `£${numericAmount.toFixed(2)}`;
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const paginate = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>
        <component :is="'link'"
            href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap"
            rel="stylesheet" />

        <Head title="Manage Couriers" />

        <div class="ci-wrap">

            <!-- Page Header -->
            <div class="ci-header">
                <div>
                    <h1 class="ci-title">Couriers</h1>
                    <p class="ci-sub">Manage shipping providers and their rates. {{ couriers.data.length }} couriers
                        shown.</p>
                </div>
                <Link :href="route('admin.couriers.create')" class="ci-btn-primary">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                Add Courier
                </Link>
            </div>

            <!-- Table (desktop) -->
            <div v-if="couriers.data.length">
                <div class="ci-table-wrap">
                    <table class="ci-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Added</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="courier in couriers.data" :key="courier.id">
                                <td class="ci-td-id">{{ courier.id }}</td>
                                <td class="ci-td-name">{{ courier.name }}</td>
                                <td>
                                    <span class="ci-badge ci-badge--type">{{ courier.type }}</span>
                                </td>
                                <td class="ci-td-cost">{{ formatCurrency(courier.cost) }}</td>
                                <td>
                                    <span
                                        :class="['ci-badge', courier.status === 'enabled' ? 'ci-badge--on' : 'ci-badge--off']">
                                        {{ courier.status === 'enabled' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="ci-td-date">{{ formatDate(courier.created_at) }}</td>
                                <td class="ci-td-actions">
                                    <Link :href="route('admin.couriers.edit', courier.id)"
                                        class="ci-action-btn ci-action-btn--edit">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.5 1.5a1.414 1.414 0 0 1 2 2L4 11H1.5V8.5L9.5 1.5Z"
                                            stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />
                                    </svg>
                                    Edit
                                    </Link>
                                    <button @click="confirmDelete(courier)" class="ci-action-btn ci-action-btn--delete">
                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 3.5h9M5 3.5V2h3v1.5M5.5 6v3.5M7.5 6v3.5M3 3.5l.5 7h6l.5-7"
                                                stroke="currentColor" stroke-width="1.4" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="ci-mobile-list">
                    <div v-for="courier in couriers.data" :key="courier.id" class="ci-mobile-card">
                        <div class="ci-mobile-card__header">
                            <div>
                                <span class="ci-mobile-card__id">#{{ courier.id }}</span>
                                <span class="ci-mobile-card__name">{{ courier.name }}</span>
                            </div>
                            <span
                                :class="['ci-badge', courier.status === 'enabled' ? 'ci-badge--on' : 'ci-badge--off']">
                                {{ courier.status === 'enabled' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="ci-mobile-card__body">
                            <div class="ci-mobile-card__row">
                                <span class="ci-mobile-card__label">Type</span>
                                <span class="ci-badge ci-badge--type">{{ courier.type }}</span>
                            </div>
                            <div class="ci-mobile-card__row">
                                <span class="ci-mobile-card__label">Cost</span>
                                <span class="ci-mobile-card__value ci-mobile-card__value--bold">{{
                                    formatCurrency(courier.cost) }}</span>
                            </div>
                            <div class="ci-mobile-card__row">
                                <span class="ci-mobile-card__label">Added</span>
                                <span class="ci-mobile-card__value">{{ formatDate(courier.created_at) }}</span>
                            </div>
                        </div>
                        <div class="ci-mobile-card__footer">
                            <Link :href="route('admin.couriers.edit', courier.id)"
                                class="ci-action-btn ci-action-btn--edit">
                            Edit
                            </Link>
                            <button @click="confirmDelete(courier)" class="ci-action-btn ci-action-btn--delete">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="couriers.last_page > 1" class="ci-pagination">
                    <span class="ci-pagination__info">
                        Page {{couriers.links.find(l => l.active)?.label}} of {{ couriers.last_page }}
                    </span>
                    <div class="ci-pagination__btns">
                        <button v-for="link in couriers.links" :key="link.label" @click.prevent="paginate(link.url)"
                            :disabled="!link.url" :class="['ci-page-btn', { 'ci-page-btn--active': link.active }]"
                            v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')" />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="ci-empty">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="6" y="12" width="28" height="20" rx="3" stroke="var(--bb-muted)" stroke-width="1.8" />
                    <path d="M6 18h28M14 12V8a6 6 0 0 1 12 0v4" stroke="var(--bb-muted)" stroke-width="1.8"
                        stroke-linecap="round" />
                </svg>
                <p class="ci-empty__title">No couriers found</p>
                <p class="ci-empty__sub">Add your first courier to get started.</p>
                <Link :href="route('admin.couriers.create')" class="ci-btn-primary">+ Add Courier</Link>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
.ci-wrap {
    --bb-navy: #1a1a2e;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;
    --bb-red-bg: #fdeef0;
    --bb-green: #4caf7d;
    --bb-green-bg: #eef7f2;
    --bb-lav: #c9b8f0;
    --bb-lav-d: #9b84d4;
    --bb-peach: #f5d5b8;
    --bb-peach-d: #c8820a;

    font-family: 'DM Sans', sans-serif;
    color: var(--bb-text);
    padding: 0.25rem 0;
}

/* ── Header ── */
.ci-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.ci-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0 0 0.25rem;
}

.ci-sub {
    font-size: 0.85rem;
    color: var(--bb-muted);
    margin: 0;
}

.ci-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: var(--bb-navy);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.6rem 1.1rem;
    font-size: 0.85rem;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    text-decoration: none;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
    white-space: nowrap;
}

.ci-btn-primary:hover {
    opacity: 0.88;
    transform: translateY(-1px);
}

/* ── Table (desktop) ── */
.ci-table-wrap {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 6px rgba(26, 26, 46, 0.05);
    overflow: hidden;
}

.ci-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

.ci-table thead {
    background: var(--bb-cream);
    border-bottom: 1px solid var(--bb-border);
}

.ci-table th {
    padding: 0.75rem 1rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
    text-align: left;
}

.ci-table tbody tr {
    border-bottom: 1px solid var(--bb-border);
    transition: background 0.12s;
}

.ci-table tbody tr:last-child {
    border-bottom: none;
}

.ci-table tbody tr:hover {
    background: #fdfcfb;
}

.ci-table td {
    padding: 0.85rem 1rem;
    vertical-align: middle;
}

.ci-td-id {
    font-weight: 600;
    color: var(--bb-muted);
    font-size: 0.82rem;
}

.ci-td-name {
    font-weight: 600;
}

.ci-td-cost {
    font-weight: 700;
}

.ci-td-date {
    color: var(--bb-muted);
    font-size: 0.83rem;
}

.ci-td-actions {
    text-align: right;
    white-space: nowrap;
}

/* ── Badges ── */
.ci-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.22rem 0.65rem;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}

.ci-badge--on {
    background: var(--bb-green-bg);
    color: #2a7a50;
}

.ci-badge--off {
    background: var(--bb-red-bg);
    color: var(--bb-red);
}

.ci-badge--type {
    background: #f8f6ff;
    color: var(--bb-lav-d);
    border: 1px solid var(--bb-lav);
}

/* ── Action buttons ── */
.ci-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.35rem 0.7rem;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.13s, color 0.13s, border-color 0.13s;
    color: var(--bb-muted);
    margin-left: 0.2rem;
}

.ci-action-btn--edit:hover {
    background: var(--bb-cream);
    border-color: var(--bb-border);
    color: var(--bb-text);
}

.ci-action-btn--delete:hover {
    background: var(--bb-red-bg);
    border-color: var(--bb-red);
    color: var(--bb-red);
}

/* ── Mobile cards ── */
.ci-mobile-list {
    display: none;
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 6px rgba(26, 26, 46, 0.05);
    overflow: hidden;
}

.ci-mobile-card {
    padding: 1rem;
    border-bottom: 1px solid var(--bb-border);
}

.ci-mobile-card:last-child {
    border-bottom: none;
}

.ci-mobile-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    gap: 0.5rem;
}

.ci-mobile-card__id {
    font-size: 0.75rem;
    color: var(--bb-muted);
    font-weight: 600;
    margin-right: 0.4rem;
}

.ci-mobile-card__name {
    font-weight: 700;
    font-size: 0.95rem;
}

.ci-mobile-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 0.85rem;
}

.ci-mobile-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.85rem;
}

.ci-mobile-card__label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bb-muted);
}

.ci-mobile-card__value {
    font-size: 0.88rem;
    color: var(--bb-text);
}

.ci-mobile-card__value--bold {
    font-weight: 700;
}

.ci-mobile-card__footer {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--bb-border);
}

/* ── Pagination ── */
.ci-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.25rem;
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    margin-top: 1rem;
    font-size: 0.83rem;
    color: var(--bb-muted);
    font-weight: 500;
}

.ci-pagination__btns {
    display: flex;
    gap: 0.35rem;
}

.ci-page-btn {
    min-width: 2.1rem;
    height: 2.1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.83rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
    color: var(--bb-text);
    padding: 0 0.5rem;
}

.ci-page-btn:hover:not(:disabled) {
    background: var(--bb-cream);
}

.ci-page-btn--active {
    background: var(--bb-navy);
    color: #fff;
    border-color: var(--bb-navy);
    font-weight: 700;
}

.ci-page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Empty state ── */
.ci-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 3.5rem 2rem;
    background: var(--bb-surface);
    border-radius: 14px;
    border: 2px dashed var(--bb-border);
    text-align: center;
}

.ci-empty__title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0.25rem 0 0;
}

.ci-empty__sub {
    font-size: 0.85rem;
    color: var(--bb-muted);
    margin: 0 0 0.5rem;
}

/* ── Responsive ── */
@media (max-width: 767px) {
    .ci-table-wrap {
        display: none;
    }

    .ci-mobile-list {
        display: block;
    }
}
</style>
