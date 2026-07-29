<script setup lang="ts">
import { useAdmin } from '@/composables/useAdmin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Address {
    id: number;
    type: 'shipping' | 'billing';
    is_default: boolean;
    line_1: string;
    line_2: string | null;
    city: string;
    postcode: string;
}

interface Order {
    id: number;
    grand_total: number;
    status: string;
    created_at: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
    addresses: Address[];
    orders: Order[];
}

const props = defineProps<{
    user: User;
}>();

const { fmtCurrency } = useAdmin();

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });

const defaultShippingAddress = computed(() =>
    props.user.addresses.find((a) => a.type === 'shipping' && a.is_default),
);
const defaultBillingAddress = computed(() =>
    props.user.addresses.find((a) => a.type === 'billing' && a.is_default),
);
</script>

<template>
    <AdminLayout>
        <Head :title="`${user.name} : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.users.index')"
                        class="adm-breadcrumb a"
                        >Users</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>#{{ user.id }}</span>
                </div>
                <h1 class="adm-title">{{ user.name }}</h1>
                <p class="adm-sub">Joined {{ formatDate(user.created_at) }}</p>
            </div>
            <span
                class="adm-badge"
                :class="user.is_admin ? 'adm-badge--blush' : 'adm-badge--off'"
            >
                {{ user.is_admin ? 'Admin' : 'Customer' }}
            </span>
        </div>

        <div class="adm-form-grid">
            <!-- Left column -->
            <div class="adm-form-left">
                <section class="adm-card">
                    <h2 class="adm-card-title">Account Details</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <p class="adm-label--sm">Email</p>
                            <a
                                :href="`mailto:${user.email}`"
                                style="color: var(--adm-stamp-deep)"
                                >{{ user.email }}</a
                            >
                        </div>
                        <div class="adm-field">
                            <p class="adm-label--sm">User ID</p>
                            <p class="adm-td--mono">#{{ user.id }}</p>
                        </div>
                    </div>
                </section>

                <section class="adm-card">
                    <h2 class="adm-card-title">Default Addresses</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <p class="adm-label--sm">Shipping</p>
                            <template v-if="defaultShippingAddress">
                                <p style="color: var(--adm-ink)">
                                    {{ defaultShippingAddress.line_1 }}
                                </p>
                                <p
                                    v-if="defaultShippingAddress.line_2"
                                    style="color: var(--adm-ink)"
                                >
                                    {{ defaultShippingAddress.line_2 }}
                                </p>
                                <p style="color: var(--adm-ink)">
                                    {{ defaultShippingAddress.city }}
                                </p>
                                <p style="color: var(--adm-ink)">
                                    {{ defaultShippingAddress.postcode }}
                                </p>
                            </template>
                            <p v-else class="adm-empty-sub">
                                No default shipping address.
                            </p>
                        </div>
                        <div class="adm-field">
                            <p class="adm-label--sm">Billing</p>
                            <template v-if="defaultBillingAddress">
                                <p style="color: var(--adm-ink)">
                                    {{ defaultBillingAddress.line_1 }}
                                </p>
                                <p
                                    v-if="defaultBillingAddress.line_2"
                                    style="color: var(--adm-ink)"
                                >
                                    {{ defaultBillingAddress.line_2 }}
                                </p>
                                <p style="color: var(--adm-ink)">
                                    {{ defaultBillingAddress.city }}
                                </p>
                                <p style="color: var(--adm-ink)">
                                    {{ defaultBillingAddress.postcode }}
                                </p>
                            </template>
                            <p v-else class="adm-empty-sub">
                                No default billing address.
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right column -->
            <div class="adm-form-right">
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Recent Orders</h2>

                    <div v-if="user.orders.length">
                        <div
                            v-for="order in user.orders"
                            :key="order.id"
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                padding: 0.6rem 0;
                                border-bottom: 1px solid var(--adm-line);
                            "
                        >
                            <Link
                                :href="route('admin.orders.show', order.id)"
                                class="adm-td--mono"
                                style="
                                    font-weight: 600;
                                    color: var(--adm-stamp-deep);
                                "
                            >
                                #{{ order.id }}
                            </Link>
                            <div style="text-align: right">
                                <p
                                    class="adm-td--price"
                                    style="color: var(--adm-ink)"
                                >
                                    {{ fmtCurrency(order.grand_total) }}
                                </p>
                                <p class="adm-sub">{{ order.status }}</p>
                            </div>
                        </div>
                        <Link
                            :href="route('admin.orders.index')"
                            class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--full"
                            style="margin-top: 0.75rem"
                        >
                            View All Orders
                        </Link>
                    </div>
                    <p v-else class="adm-empty-sub">No recent orders.</p>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
