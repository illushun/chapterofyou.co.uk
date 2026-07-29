<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Message {
    id: number;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    is_read: boolean;
    created_at: string;
}

defineProps<{
    message: Message;
}>();

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
</script>

<template>
    <AdminLayout>
        <Head :title="`Message #${message.id} : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.messages.index')"
                        class="adm-breadcrumb a"
                        >Messages</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>#{{ message.id }}</span>
                </div>
                <h1 class="adm-title">Message #{{ message.id }}</h1>
                <p class="adm-sub">
                    Received {{ formatDate(message.created_at) }}
                </p>
            </div>
            <span
                class="adm-badge"
                :class="message.is_read ? 'adm-badge--off' : 'adm-badge--blush'"
            >
                {{ message.is_read ? 'Read' : 'Unread' }}
            </span>
        </div>

        <div class="adm-form-grid">
            <!-- Left column -->
            <div class="adm-form-left">
                <section class="adm-card">
                    <h2 class="adm-card-title">
                        <span v-if="message.subject">{{
                            message.subject
                        }}</span>
                        <span v-else class="adm-card-title-note"
                            >No subject</span
                        >
                    </h2>
                    <p
                        style="
                            color: var(--adm-ink);
                            line-height: 1.7;
                            white-space: pre-wrap;
                        "
                    >
                        {{ message.message }}
                    </p>
                </section>

                <section class="adm-card">
                    <h2 class="adm-card-title">Sender Details</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <p class="adm-label--sm">Name</p>
                            <p style="color: var(--adm-ink)">
                                {{ message.name }}
                            </p>
                        </div>
                        <div class="adm-field">
                            <p class="adm-label--sm">Email</p>
                            <a
                                :href="`mailto:${message.email}`"
                                style="color: var(--adm-stamp-deep)"
                                >{{ message.email }}</a
                            >
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right column -->
            <div class="adm-form-right">
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Status</h2>
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        "
                    >
                        <span class="adm-sub">Read status</span>
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
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
