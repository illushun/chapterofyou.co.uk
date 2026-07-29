<script setup lang="ts">
import { useAdmin } from '@/composables/useAdmin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    is_admin: boolean;
}

interface UsersPaginated {
    data: User[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

defineProps<{
    users: UsersPaginated;
}>();

const { paginate } = useAdmin();

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
</script>

<template>
    <AdminLayout>
        <Head title="Users : Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Users</h1>
                <p class="adm-sub">Registered customers and admins</p>
            </div>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom: 1.5rem">
            <!-- Desktop table -->
            <div v-if="users.data.length" class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">#</th>
                            <th class="adm-th">Name</th>
                            <th class="adm-th">Email</th>
                            <th class="adm-th">Joined</th>
                            <th class="adm-th">Role</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="adm-row"
                        >
                            <td class="adm-td adm-td--mono">{{ user.id }}</td>
                            <td class="adm-td">{{ user.name }}</td>
                            <td
                                class="adm-td"
                                style="color: var(--adm-ink-dim)"
                            >
                                {{ user.email }}
                            </td>
                            <td
                                class="adm-td"
                                style="color: var(--adm-ink-dim)"
                            >
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="adm-td">
                                <span
                                    class="adm-badge"
                                    :class="
                                        user.is_admin
                                            ? 'adm-badge--blush'
                                            : 'adm-badge--off'
                                    "
                                >
                                    {{ user.is_admin ? 'Admin' : 'Customer' }}
                                </span>
                            </td>
                            <td class="adm-td adm-td--actions">
                                <Link
                                    :href="route('admin.users.show', user.id)"
                                    class="adm-action adm-action--edit"
                                >
                                    View</Link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div v-if="users.data.length" class="adm-mob-list">
                <Link
                    v-for="user in users.data"
                    :key="user.id"
                    :href="route('admin.users.show', user.id)"
                    class="adm-mob-card"
                    style="text-decoration: none"
                >
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                        "
                    >
                        <span style="font-weight: 600; color: var(--adm-ink)">{{
                            user.name
                        }}</span>
                        <span
                            class="adm-badge"
                            :class="
                                user.is_admin
                                    ? 'adm-badge--blush'
                                    : 'adm-badge--off'
                            "
                        >
                            {{ user.is_admin ? 'Admin' : 'Customer' }}
                        </span>
                    </div>
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                        "
                    >
                        <div>
                            <p class="adm-label--sm">Email</p>
                            <p style="color: var(--adm-ink)">
                                {{ user.email }}
                            </p>
                        </div>
                        <div style="text-align: right">
                            <p class="adm-label--sm">Joined</p>
                            <p style="color: var(--adm-ink)">
                                {{ formatDate(user.created_at) }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="!users.data.length" class="adm-empty">
                <div class="adm-empty-icon">
                    <Users :size="28" :stroke-width="1.5" />
                </div>
                <p class="adm-empty-title">No users yet</p>
                <p class="adm-empty-sub">
                    Registered customers will appear here.
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="adm-pagination">
            <div class="adm-page-btns">
                <button
                    v-for="link in users.links"
                    :key="link.label"
                    @click.prevent="paginate(link.url)"
                    :disabled="!link.url"
                    class="adm-page-btn"
                    :class="{ 'adm-page-btn--active': link.active }"
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
