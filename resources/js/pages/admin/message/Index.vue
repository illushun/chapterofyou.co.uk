<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

// Interfaces for data structure
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

const props = defineProps<{
    messages: MessagesPaginated;
}>();

const formatDate = (dateString: string): string => {
    // Standard format for cards and tables
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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

        <Head title="Manage Messages" />

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Messages (Recent)</h2>
        </div>

        <div v-if="messages.data.length" class="rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg">

            <!--
                DESKTOP TABLE VIEW
                (Hidden below 'md' breakpoint, uses full table structure)
            -->
            <div class="hidden md:block relative rounded-lg -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="text-left bg-secondary-light font-bold text-copy uppercase border-b-2 border-copy">
                            <th class="px-4 py-3">Message ID</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Have Read?</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="message in messages.data" :key="message.id"
                            class="hover:bg-secondary-light transition">
                            <td class="px-4 py-3 font-semibold text-primary">#{{ message.id }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-copy-light">{{ message.name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-copy-light">{{ message.email }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="message.subject" class="text-xs text-copy-light">{{ message.subject
                                }}</span>
                                <span v-else class="text-xs text-copy-light italic">No subject..</span>
                            </td>
                            <td class="px-4 py-3">{{ formatDate(message.created_at) }}</td>
                            <td class="px-4 py-3 font-bold text-primary">{{ message.is_read }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <Link :href="route('admin.messages.show', message.id)"
                                    class="text-blue-500 hover:text-blue-700 transition font-semibold">
                                View Message
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!--
                MOBILE CARD VIEW
                (Visible below 'md' breakpoint, stacked layout for small screens)
            -->
            <div class="md:hidden divide-y divide-copy-light/50">
                <div v-for="message in messages.data" :key="message.id"
                    class="p-4 bg-foreground hover:bg-secondary-light transition">

                    <!-- Message ID and Status -->
                    <div class="flex justify-between items-start mb-3 border-b border-copy-light/30 pb-2">
                        <div>
                            <div class="text-xs text-copy-light uppercase font-medium">Message ID</div>
                            <Link :href="route('admin.messages.show', message.id)"
                                class="text-xl font-bold text-primary hover:underline">
                            #{{ message.id }}
                            </Link>
                        </div>
                        <span :class="['mt-1 px-3 py-1 rounded-full text-xs font-semibold uppercase flex-shrink-0']">
                            {{ message.is_read }}
                        </span>
                    </div>

                    <!-- User Info -->
                    <div class="py-2 border-b border-copy-light/30">
                        <div class="text-xs text-copy-light uppercase font-medium">User</div>
                        <div class="mt-1 text-sm text-copy-light">{{ message.name }}</div>
                        <div class="mt-1 text-sm text-copy-light">{{ message.email }}</div>
                    </div>

                    <!-- Date, Have Read, and Actions -->
                    <div class="flex justify-between items-end pt-3">
                        <!-- Date & Have Read -->
                        <div class="flex flex-col space-y-1">
                            <div>
                                <div class="text-xs text-copy-light uppercase font-medium">Have Read?</div>
                                <div class="font-bold text-lg text-primary">{{ message.is_read }}
                                </div>
                            </div>
                            <div class="text-xs text-copy-light italic">
                                Created: {{ formatDate(message.created_at) }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0">
                            <Link :href="route('admin.messages.show', message.id)"
                                class="px-4 py-2 text-sm font-semibold transition border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-md">
                            View
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div v-else class="text-center p-12 border-4 border-dashed border-copy-light rounded-2xl bg-secondary-light/50">
            <p class="text-xl font-semibold text-copy mb-2">No messages found.</p>
        </div>

        <div v-if="messages.last_page > 1" class="mt-6 flex justify-center">
            <ol class="flex gap-2 text-sm font-medium">
                <li v-for="link in messages.links" :key="link.label">
                    <button @click.prevent="paginate(link.url)" :disabled="!link.url"
                        :class="{ 'px-4 py-2 border-2 border-copy transition relative -m-0.5 font-bold': true, 'bg-primary text-primary-content shadow-md': link.active, 'bg-foreground hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed': !link.active }"
                        v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')"
                        :aria-label="link.label">
                    </button>
                </li>
            </ol>
        </div>
    </AdminLayout>
</template>
