<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now      = Carbon::now();
        $start30  = $now->copy()->subDays(30)->startOfDay();
        $start7   = $now->copy()->subDays(7)->startOfDay();
        $start60  = $now->copy()->subDays(60)->startOfDay();    // for comparison period
        $prevStart = $now->copy()->subDays(60)->startOfDay();
        $prevEnd   = $now->copy()->subDays(31)->endOfDay();

        // ── Products ──────────────────────────────────────────────────────────
        $productStats = [
            'total'       => Product::whereNull('parent_product_id')->count(),
            'in_stock'    => Product::whereNull('parent_product_id')->where('status', 'enabled')->where('stock_qty', '>', 0)->count(),
            'out_of_stock' => Product::whereNull('parent_product_id')->where('status', 'enabled')->where('stock_qty', '<=', 0)->count(),
            'disabled'    => Product::whereNull('parent_product_id')->where('status', 'disabled')->count(),
            'low_stock'   => Product::whereNull('parent_product_id')->where('status', 'enabled')->where('stock_qty', '>', 0)->where('stock_qty', '<=', 5)->count(),
        ];

        // ── Orders — current 30 days ──────────────────────────────────────────
        $current = Order::where('status', 'successful')
            ->where('created_at', '>=', $start30)
            ->selectRaw('COUNT(id) as count, SUM(grand_total) as revenue, AVG(grand_total) as avg_order')
            ->first();

        // ── Orders — previous 30 days (for % change) ─────────────────────────
        $previous = Order::where('status', 'successful')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->selectRaw('COUNT(id) as count, SUM(grand_total) as revenue')
            ->first();

        $pctChange = fn ($curr, $prev) => $prev > 0
            ? round((($curr - $prev) / $prev) * 100, 1)
            : ($curr > 0 ? 100.0 : 0.0);

        // ── Revenue trend — last 30 days ──────────────────────────────────────
        $rawTrend = Order::where('status', 'successful')
            ->where('created_at', '>=', $start30)
            ->selectRaw('DATE(created_at) as date, COUNT(id) as orders, SUM(grand_total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill every day so chart has no gaps
        $trend = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $trend[] = [
                'date'    => $d,
                'label'   => $now->copy()->subDays($i)->format('d M'),
                'revenue' => (float) ($rawTrend[$d]->revenue ?? 0),
                'orders'  => (int)   ($rawTrend[$d]->orders  ?? 0),
            ];
        }

        // ── Order status breakdown ─────────────────────────────────────────────
        $statusBreakdown = Order::where('created_at', '>=', $start30)
            ->selectRaw('status, COUNT(id) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Top selling products (last 30 days) ───────────────────────────────
        $topProducts = DB::table('order_item')
            ->join('order', 'order_item.order_id', '=', 'order.id')
            ->join('product', 'order_item.product_id', '=', 'product.id')
            ->where('order.status', 'successful')
            ->where('order.created_at', '>=', $start30)
            ->selectRaw('product.id, product.name, product.mpn, SUM(order_item.quantity) as units_sold, SUM(order_item.product_total) as revenue')
            ->groupBy('product.id', 'product.name', 'product.mpn')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        // ── Recent orders ─────────────────────────────────────────────────────
        $recentOrders = Order::with('user:id,name')
            ->select('id', 'user_id', 'first_name', 'last_name', 'grand_total', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($o) => [
                'id'         => $o->id,
                'name'       => "{$o->first_name} {$o->last_name}",
                'total'      => (float) $o->grand_total,
                'status'     => $o->status,
                'created_at' => $o->created_at->format('d M, H:i'),
            ]);

        // ── Customers ─────────────────────────────────────────────────────────
        $newCustomers30 = User::where('created_at', '>=', $start30)->where('is_admin', false)->count();
        $newCustomers7  = User::where('created_at', '>=', $start7)->where('is_admin', false)->count();
        $totalCustomers = User::where('is_admin', false)->count();

        // ── Orders pending dispatch ────────────────────────────────────────────
        $awaitingDispatch = Order::whereIn('status', ['successful', 'processing'])->count();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'products'         => $productStats,
                'current_period'   => [
                    'orders'       => (int)   ($current->count   ?? 0),
                    'revenue'      => (float) ($current->revenue ?? 0),
                    'avg_order'    => (float) ($current->avg_order ?? 0),
                ],
                'previous_period'  => [
                    'orders'       => (int)   ($previous->count   ?? 0),
                    'revenue'      => (float) ($previous->revenue ?? 0),
                ],
                'pct_change'       => [
                    'orders'       => $pctChange($current->count ?? 0, $previous->count   ?? 0),
                    'revenue'      => $pctChange($current->revenue ?? 0, $previous->revenue ?? 0),
                ],
                'trend'            => $trend,
                'status_breakdown' => $statusBreakdown,
                'top_products'     => $topProducts,
                'recent_orders'    => $recentOrders,
                'customers'        => [
                    'total'        => $totalCustomers,
                    'new_30'       => $newCustomers30,
                    'new_7'        => $newCustomers7,
                ],
                'awaiting_dispatch' => $awaitingDispatch,
            ],
        ]);
    }
}
