<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    posts: {
        data: Array<{
            id: number;
            title: string;
            slug: string;
            status: 'draft' | 'published';
            published_at: string | null;
            created_at: string;
            author: { name: string } | null;
        }>;
        links: any[];
        meta: any;
    };
    filters: { status?: string; search?: string };
}>();

const search = ref('');

function applySearch() {
    router.get(route('admin.journal.index'), { search: search.value }, { preserveState: true });
}
function filterStatus(status: string) {
    router.get(route('admin.journal.index'), { status }, { preserveState: true });
}
function deletePost(id: number, title: string) {
    if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
    router.delete(route('admin.journal.destroy', id), { preserveScroll: true });
}
</script>

<template>
    <AdminLayout>
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Journal</h1>
                <p class="adm-sub">Write articles to improve your SEO and connect with customers.</p>
            </div>
            <Link :href="route('admin.journal.create')" class="adm-btn adm-btn--primary">
            + New Post
            </Link>
        </div>

        <!-- Filters -->
        <div class="adm-card adm-card--sm"
            style="margin-bottom: 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            <div class="adm-status-btns">
                <button @click="filterStatus('')" class="adm-status-btn"
                    :class="!filters.status ? 'adm-status-btn--active' : ''">All</button>
                <button @click="filterStatus('published')" class="adm-status-btn"
                    :class="filters.status === 'published' ? 'adm-status-btn--active' : ''">Published</button>
                <button @click="filterStatus('draft')" class="adm-status-btn"
                    :class="filters.status === 'draft' ? 'adm-status-btn--active' : ''">Drafts</button>
            </div>
            <div style="display:flex; gap:0.5rem; flex:1; min-width:200px;">
                <input v-model="search" type="text" class="adm-input" placeholder="Search posts…"
                    @keyup.enter="applySearch" style="flex:1;" />
                <button @click="applySearch" class="adm-btn adm-btn--ghost adm-btn--sm">Search</button>
            </div>
        </div>

        <!-- Table -->
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead class="adm-thead">
                    <tr>
                        <th class="adm-th">Title</th>
                        <th class="adm-th">Status</th>
                        <th class="adm-th">Published</th>
                        <th class="adm-th">Author</th>
                        <th class="adm-th">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="post in posts.data" :key="post.id" class="adm-row">
                        <td class="adm-td">
                            <div style="font-weight:600; color: var(--bb-text);">{{ post.title }}</div>
                            <div style="font-size:0.75rem; color: var(--bb-muted); font-family: monospace;">{{ post.slug
                            }}</div>
                        </td>
                        <td class="adm-td">
                            <span :class="post.status === 'published' ? 'adm-badge--on' : 'adm-badge--warn'">
                                {{ post.status }}
                            </span>
                        </td>
                        <td class="adm-td" style="font-size:0.82rem; color: var(--bb-muted);">
                            {{ post.published_at ?? '—' }}
                        </td>
                        <td class="adm-td" style="font-size:0.82rem;">
                            {{ post.author?.name ?? '—' }}
                        </td>
                        <td class="adm-td adm-td--actions">
                            <a :href="`/journal/${post.slug}`" target="_blank" class="adm-action--edit"
                                title="View live">↗</a>
                            <Link :href="route('admin.journal.edit', post.id)" class="adm-action--edit">Edit</Link>
                            <button @click="deletePost(post.id, post.title)" class="adm-action--del">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!posts.data.length">
                        <td colspan="5" class="adm-td"
                            style="text-align:center; padding:3rem; color:var(--bb-muted); font-style:italic;">
                            No posts yet.
                            <Link :href="route('admin.journal.create')" style="color:var(--bb-lav-d);">Write your first
                            article</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="posts.links?.length > 3" class="adm-pagination">
            <template v-for="link in posts.links" :key="link.label">
                <button v-if="link.url" @click="router.visit(link.url)" class="adm-page-btn"
                    :class="{ 'adm-page-btn--active': link.active }" v-html="link.label" />
                <span v-else class="adm-page-btn" style="opacity:0.35;" v-html="link.label" />
            </template>
        </div>
    </AdminLayout>
</template>
