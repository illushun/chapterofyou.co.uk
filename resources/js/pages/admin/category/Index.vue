<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Category {
    id: number;
    name: string;
    image: string;
    status: 'enabled' | 'disabled';
    created_at: string;
}

interface CategoriesPaginated {
    data: Category[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

const props = defineProps<{
    categories: CategoriesPaginated;
}>();

const confirmDelete = (category: Category) => {
    console.info(`Attempting to delete category: ${category.name}`);
    if (confirm(`Are you sure you want to delete the category: ${category.name}? This action cannot be undone.`)) {
        router.delete(route('admin.categories.destroy', category.id), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Category deleted successfully.');
            },
        });
    }
};

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

const getStatusClasses = (status: Category['status']) => {
    switch (status) {
        case 'enabled':
            return 'bg-green-500/20 text-green-700 border border-green-700';
        case 'disabled':
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

        <Head title="Manage Categories" />

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Categories</h2>
            <Link href=""
                class="relative rounded-lg -m-0.5 px-4 py-2 text-sm font-bold text-primary-content transition border-2 border-copy bg-primary hover:bg-primary-dark shadow-md">
            + Add New Category
            </Link>
        </div>

        <div v-if="categories.data.length" class="rounded-lg border-2 border-copy bg-[var(--primary-content)]">

            <!--
                DESKTOP TABLE VIEW
                (Hidden below 'md' breakpoint, uses full table structure)
            -->
            <div class="hidden md:block relative rounded-lg -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="text-left bg-secondary-light font-bold text-copy uppercase border-b-2 border-copy">
                            <th class="px-4 py-3"></th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Created At</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="category in categories.data" :key="category.id"
                            class="hover:bg-secondary-light transition">
                            <td class="px-4 py-3">
                                <img :src="category.image ?? 'https://placehold.co/50x50/333/fff?text=NO'"
                                    :alt="category.name" class="size-10 object-contain border border-copy p-1 rounded">
                            </td>
                            <td class="px-4 py-3">{{ category.name }}</td>
                            <td class="px-4 py-3">
                                <span
                                    :class="['px-3 py-1 rounded-full text-xs font-semibold uppercase', getStatusClasses(category.status)]">
                                    {{ category.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ formatDate(category.created_at) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
