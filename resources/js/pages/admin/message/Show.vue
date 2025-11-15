<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Message {
    id: number;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    is_read: boolean;
    created_at: string;
}

const props = defineProps<{
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

        <Head :title="`Message #${review.id}`" />

        <div class="flex justify-between items-start mb-6 border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Message #{{ message.id }}</h2>
                <p class="text-copy-light">Created at {{ formatDate(message.created_at) }}</p>
            </div>
            <span :class="['px-4 py-1.5 rounded-full text-lg font-bold uppercase border-2']">
                {{ nessage.is_read }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">
                            <span v-if="message.subject">{{ message.subject }}</span>
                            <span v-else class="italic">No Subject</span>
                        </h3>

                        <div class="text-copy-light text-md leading-relaxed">
                            <p class="text-copy">{{ message.message }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">User Details
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-copy">
                            <div><span class="font-semibold">Name:</span> {{ message.name }}
                            </div>
                            <div><span class="font-semibold">Email:</span> <a :href="`mailto:${message.email}`"
                                    class="text-primary hover:underline">{{ message.email }}</a></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="sticky top-8 rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy-light pb-3">Message Actions
                        </h3>

                        <div class="space-y-3 text-copy text-lg mb-6">
                            <div class="flex justify-between">
                                <span>Have Read:</span>
                                <span :class="['font-bold uppercase']">{{ message.is_read
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
