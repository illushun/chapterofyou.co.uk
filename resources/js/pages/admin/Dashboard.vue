<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

// ── Types ──────────────────────────────────────────────────────────────────
interface TrendPoint { date: string; label: string; revenue: number; orders: number; }
interface TopProduct { id: number; name: string; mpn: string; units_sold: number; revenue: number; }
interface RecentOrder { id: number; name: string; total: number; status: string; created_at: string; }

interface Stats {
    products: { total: number; in_stock: number; out_of_stock: number; disabled: number; low_stock: number; };
    current_period: { orders: number; revenue: number; avg_order: number; };
    previous_period: { orders: number; revenue: number; };
    pct_change: { orders: number; revenue: number; };
    trend: TrendPoint[];
    status_breakdown: Record<string, number>;
    top_products: TopProduct[];
    recent_orders: RecentOrder[];
    customers: { total: number; new_30: number; new_7: number; };
    awaiting_dispatch: number;
}

const props = defineProps<{ stats: Stats }>();

// ── Helpers ────────────────────────────────────────────────────────────────
// Laravel serialises decimal DB columns as strings — always cast via Number()
const n = (v: unknown): number => Number(v) || 0;
const fmt = (v: unknown) => `£${n(v).toFixed(2)}`;
const fmtK = (v: unknown) => n(v) >= 1000 ? `£${(n(v) / 1000).toFixed(1)}k` : fmt(v);

const pctClass = (v: unknown) => n(v) >= 0 ? 'text-green-600' : 'text-red-500';
const pctLabel = (v: unknown) => `${n(v) >= 0 ? '▲' : '▼'} ${Math.abs(n(v))}% vs prev 30d`;

const statusColour: Record<string, string> = {
    successful: 'bg-green-100 text-green-700',
    shipped: 'bg-emerald-100 text-emerald-700',
    processing: 'bg-blue-100 text-blue-700',
    pending: 'bg-yellow-100 text-yellow-700',
    cancelled: 'bg-red-100 text-red-600',
    failed: 'bg-red-100 text-red-600',
};

// ── SVG sparkline ──────────────────────────────────────────────────────────
function sparklinePath(points: number[], w = 200, h = 50): string {
    if (!points.length) return '';
    const max = Math.max(...points, 1);
    const step = w / (points.length - 1 || 1);
    return points
        .map((v, i) => `${i === 0 ? 'M' : 'L'} ${(i * step).toFixed(1)} ${(h - (v / max) * h).toFixed(1)}`)
        .join(' ');
}

// Revenue sparkline (last 30 days)
const revenueLine = computed(() =>
    sparklinePath(props.stats.trend.map(t => Number(t.revenue) || 0))
);

// ── Bar chart for revenue trend (last 30 days) ─────────────────────────────
const barChart = computed(() => {
    const data = props.stats.trend;
    // Show last 14 for readability
    const slice = data.slice(-14);
    const maxSlice = Math.max(...slice.map(d => Number(d.revenue) || 0), 1);
    return slice.map(d => ({
        label: d.label,
        revenue: Number(d.revenue) || 0,
        height: Math.round(((Number(d.revenue) || 0) / maxSlice) * 100),
        orders: Number(d.orders) || 0,
    }));
});

// ── Donut chart for order status ───────────────────────────────────────────
const donutSegments = computed(() => {
    const breakdown = props.stats.status_breakdown;
    const total = Object.values(breakdown).reduce((a, b) => Number(a) + Number(b), 0) || 1;
    const colours: Record<string, string> = {
        successful: '#22c55e', shipped: '#10b981', processing: '#3b82f6',
        pending: '#eab308', cancelled: '#ef4444', failed: '#f97316',
    };
    let offset = 0;
    return Object.entries(breakdown).map(([status, count]) => {
        const pct = (Number(count) / total) * 100;
        const seg = { status, count, pct: Math.round(pct), offset: Math.round(offset), colour: colours[status] ?? '#9ca3af' };
        offset += pct;
        return seg;
    });
});
</script>

<template>
    <AdminLayout>

        <Head title="Admin Dashboard" />

        <div class="mb-6 flex items-center justify-between border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">Dashboard</h2>
            <span class="text-xs text-copy-light bg-secondary-light px-3 py-1 rounded-full border border-copy-light">
                Last 30 days
            </span>
        </div>

        <!-- ── KPI cards ── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <!-- Revenue -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4">
                <p class="text-xs font-medium text-copy-light uppercase tracking-wider mb-1">Revenue</p>
                <p class="text-2xl font-black text-copy">{{ fmtK(stats.current_period.revenue) }}</p>
                <p class="text-xs mt-1" :class="pctClass(stats.pct_change.revenue)">
                    {{ pctLabel(stats.pct_change.revenue) }}
                </p>
                <!-- Sparkline -->
                <svg viewBox="0 0 200 50" class="w-full h-8 mt-2 opacity-60" fill="none">
                    <path :d="revenueLine" stroke="currentColor" stroke-width="1.5" class="text-primary"
                        stroke-linejoin="round" />
                </svg>
            </div>

            <!-- Orders -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4">
                <p class="text-xs font-medium text-copy-light uppercase tracking-wider mb-1">Orders</p>
                <p class="text-2xl font-black text-copy">{{ stats.current_period.orders }}</p>
                <p class="text-xs mt-1" :class="pctClass(stats.pct_change.orders)">
                    {{ pctLabel(stats.pct_change.orders) }}
                </p>
                <p class="text-xs text-copy-light mt-2">
                    Avg: {{ fmt(stats.current_period.avg_order) }}
                </p>
            </div>

            <!-- Awaiting dispatch -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4">
                <p class="text-xs font-medium text-copy-light uppercase tracking-wider mb-1">Awaiting Dispatch</p>
                <p class="text-2xl font-black"
                    :class="stats.awaiting_dispatch > 0 ? 'text-amber-600' : 'text-green-600'">
                    {{ stats.awaiting_dispatch }}
                </p>
                <p class="text-xs text-copy-light mt-1">Successful + Processing</p>
                <Link :href="route('admin.orders.index', { status: 'successful' })"
                    class="text-xs text-primary hover:underline mt-2 block">
                View orders →
                </Link>
            </div>

            <!-- New customers -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-4">
                <p class="text-xs font-medium text-copy-light uppercase tracking-wider mb-1">New Customers</p>
                <p class="text-2xl font-black text-copy">{{ stats.customers.new_30 }}</p>
                <p class="text-xs text-copy-light mt-1">{{ stats.customers.new_7 }} this week</p>
                <p class="text-xs text-copy-light">{{ stats.customers.total }} total</p>
            </div>

        </div>

        <!-- ── Revenue bar chart + order status donut ── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            <!-- Bar chart -->
            <div class="log:col-span-2 relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                <h3 class="text-base font-bold text-copy mb-4 border-b border-copy-light pb-2">
                    Revenue — Last 14 Days
                </h3>
                <div class="flex items-end gap-1 h-40">
                    <div v-for="bar in barChart" :key="bar.label"
                        class="flex-1 flex flex-col items-center gap-1 group relative">
                        <!-- Tooltip -->
                        <div
                            class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2
                                    opacity-0 group-hover:opacity-100 transition
                                    bg-copy text-foreground text-xs rounded px-2 py-1 whitespace-nowrap z-10 pointer-events-none">
                            {{ bar.label }}<br />{{ fmt(bar.revenue) }}<br />{{ bar.orders }} order{{ bar.orders !==
                                1 ? 's' : '' }}
                        </div>
                        <div class="w-full rounded-t transition-all duration-300" :style="{
                            height: bar.height + '%',
                            minHeight: bar.revenue > 0 ? '4px' : '2px',
                            backgroundColor: bar.revenue > 0 ? 'var(--primary)' : 'var(--border)',
                            opacity: bar.revenue > 0 ? 1 : 0.3,
                        }">
                        </div>
                        <span class="text-[9px] text-copy-light leading-none rotate-45 origin-left translate-x-1">
                            {{ bar.label.split(' ')[0] }}
                        </span>
                    </div>
                </div>
                <p v-if="stats.current_period.revenue === 0" class="text-center text-copy-light text-sm mt-4 italic">
                    No revenue data for this period yet.
                </p>
            </div>

            <!-- Order status donut -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                <h3 class="text-base font-bold text-copy mb-4 border-b border-copy-light pb-2">
                    Orders by Status <span class="font-normal text-copy-light text-xs">(30d)</span>
                </h3>

                <!-- SVG donut -->
                <div class="flex justify-center mb-4">
                    <svg viewBox="0 0 42 42" class="w-28 h-28">
                        <circle cx="21" cy="21" r="15.915" fill="none" stroke="#e5e7eb" stroke-width="5" />
                        <circle v-for="seg in donutSegments" :key="seg.status" cx="21" cy="21" r="15.915" fill="none"
                            :stroke="seg.colour" stroke-width="5" :stroke-dasharray="`${seg.pct} ${100 - seg.pct}`"
                            :stroke-dashoffset="100 - seg.offset" stroke-linecap="butt"
                            style="transform: rotate(-90deg); transform-origin: 50% 50%;" />
                        <!-- Centre total -->
                        <text x="21" y="21" text-anchor="middle" dominant-baseline="middle" class="fill-current"
                            style="font-size: 5px; font-weight: 700;">
                            {{Object.values(stats.status_breakdown).reduce((a, b) => a + b, 0)}}
                        </text>
                        <text x="21" y="26" text-anchor="middle" dominant-baseline="middle"
                            style="font-size: 3px; fill: #9ca3af;">orders</text>
                    </svg>
                </div>

                <!-- Legend -->
                <div class="space-y-1.5">
                    <div v-for="seg in donutSegments" :key="seg.status"
                        class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                :style="{ backgroundColor: seg.colour }"></span>
                            <span class="capitalize text-copy">{{ seg.status }}</span>
                        </div>
                        <span class="font-bold text-copy">{{ seg.count }}</span>
                    </div>
                    <p v-if="!donutSegments.length" class="text-copy-light text-xs italic text-center">
                        No orders yet.
                    </p>
                </div>
            </div>

        </div>

        <!-- ── Bottom row: top products + recent orders + product health ── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Top products -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden">
                <div class="px-5 py-4 border-b border-copy-light flex items-center justify-between">
                    <h3 class="text-base font-bold text-copy">Top Products</h3>
                    <span class="text-xs text-copy-light">by units sold (30d)</span>
                </div>
                <div class="divide-y divide-copy-light">
                    <div v-if="!stats.top_products.length" class="px-5 py-6 text-center text-copy-light text-sm italic">
                        No sales data yet.
                    </div>
                    <div v-for="(p, i) in stats.top_products" :key="p.id"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-secondary-light transition">
                        <span
                            class="w-5 h-5 rounded-full text-xs font-black flex items-center justify-center flex-shrink-0"
                            :class="i === 0 ? 'bg-amber-100 text-amber-700' : i === 1 ? 'bg-gray-100 text-gray-600' : i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-secondary-light text-copy-light'">
                            {{ i + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-copy truncate">{{ p.name }}</p>
                            <p class="text-xs text-copy-light font-mono">{{ p.mpn }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-copy">{{ p.units_sold }} sold</p>
                            <p class="text-xs text-copy-light">{{ fmt(p.revenue) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent orders -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground overflow-hidden">
                <div class="px-5 py-4 border-b border-copy-light flex items-center justify-between">
                    <h3 class="text-base font-bold text-copy">Recent Orders</h3>
                    <Link :href="route('admin.orders.index')" class="text-xs text-primary hover:underline">
                    View all →
                    </Link>
                </div>
                <div class="divide-y divide-copy-light">
                    <div v-if="!stats.recent_orders.length"
                        class="px-5 py-6 text-center text-copy-light text-sm italic">
                        No orders yet.
                    </div>
                    <Link v-for="order in stats.recent_orders" :key="order.id"
                        :href="route('admin.orders.show', order.id)"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-secondary-light transition block">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-copy truncate">{{ order.name }}</p>
                        <p class="text-xs text-copy-light">{{ order.created_at }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-copy">{{ fmt(order.total) }}</p>
                        <span class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                            :class="statusColour[order.status] ?? 'bg-gray-100 text-gray-600'">
                            {{ order.status }}
                        </span>
                    </div>
                    </Link>
                </div>
            </div>

            <!-- Product health -->
            <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                <h3 class="text-base font-bold text-copy mb-4 border-b border-copy-light pb-2">Product Health</h3>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-copy">In Stock</span>
                        <span class="font-bold text-green-600">{{ stats.products.in_stock }}</span>
                    </div>
                    <!-- Stock bar -->
                    <div class="h-2 rounded-full bg-secondary-light overflow-hidden">
                        <div class="h-full rounded-full bg-green-500 transition-all"
                            :style="{ width: stats.products.total > 0 ? (stats.products.in_stock / stats.products.total * 100) + '%' : '0%' }">
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-copy">Out of Stock</span>
                        <span class="font-bold text-amber-600">{{ stats.products.out_of_stock }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-secondary-light overflow-hidden">
                        <div class="h-full rounded-full bg-amber-500 transition-all"
                            :style="{ width: stats.products.total > 0 ? (stats.products.out_of_stock / stats.products.total * 100) + '%' : '0%' }">
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-copy">Low Stock <span
                                class="text-xs text-copy-light">(≤5)</span></span>
                        <span class="font-bold text-orange-500">{{ stats.products.low_stock }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-copy">Disabled / Draft</span>
                        <span class="font-bold text-red-500">{{ stats.products.disabled }}</span>
                    </div>

                    <div class="pt-3 border-t border-copy-light flex justify-between items-center">
                        <span class="text-sm font-bold text-copy">Total Products</span>
                        <span class="font-black text-copy text-lg">{{ stats.products.total }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <Link :href="route('admin.products.index')"
                        class="block w-full text-center py-2 rounded-lg border-2 border-copy text-sm font-bold hover:bg-secondary-light transition">
                    Manage Products
                    </Link>
                    <Link :href="route('admin.products.index', { filter: 'out_of_stock' })"
                        v-if="stats.products.out_of_stock > 0"
                        class="block w-full text-center py-1.5 rounded-lg border border-amber-300 bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition">
                    ⚠ {{ stats.products.out_of_stock }} out of stock
                    </Link>
                </div>
            </div>

        </div>

    </AdminLayout>
</template>
