<script setup lang="ts">
import { useAdmin } from '@/composables/useAdmin';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingCart } from 'lucide-vue-next';

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

defineProps<{
    carts: CartsPaginated;
}>();

const { paginate } = useAdmin();

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

const getStatus = (cart: Cart) => {
    if (cart.user_id) return { label: 'Registered', cls: 'adm-badge--on' };
    if (cart.session_id) return { label: 'Guest', cls: 'adm-badge--warn' };
    return { label: 'Unknown', cls: 'adm-badge--off' };
};

const isExpired = (expiresAt: string | null): boolean => {
    if (!expiresAt) return false;
    return new Date(expiresAt) < new Date();
};
</script>

<template>
    <AdminLayout>
        <Head title="Carts : Admin" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Carts</h1>
                <p class="adm-sub">
                    Active and abandoned baskets across the store
                </p>
            </div>
        </div>

        <!-- Table card -->
        <div class="adm-card adm-card--flush" style="margin-bottom: 1.5rem">
            <!-- Desktop table -->
            <div v-if="carts.data.length" class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr class="adm-thead">
                            <th class="adm-th">Type</th>
                            <th class="adm-th">Customer / Session</th>
                            <th class="adm-th">Items</th>
                            <th class="adm-th">Last Activity</th>
                            <th class="adm-th">Expires</th>
                            <th class="adm-th adm-th--right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="cart in carts.data"
                            :key="cart.id"
                            class="adm-row"
                        >
                            <td class="adm-td">
                                <span
                                    class="adm-badge"
                                    :class="getStatus(cart).cls"
                                    >{{ getStatus(cart).label }}</span
                                >
                            </td>
                            <td class="adm-td">
                                <span v-if="cart.user">
                                    <Link
                                        :href="
                                            route(
                                                'admin.users.show',
                                                cart.user.id,
                                            )
                                        "
                                        class="ci-name-link"
                                        >{{ cart.user.name }}</Link
                                    >
                                    <p
                                        class="adm-sub"
                                        style="margin-top: 0.1rem"
                                    >
                                        {{ cart.user.email }}
                                    </p>
                                </span>
                                <span
                                    v-else
                                    class="adm-td--mono"
                                    style="word-break: break-all"
                                >
                                    {{
                                        cart.session_id
                                            ? cart.session_id
                                            : 'No session'
                                    }}
                                </span>
                            </td>
                            <td class="adm-td" style="font-weight: 600">
                                {{ cart.items_count }}
                            </td>
                            <td
                                class="adm-td"
                                style="color: var(--adm-ink-dim)"
                            >
                                {{ formatDate(cart.updated_at) }}
                            </td>
                            <td
                                class="adm-td"
                                :style="
                                    isExpired(cart.expires_at)
                                        ? 'color:var(--adm-danger)'
                                        : 'color:var(--adm-ink-dim)'
                                "
                            >
                                {{ formatDate(cart.expires_at) }}
                            </td>
                            <td class="adm-td adm-td--actions">
                                <Link
                                    :href="route('admin.carts.show', cart.id)"
                                    class="adm-action adm-action--edit"
                                >
                                    View</Link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div v-if="carts.data.length" class="adm-mob-list">
                <Link
                    v-for="cart in carts.data"
                    :key="cart.id"
                    :href="route('admin.carts.show', cart.id)"
                    class="adm-mob-card"
                    style="text-decoration: none"
                >
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                        "
                    >
                        <span class="adm-badge" :class="getStatus(cart).cls">{{
                            getStatus(cart).label
                        }}</span>
                        <div style="text-align: right">
                            <p class="adm-label--sm">Items</p>
                            <p style="font-weight: 600; color: var(--adm-ink)">
                                {{ cart.items_count }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <p class="adm-label--sm">Customer / Session</p>
                        <p v-if="cart.user" style="color: var(--adm-ink)">
                            {{ cart.user.name }}
                        </p>
                        <p
                            v-else
                            class="adm-td--mono"
                            style="word-break: break-all"
                        >
                            {{ cart.session_id || 'No session' }}
                        </p>
                    </div>
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                        "
                    >
                        <div>
                            <p class="adm-label--sm">Last Activity</p>
                            <p style="color: var(--adm-ink)">
                                {{ formatDate(cart.updated_at) }}
                            </p>
                        </div>
                        <div style="text-align: right">
                            <p class="adm-label--sm">Expires</p>
                            <p
                                :style="
                                    isExpired(cart.expires_at)
                                        ? 'color:var(--adm-danger)'
                                        : 'color:var(--adm-ink)'
                                "
                            >
                                {{ formatDate(cart.expires_at) }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="!carts.data.length" class="adm-empty">
                <div class="adm-empty-icon">
                    <ShoppingCart :size="28" :stroke-width="1.5" />
                </div>
                <p class="adm-empty-title">No active carts</p>
                <p class="adm-empty-sub">
                    Carts started by shoppers will appear here.
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="carts.last_page > 1" class="adm-pagination">
            <div class="adm-page-btns">
                <button
                    v-for="link in carts.links"
                    :key="link.label"
                    @click.prevent="paginate(link.url)"
                    :disabled="!link.url"
                    class="adm-page-btn"
                    :class="{ 'adm-page-btn--active': link.active }"
                    v-html="
                        link.label
                            .replace('&laquo; Previous', '←')
                            .replace('Next &raquo;', '→')
                    "
                ></button>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.ci-name-link {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--adm-ink);
    text-decoration: none;
    transition: color 0.15s;
}

.ci-name-link:hover {
    color: var(--adm-stamp-deep);
    text-decoration: underline;
}
</style>
