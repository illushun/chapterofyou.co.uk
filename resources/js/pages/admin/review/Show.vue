<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    mpn: string;
    name: string;
}

interface Review {
    id: number;
    user_id: number | null;
    product_id: number;
    message: string;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
}

const props = defineProps<{
    review: Review;
}>();

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

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
</script>

<template>
    <AdminLayout>

        <Head :title="`Review #${review.id}`" />

        <div class="flex justify-between items-start mb-6 border-b-2 border-copy pb-2">
            <div>
                <h2 class="text-3xl font-black">Review #{{ review.id }}</h2>
                <p class="text-copy-light">Reviewed on {{ formatDate(review.created_at) }}</p>
            </div>
            <span
                :class="['px-4 py-1.5 rounded-full text-lg font-bold uppercase border-2', getStatusClasses(review.status)]">
                {{ review.status }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Review</h3>
                        <div class="flex items-center justify-start py-3">
                            <div class="flex flex-col">
                                <span class="font-semibold text-copy">{{ review.rating }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-lg block">{{ review.message }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Customer Details
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-copy">
                            <div><span class="font-semibold">Name:</span> {{ review.user.name }}
                            </div>
                            <div><span class="font-semibold">Email:</span> <a :href="`mailto:${review.user.email}`"
                                    class="text-primary hover:underline">{{ review.user.email }}</a></div>
                            <div><span class="font-semibold">User Account:</span>
                                <Link v-if="review.user" :href="route('admin.users.show', review.user.id)"
                                    class="text-primary hover:underline">
                                {{ review.user.name }} (#{{ review.user.id }})
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="sticky top-8 rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-2xl font-black text-copy mb-4 border-b-2 border-copy-light pb-3">Review Actions
                        </h3>

                        <div class="space-y-3 text-copy text-lg">
                            <div class="flex justify-between">
                                <span>Status</span>
                                <span class="font-bold">{{ review.status }}</span>
                            </div>
                        </div>

                        <button
                            class="mt-6 w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg bg-secondary-light hover:bg-secondary text-copy"
                            disabled>
                            Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
