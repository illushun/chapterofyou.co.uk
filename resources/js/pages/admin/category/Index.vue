<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Tags } from 'lucide-vue-next';

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
    if (
        confirm(`Delete category "${category.name}"?\n\nThis cannot be undone.`)
    ) {
        router.delete(route('admin.categories.destroy', category.id), {
            preserveScroll: true,
        });
    }
};

const paginate = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <Head title="Categories : Admin" />

        <!-- Header -->
        <div class="ci-header">
            <div>
                <h1 class="ci-title">Categories</h1>
                <p class="ci-sub">{{ categories.total }} categories total</p>
            </div>
            <Link
                :href="route('admin.categories.create')"
                class="ci-btn-primary"
            >
                <svg
                    width="14"
                    height="14"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                >
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
                            <th class="ci-th" style="width: 52px"></th>
                            <th class="ci-th">Name</th>
                            <th class="ci-th">Status</th>
                            <th class="ci-th ci-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="cat in categories.data"
                            :key="cat.id"
                            class="ci-row"
                        >
                            <!-- Image -->
                            <td class="ci-td">
                                <div class="ci-thumb">
                                    <img
                                        v-if="cat.image"
                                        :src="cat.image"
                                        :alt="cat.name"
                                        class="ci-thumb-img"
                                    />
                                    <span v-else class="ci-thumb-nil">-</span>
                                </div>
                            </td>
                            <!-- Name -->
                            <td class="ci-td">
                                <Link
                                    :href="
                                        route('admin.categories.edit', cat.id)
                                    "
                                    class="ci-name-link"
                                >
                                    {{ cat.name }}
                                </Link>
                            </td>
                            <!-- Status -->
                            <td class="ci-td">
                                <span
                                    class="ci-badge"
                                    :class="
                                        cat.status === 'enabled'
                                            ? 'ci-badge--on'
                                            : 'ci-badge--off'
                                    "
                                >
                                    {{
                                        cat.status === 'enabled'
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="ci-td ci-td--actions">
                                <Link
                                    :href="
                                        route('admin.categories.edit', cat.id)
                                    "
                                    class="ci-action ci-action--edit"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="confirmDelete(cat)"
                                    class="ci-action ci-action--del"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="ci-mob-list">
                <div
                    v-for="cat in categories.data"
                    :key="cat.id"
                    class="ci-mob-card"
                >
                    <div class="ci-mob-head">
                        <div class="ci-thumb ci-thumb--lg">
                            <img
                                v-if="cat.image"
                                :src="cat.image"
                                :alt="cat.name"
                                class="ci-thumb-img"
                            />
                            <span v-else class="ci-thumb-nil">-</span>
                        </div>
                        <div class="ci-mob-info">
                            <Link
                                :href="route('admin.categories.edit', cat.id)"
                                class="ci-name-link ci-name-link--lg"
                            >
                                {{ cat.name }}
                            </Link>
                            <span
                                class="ci-badge"
                                :class="
                                    cat.status === 'enabled'
                                        ? 'ci-badge--on'
                                        : 'ci-badge--off'
                                "
                            >
                                {{
                                    cat.status === 'enabled'
                                        ? 'Active'
                                        : 'Inactive'
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="ci-mob-foot">
                        <Link
                            :href="route('admin.categories.edit', cat.id)"
                            class="ci-btn-sm ci-btn-sm--edit"
                            >Edit
                        </Link>
                        <button
                            @click="confirmDelete(cat)"
                            class="ci-btn-sm ci-btn-sm--del"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="!categories.data.length" class="ci-empty">
                <div class="ci-empty-icon">
                    <Tags :size="28" :stroke-width="1.5" />
                </div>
                <p class="ci-empty-title">No categories yet</p>
                <p class="ci-empty-sub">
                    Add your first category to get started.
                </p>
                <Link
                    :href="route('admin.categories.create')"
                    class="ci-btn-primary"
                    >Add Category</Link
                >
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="categories.last_page > 1" class="ci-pagination">
            <p class="ci-page-info">
                Page <strong>{{ categories.current_page }}</strong> of
                <strong>{{ categories.last_page }}</strong>
            </p>
            <div class="ci-page-btns">
                <button
                    v-for="link in categories.links"
                    :key="link.label"
                    @click.prevent="paginate(link.url)"
                    :disabled="!link.url"
                    class="ci-page-btn"
                    :class="{ 'ci-page-btn--active': link.active }"
                    v-html="
                        link.label
                            .replace('&laquo; Previous', '←')
                            .replace('Next &raquo;', '→')
                    "
                ></button>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.ci-header,
.ci-card {
    font-family: var(--adm-font);
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
    font-family: var(--adm-display);
    font-style: italic;
    font-size: 2rem;
    font-weight: 400;
    letter-spacing: -0.01em;
    color: var(--adm-ink);
    margin-bottom: 0.2rem;
}

.ci-sub {
    font-size: 0.82rem;
    color: var(--adm-ink-dim);
}

.ci-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.1rem;
    border-radius: 8px;
    background: var(--adm-charcoal);
    color: var(--adm-paper-raised);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition:
        opacity 0.15s,
        transform 0.15s;
    white-space: nowrap;
}

.ci-btn-primary:hover {
    opacity: 0.88;
    transform: translateY(-1px);
}

/* ── Card ── */
.ci-card {
    background: var(--adm-paper-raised);
    border-radius: 14px;
    border: 1px solid var(--adm-line);
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
    background: var(--adm-paper);
    border-bottom: 1px solid var(--adm-line);
}

.ci-th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--adm-ink-dim);
    white-space: nowrap;
}

.ci-th--right {
    text-align: right;
}

.ci-row {
    border-bottom: 1px solid var(--adm-line);
    transition: background 0.12s;
}

.ci-row:last-child {
    border-bottom: none;
}

.ci-row:hover {
    background: var(--adm-paper);
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
    border: 1px solid var(--adm-line);
    overflow: hidden;
    background: var(--adm-paper);
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
    color: var(--adm-ink-dim);
}

/* ── Name link ── */
.ci-name-link {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--adm-ink);
    text-decoration: none;
    transition: color 0.15s;
}

.ci-name-link:hover {
    color: var(--adm-stamp-deep);
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
    background: var(--adm-success-bg);
    color: var(--adm-success);
}

.ci-badge--off {
    background: var(--adm-paper-sunk);
    color: var(--adm-ink-dim);
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
    font-family: var(--adm-font);
    transition:
        background 0.12s,
        color 0.12s,
        border-color 0.12s;
}

.ci-action--edit {
    color: var(--adm-ink-dim);
}

.ci-action--edit:hover {
    background: var(--adm-paper);
    border-color: var(--adm-line);
    color: var(--adm-ink);
}

.ci-action--del {
    color: var(--adm-danger);
}

.ci-action--del:hover {
    background: var(--adm-danger-bg);
    border-color: var(--adm-danger);
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
    border-bottom: 1px solid var(--adm-line);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: background 0.12s;
}

.ci-mob-card:last-child {
    border-bottom: none;
}

.ci-mob-card:hover {
    background: var(--adm-paper);
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
    border-top: 1px solid var(--adm-line);
}

.ci-btn-sm {
    display: inline-flex;
    align-items: center;
    padding: 0.42rem 0.85rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid var(--adm-line);
    cursor: pointer;
    text-decoration: none;
    font-family: var(--adm-font);
    transition: background 0.12s;
}

.ci-btn-sm--edit {
    background: var(--adm-paper);
    color: var(--adm-ink);
}

.ci-btn-sm--edit:hover {
    background: var(--adm-line);
}

.ci-btn-sm--del {
    background: var(--adm-danger-bg);
    color: var(--adm-danger);
    border-color: var(--adm-danger);
}

.ci-btn-sm--del:hover {
    background: var(--adm-danger);
    color: var(--adm-paper-raised);
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
    color: var(--adm-ink-faint);
}

.ci-empty-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--adm-ink);
}

.ci-empty-sub {
    font-size: 0.85rem;
    color: var(--adm-ink-dim);
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
    color: var(--adm-ink-dim);
}

.ci-page-info strong {
    color: var(--adm-ink);
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
    border: 1px solid var(--adm-line);
    background: var(--adm-paper-raised);
    color: var(--adm-ink-dim);
    font-size: 0.82rem;
    font-weight: 500;
    font-family: var(--adm-font);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition:
        background 0.12s,
        color 0.12s;
}

.ci-page-btn:hover:not(:disabled) {
    background: var(--adm-paper);
    color: var(--adm-ink);
}

.ci-page-btn--active {
    background: var(--adm-charcoal);
    color: var(--adm-paper-raised);
    border-color: var(--adm-charcoal);
    font-weight: 600;
}

.ci-page-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>
