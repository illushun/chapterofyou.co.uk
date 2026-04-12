<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\GiftVoucherOrder;
use App\Models\JournalPost;
use App\Models\Order;
use App\Models\Product;
use App\Models\Product\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now    = now();
        $from30 = $now->copy()->subDays(30)->startOfDay();
        $from60 = $now->copy()->subDays(60)->startOfDay();
        $from7  = $now->copy()->subDays(7)->startOfDay();

        // ── Revenue & orders: current 30d vs previous 30d ─────────────────
        $current = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from30, $now])
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(grand_total),0) as revenue, COALESCE(AVG(grand_total),0) as avg_order')
            ->first();

        $previous = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from60, $from30])
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(grand_total),0) as revenue')
            ->first();

        $pctRevenue = $previous->revenue > 0
            ? round((($current->revenue - $previous->revenue) / $previous->revenue) * 100)
            : 0;
        $pctOrders = $previous->orders > 0
            ? round((($current->orders - $previous->orders) / $previous->orders) * 100)
            : 0;

        // ── Daily trend (last 30 days) ─────────────────────────────────────
        $trend = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from30, $now])
            ->selectRaw("DATE(created_at) as date, COALESCE(SUM(grand_total),0) as revenue, COUNT(*) as orders")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trendPoints = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date  = $now->copy()->subDays($i)->format('Y-m-d');
            $point = $trend->get($date);
            $trendPoints->push([
                'date'    => $date,
                'label'   => $now->copy()->subDays($i)->format('d M'),
                'revenue' => $point ? $point->revenue : 0,
                'orders'  => $point ? $point->orders : 0,
            ]);
        }

        // ── Order status breakdown ─────────────────────────────────────────
        $statusBreakdown = Order::whereBetween('created_at', [$from30, $now])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Top products ───────────────────────────────────────────────────
        $topProducts = DB::table('order_item')
            ->join('order', 'order_item.order_id', '=', 'order.id')
            ->join('product', 'order_item.product_id', '=', 'product.id')
            ->where('order.status', 'successful')
            ->whereBetween('order.created_at', [$from30, $now])
            ->where('product.mpn', '!=', 'GIFT-VOUCHER')
            ->selectRaw('product.id, product.name, product.mpn, SUM(order_item.quantity) as units_sold, COALESCE(SUM(order_item.product_total),0) as revenue')
            ->groupBy('product.id', 'product.name', 'product.mpn')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        // ── Recent orders ──────────────────────────────────────────────────
        $recentOrders = Order::where('status', 'successful')
            ->latest()
            ->limit(6)
            ->get(['id', 'first_name', 'last_name', 'grand_total', 'status', 'created_at'])
            ->map(fn ($o) => [
                'id'         => $o->id,
                'name'       => "{$o->first_name} {$o->last_name}",
                'total'      => $o->grand_total,
                'status'     => $o->status,
                'created_at' => $o->created_at->format('d M Y, H:i'),
            ]);

        // ── Customers ──────────────────────────────────────────────────────
        $customersNew30  = User::where('is_admin', false)->whereBetween('created_at', [$from30, $now])->count();
        $customersNew7   = User::where('is_admin', false)->whereBetween('created_at', [$from7,  $now])->count();
        $customersTotal  = User::where('is_admin', false)->count();

        // Returning = users who placed more than 1 successful order in last 30d
        $returning30 = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from30, $now])
            ->whereNotNull('user_id')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // ── Product health ─────────────────────────────────────────────────
        $products = Product::whereNull('parent_product_id')
            ->where('mpn', '!=', 'GIFT-VOUCHER')
            ->get(['id', 'status', 'stock_qty']);

        $productStats = [
            'total'        => $products->count(),
            'in_stock'     => $products->where('status', 'enabled')->where('stock_qty', '>', 0)->count(),
            'out_of_stock' => $products->where('status', 'enabled')->where('stock_qty', '<=', 0)->count(),
            'disabled'     => $products->where('status', 'disabled')->count(),
            'low_stock'    => $products->where('status', 'enabled')->where('stock_qty', '>', 0)->where('stock_qty', '<=', 5)->count(),
        ];

        // ── Cart abandonment ───────────────────────────────────────────────
        $activeCarts = Cart::whereHas('items')
            ->whereBetween('updated_at', [$from30, $now])
            ->count();

        $completedOrders30 = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from30, $now])
            ->count();

        $totalCartActivity = $activeCarts + $completedOrders30;
        $abandonmentRate   = $totalCartActivity > 0
            ? round(($activeCarts / $totalCartActivity) * 100)
            : 0;

        // ── Vouchers & gift cards ──────────────────────────────────────────
        $ordersWithVoucher = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from30, $now])
            ->where('voucher_discount', '>', 0)
            ->count();

        $totalDiscountGiven = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from30, $now])
            ->sum('voucher_discount');

        $giftVouchersSold = GiftVoucherOrder::whereHas(
            'order',
            fn ($q) =>
            $q->where('status', 'successful')
              ->whereBetween('created_at', [$from30, $now])
        )->count();

        $giftVouchersPending = GiftVoucherOrder::whereNull('fulfilled_at')->count();

        // ── Reviews ────────────────────────────────────────────────────────
        $pendingReviews  = Review::where('status', 'pending')->count();
        $approvedReviews = Review::where('status', 'approved')->count();
        $avgRating       = Review::where('status', 'approved')->avg('rating') ?? 0;

        // ── Journal ────────────────────────────────────────────────────────
        $journalTotal = JournalPost::published()->count();
        $journalViews = JournalPost::published()->sum('views');
        $topPosts = JournalPost::published()
            ->orderByDesc('views')
            ->limit(4)
            ->get(['id', 'title', 'slug', 'views', 'published_at'])
            ->map(fn ($p) => [
                'id'           => $p->id,
                'title'        => $p->title,
                'slug'         => $p->slug,
                'views'        => $p->views,
                'published_at' => $p->published_at->format('d M Y'),
            ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'products'        => $productStats,
                'current_period'  => [
                    'orders'    => $current->orders,
                    'revenue'   => $current->revenue,
                    'avg_order' => $current->avg_order,
                ],
                'previous_period' => [
                    'orders'  => $previous->orders,
                    'revenue' => $previous->revenue,
                ],
                'pct_change' => [
                    'orders'  => $pctOrders,
                    'revenue' => $pctRevenue,
                ],
                'trend'            => $trendPoints,
                'status_breakdown' => $statusBreakdown,
                'top_products'     => $topProducts,
                'recent_orders'    => $recentOrders,
                'customers' => [
                    'total'        => $customersTotal,
                    'new_30'       => $customersNew30,
                    'new_7'        => $customersNew7,
                    'returning_30' => $returning30,
                ],
                'vouchers' => [
                    'orders_with_voucher'   => $ordersWithVoucher,
                    'total_discount_given'  => $totalDiscountGiven,
                    'gift_vouchers_sold'    => $giftVouchersSold,
                    'gift_vouchers_pending' => $giftVouchersPending,
                ],
                'cart_abandonment' => [
                    'active_carts'     => $activeCarts,
                    'completed_orders' => $completedOrders30,
                    'abandonment_rate' => $abandonmentRate,
                ],
                'reviews' => [
                    'pending_approval' => $pendingReviews,
                    'average_rating'   => round($avgRating, 1),
                    'total_approved'   => $approvedReviews,
                ],
                'journal' => [
                    'total_posts' => $journalTotal,
                    'total_views' => $journalViews,
                    'top_posts'   => $topPosts,
                ],
            ],
        ]);
    }
}
