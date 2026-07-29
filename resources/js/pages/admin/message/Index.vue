<script setup lang="ts">
import { useAdmin } from '@/composables/useAdmin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Mail } from 'lucide-vue-next';

interface Message {
    id: number;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    is_read: boolean;
    created_at: string;
}

interface MessagesPaginated {
    data: Message[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

defineProps<{
    messages: MessagesPaginated;
}>();

const { paginate } = useAdmin();

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
</script>

<template>
    <AdminLayout>
        <Head title="Messages : Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Messages</h1>
                <p class="adm-sub">
                    Contact form submissions from the storefront
                </p>
            </div>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom: 1.5rem">
            <!-- Desktop table -->
            <div v-if="messages.data.length" class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">#</th>
                            <th class="adm-th">Name</th>
                            <th class="adm-th">Email</th>
                            <th class="adm-th">Subject</th>
                            <th class="adm-th">Date</th>
                            <th class="adm-th">Status</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="message in messages.data"
                            :key="message.id"
                            class="adm-row"
                        >
                            <td class="adm-td adm-td--mono">
                                #{{ message.id }}
                            </td>
                            <td class="adm-td">{{ message.name }}</td>
                            <td
                                class="adm-td"
                                style="color: var(--adm-ink-dim)"
                            >
                                {{ message.email }}
                            </td>
                            <td class="adm-td">
                                <span v-if="message.subject">{{
                                    message.subject
                                }}</span>
                                <span
                                    v-else
                                    style="
                                        font-style: italic;
                                        color: var(--adm-ink-dim);
                                    "
                                    >No subject</span
                                >
                            </td>
                            <td
                                class="adm-td"
                                style="color: var(--adm-ink-dim)"
                            >
                                {{ formatDate(message.created_at) }}
                            </td>
                            <td class="adm-td">
                                <span
                                    class="adm-badge"
                                    :class="
                                        message.is_read
                                            ? 'adm-badge--off'
                                            : 'adm-badge--blush'
                                    "
                                >
                                    {{ message.is_read ? 'Read' : 'Unread' }}
                                </span>
                            </td>
                            <td class="adm-td adm-td--actions">
                                <Link
                                    :href="
                                        route('admin.messages.show', message.id)
                                    "
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
            <div v-if="messages.data.length" class="adm-mob-list">
                <Link
                    v-for="message in messages.data"
                    :key="message.id"
                    :href="route('admin.messages.show', message.id)"
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
                        <span
                            class="adm-td--mono"
                            style="font-weight: 600; color: var(--adm-ink)"
                            >#{{ message.id }}</span
                        >
                        <span
                            class="adm-badge"
                            :class="
                                message.is_read
                                    ? 'adm-badge--off'
                                    : 'adm-badge--blush'
                            "
                        >
                            {{ message.is_read ? 'Read' : 'Unread' }}
                        </span>
                    </div>
                    <div>
                        <p class="adm-label--sm">From</p>
                        <p style="color: var(--adm-ink)">{{ message.name }}</p>
                        <p class="adm-sub">{{ message.email }}</p>
                    </div>
                    <p
                        style="
                            font-size: 0.72rem;
                            color: var(--adm-ink-dim);
                            font-style: italic;
                        "
                    >
                        {{ formatDate(message.created_at) }}
                    </p>
                </Link>
            </div>

            <div v-if="!messages.data.length" class="adm-empty">
                <div class="adm-empty-icon">
                    <Mail :size="28" :stroke-width="1.5" />
                </div>
                <p class="adm-empty-title">No messages yet</p>
                <p class="adm-empty-sub">
                    Contact form submissions will appear here.
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="messages.last_page > 1" class="adm-pagination">
            <div class="adm-page-btns">
                <button
                    v-for="link in messages.links"
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
