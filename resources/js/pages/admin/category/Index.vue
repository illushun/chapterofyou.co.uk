<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    image: string;
    status: 'enabled' | 'disabled';
}
interface CategoriesPaginated {
    data: Category[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{ categories: CategoriesPaginated }>();

const confirmDelete = (category: Category) => {
    if (confirm(`Delete category "${category.name}"?\n\nThis cannot be undone.`)) {
        router.delete(route('admin.categories.destroy', category.id), { preserveScroll: true });
    }
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <AdminLayout>

        <Head title="Categories — Admin" />

        <!-- Header -->
        <div class="ci-header">
            <div>
                <h1 class="ci-title">Categories</h1>
                <p class="ci-sub">{{ categories.total }} categories total</p>
            </div>
            <Link :href="route('admin.categories.create')" class="ci-btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            Add Category
            </Link>
        </div>

        <!-- Table card -->
        <div class="ci-card">

            <!-- Desktop table -->
            <div class="ci-table-wrap">
                <table class="ci-table">
                    <thead>
                        <tr class="ci-thead">
                            <th class="ci-th" style="width:52px"></th>
                            <th class="ci-th">Name</th>
                            <th class="ci-th">Status</th>
                            <th class="ci-th ci-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in categories.data" :key="cat.id" class="ci-row">
                            <!-- Image -->
                            <td class="ci-td">
                                <div class="ci-thumb">
                                    <img v-if="cat.image" :src="cat.image" :alt="cat.name" class="ci-thumb-img" />
                                    <span v-else class="ci-thumb-nil">—</span>
                                </div>
                            </td>
                            <!-- Name -->
                            <td class="ci-td">
                                <Link :href="route('admin.categories.edit', cat.id)" class="ci-name-link">
                                {{ cat.name }}
                                </Link>
                            </td>
                            <!-- Status -->
                            <td class="ci-td">
                                <span class="ci-badge"
                                    :class="cat.status === 'enabled' ? 'ci-badge--on' : 'ci-badge--off'">
                                    {{ cat.status === 'enabled' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="ci-td ci-td--actions">
                                <Link :href="route('admin.categories.edit', cat.id)" class="ci-action ci-action--edit">
                                Edit
                                </Link>
                                <button @click="confirmDelete(cat)" class="ci-action ci-action--del">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="ci-mob-list">
                <div v-for="cat in categories.data" :key="cat.id" class="ci-mob-card">
                    <div class="ci-mob-head">
                        <div class="ci-thumb ci-thumb--lg">
                            <img v-if="cat.image" :src="cat.image" :alt="cat.name" class="ci-thumb-img" />
                            <span v-else class="ci-thumb-nil">—</span>
                        </div>
                        <div class="ci-mob-info">
                            <Link :href="route('admin.categories.edit', cat.id)" class="ci-name-link ci-name-link--lg">
                            {{ cat.name }}
                            </Link>
                            <span class="ci-badge" :class="cat.status === 'enabled' ? 'ci-badge--on' : 'ci-badge--off'">
                                {{ cat.status === 'enabled' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="ci-mob-foot">
                        <Link :href="route('admin.categories.edit', cat.id)" class="ci-btn-sm ci-btn-sm--edit">Edit
                        </Link>
                        <button @click="confirmDelete(cat)" class="ci-btn-sm ci-btn-sm--del">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="!categories.data.length" class="ci-empty">
                <div class="ci-empty-icon">🏷️</div>
                <p class="ci-empty-title">No categories yet</p>
                <p class="ci-empty-sub">Add your first category to get started.</p>
                <Link :href="route('admin.categories.create')" class="ci-btn-primary">Add Category</Link>
            </div>

        </div>

        <!-- Pagination -->
        <div v-if="categories.last_page > 1" class="ci-pagination">
            <p class="ci-page-info">
                Page <strong>{{ categories.current_page }}</strong> of <strong>{{ categories.last_page }}</strong>
            </p>
            <div class="ci-page-btns">
                <button v-for="link in categories.links" :key="link.label" @click.prevent="paginate(link.url)"
                    :disabled="!link.url" class="ci-page-btn" :class="{ 'ci-page-btn--active': link.active }"
                    v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')">
                </button>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.ci-header,
.ci-card {
    --bb-navy: #1a1a2e;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;
    --bb-red-bg: #fdeef0;
    --bb-green: #4caf7d;
    --bb-blush-d: #d4899a;
    font-family: 'DM Sans', sans-serif;
}

/* ── Header ── */
.ci-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.ci-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--bb-text);
    margin-bottom: 0.2rem;
}

.ci-sub {
    font-size: 0.82rem;
    color: var(--bb-muted);
}

.ci-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.1rem;
    border-radius: 8px;
    background: var(--bb-navy);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
    white-space: nowrap;
}

.ci-btn-primary:hover {
    opacity: 0.88;
    transform: translateY(-1px);
}

/* ── Card ── */
.ci-card {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 8px rgba(26, 26, 46, 0.05);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

/* ── Desktop table ── */
.ci-table-wrap {
    display: none;
    overflow-x: auto;
}

@media (min-width: 768px) {
    .ci-table-wrap {
        display: block;
    }
}

.ci-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.ci-thead {
    background: var(--bb-cream);
    border-bottom: 1px solid var(--bb-border);
}

.ci-th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--bb-muted);
    white-space: nowrap;
}

.ci-th--right {
    text-align: right;
}

.ci-row {
    border-bottom: 1px solid var(--bb-border);
    transition: background 0.12s;
}

.ci-row:last-child {
    border-bottom: none;
}

.ci-row:hover {
    background: #fdfcfb;
}

.ci-td {
    padding: 0.85rem 1rem;
    vertical-align: middle;
}

.ci-td--actions {
    text-align: right;
    white-space: nowrap;
}

/* ── Thumbnail ── */
.ci-thumb {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    overflow: hidden;
    background: var(--bb-cream);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ci-thumb--lg {
    width: 48px;
    height: 48px;
}

.ci-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ci-thumb-nil {
    font-size: 0.7rem;
    color: var(--bb-muted);
}

/* ── Name link ── */
.ci-name-link {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--bb-text);
    text-decoration: none;
    transition: color 0.15s;
}

.ci-name-link:hover {
    color: var(--bb-blush-d);
    text-decoration: underline;
}

.ci-name-link--lg {
    font-size: 0.95rem;
}

/* ── Badge ── */
.ci-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.22rem 0.65rem;
    border-radius: 999px;
}

.ci-badge--on {
    background: #e8f5ee;
    color: #2a7a50;
}

.ci-badge--off {
    background: #f3f3f8;
    color: var(--bb-muted);
}

/* ── Table action buttons ── */
.ci-action {
    display: inline-flex;
    align-items: center;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    border: 1px solid transparent;
    text-decoration: none;
    cursor: pointer;
    background: none;
    margin-left: 0.35rem;
    font-family: 'DM Sans', sans-serif;
    transition: background 0.12s, color 0.12s, border-color 0.12s;
}

.ci-action--edit {
    color: var(--bb-muted);
}

.ci-action--edit:hover {
    background: var(--bb-cream);
    border-color: var(--bb-border);
    color: var(--bb-text);
}

.ci-action--del {
    color: var(--bb-red);
}

.ci-action--del:hover {
    background: var(--bb-red-bg);
    border-color: var(--bb-red);
}

/* ── Mobile cards ── */
.ci-mob-list {
    display: flex;
    flex-direction: column;
}

@media (min-width: 768px) {
    .ci-mob-list {
        display: none;
    }
}

.ci-mob-card {
    padding: 1rem;
    border-bottom: 1px solid var(--bb-border);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: background 0.12s;
}

.ci-mob-card:last-child {
    border-bottom: none;
}

.ci-mob-card:hover {
    background: #fdfcfb;
}

.ci-mob-head {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.ci-mob-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.ci-mob-foot {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--bb-border);
}

.ci-btn-sm {
    display: inline-flex;
    align-items: center;
    padding: 0.42rem 0.85rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid var(--bb-border);
    cursor: pointer;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
    transition: background 0.12s;
}

.ci-btn-sm--edit {
    background: var(--bb-cream);
    color: var(--bb-text);
}

.ci-btn-sm--edit:hover {
    background: var(--bb-border);
}

.ci-btn-sm--del {
    background: var(--bb-red-bg);
    color: var(--bb-red);
    border-color: var(--bb-red);
}

.ci-btn-sm--del:hover {
    background: var(--bb-red);
    color: #fff;
}

/* ── Empty ── */
.ci-empty {
    padding: 4rem 2rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.ci-empty-icon {
    font-size: 2.5rem;
    opacity: 0.5;
}

.ci-empty-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--bb-text);
}

.ci-empty-sub {
    font-size: 0.85rem;
    color: var(--bb-muted);
}

/* ── Pagination ── */
.ci-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.ci-page-info {
    font-size: 0.82rem;
    color: var(--bb-muted);
}

.ci-page-info strong {
    color: var(--bb-text);
}

.ci-page-btns {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.ci-page-btn {
    min-width: 36px;
    height: 34px;
    padding: 0 0.6rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    color: var(--bb-muted);
    font-size: 0.82rem;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.12s, color 0.12s;
}

.ci-page-btn:hover:not(:disabled) {
    background: var(--bb-cream);
    color: var(--bb-text);
}

.ci-page-btn--active {
    background: var(--bb-navy);
    color: #fff;
    border-color: var(--bb-navy);
    font-weight: 600;
}

.ci-page-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>
