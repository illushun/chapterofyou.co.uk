<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface ProductStats {
    total_products: number;
    in_stock: number;
    out_of_stock: number;
    disabled: number;
}

interface OrderStats {
    count: number;
    revenue: number;
}

interface RevenueTrend {
    date: string;
    daily_revenue: number;
}

interface DashboardProps {
    stats: {
        period: string;
        products: ProductStats;
        orders: OrderStats;
        revenue_trend: RevenueTrend[];
    };
}

const props = defineProps<DashboardProps>();

// Simple component for a statistical card
const StatCard = (value: string | number, label: string, icon: string, colorClass: string) => ({
    value,
    label,
    icon,
    colorClass,
});

const statCards = computed(() => [
    StatCard(props.stats.products.total_products, 'Total Products', 'M10.5 6a7.5 7.5 0 100 15 7.5 7.5 0 000-15zM2 12a10 10 0 1120 0 10 10 0 01-20 0z', 'text-primary'),
    StatCard(props.stats.orders.count, `Total Orders (${props.stats.period})`, 'M12 8c1.333-.553 2.667-.553 4 0 1.333.553 2.667 0 4 0 1.333.553 2.667 1.333 4 1.333v7.333a2 2 0 01-2 2H4a2 2 0 01-2-2V9.333c1.333 0 2.667-.553 4 0 1.333.553 2.667 0 4 0 1.333-.553 2.667-.553 4 0z', 'text-green-500'),
    StatCard(`£${props.stats.orders.revenue.toFixed(2)}`, `Total Revenue (${props.stats.period})`, 'M12 6v6m0 0v6M12 6a3 3 0 110-6 3 3 0 010 6zm0 12a3 3 0 110-6 3 3 0 010 6z', 'text-blue-500'),
    StatCard(props.stats.products.out_of_stock, 'Out of Stock', 'M12 9v2m0 4h.01M10.5 21a9 9 0 100-18 9 9 0 000 18z', 'text-error'),
]);

// Simple chart data preparation
const chartData = computed(() => {
    // Fill in missing days for a smooth line chart
    const last7Days = Array(7).fill(0).map((_, i) => {
        const date = new Date();
        date.setDate(date.getDate() - (6 - i));
        return date.toISOString().split('T')[0];
    });

    return last7Days.map(date => {
        const trend = props.stats.revenue_trend.find(t => t.date === date);
        return trend ? trend.daily_revenue : 0;
    });
});
</script>

<template>
    <AdminLayout>
        <Head title="Admin Dashboard" />

        <h2 class="text-3xl font-black mb-6 border-b-2 border-copy pb-2">Dashboard Overview</h2>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="card in statCards" :key="card.label"
                class="rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg"
            >
                <div class="relative rounded-lg -m-0.5 p-5 bg-foreground border-2 border-copy transition hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-3xl font-extrabold text-copy">{{ card.value }}</p>
                        <svg class="size-8 opacity-70" :class="card.colorClass" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"></path>
                        </svg>
                    </div>
                    <p class="mt-1 text-sm font-medium text-copy-light">{{ card.label }}</p>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg">
                <div class="relative rounded-lg -m-0.5 p-6 bg-foreground border-2 border-copy">
                    <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Revenue Last 7 Days</h3>
                    <div class="h-64 flex items-center justify-center text-copy-light border-2 border-dashed border-copy-light p-4">
                        <p class="text-lg">Chart Placeholder: [{{ chartData.join(', ') }}]</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border-2 border-copy bg-[var(--primary-content)] shadow-lg">
                <div class="relative rounded-lg -m-0.5 p-6 bg-foreground border-2 border-copy">
                    <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Product Health</h3>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center text-lg text-copy font-semibold">
                            <span>Enabled & In Stock</span>
                            <span class="text-green-500 font-extrabold">{{ props.stats.products.in_stock }}</span>
                        </li>
                        <li class="flex justify-between items-center text-lg text-copy font-semibold">
                            <span>Out of Stock</span>
                            <span class="text-yellow-500 font-extrabold">{{ props.stats.products.out_of_stock }}</span>
                        </li>
                        <li class="flex justify-between items-center text-lg text-copy font-semibold">
                            <span>Disabled/Draft</span>
                            <span class="text-error font-extrabold">{{ props.stats.products.disabled }}</span>
                        </li>
                        <li class="pt-3 border-t-2 border-copy-light flex justify-between items-center text-xl text-copy font-black">
                            <span>Total Unique Products</span>
                            <span>{{ props.stats.products.total_products }}</span>
                        </li>
                    </ul>
                    <Link :href="route('admin.products.index')" class="mt-4 w-full block text-center py-2 rounded-lg border-2 border-copy text-sm font-bold bg-secondary-light hover:bg-secondary transition">
                        Manage Products →
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
