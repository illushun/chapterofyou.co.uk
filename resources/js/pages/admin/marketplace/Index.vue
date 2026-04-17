<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

interface Connection {
    shop_name: string | null;
    shop_id: string | null;
    scopes: string | null;
    expires_at: string | null;
}

interface Stats {
    listings_count: number;
    orders_count: number;
    last_import: string | null;
}

const props = defineProps<{
    connection: Connection | null;
    stats: Stats | null;
}>();

const page = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const formatDate = (iso: string | null) =>
    iso ? new Date(iso).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }) : 'Never';

const disconnect = () => {
    if (confirm('Disconnect your Etsy shop? Existing imported orders will remain.')) {
        router.post(route('admin.marketplace.etsy.disconnect'));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Marketplaces — Admin" />

        <div class="adm-header">
            <div>
                <h1 class="adm-title">Marketplaces</h1>
                <p class="adm-sub">Connect external platforms to sync products and import orders</p>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="flash.success" class="adm-flash adm-flash--success" style="margin-bottom:1.25rem">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="adm-flash adm-flash--error" style="margin-bottom:1.25rem">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            {{ flash.error }}
        </div>

        <!-- Etsy card -->
        <div class="adm-card mkp-etsy-card">

            <!-- Card header -->
            <div class="mkp-card-head">
                <div class="mkp-logo">e</div>
                <div class="mkp-card-meta">
                    <h2 class="mkp-card-title">Etsy</h2>
                    <p class="mkp-card-desc">Export products &amp; import orders from your Etsy shop</p>
                </div>
                <span v-if="connection" class="adm-badge adm-badge--on mkp-status-badge">Connected</span>
                <span v-else class="adm-badge adm-badge--off mkp-status-badge">Not connected</span>
            </div>

            <!-- Connected state -->
            <template v-if="connection">

                <!-- Stats -->
                <div class="adm-stats mkp-stats">
                    <div class="adm-stat adm-stat--lav">
                        <div class="adm-stat-val">{{ stats?.listings_count ?? 0 }}</div>
                        <div class="adm-stat-label">Listings</div>
                    </div>
                    <div class="adm-stat adm-stat--sage">
                        <div class="adm-stat-val">{{ stats?.orders_count ?? 0 }}</div>
                        <div class="adm-stat-label">Orders imported</div>
                    </div>
                    <div class="adm-stat">
                        <div class="adm-stat-val mkp-import-date">{{ formatDate(stats?.last_import ?? null) }}</div>
                        <div class="adm-stat-label">Last import</div>
                    </div>
                </div>

                <p class="mkp-shop-line">
                    <span class="mkp-shop-label">Shop</span>
                    {{ connection.shop_name ?? 'Unknown' }}
                    <span v-if="connection.shop_id" class="mkp-shop-id">#{{ connection.shop_id }}</span>
                </p>

                <div class="mkp-actions">
                    <Link :href="route('admin.marketplace.etsy.products')" class="adm-btn adm-btn--primary">
                        Manage Products
                    </Link>
                    <Link :href="route('admin.marketplace.etsy.orders')" class="adm-btn adm-btn--ghost">
                        View Orders
                    </Link>
                    <button @click="disconnect" class="adm-btn adm-btn--danger mkp-disconnect-btn">
                        Disconnect
                    </button>
                </div>
            </template>

            <!-- Disconnected state -->
            <template v-else>
                <p class="mkp-connect-desc">
                    Connect your Etsy shop to export products as draft listings and automatically import
                    new orders. You'll be redirected to Etsy to authorise access.
                </p>
                <Link :href="route('admin.marketplace.etsy.connect')" class="adm-btn mkp-connect-btn">
                    Connect Etsy Shop
                </Link>
            </template>

        </div>

    </AdminLayout>
</template>

<style scoped>
.mkp-etsy-card { max-width: 640px; }

.mkp-card-head {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.mkp-logo {
    width: 44px;
    height: 44px;
    border-radius: var(--bb-radius-md);
    background: #f56400;
    color: #fff;
    font-size: 1.4rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-style: italic;
}

.mkp-card-meta { flex: 1; min-width: 0; }
.mkp-card-title { font-size: 1rem; font-weight: 700; color: var(--bb-text); }
.mkp-card-desc  { font-size: 0.8rem; color: var(--bb-muted); margin-top: 0.1rem; }
.mkp-status-badge { flex-shrink: 0; }

.mkp-stats {
    grid-template-columns: repeat(3, 1fr);
    margin-bottom: 1rem;
}

.mkp-import-date { font-size: 0.85rem; letter-spacing: 0; }

.mkp-shop-line {
    font-size: 0.85rem;
    color: var(--bb-text);
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.mkp-shop-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--bb-muted);
}
.mkp-shop-id { font-size: 0.78rem; color: var(--bb-muted); }

.mkp-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    align-items: center;
}

.mkp-disconnect-btn { margin-left: auto; }

.mkp-connect-desc {
    font-size: 0.875rem;
    color: var(--bb-muted);
    line-height: 1.55;
    margin-bottom: 1.25rem;
    max-width: 480px;
}

.mkp-connect-btn {
    background: #f56400;
    color: #fff;
    font-weight: 700;
}
.mkp-connect-btn:hover:not(:disabled) { opacity: 0.88; }
</style>
