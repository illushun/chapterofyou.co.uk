<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Cart {
    id: number;
    user_id: number | null;
    user: User | null;
    session_id: string | null;
    items_count: number;
    expires_at: string | null;
    updated_at: string;
}

interface CartsPaginated {
    data: Cart[];
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
}

const props = defineProps<{
    carts: CartsPaginated;
}>();

const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'N/A';
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

const getStatus = (cart: Cart) => {
    if (cart.user_id) return { label: 'Registered', color: 'bg-green-500/20 text-green-700 border border-green-700' };
    if (cart.session_id) return { label: 'Guest', color: 'bg-yellow-500/20 text-yellow-700 border border-yellow-700' };
    return { label: 'Unknown', color: 'bg-gray-500/20 text-gray-700 border border-gray-700' };
}

const isExpired = (expiresAt: string | null): boolean => {
    if (!expiresAt) return false;
    return new Date(expiresAt) < new Date();
}
</script>

<template>
    <AdminLayout>

        <Head title="Active Carts" />

        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Active Carts</h2>
        </div>

        <div v-if="carts.data.length" class="rounded-lg border-2 border-copy bg-[var(--primary-content)]">

            <!--
                DESKTOP TABLE VIEW
                (Hidden below 'md' breakpoint, uses full table structure)
            -->
            <div class="hidden md:block relative rounded-lg -m-0.5 border-2 border-copy bg-foreground overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-copy-light/50">
                    <thead>
                        <tr class="text-left bg-secondary-light font-bold text-copy uppercase border-b-2 border-copy">
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Customer/Session</th>
                            <th class="px-4 py-3">Items</th>
                            <th class="px-4 py-3">Last Activity</th>
                            <th class="px-4 py-3">Expires</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-copy-light/50">
                        <tr v-for="cart in carts.data" :key="cart.id" class="hover:bg-secondary-light transition">
                            <td class="px-4 py-3">
                                <span
                                    :class="['px-2 py-0.5 rounded-full text-xs font-semibold uppercase', getStatus(cart).color]">
                                    {{ getStatus(cart).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="cart.user">
                                    <Link :href="route('admin.users.show', cart.user.id)" class="hover:underline">{{
                                        cart.user.name }}</Link><br>
                                    <span class="text-xs text-copy-light">{{ cart.user.email }}</span>
                                </span>
                                <span v-else class="text-copy-light text-xs break-all">{{ cart.session_id ? `Session ID:
                                    ${cart.session_id}` : 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold">{{ cart.items_count }}</td>
                            <td class="px-4 py-3 text-copy-light">{{ formatDate(cart.updated_at) }}</td>
                            <td class="px-4 py-3 text-copy-light" :class="{ 'text-error': isExpired(cart.expires_at) }">
                                {{ formatDate(cart.expires_at) }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <Link :href="route('admin.carts.show', cart.id)"
                                    class="text-blue-500 hover:text-blue-700 transition font-semibold">
                                View
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
                <div v-for="cart in carts.data" :key="cart.id"
                    class="p-4 bg-foreground hover:bg-secondary-light transition">

                    <!-- Header: Cart Type and Item Count -->
                    <div class="flex justify-between items-start border-b border-copy-light/30 pb-2 mb-2">
                        <div>
                            <span
                                :class="['px-2 py-0.5 rounded-full text-xs font-semibold uppercase', getStatus(cart).color]">
                                {{ getStatus(cart).label }} Cart
                            </span>
                            <div class="text-xs text-copy-light uppercase font-medium mt-1">Cart ID: {{ cart.id }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-copy-light uppercase font-medium">Items</div>
                            <div class="text-lg font-bold text-copy">{{ cart.items_count }}</div>
                        </div>
                    </div>

                    <!-- Customer/Session Info -->
                    <div class="py-2 border-b border-copy-light/30">
                        <div class="text-xs text-copy-light uppercase font-medium">Customer/Session</div>
                        <div v-if="cart.user" class="mt-1">
                            <Link :href="route('admin.users.show', cart.user.id)"
                                class="font-semibold text-copy hover:underline">{{ cart.user.name }}</Link>
                            <div class="text-sm text-copy-light">{{ cart.user.email }}</div>
                        </div>
                        <div v-else class="text-copy-light text-sm break-all mt-1">
                            {{ cart.session_id ? `Session: ${cart.session_id}` : 'No Session ID' }}
                        </div>
                    </div>

                    <!-- Activity and Expiry -->
                    <div class="flex justify-between items-end pt-3">
                        <!-- Activity -->
                        <div>
                            <div class="text-xs text-copy-light uppercase font-medium">Last Activity</div>
                            <div class="text-sm font-semibold text-copy">{{ formatDate(cart.updated_at) }}</div>
                            <div class="text-xs text-copy-light uppercase font-medium mt-2">Expires</div>
                            <div class="text-sm font-semibold text-copy"
                                :class="{ 'text-error': isExpired(cart.expires_at) }">
                                {{ formatDate(cart.expires_at) }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0">
                            <Link :href="route('admin.carts.show', cart.id)"
                                class="px-3 py-1 text-sm font-semibold transition border-2 border-copy bg-primary text-primary-content hover:bg-primary-dark rounded-lg shadow-md">
                            View Contents
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div v-else class="text-center p-12 border-4 border-dashed border-copy-light rounded-2xl bg-secondary-light/50">
            <p class="text-xl font-semibold text-copy mb-2">No active carts found.</p>
        </div>

        <div v-if="carts.last_page > 1" class="mt-6 flex justify-center">
            <ol class="flex gap-2 text-sm font-medium">
                <li v-for="link in carts.links" :key="link.label">
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
