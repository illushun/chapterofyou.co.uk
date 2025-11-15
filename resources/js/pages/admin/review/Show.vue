<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import StarRating from '@/components/ui/coy/StarRating.vue';

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
    rating: number;
    review_images: string[];
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    user: User;
    product: Product;
}

const props = defineProps<{
    review: Review;
}>();

const form = useForm({
    status: props.review.status,
});

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const getStatusClasses = (status: Review['status']) => {
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

const updateStatus = (newStatus: Review['status']) => {
    if (newStatus === props.review.status) {
        alert(`Review is already ${newStatus}.`);
        return;
    }

    // Set the status and submit the PUT request
    form.status = newStatus;
    form.put(route('admin.reviews.update', props.review.id), {
        preserveScroll: true,
        onSuccess: () => {
            // No need to manually update review.status, Inertia reloads prop on success
        },
        onError: (errors) => {
            console.error('Status update failed:', errors);
            form.status = props.review.status;
        }
    });
};

const isPending = computed(() => props.review.status === 'pending');
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
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Review Content
                        </h3>

                        <div class="flex items-center justify-start py-3 mb-4">
                            <StarRating :rating="review.rating" :size="24" class="text-yellow-500 mr-4" />
                            <span class="font-bold text-lg text-copy">{{ review.rating }} / 5 Stars</span>
                        </div>

                        <div class="text-copy-light text-md leading-relaxed">
                            <p class="text-copy">{{ review.message }}</p>
                        </div>

                        <div v-if="review.review_images && review.review_images.length"
                            class="mt-6 border-t-2 border-copy-light pt-4">
                            <h4 class="font-semibold text-copy mb-3">Images:</h4>
                            <div class="flex gap-4">
                                <img v-for="(image, index) in review.review_images" :key="index" :src="image"
                                    :alt="`Review image ${index + 1}`"
                                    class="size-20 object-cover rounded-lg border border-copy cursor-pointer transition hover:opacity-80" />
                            </div>
                        </div>

                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Product Details
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-copy">
                            <div><span class="font-semibold">Product Name:</span> {{ review.product.name }}</div>
                            <div><span class="font-semibold">MPN:</span> {{ review.product.mpn }}</div>
                            <div><span class="font-semibold">Product ID:</span> {{ review.product.id }}</div>
                            <div><span class="font-semibold">View Product:</span>
                                <Link :href="route('products.show', review.product.id)" target="_blank"
                                    class="text-primary hover:underline">
                                View Live
                                </Link>
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

                        <div class="space-y-3 text-copy text-lg mb-6">
                            <div class="flex justify-between">
                                <span>Current Status:</span>
                                <span :class="['font-bold uppercase', getStatusClasses(review.status)]">{{ review.status
                                    }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <button @click="updateStatus('approved')"
                                :disabled="form.processing || review.status === 'approved'"
                                class="w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg"
                                :class="{
                                    'bg-green-500 hover:bg-green-600 text-white': review.status !== 'approved',
                                    'bg-gray-300 text-gray-500 cursor-not-allowed': review.status === 'approved'
                                }">
                                {{ review.status === 'approved' ? 'Approved' : 'Approve Review' }}
                            </button>

                            <button @click="updateStatus('rejected')"
                                :disabled="form.processing || review.status === 'rejected'"
                                class="w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg"
                                :class="{
                                    'bg-red-500 hover:bg-red-600 text-white': review.status !== 'rejected',
                                    'bg-gray-300 text-gray-500 cursor-not-allowed': review.status === 'rejected'
                                }">
                                {{ review.status === 'rejected' ? 'Rejected' : 'Reject Review' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
