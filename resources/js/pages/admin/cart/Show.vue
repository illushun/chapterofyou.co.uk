<script setup lang="ts">
import { useAdmin } from '@/composables/useAdmin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
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
    cost: number;
}

interface CartItem {
    id: number;
    product_id: number;
    product: Product;
    quantity: number;
}

interface Cart {
    id: number;
    user_id: number | null;
    user: User | null;
    session_id: string | null;
    expires_at: string | null;
    updated_at: string;
    items: CartItem[];
}

const props = defineProps<{
    cart: Cart;
}>();

const { fmtCurrency } = useAdmin();

const formatDate = (dateString: string | null): string => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const subtotal = computed(() => {
    return props.cart.items.reduce(
        (sum, item) => sum + item.product.cost * item.quantity,
        0,
    );
});

const cartType = computed(() =>
    props.cart.user_id ? 'Registered user cart' : 'Guest cart',
);
</script>

<template>
    <AdminLayout>
        <Head :title="`Cart #${cart.id} : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.carts.index')"
                        class="adm-breadcrumb a"
                        >Carts</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>#{{ cart.id }}</span>
                </div>
                <h1 class="adm-title">Cart #{{ cart.id }}</h1>
                <p class="adm-sub">
                    Last updated {{ formatDate(cart.updated_at) }}
                </p>
            </div>
        </div>

        <div class="adm-form-grid">
            <!-- Left column -->
            <div class="adm-form-left">
                <section class="adm-card adm-card--flush">
                    <div style="padding: 1.5rem 1.5rem 0.85rem">
                        <h2
                            class="adm-card-title"
                            style="border: none; padding: 0; margin: 0"
                        >
                            Cart contents ({{ cart.items.length }} unique
                            product{{ cart.items.length !== 1 ? 's' : '' }})
                        </h2>
                    </div>
                    <div v-if="cart.items.length">
                        <div
                            v-for="item in cart.items"
                            :key="item.id"
                            class="adm-row"
                            style="
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                padding: 0.85rem 1.5rem;
                            "
                        >
                            <div>
                                <p
                                    style="
                                        font-weight: 600;
                                        color: var(--adm-ink);
                                    "
                                >
                                    {{ item.product.name }}
                                </p>
                                <p
                                    class="adm-td--mono"
                                    style="margin-top: 0.15rem"
                                >
                                    {{ item.product.mpn }} &middot;
                                    {{ fmtCurrency(item.product.cost) }} each
                                </p>
                            </div>
                            <div style="text-align: right">
                                <p
                                    class="adm-td--price"
                                    style="font-size: 1.05rem"
                                >
                                    {{
                                        fmtCurrency(
                                            item.product.cost * item.quantity,
                                        )
                                    }}
                                </p>
                                <p class="adm-sub">Qty: {{ item.quantity }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="adm-empty-sub" style="padding: 1.5rem">
                        This cart is currently empty.
                    </p>
                </section>
            </div>

            <!-- Right column -->
            <div class="adm-form-right">
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Cart Status</h2>

                    <div class="adm-field">
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            "
                        >
                            <span class="adm-sub">Type</span>
                            <span
                                style="font-weight: 600; color: var(--adm-ink)"
                                >{{ cartType }}</span
                            >
                        </div>
                    </div>
                    <div class="adm-field">
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            "
                        >
                            <span class="adm-sub">Total Value</span>
                            <span
                                class="adm-stat-val"
                                style="font-size: 1.4rem"
                                >{{ fmtCurrency(subtotal) }}</span
                            >
                        </div>
                    </div>
                    <div
                        class="adm-field"
                        style="
                            border-top: 1px dashed var(--adm-line);
                            padding-top: 0.85rem;
                        "
                    >
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            "
                        >
                            <span class="adm-sub">Expires</span>
                            <span
                                style="font-weight: 600; color: var(--adm-ink)"
                                >{{ formatDate(cart.expires_at) }}</span
                            >
                        </div>
                    </div>

                    <div
                        class="adm-field"
                        style="
                            border-top: 1px dashed var(--adm-line);
                            padding-top: 0.85rem;
                        "
                    >
                        <template v-if="cart.user">
                            <p class="adm-label--sm">Associated user</p>
                            <Link
                                :href="route('admin.users.show', cart.user_id!)"
                                class="or-name-link"
                            >
                                {{ cart.user.name }} ({{ cart.user.email }})
                            </Link>
                        </template>
                        <template v-else>
                            <p class="adm-label--sm">Session ID</p>
                            <p
                                class="adm-td--mono"
                                style="word-break: break-all"
                            >
                                {{ cart.session_id || '-' }}
                            </p>
                        </template>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.or-name-link {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--adm-stamp-deep);
    text-decoration: none;
}

.or-name-link:hover {
    text-decoration: underline;
}
</style>
