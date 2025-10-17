<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\Order;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Dashboard with key metrics.
     */
    public function index()
    {
        $days = 30; // Data period

        // 1. Product Counts
        $productStats = [
            'total_products' => Product::whereNull('parent_product_id')->count(),
            'in_stock' => Product::whereNull('parent_product_id')->where('stock_qty', '>', 0)->count(),
            'out_of_stock' => Product::whereNull('parent_product_id')->where('stock_qty', 0)->count(),
            'disabled' => Product::where('status', 'disabled')->count(),
        ];

        // 2. Order & Revenue Stats
        $startDate = Carbon::now()->subDays($days);

        $orderStats = Order::select(
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(grand_total) as total_revenue')
            )
            ->where('status', 'successful') // Only count successful orders
            ->where('created_at', '>=', $startDate)
            ->first();

        // 3. Revenue Trend (last 7 days - simplified for dashboard chart)
        $revenueTrend = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(grand_total) as daily_revenue')
            )
            ->where('status', 'successful')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'period' => "Last {$days} days",
                'products' => $productStats,
                'orders' => [
                    'count' => (int) $orderStats->total_orders,
                    'revenue' => (float) $orderStats->total_revenue,
                ],
                'revenue_trend' => $revenueTrend,
            ]
        ]);
    }
}
