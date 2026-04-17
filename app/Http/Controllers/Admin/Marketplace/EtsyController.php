<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Http\Controllers\Controller;
use App\Jobs\ImportEtsyOrders;
use App\Models\MarketplaceConnection;
use App\Models\MarketplaceListing;
use App\Models\MarketplaceProductSetting;
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
                'enabled_count'  => MarketplaceProductSetting::where('marketplace', 'etsy')->where('enabled', true)->count(),
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
    // Products list
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

        if ($request->filled('filter')) {
            match ($request->filter) {
                'enabled'      => $query->whereHas('etsySetting', fn ($q) => $q->where('enabled', true)),
                'not_exported' => $query->whereHas('etsySetting', fn ($q) => $q->where('enabled', true))
                                        ->whereDoesntHave('etsyListing'),
                'exported'     => $query->whereHas('etsyListing'),
                default        => null,
            };
        }

        $products = $query->paginate(20)->withQueryString();
        $ids      = $products->pluck('id');

        $listingMap = MarketplaceListing::where('marketplace', 'etsy')
            ->whereIn('product_id', $ids)->get()->keyBy('product_id');

        $settingMap = MarketplaceProductSetting::where('marketplace', 'etsy')
            ->whereIn('product_id', $ids)->get()->keyBy('product_id');

        $products->getCollection()->transform(function ($product) use ($listingMap, $settingMap) {
            $listing = $listingMap->get($product->id);
            $setting = $settingMap->get($product->id);

            $product->etsy_listing = $listing ? [
                'listing_id'     => $listing->listing_id,
                'status'         => $listing->status,
                'last_synced_at' => $listing->last_synced_at?->toISOString(),
                'sync_error'     => $listing->sync_error,
            ] : null;

            $product->etsy_setting = $setting ? [
                'enabled'              => $setting->enabled,
                'has_overrides'        => (bool) ($setting->override_title || $setting->override_description || $setting->override_price),
                'override_title'       => $setting->override_title,
                'override_price'       => $setting->override_price,
            ] : null;

            return $product;
        });

        return Inertia::render('admin/marketplace/etsy/Products', [
            'products'   => $products,
            'filters'    => $request->only(['search', 'filter']),
            'connection' => $this->connectionSummary(),
        ]);
    }

    // ──────────────────────────────────────────────────
    // Product enable / disable
    // ──────────────────────────────────────────────────

    public function toggleProduct(Product $product): RedirectResponse
    {
        $setting = MarketplaceProductSetting::firstOrCreate(
            ['product_id' => $product->id, 'marketplace' => 'etsy'],
            ['enabled' => false]
        );

        $setting->update(['enabled' => ! $setting->enabled]);

        $state = $setting->enabled ? 'enabled' : 'disabled';

        return back()->with('success', "'{$product->name}' {$state} for Etsy.");
    }

    // ──────────────────────────────────────────────────
    // Product settings (overrides)
    // ──────────────────────────────────────────────────

    public function productSettings(Product $product): Response
    {
        $product->load(['images:product_id,image,status']);

        $setting = MarketplaceProductSetting::firstOrCreate(
            ['product_id' => $product->id, 'marketplace' => 'etsy'],
            ['enabled' => true]
        );

        $listing = MarketplaceListing::where('product_id', $product->id)
            ->where('marketplace', 'etsy')
            ->first();

        return Inertia::render('admin/marketplace/etsy/ProductSettings', [
            'product' => [
                'id'          => $product->id,
                'mpn'         => $product->mpn,
                'name'        => $product->name,
                'description' => $product->description,
                'cost'        => $product->cost,
                'stock_qty'   => $product->stock_qty,
                'images'      => $product->images,
            ],
            'setting' => [
                'enabled'              => $setting->enabled,
                'override_title'       => $setting->override_title ?? '',
                'override_description' => $setting->override_description ?? '',
                'override_price'       => $setting->override_price,
                'override_tags'        => $setting->override_tags ?? '',
            ],
            'listing' => $listing ? [
                'listing_id'     => $listing->listing_id,
                'status'         => $listing->status,
                'last_synced_at' => $listing->last_synced_at?->toISOString(),
                'sync_error'     => $listing->sync_error,
            ] : null,
            'connection' => $this->connectionSummary(),
        ]);
    }

    public function saveProductSettings(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'enabled'              => ['required', 'boolean'],
            'override_title'       => ['nullable', 'string', 'max:140'],
            'override_description' => ['nullable', 'string'],
            'override_price'       => ['nullable', 'numeric', 'min:0'],
            'override_tags'        => ['nullable', 'string', 'max:500'],
        ]);

        MarketplaceProductSetting::updateOrCreate(
            ['product_id' => $product->id, 'marketplace' => 'etsy'],
            [
                'enabled'              => $data['enabled'],
                'override_title'       => $data['override_title'] ?: null,
                'override_description' => $data['override_description'] ?: null,
                'override_price'       => $data['override_price'] ?: null,
                'override_tags'        => $data['override_tags'] ?: null,
            ]
        );

        return back()->with('success', 'Etsy settings saved.');
    }

    // ──────────────────────────────────────────────────
    // Export / Sync / Unlink
    // ──────────────────────────────────────────────────

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

        return ['shop_name' => $c->shop_name, 'shop_id' => $c->shop_id];
    }
}
