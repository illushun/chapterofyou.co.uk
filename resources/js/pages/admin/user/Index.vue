<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

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

const props = defineProps<{
    users: UsersPaginated;
}>();

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
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
        <Head title="Manage Users" />

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Registered Users</h2>
        </div>

        <div v-if="users.data.length" class="overflow-x-auto rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg">
            <div class="relative rounded-lg -m-0.5 border-2 border-copy bg-foreground">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="text-left bg-secondary-light font-bold text-copy uppercase border-b-2 border-copy">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Joined</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-secondary-light transition">
                            <td class="px-4 py-3 font-semibold">{{ user.id }}</td>
                            <td class="px-4 py-3 font-semibold">{{ user.name }}</td>
                            <td class="px-4 py-3">{{ user.email }}</td>
                            <td class="px-4 py-3 text-copy-light">{{ formatDate(user.created_at) }}</td>
                            <td class="px-4 py-3">
                                <span v-if="user.is_admin" class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase bg-primary-light text-primary border border-primary-dark">
                                    Admin
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase bg-gray-500/20 text-gray-700 border border-gray-700">
                                    Customer
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <Link :href="route('admin.users.show', user.id)" class="text-blue-500 hover:text-blue-700 transition font-semibold">
                                    View Details
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else class="text-center p-12 border-4 border-dashed border-copy-light rounded-2xl bg-secondary-light/50">
            <p class="text-xl font-semibold text-copy mb-2">No users found.</p>
        </div>

        <div v-if="users.last_page > 1" class="mt-6 flex justify-center">
            <ol class="flex gap-2 text-sm font-medium">
                <li v-for="link in users.links" :key="link.label">
                    <button
                        @click.prevent="paginate(link.url)"
                        :disabled="!link.url"
                        :class="{'px-4 py-2 border-2 border-copy transition relative -m-0.5 font-bold': true, 'bg-primary text-primary-content shadow-md': link.active, 'bg-foreground hover:bg-secondary-light disabled:opacity-50 disabled:cursor-not-allowed': !link.active}"
                        v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')"
                        :aria-label="link.label"
                    >
                    </button>
                </li>
            </ol>
        </div>
    </AdminLayout>
</template>
