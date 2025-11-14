<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

// Interfaces for data structure
interface User {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    mpn: string;
}

interface Review {
    id: number;
    user_id: number | null;
    rating: number;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
}

interface ReviewsPaginated {
    data: Review[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

const props = defineProps<{
    reviews: ReviewsPaginated;
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

const getStatusClasses = (status: Order['status']) => {
    switch (status) {
        case 'approved':
            return 'bg-green-500/20 text-green-700 border border-green-700';
        case 'pending':
            return 'bg-yellow-500/20 text-yellow-700 border border-yellow-700';
        case 'rejected':
            return 'bg-red-500/20 text-error border border-error-dark';
        default:
            return 'bg-gray-500/20 text-gray-700 border border-gray-700';
    }
};

const paginate = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>

        <Head title="Manage Reviews" />

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Reviews (Recent)</h2>
        </div>

        <div v-if="orders.data.length" class="rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg">

            <!--
                DESKTOP TABLE VIEW
                (Hidden below 'md' breakpoint, uses full table structure)
            -->
            <div class="hidden md:block relative rounded-lg -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="text-left bg-secondary-light font-bold text-copy uppercase border-b-2 border-copy">
                            <th class="px-4 py-3">Review ID</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Rating</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="review in reviews.data" :key="review.id" class="hover:bg-secondary-light transition">
                            <td class="px-4 py-3 font-semibold text-primary">#{{ review.id }}</td>
                            <td class="px-4 py-3">
                                <span v-if="order.user">
                                    <Link :href="route('admin.users.show', review.user.id)" class="hover:underline">{{
                                        order.user.name }}</Link><br>
                                    <span class="text-xs text-copy-light">{{ review.user.email }}</span>
                                </span>
                                <span v-else class="text-copy-light italic">Guest</span>
                            </td>
                            <td class="px-4 py-3">{{ formatDate(review.created_at) }}</td>
                            <td class="px-4 py-3 font-bold text-primary">{{ review.rating }}</td>
                            <td class="px-4 py-3">
                                <span
                                    :class="['px-3 py-1 rounded-full text-xs font-semibold uppercase', getStatusClasses(review.status)]">
                                    {{ review.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <Link :href="route('admin.reviews.show', order.id)"
                                    class="text-blue-500 hover:text-blue-700 transition font-semibold">
                                View Details
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
                <div v-for="review in reviews.data" :key="review.id"
                    class="p-4 bg-foreground hover:bg-secondary-light transition">

                    <!-- Order ID and Status -->
                    <div class="flex justify-between items-start mb-3 border-b border-copy-light/30 pb-2">
                        <div>
                            <div class="text-xs text-copy-light uppercase font-medium">Order ID</div>
                            <Link :href="route('admin.reviews.show', order.id)"
                                class="text-xl font-bold text-primary hover:underline">
                            #{{ review.id }}
                            </Link>
                        </div>
                        <span
                            :class="['mt-1 px-3 py-1 rounded-full text-xs font-semibold uppercase flex-shrink-0', getStatusClasses(review.status)]">
                            {{ review.status }}
                        </span>
                    </div>

                    <!-- Customer Info -->
                    <div class="py-2 border-b border-copy-light/30">
                        <div class="text-xs text-copy-light uppercase font-medium">Customer</div>
                        <div v-if="review.user" class="mt-1">
                            <Link :href="route('admin.users.show', review.user.id)"
                                class="font-semibold text-copy hover:underline">{{ review.user.name }}</Link>
                            <div class="text-sm text-copy-light">{{ review.user.email }}</div>
                        </div>
                    </div>

                    <!-- Date, Rating, and Actions -->
                    <div class="flex justify-between items-end pt-3">
                        <!-- Date & Total -->
                        <div class="flex flex-col space-y-1">
                            <div>
                                <div class="text-xs text-copy-light uppercase font-medium">Rating</div>
                                <div class="font-bold text-lg text-primary">{{ review.rating }}
                                </div>
                            </div>
                            <div class="text-xs text-copy-light italic">
                                Placed: {{ formatDate(review.created_at) }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0">
                            <Link :href="route('admin.reviews.show', review.id)"
                                class="px-4 py-2 text-sm font-semibold transition border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-md">
                            View
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div v-else class="text-center p-12 border-4 border-dashed border-copy-light rounded-2xl bg-secondary-light/50">
            <p class="text-xl font-semibold text-copy mb-2">No reviews found.</p>
        </div>

        <div v-if="reviews.last_page > 1" class="mt-6 flex justify-center">
            <ol class="flex gap-2 text-sm font-medium">
                <li v-for="link in reviews.links" :key="link.label">
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
