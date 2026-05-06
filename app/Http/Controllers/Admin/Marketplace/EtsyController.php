<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Http\Controllers\Controller;
use App\Jobs\ImportEtsyOrders;
use App\Models\MarketplaceConnection;
use App\Models\MarketplaceListing;
use App\Models\MarketplaceProductSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EtsyController extends Controller
{
    public function __construct(private readonly EtsyService $etsy) {}

    // ──────────────────────────────────────────────────
    // Overview
    // ──────────────────────────────────────────────────

    public function index(): Response
    {
        $connection = MarketplaceConnection::where('marketplace', 'etsy')->first();

        $stats            = null;
        $shippingProfiles = [];

        if ($connection) {
            $stats = [
                'listings_count' => MarketplaceListing::where('marketplace', 'etsy')->count(),
                'orders_count'   => Order::where('source', 'etsy')->count(),
                'enabled_count'  => MarketplaceProductSetting::where('marketplace', 'etsy')->where('enabled', true)->count(),
                'last_import'    => $connection->last_order_import_at?->toISOString(),
            ];

            $shippingProfiles = $this->etsy->getShippingProfiles($connection);
        }

        return Inertia::render('admin/marketplace/Index', [
            'connection' => $connection ? [
                'shop_name'                   => $connection->shop_name,
                'shop_id'                     => $connection->shop_id,
                'scopes'                      => $connection->scopes,
                'expires_at'                  => $connection->expires_at?->toISOString(),
                'default_shipping_profile_id' => $connection->default_shipping_profile_id,
            ] : null,
            'stats'             => $stats,
            'shipping_profiles' => $shippingProfiles,
        ]);
    }

    public function saveShippingProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipping_profile_id' => ['required', 'string'],
        ]);

        MarketplaceConnection::where('marketplace', 'etsy')
            ->update(['default_shipping_profile_id' => $data['shipping_profile_id']]);

        return back()->with('success', 'Default shipping profile saved.');
    }

    public function linkExistingListing(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'listing_id' => ['required', 'string'],
        ]);

        MarketplaceListing::updateOrCreate(
            ['product_id' => $product->id, 'marketplace' => 'etsy'],
            [
                'listing_id'     => $data['listing_id'],
                'status'         => 'synced',
                'sync_error'     => null,
                'last_synced_at' => now(),
            ]
        );

        return back()->with('success', "Listing #{$data['listing_id']} linked to '{$product->name}'.");
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

        if ($shopInfoError = session()->pull('shop_info_error')) {
            return redirect()->route('admin.marketplace.etsy.index')
                ->with('warning', 'Etsy token saved, but could not fetch shop info: ' . $shopInfoError);
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

    public function refreshShopInfo(): RedirectResponse
    {
        $connection = MarketplaceConnection::where('marketplace', 'etsy')->first();

        if (! $connection) {
            return back()->with('error', 'No Etsy connection found.');
        }

        try {
            $this->etsy->fetchAndStoreShopInfo($connection);
        } catch (Exception $e) {
            return back()->with('error', 'Could not fetch shop info: ' . $e->getMessage());
        }

        return back()->with('success', 'Shop info refreshed: ' . $connection->fresh()->shop_name);
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
    // Products CSV export (manual / no API required)
    // ──────────────────────────────────────────────────

    public function exportProductsCsv(): StreamedResponse
    {
        $settings = MarketplaceProductSetting::where('marketplace', 'etsy')
            ->where('enabled', true)
            ->with(['product' => fn ($q) => $q->select('id', 'mpn', 'name', 'description', 'cost', 'stock_qty')])
            ->get()
            ->filter(fn ($s) => $s->product !== null);

        $filename = 'etsy-listings-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($settings) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, ['TITLE', 'DESCRIPTION', 'PRICE', 'CURRENCY_CODE', 'QUANTITY', 'SKU', 'TAGS', 'WHO_MADE', 'IS_SUPPLY', 'WHEN_MADE']);

            foreach ($settings as $setting) {
                $product = $setting->product;
                fputcsv($out, [
                    $setting->effectiveTitle($product),
                    $setting->effectiveDescription($product),
                    number_format($setting->effectivePrice($product), 2, '.', ''),
                    'GBP',
                    max(1, (int) $product->stock_qty),
                    $product->mpn,
                    implode(', ', array_slice($setting->tagsArray(), 0, 13)),
                    'i_did',
                    'FALSE',
                    'made_to_order',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ──────────────────────────────────────────────────
    // Orders CSV import (manual / no API required)
    // ──────────────────────────────────────────────────

    public function importOrdersCsv(): Response
    {
        $result = session()->pull('etsy_csv_import_result');

        return Inertia::render('admin/marketplace/etsy/ImportOrdersCsv', [
            'connection' => $this->connectionSummary(),
            'result'     => $result,
        ]);
    }

    public function processOrdersCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        $handle = fopen($request->file('csv_file')->getPathname(), 'r');
        $raw    = fgetcsv($handle);

        if (! $raw) {
            fclose($handle);
            return back()->with('error', 'Could not read the CSV. Make sure it is a valid Etsy orders CSV.');
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $raw);

        $findCol = function (array $aliases) use ($headers): int|false {
            foreach ($aliases as $alias) {
                $idx = array_search(strtolower($alias), $headers);
                if ($idx !== false) return (int) $idx;
            }
            return false;
        };

        $col = [
            'order_id'    => $findCol(['order id', 'order_id']),
            'buyer_name'  => $findCol(['buyers name', 'buyer name', 'buyers_name', 'buyer_name']),
            'buyer_email' => $findCol(['buyer email', 'buyer_email']),
            'sku'         => $findCol(['sku']),
            'quantity'    => $findCol(['quantity', 'qty']),
            'price'       => $findCol(['price']),
            'shipping'    => $findCol(['order shipping', 'order_shipping', 'shipping']),
            'tax'         => $findCol(['order sales tax', 'order_sales_tax', 'sales tax', 'tax']),
            'order_total' => $findCol(['order total', 'order_total', 'total']),
            'address1'    => $findCol(['ship address1', 'ship address 1', 'ship_address1']),
            'address2'    => $findCol(['ship address2', 'ship address 2', 'ship_address2']),
            'city'        => $findCol(['ship city', 'ship_city']),
            'state'       => $findCol(['ship state', 'ship_state']),
            'zipcode'     => $findCol(['ship zipcode', 'ship zip', 'ship_zipcode', 'ship postcode']),
            'country'     => $findCol(['ship country', 'ship_country']),
            'listing_id'  => $findCol(['listing id', 'listing_id']),
        ];

        if ($col['order_id'] === false) {
            fclose($handle);
            return back()->with('error', 'Could not find an "Order ID" column. Please upload an unmodified Etsy orders CSV.');
        }

        $orderRows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;
            $orderId = trim($row[$col['order_id']] ?? '');
            if (! $orderId) continue;
            $orderRows[$orderId][] = $row;
        }
        fclose($handle);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($orderRows as $orderId => $rows) {
            if (Order::where('marketplace_order_id', $orderId)->where('source', 'etsy')->exists()) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($orderId, $rows, $col, &$imported) {
                    $first = $rows[0];
                    $get   = fn (int|false $i) => $i !== false ? trim($first[$i] ?? '') : '';

                    $nameParts = array_pad(explode(' ', $get($col['buyer_name']), 2), 2, '');
                    $firstName = $nameParts[0] ?: 'Etsy';
                    $lastName  = $nameParts[1] ?: 'Buyer';

                    $shipping   = $this->parseCsvAmount($get($col['shipping']));
                    $tax        = $this->parseCsvAmount($get($col['tax']));
                    $grandTotal = $this->parseCsvAmount($get($col['order_total']));
                    $costTotal  = max(0, round($grandTotal - $shipping - $tax, 2));
                    $country    = $this->resolveCountryCode($get($col['country']));

                    $order = Order::create([
                        'payment_intent_id'    => 'etsy_csv_' . $orderId,
                        'payment_type'         => 'etsy',
                        'source'               => 'etsy',
                        'marketplace_order_id' => $orderId,
                        'first_name'           => $firstName,
                        'last_name'            => $lastName,
                        'email'                => $get($col['buyer_email']),
                        'telephone'            => null,
                        'cost_total'           => $costTotal,
                        'shipping_total'       => $shipping,
                        'tax_total'            => $tax,
                        'grand_total'          => $grandTotal,
                        'billing_line_1'       => $get($col['address1']),
                        'billing_line_2'       => $get($col['address2']) ?: null,
                        'billing_city'         => $get($col['city']),
                        'billing_county'       => $get($col['state']) ?: null,
                        'billing_postcode'     => $get($col['zipcode']),
                        'billing_country'      => $country,
                        'shipping_line_1'      => $get($col['address1']),
                        'shipping_line_2'      => $get($col['address2']) ?: null,
                        'shipping_city'        => $get($col['city']),
                        'shipping_county'      => $get($col['state']) ?: null,
                        'shipping_postcode'    => $get($col['zipcode']),
                        'shipping_country'     => $country,
                        'status'               => 'processing',
                    ]);

                    foreach ($rows as $row) {
                        $getRow    = fn (int|false $i) => $i !== false ? trim($row[$i] ?? '') : '';
                        $listingId = $getRow($col['listing_id']);
                        $sku       = $getRow($col['sku']);
                        $product   = null;

                        if ($listingId) {
                            $ml      = MarketplaceListing::where('marketplace', 'etsy')->where('listing_id', $listingId)->first();
                            $product = $ml?->product;
                        }

                        if (! $product && $sku) {
                            $product = Product::where('mpn', $sku)->first();
                        }

                        $qty   = max(1, (int) ($getRow($col['quantity']) ?: 1));
                        $price = $this->parseCsvAmount($getRow($col['price']));

                        OrderItem::create([
                            'order_id'      => $order->id,
                            'product_id'    => $product?->id,
                            'quantity'      => $qty,
                            'product_cost'  => $price,
                            'product_total' => round($price * $qty, 2),
                        ]);
                    }

                    $imported++;
                });
            } catch (Exception $e) {
                $errors[] = "Order #{$orderId}: " . $e->getMessage();
            }
        }

        session()->put('etsy_csv_import_result', [
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);

        return redirect()->route('admin.marketplace.etsy.orders.import-csv')
            ->with('success', "Imported {$imported} order(s), skipped {$skipped} duplicate(s).");
    }

    private function parseCsvAmount(string $value): float
    {
        $clean = preg_replace('/[^\d.\-]/', '', $value);
        return (float) ($clean ?: '0');
    }

    private function resolveCountryCode(string $name): string
    {
        $map = [
            'united kingdom' => 'GB', 'great britain' => 'GB', 'england' => 'GB',
            'scotland' => 'GB', 'wales' => 'GB', 'uk' => 'GB',
            'united states' => 'US', 'united states of america' => 'US', 'usa' => 'US',
            'canada' => 'CA', 'australia' => 'AU', 'new zealand' => 'NZ',
            'germany' => 'DE', 'france' => 'FR', 'ireland' => 'IE',
            'netherlands' => 'NL', 'italy' => 'IT', 'spain' => 'ES',
            'belgium' => 'BE', 'sweden' => 'SE', 'norway' => 'NO',
            'denmark' => 'DK', 'finland' => 'FI', 'switzerland' => 'CH',
            'austria' => 'AT', 'portugal' => 'PT', 'japan' => 'JP',
        ];

        $lower = strtolower(trim($name));
        if (isset($map[$lower])) return $map[$lower];
        if (strlen($name) === 2) return strtoupper($name);
        return 'GB';
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
