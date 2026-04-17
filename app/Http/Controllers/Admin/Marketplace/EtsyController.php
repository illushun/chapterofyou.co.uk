<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Http\Controllers\Controller;
use App\Jobs\ImportEtsyOrders;
use App\Models\MarketplaceConnection;
use App\Models\MarketplaceListing;
use App\Models\Order;
use App\Models\Product;
use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EtsyController extends Controller
{
    public function __construct(private readonly EtsyService $etsy) {}

    // ──────────────────────────────────────────────────
    // Overview
    // ──────────────────────────────────────────────────

    public function index(): Response
    {
        $connection = MarketplaceConnection::where('marketplace', 'etsy')->first();

        $stats = null;
        if ($connection) {
            $stats = [
                'listings_count' => MarketplaceListing::where('marketplace', 'etsy')->count(),
                'orders_count'   => Order::where('source', 'etsy')->count(),
                'last_import'    => $connection->last_order_import_at?->toISOString(),
            ];
        }

        return Inertia::render('admin/marketplace/Index', [
            'connection' => $connection ? [
                'shop_name'  => $connection->shop_name,
                'shop_id'    => $connection->shop_id,
                'scopes'     => $connection->scopes,
                'expires_at' => $connection->expires_at?->toISOString(),
            ] : null,
            'stats' => $stats,
        ]);
    }

    // ──────────────────────────────────────────────────
    // OAuth
    // ──────────────────────────────────────────────────

    public function connect(): RedirectResponse
    {
        $url = $this->etsy->getAuthorizationUrl();
        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('admin.marketplace.etsy.index')
                ->with('error', 'Etsy connection was denied: ' . $request->error_description);
        }

        try {
            $this->etsy->handleCallback($request->code, $request->state);
        } catch (Exception $e) {
            return redirect()->route('admin.marketplace.etsy.index')
                ->with('error', 'Could not connect to Etsy: ' . $e->getMessage());
        }

        return redirect()->route('admin.marketplace.etsy.index')
            ->with('success', 'Etsy shop connected successfully!');
    }

    public function disconnect(): RedirectResponse
    {
        MarketplaceConnection::where('marketplace', 'etsy')->delete();

        return redirect()->route('admin.marketplace.etsy.index')
            ->with('success', 'Etsy shop disconnected.');
    }

    // ──────────────────────────────────────────────────
    // Products
    // ──────────────────────────────────────────────────

    public function products(Request $request): Response
    {
        $query = Product::with(['images:product_id,image,status'])
            ->select('id', 'mpn', 'name', 'cost', 'stock_qty', 'status')
            ->whereNull('parent_product_id')
            ->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('mpn', 'like', "%{$s}%");
            });
        }

        $products = $query->paginate(20)->withQueryString();

        $listingMap = MarketplaceListing::where('marketplace', 'etsy')
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $products->getCollection()->transform(function ($product) use ($listingMap) {
            $listing = $listingMap->get($product->id);
            $product->etsy_listing = $listing ? [
                'listing_id'     => $listing->listing_id,
                'status'         => $listing->status,
                'last_synced_at' => $listing->last_synced_at?->toISOString(),
                'sync_error'     => $listing->sync_error,
            ] : null;
            return $product;
        });

        return Inertia::render('admin/marketplace/etsy/Products', [
            'products'   => $products,
            'filters'    => $request->only('search'),
            'connection' => $this->connectionSummary(),
        ]);
    }

    public function exportProduct(Product $product): RedirectResponse
    {
        try {
            $listing = $this->etsy->exportProduct($product);
        } catch (Exception $e) {
            MarketplaceListing::updateOrCreate(
                ['product_id' => $product->id, 'marketplace' => 'etsy'],
                ['status' => 'error', 'sync_error' => $e->getMessage()]
            );
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }

        return back()->with('success', "'{$product->name}' exported to Etsy as a draft listing (#{$listing->listing_id}).");
    }

    public function syncProduct(Product $product): RedirectResponse
    {
        try {
            $this->etsy->syncProduct($product);
        } catch (Exception $e) {
            MarketplaceListing::where('product_id', $product->id)
                ->where('marketplace', 'etsy')
                ->update(['status' => 'error', 'sync_error' => $e->getMessage()]);
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }

        return back()->with('success', "'{$product->name}' synced to Etsy.");
    }

    public function unlinkProduct(Product $product): RedirectResponse
    {
        MarketplaceListing::where('product_id', $product->id)
            ->where('marketplace', 'etsy')
            ->delete();

        return back()->with('success', "'{$product->name}' unlinked from Etsy.");
    }

    // ──────────────────────────────────────────────────
    // Orders
    // ──────────────────────────────────────────────────

    public function orders(Request $request): Response
    {
        $query = Order::with('items.product:id,mpn,name')
            ->where('source', 'etsy')
            ->select('id', 'marketplace_order_id', 'first_name', 'last_name', 'email',
                     'grand_total', 'status', 'created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('marketplace_order_id', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%");
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('admin/marketplace/etsy/Orders', [
            'orders'     => $orders,
            'filters'    => $request->only(['status', 'search']),
            'statuses'   => \App\Http\Controllers\Admin\AdminOrderController::STATUSES,
            'connection' => $this->connectionSummary(),
        ]);
    }

    public function importOrders(): RedirectResponse
    {
        try {
            ImportEtsyOrders::dispatch();
        } catch (Exception $e) {
            return back()->with('error', 'Could not start import: ' . $e->getMessage());
        }

        return back()->with('success', 'Etsy order import started. New orders will appear shortly.');
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    private function connectionSummary(): ?array
    {
        $c = MarketplaceConnection::where('marketplace', 'etsy')->first();
        if (! $c) return null;

        return [
            'shop_name' => $c->shop_name,
            'shop_id'   => $c->shop_id,
        ];
    }
}
