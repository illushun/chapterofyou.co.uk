<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// ── Types ──────────────────────────────────────────────────────────────────
interface TrendPoint { date: string; label: string; revenue: number; orders: number; }
interface TopProduct { id: number; name: string; mpn: string; units_sold: number; revenue: number; }
interface RecentOrder { id: number; name: string; total: number; status: string; created_at: string; }
interface TopPost { id: number; title: string; slug: string; views: number; published_at: string; }

interface Stats {
    products: {
        total: number; in_stock: number; out_of_stock: number;
        disabled: number; low_stock: number;
    };
    current_period: { orders: number; revenue: number; avg_order: number; };
    previous_period: { orders: number; revenue: number; };
    pct_change: { orders: number; revenue: number; };
    trend: TrendPoint[];
    status_breakdown: Record<string, number>;
    top_products: TopProduct[];
    recent_orders: RecentOrder[];
    customers: { total: number; new_30: number; new_7: number; returning_30: number; };
    vouchers: {
        orders_with_voucher: number;
        total_discount_given: number;
        gift_vouchers_sold: number;
        gift_vouchers_pending: number;
    };
    cart_abandonment: {
        active_carts: number;
        completed_orders: number;
        abandonment_rate: number;
    };
    reviews: {
        pending_approval: number;
        average_rating: number;
        total_approved: number;
    };
    journal: {
        total_posts: number;
        total_views: number;
        top_posts: TopPost[];
    };
}

const props = defineProps<{ stats: Stats }>();

// ── Helpers ────────────────────────────────────────────────────────────────
const n = (v: unknown): number => Number(v) || 0;
const fmt = (v: unknown) => `£${n(v).toFixed(2)}`;
const fmtK = (v: unknown) => n(v) >= 1000 ? `£${(n(v) / 1000).toFixed(1)}k` : fmt(v);
const fmtNum = (v: unknown) => n(v).toLocaleString();

const pctArrow = (v: unknown) => n(v) >= 0 ? '▲' : '▼';
const pctColour = (v: unknown) => n(v) >= 0 ? 'var(--bb-sage-d)' : 'var(--bb-red)';

// ── Sparkline ──────────────────────────────────────────────────────────────
function sparklinePath(points: number[], w = 200, h = 40): string {
    if (points.length < 2) return '';
    const max = Math.max(...points, 1);
    const step = w / (points.length - 1);
    return points
        .map((v, i) => `${i === 0 ? 'M' : 'L'} ${(i * step).toFixed(1)} ${(h - (v / max) * h).toFixed(1)}`)
        .join(' ');
}
const revenueLine = computed(() =>
    sparklinePath(props.stats.trend.map(t => n(t.revenue)))
);

// ── Bar chart ──────────────────────────────────────────────────────────────
const barChart = computed(() => {
    const slice = props.stats.trend.slice(-14);
    const maxVal = Math.max(...slice.map(d => n(d.revenue)), 1);
    return slice.map(d => ({
        label: d.label,
        revenue: n(d.revenue),
        orders: n(d.orders),
        height: Math.round((n(d.revenue) / maxVal) * 100),
    }));
});

// ── Donut ──────────────────────────────────────────────────────────────────
const donutSegments = computed(() => {
    const breakdown = props.stats.status_breakdown;
    const total = Object.values(breakdown).reduce((a, b) => n(a) + n(b), 0) || 1;
    const colours: Record<string, string> = {
        successful: '#4caf7d', shipped: '#2d9e6a', processing: '#9b84d4',
        pending: '#f5c842', cancelled: '#e05c6e', failed: '#e07a5c',
    };
    let offset = 0;
    return Object.entries(breakdown).map(([status, count]) => {
        const pct = (n(count) / total) * 100;
        const seg = { status, count: n(count), pct: Math.round(pct), offset: Math.round(offset), colour: colours[status] ?? '#9ca3af' };
        offset += pct;
        return seg;
    });
});

const totalOrders = computed(() =>
    Object.values(props.stats.status_breakdown).reduce((a, b) => n(a) + n(b), 0)
);

// ── Retention ──────────────────────────────────────────────────────────────
const returnRate = computed(() => {
    const total = n(props.stats.customers.new_30) + n(props.stats.customers.returning_30);
    if (!total) return 0;
    return Math.round((n(props.stats.customers.returning_30) / total) * 100);
});
</script>

<template>
    <AdminLayout>

        <Head title="Dashboard" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <h1 class="adm-title">Dashboard</h1>
                <p class="adm-sub">Overview of the last 30 days</p>
            </div>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <Link :href="route('admin.orders.index')" class="adm-btn adm-btn--primary adm-btn--sm">
                View Orders
                </Link>
                <Link :href="route('admin.products.index')" class="adm-btn adm-btn--ghost adm-btn--sm">
                Products
                </Link>
            </div>
        </div>

        <!-- ── KPI row ── -->
        <div class="db-kpi-grid">

            <!-- Revenue -->
            <div class="adm-card db-kpi-card">
                <div class="db-kpi-top">
                    <div class="db-kpi-icon db-kpi-icon--lav">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <span class="db-kpi-pct" :style="{ color: pctColour(stats.pct_change.revenue) }">
                        {{ pctArrow(stats.pct_change.revenue) }} {{ Math.abs(n(stats.pct_change.revenue)) }}%
                    </span>
                </div>
                <p class="db-kpi-val">{{ fmtK(stats.current_period.revenue) }}</p>
                <p class="db-kpi-label">Revenue</p>
                <svg viewBox="0 0 200 40" class="db-sparkline" fill="none" preserveAspectRatio="none">
                    <path :d="revenueLine" stroke="var(--bb-lav-d)" stroke-width="1.5" stroke-linejoin="round"
                        fill="none" />
                </svg>
            </div>

            <!-- Orders -->
            <div class="adm-card db-kpi-card">
                <div class="db-kpi-top">
                    <div class="db-kpi-icon db-kpi-icon--blush">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <path d="M16 10a4 4 0 0 1-8 0" />
                        </svg>
                    </div>
                    <span class="db-kpi-pct" :style="{ color: pctColour(stats.pct_change.orders) }">
                        {{ pctArrow(stats.pct_change.orders) }} {{ Math.abs(n(stats.pct_change.orders)) }}%
                    </span>
                </div>
                <p class="db-kpi-val">{{ stats.current_period.orders }}</p>
                <p class="db-kpi-label">Orders</p>
                <p class="db-kpi-sub">Avg {{ fmt(stats.current_period.avg_order) }} per order</p>
            </div>

            <!-- Cart abandonment -->
            <div class="adm-card db-kpi-card">
                <div class="db-kpi-top">
                    <div class="db-kpi-icon db-kpi-icon--peach">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                    </div>
                    <span class="db-kpi-badge" :style="n(stats.cart_abandonment.abandonment_rate) > 70
                        ? 'background:var(--bb-red-bg);color:var(--bb-red)'
                        : 'background:var(--bb-green-bg);color:var(--bb-sage-d)'">
                        {{ n(stats.cart_abandonment.abandonment_rate) }}% rate
                    </span>
                </div>
                <p class="db-kpi-val">{{ stats.cart_abandonment.active_carts }}</p>
                <p class="db-kpi-label">Abandoned Carts</p>
                <p class="db-kpi-sub">{{ stats.cart_abandonment.completed_orders }} carts converted to orders</p>
            </div>

            <!-- New customers -->
            <div class="adm-card db-kpi-card">
                <div class="db-kpi-top">
                    <div class="db-kpi-icon db-kpi-icon--sage">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                </div>
                <p class="db-kpi-val">{{ stats.customers.new_30 }}</p>
                <p class="db-kpi-label">New Customers</p>
                <p class="db-kpi-sub">{{ stats.customers.new_7 }} this week · {{ stats.customers.total }} total</p>
            </div>

        </div>

        <!-- ── Revenue chart + Order status ── -->
        <div class="db-charts-grid">

            <!-- Bar chart -->
            <div class="adm-card adm-card--flush">
                <div class="db-card-header">
                    <h3 class="adm-card-title" style="border:none; padding:0; margin:0;">Revenue — Last 14 Days</h3>
                </div>
                <div class="db-bar-chart">
                    <div v-for="bar in barChart" :key="bar.label" class="db-bar-col"
                        :title="`${bar.label}: ${fmt(bar.revenue)} (${bar.orders} orders)`">
                        <div class="db-bar-tooltip">
                            <strong>{{ bar.label }}</strong><br />
                            {{ fmt(bar.revenue) }}<br />
                            {{ bar.orders }} order{{ bar.orders !== 1 ? 's' : '' }}
                        </div>
                        <div class="db-bar" :style="{
                            height: (bar.height || 2) + '%',
                            background: bar.revenue > 0 ? 'var(--bb-lav-d)' : 'var(--bb-border)',
                            opacity: bar.revenue > 0 ? 1 : 0.4,
                        }">
                        </div>
                        <span class="db-bar-label">{{ bar.label.split(' ')[0] }}</span>
                    </div>
                    <p v-if="!barChart.some(b => b.revenue > 0)" class="db-empty-note">No revenue data yet.</p>
                </div>
            </div>

            <!-- Donut -->
            <div class="adm-card">
                <h3 class="adm-card-title">Orders by Status</h3>
                <div class="db-donut-wrap">
                    <svg viewBox="0 0 42 42" class="db-donut-svg">
                        <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--bb-border)" stroke-width="5" />
                        <circle v-for="seg in donutSegments" :key="seg.status" cx="21" cy="21" r="15.915" fill="none"
                            :stroke="seg.colour" stroke-width="5" :stroke-dasharray="`${seg.pct} ${100 - seg.pct}`"
                            :stroke-dashoffset="100 - seg.offset"
                            style="transform:rotate(-90deg); transform-origin:50% 50%;" />
                        <text x="21" y="19.5" text-anchor="middle" dominant-baseline="middle"
                            style="font-size:5.5px; font-weight:700; fill:var(--bb-text);">
                            {{ totalOrders }}
                        </text>
                        <text x="21" y="24.5" text-anchor="middle" dominant-baseline="middle"
                            style="font-size:3px; fill:var(--bb-muted);">orders</text>
                    </svg>
                </div>
                <div class="db-donut-legend">
                    <div v-for="seg in donutSegments" :key="seg.status" class="db-legend-row">
                        <div class="db-legend-left">
                            <span class="db-legend-dot" :style="{ background: seg.colour }"></span>
                            <span class="db-legend-label">{{ seg.status }}</span>
                        </div>
                        <span class="db-legend-val">{{ seg.count }}</span>
                    </div>
                    <p v-if="!donutSegments.length" class="db-empty-note">No orders yet.</p>
                </div>
            </div>

        </div>

        <!-- ── Marketing & insights row ── -->
        <div class="db-insights-grid">

            <!-- Customer retention -->
            <div class="adm-card">
                <h3 class="adm-card-title">Customer Insights</h3>
                <div class="db-retention-wrap">
                    <!-- Returning vs new pill chart -->
                    <div class="db-retention-bar">
                        <div class="db-retention-seg db-retention-seg--new" :style="{ width: (100 - returnRate) + '%' }"
                            :title="`New: ${stats.customers.new_30}`"></div>
                        <div class="db-retention-seg db-retention-seg--return" :style="{ width: returnRate + '%' }"
                            :title="`Returning: ${stats.customers.returning_30}`"></div>
                    </div>
                    <div class="db-retention-legend">
                        <div class="db-ret-item">
                            <span class="db-ret-dot db-ret-dot--new"></span>
                            <span>New (30d)</span>
                            <strong>{{ stats.customers.new_30 }}</strong>
                        </div>
                        <div class="db-ret-item">
                            <span class="db-ret-dot db-ret-dot--return"></span>
                            <span>Returning</span>
                            <strong>{{ stats.customers.returning_30 }}</strong>
                        </div>
                    </div>
                    <div class="db-retention-rate">
                        <span class="db-rate-num">{{ returnRate }}%</span>
                        <span class="db-rate-label">repeat purchase rate</span>
                    </div>
                </div>
            </div>

            <!-- Voucher stats -->
            <div class="adm-card">
                <h3 class="adm-card-title">Vouchers &amp; Gift Cards</h3>
                <div class="db-stat-list">
                    <div class="db-stat-row">
                        <span class="db-stat-lbl">Orders with discount code</span>
                        <span class="db-stat-val adm-badge--lav">{{ stats.vouchers.orders_with_voucher }}</span>
                    </div>
                    <div class="db-stat-row">
                        <span class="db-stat-lbl">Total discount given</span>
                        <span class="db-stat-val" style="color:var(--bb-sage-d); font-weight:700;">{{
                            fmt(stats.vouchers.total_discount_given) }}</span>
                    </div>
                    <div class="db-stat-row">
                        <span class="db-stat-lbl">Gift vouchers sold (30d)</span>
                        <span class="db-stat-val adm-badge--blush">{{ stats.vouchers.gift_vouchers_sold }}</span>
                    </div>
                    <div class="db-stat-row">
                        <span class="db-stat-lbl">Gift vouchers awaiting fulfilment</span>
                        <span class="db-stat-val"
                            :class="stats.vouchers.gift_vouchers_pending > 0 ? 'adm-badge--warn' : 'adm-badge--on'">
                            {{ stats.vouchers.gift_vouchers_pending }}
                        </span>
                    </div>
                </div>
                <Link v-if="stats.vouchers.gift_vouchers_pending > 0" :href="route('admin.gift-vouchers.index')"
                    class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--full" style="margin-top:0.75rem;">
                Fulfil pending gift vouchers →
                </Link>
            </div>

            <!-- Reviews -->
            <div class="adm-card">
                <h3 class="adm-card-title">Reviews</h3>
                <div class="db-reviews-centre">
                    <div class="db-avg-rating">
                        <span class="db-avg-num">{{ n(stats.reviews.average_rating).toFixed(1) }}</span>
                        <div class="db-stars">
                            <span v-for="i in 5" :key="i" class="db-star"
                                :class="i <= Math.round(n(stats.reviews.average_rating)) ? 'db-star--filled' : ''">
                                ★
                            </span>
                        </div>
                        <span class="db-avg-sub">{{ stats.reviews.total_approved }} approved reviews</span>
                    </div>
                </div>
                <div v-if="stats.reviews.pending_approval > 0" class="db-pending-reviews">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4M12 16h.01" />
                    </svg>
                    <span>{{ stats.reviews.pending_approval }} review{{ stats.reviews.pending_approval !== 1 ? 's' : ''
                        }} awaiting approval</span>
                    <Link :href="route('admin.reviews.index')" class="db-pending-link">Review →</Link>
                </div>
                <p v-else class="db-empty-note" style="margin-top:0.75rem;">All reviews are approved.</p>
            </div>

        </div>

        <!-- ── Bottom row: top products + recent orders + journal ── -->
        <div class="db-bottom-grid">

            <!-- Top products -->
            <div class="adm-card adm-card--flush">
                <div class="db-card-header">
                    <h3 class="adm-card-title" style="border:none; padding:0; margin:0;">Top Products</h3>
                    <span class="db-card-header-note">by units sold (30d)</span>
                </div>
                <div class="db-list">
                    <p v-if="!stats.top_products.length" class="db-empty-note" style="padding:1.5rem;">No sales data
                        yet.</p>
                    <div v-for="(p, i) in stats.top_products" :key="p.id" class="db-list-row">
                        <span class="db-rank" :class="`db-rank--${i + 1}`">{{ i + 1 }}</span>
                        <div class="db-list-info">
                            <p class="db-list-name">{{ p.name }}</p>
                            <p class="db-list-meta">{{ p.mpn }}</p>
                        </div>
                        <div class="db-list-right">
                            <p class="db-list-primary">{{ p.units_sold }} sold</p>
                            <p class="db-list-secondary">{{ fmt(p.revenue) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent orders -->
            <div class="adm-card adm-card--flush">
                <div class="db-card-header">
                    <h3 class="adm-card-title" style="border:none; padding:0; margin:0;">Recent Orders</h3>
                    <Link :href="route('admin.orders.index')" class="db-view-all">View all →</Link>
                </div>
                <div class="db-list">
                    <p v-if="!stats.recent_orders.length" class="db-empty-note" style="padding:1.5rem;">No orders yet.
                    </p>
                    <Link v-for="order in stats.recent_orders" :key="order.id"
                        :href="route('admin.orders.show', order.id)" class="db-list-row db-list-row--link">
                    <div class="db-list-info">
                        <p class="db-list-name">{{ order.name }}</p>
                        <p class="db-list-meta">{{ order.created_at }}</p>
                    </div>
                    <div class="db-list-right">
                        <p class="db-list-primary">{{ fmt(order.total) }}</p>
                        <span class="adm-badge" :class="{
                            'adm-badge--on': order.status === 'successful' || order.status === 'shipped',
                            'adm-badge--lav': order.status === 'processing',
                            'adm-badge--warn': order.status === 'pending',
                            'adm-badge--red': order.status === 'cancelled' || order.status === 'failed',
                        }">{{ order.status }}</span>
                    </div>
                    </Link>
                </div>
            </div>

            <!-- Journal performance -->
            <div class="adm-card adm-card--flush">
                <div class="db-card-header">
                    <h3 class="adm-card-title" style="border:none; padding:0; margin:0;">Journal</h3>
                    <Link :href="route('admin.journal.index')" class="db-view-all">Manage →</Link>
                </div>
                <div class="db-journal-summary">
                    <div class="db-journal-stat">
                        <span class="db-journal-num">{{ stats.journal.total_posts }}</span>
                        <span class="db-journal-lbl">posts</span>
                    </div>
                    <div class="db-journal-sep" aria-hidden="true"></div>
                    <div class="db-journal-stat">
                        <span class="db-journal-num">{{ fmtNum(stats.journal.total_views) }}</span>
                        <span class="db-journal-lbl">total views</span>
                    </div>
                </div>
                <div class="db-list">
                    <p v-if="!stats.journal.top_posts?.length" class="db-empty-note" style="padding:1rem;">No published
                        posts yet.</p>
                    <a v-for="post in stats.journal.top_posts" :key="post.id" :href="`/journal/${post.slug}`"
                        target="_blank" class="db-list-row db-list-row--link">
                        <div class="db-list-info">
                            <p class="db-list-name">{{ post.title }}</p>
                            <p class="db-list-meta">{{ post.published_at }}</p>
                        </div>
                        <div class="db-list-right">
                            <p class="db-list-primary">{{ fmtNum(post.views) }}</p>
                            <p class="db-list-secondary">views</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        <!-- ── Product health ── -->
        <div class="adm-card" style="margin-top:1.5rem;">
            <h3 class="adm-card-title">Product Health</h3>
            <div class="db-product-health">
                <div class="db-ph-stat">
                    <span class="db-ph-num" style="color:var(--bb-sage-d);">{{ stats.products.in_stock }}</span>
                    <span class="db-ph-lbl">In Stock</span>
                    <div class="db-ph-bar">
                        <div class="db-ph-fill" style="background:var(--bb-sage-d);"
                            :style="{ width: stats.products.total ? (stats.products.in_stock / stats.products.total * 100) + '%' : '0%' }">
                        </div>
                    </div>
                </div>
                <div class="db-ph-stat">
                    <span class="db-ph-num" style="color:var(--bb-peach-d);">{{ stats.products.out_of_stock }}</span>
                    <span class="db-ph-lbl">Out of Stock</span>
                    <div class="db-ph-bar">
                        <div class="db-ph-fill" style="background:var(--bb-peach-d);"
                            :style="{ width: stats.products.total ? (stats.products.out_of_stock / stats.products.total * 100) + '%' : '0%' }">
                        </div>
                    </div>
                </div>
                <div class="db-ph-stat">
                    <span class="db-ph-num" style="color:var(--bb-blush-d);">{{ stats.products.low_stock }}</span>
                    <span class="db-ph-lbl">Low Stock (≤5)</span>
                    <div class="db-ph-bar">
                        <div class="db-ph-fill" style="background:var(--bb-blush-d);"
                            :style="{ width: stats.products.total ? (stats.products.low_stock / stats.products.total * 100) + '%' : '0%' }">
                        </div>
                    </div>
                </div>
                <div class="db-ph-stat">
                    <span class="db-ph-num" style="color:var(--bb-muted);">{{ stats.products.disabled }}</span>
                    <span class="db-ph-lbl">Disabled</span>
                    <div class="db-ph-bar">
                        <div class="db-ph-fill" style="background:var(--bb-muted);"
                            :style="{ width: stats.products.total ? (stats.products.disabled / stats.products.total * 100) + '%' : '0%' }">
                        </div>
                    </div>
                </div>
                <div class="db-ph-total">
                    <span class="db-ph-total-num">{{ stats.products.total }}</span>
                    <span class="db-ph-total-lbl">total products</span>
                </div>
            </div>
            <div style="display:flex; gap:0.5rem; margin-top:1rem; flex-wrap:wrap;">
                <Link :href="route('admin.products.index')" class="adm-btn adm-btn--ghost adm-btn--sm">
                Manage Products
                </Link>
                <Link v-if="stats.products.out_of_stock > 0"
                    :href="route('admin.products.index', { filter: 'out_of_stock' })" class="adm-btn adm-btn--sm"
                    style="background:var(--bb-red-bg); color:var(--bb-red); border-color:var(--bb-red);">
                ⚠ {{ stats.products.out_of_stock }} out of stock
                </Link>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* ── KPI Grid ── */
.db-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1100px) {
    .db-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 580px) {
    .db-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.db-kpi-card {
    padding: 1.25rem !important;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.db-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.35rem;
}

.db-kpi-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.db-kpi-icon--lav {
    background: var(--bb-lav-bg, #f3f0ff);
    color: var(--bb-lav-d);
}

.db-kpi-icon--blush {
    background: var(--bb-blush-bg, #fff0f2);
    color: var(--bb-blush-d);
}

.db-kpi-icon--peach {
    background: var(--bb-peach-bg, #fff7f0);
    color: var(--bb-peach-d);
}

.db-kpi-icon--sage {
    background: var(--bb-green-bg);
    color: var(--bb-sage-d);
}

.db-kpi-pct {
    font-size: 0.72rem;
    font-weight: 700;
}

.db-kpi-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
}

.db-kpi-val {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--bb-text);
    line-height: 1.1;
}

.db-kpi-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--bb-muted);
}

.db-kpi-sub {
    font-size: 0.75rem;
    color: var(--bb-muted);
    margin-top: 0.15rem;
}

.db-sparkline {
    width: 100%;
    height: 32px;
    margin-top: 0.5rem;
}

/* ── Charts grid ── */
.db-charts-grid {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 860px) {
    .db-charts-grid {
        grid-template-columns: 1fr;
    }
}

.db-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem 0.85rem;
    border-bottom: 1px solid var(--bb-border);
}

.db-card-header-note {
    font-size: 0.72rem;
    color: var(--bb-muted);
}

.db-view-all {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--bb-lav-d);
    text-decoration: none;
}

.db-view-all:hover {
    text-decoration: underline;
}

/* Bar chart */
.db-bar-chart {
    display: flex;
    align-items: flex-end;
    gap: 3px;
    height: 140px;
    padding: 1rem 1.25rem 2rem;
    position: relative;
}

.db-bar-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 3px;
    height: 100%;
    position: relative;
    cursor: default;
}

.db-bar-col:hover .db-bar-tooltip {
    opacity: 1;
    pointer-events: none;
}

.db-bar-tooltip {
    position: absolute;
    bottom: calc(100% + 4px);
    left: 50%;
    transform: translateX(-50%);
    background: var(--bb-navy);
    color: #fff;
    font-size: 0.68rem;
    line-height: 1.4;
    padding: 5px 8px;
    border-radius: 6px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.15s;
    z-index: 10;
    text-align: center;
    pointer-events: none;
}

.db-bar {
    width: 100%;
    border-radius: 4px 4px 0 0;
    min-height: 3px;
    transition: opacity 0.2s;
}

.db-bar-label {
    font-size: 0.6rem;
    color: var(--bb-muted);
    transform: rotate(40deg) translateX(2px);
    transform-origin: left center;
    white-space: nowrap;
}

/* Donut */
.db-donut-wrap {
    display: flex;
    justify-content: center;
    padding: 0.75rem 0;
}

.db-donut-svg {
    width: 110px;
    height: 110px;
}

.db-donut-legend {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.db-legend-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
}

.db-legend-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.db-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.db-legend-label {
    color: var(--bb-text);
    text-transform: capitalize;
}

.db-legend-val {
    font-weight: 700;
    color: var(--bb-text);
}

/* ── Insights grid ── */
.db-insights-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1000px) {
    .db-insights-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 620px) {
    .db-insights-grid {
        grid-template-columns: 1fr;
    }
}

/* Retention */
.db-retention-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.db-retention-bar {
    height: 10px;
    border-radius: 999px;
    overflow: hidden;
    display: flex;
    background: var(--bb-border);
}

.db-retention-seg {
    height: 100%;
    transition: width 0.5s ease;
}

.db-retention-seg--new {
    background: var(--bb-lav-d);
}

.db-retention-seg--return {
    background: var(--bb-sage-d);
}

.db-retention-legend {
    display: flex;
    gap: 1.25rem;
}

.db-ret-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: var(--bb-muted);
}

.db-ret-item strong {
    color: var(--bb-text);
    margin-left: 0.2rem;
}

.db-ret-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.db-ret-dot--new {
    background: var(--bb-lav-d);
}

.db-ret-dot--return {
    background: var(--bb-sage-d);
}

.db-retention-rate {
    text-align: center;
    padding-top: 0.5rem;
    border-top: 1px solid var(--bb-border);
}

.db-rate-num {
    font-size: 2rem;
    font-weight: 800;
    color: var(--bb-text);
    display: block;
    line-height: 1.1;
}

.db-rate-label {
    font-size: 0.72rem;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* Stat list */
.db-stat-list {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.db-stat-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.85rem;
}

.db-stat-lbl {
    color: var(--bb-muted);
}

.db-stat-val {
    font-weight: 700;
}

/* Reviews */
.db-reviews-centre {
    display: flex;
    justify-content: center;
    padding: 0.5rem 0 0.75rem;
}

.db-avg-rating {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
}

.db-avg-num {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--bb-text);
    line-height: 1;
}

.db-stars {
    display: flex;
    gap: 0.15rem;
}

.db-star {
    font-size: 1rem;
    color: var(--bb-border);
}

.db-star--filled {
    color: #f5c842;
}

.db-avg-sub {
    font-size: 0.75rem;
    color: var(--bb-muted);
}

.db-pending-reviews {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.65rem 0.85rem;
    border-radius: 8px;
    background: var(--bb-red-bg);
    border: 1px solid #f5b8c0;
    color: var(--bb-red);
    font-size: 0.82rem;
}

.db-pending-reviews svg {
    flex-shrink: 0;
}

.db-pending-link {
    margin-left: auto;
    font-weight: 700;
    color: var(--bb-red);
    text-decoration: none;
}

.db-pending-link:hover {
    text-decoration: underline;
}

/* ── Bottom grid ── */
.db-bottom-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1000px) {
    .db-bottom-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 620px) {
    .db-bottom-grid {
        grid-template-columns: 1fr;
    }
}

/* List rows */
.db-list {
    display: flex;
    flex-direction: column;
}

.db-list-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid var(--bb-border);
}

.db-list-row:last-child {
    border-bottom: none;
}

.db-list-row--link {
    text-decoration: none;
    transition: background 0.12s;
    cursor: pointer;
}

.db-list-row--link:hover {
    background: var(--bb-cream);
}

.db-list-info {
    flex: 1;
    min-width: 0;
}

.db-list-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--bb-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.db-list-meta {
    font-size: 0.72rem;
    color: var(--bb-muted);
    margin-top: 0.1rem;
}

.db-list-right {
    text-align: right;
    flex-shrink: 0;
}

.db-list-primary {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--bb-text);
}

.db-list-secondary {
    font-size: 0.72rem;
    color: var(--bb-muted);
}

/* Rank badges */
.db-rank {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 0.68rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.db-rank--1 {
    background: #fef3c7;
    color: #d97706;
}

.db-rank--2 {
    background: #f3f4f6;
    color: #6b7280;
}

.db-rank--3 {
    background: #fef0e7;
    color: #c2622e;
}

.db-rank--4,
.db-rank--5 {
    background: var(--bb-cream);
    color: var(--bb-muted);
}

/* Journal summary */
.db-journal-summary {
    display: flex;
    align-items: center;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid var(--bb-border);
    gap: 0;
}

.db-journal-stat {
    flex: 1;
    text-align: center;
}

.db-journal-num {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--bb-text);
    display: block;
}

.db-journal-lbl {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--bb-muted);
}

.db-journal-sep {
    width: 1px;
    height: 32px;
    background: var(--bb-border);
}

/* Product health */
.db-product-health {
    display: grid;
    grid-template-columns: repeat(4, 1fr) auto;
    gap: 1rem 1.5rem;
    align-items: start;
}

@media (max-width: 860px) {
    .db-product-health {
        grid-template-columns: repeat(2, 1fr);
    }
}

.db-ph-stat {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.db-ph-num {
    font-size: 1.6rem;
    font-weight: 800;
    line-height: 1;
}

.db-ph-lbl {
    font-size: 0.72rem;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

.db-ph-bar {
    height: 4px;
    background: var(--bb-border);
    border-radius: 999px;
    overflow: hidden;
}

.db-ph-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease;
}

.db-ph-total {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-end;
    border-left: 1px solid var(--bb-border);
    padding-left: 1.5rem;
}

@media (max-width: 860px) {
    .db-ph-total {
        border-left: none;
        padding-left: 0;
        align-items: flex-start;
        border-top: 1px solid var(--bb-border);
        padding-top: 0.75rem;
    }
}

.db-ph-total-num {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--bb-text);
    line-height: 1;
}

.db-ph-total-lbl {
    font-size: 0.7rem;
    color: var(--bb-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* Misc */
.db-empty-note {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-style: italic;
    text-align: center;
}
</style>
