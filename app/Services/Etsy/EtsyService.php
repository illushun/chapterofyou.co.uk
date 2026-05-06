<?php

namespace App\Services\Etsy;

use App\Models\MarketplaceConnection;
use App\Models\MarketplaceListing;
use App\Models\MarketplaceProductSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EtsyService
{
    private const BASE_URL  = 'https://api.etsy.com/v3/application';
    private const AUTH_URL  = 'https://www.etsy.com/oauth/connect';
    private const TOKEN_URL = 'https://api.etsy.com/v3/public/oauth/token';
    private const SCOPES    = 'listings_r listings_w listings_d transactions_r shops_r';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientId     = config('services.etsy.client_id', '');
        $this->clientSecret = config('services.etsy.client_secret', '');
        $this->redirectUri  = config('services.etsy.redirect', '');
    }

    // ──────────────────────────────────────────────────
    // OAuth
    // ──────────────────────────────────────────────────

    public function getAuthorizationUrl(): string
    {
        $verifier  = $this->generateCodeVerifier();
        $challenge = $this->generateCodeChallenge($verifier);
        $state     = Str::random(32);

        session([
            'etsy_code_verifier' => $verifier,
            'etsy_state'         => $state,
        ]);

        return self::AUTH_URL . '?' . http_build_query([
            'response_type'         => 'code',
            'redirect_uri'          => $this->redirectUri,
            'scope'                 => self::SCOPES,
            'client_id'             => $this->clientId,
            'state'                 => $state,
            'code_challenge'        => $challenge,
            'code_challenge_method' => 'S256',
        ]);
    }

    public function handleCallback(string $code, string $state): MarketplaceConnection
    {
        if ($state !== session('etsy_state')) {
            throw new Exception('OAuth state mismatch — possible CSRF attempt.');
        }

        $verifier = session('etsy_code_verifier');

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'code'          => $code,
            'code_verifier' => $verifier,
        ]);

        $this->assertOk($response, 'Token exchange failed');

        $tokens = $response->json();
        $connection = $this->upsertConnection($tokens);

        session()->forget(['etsy_code_verifier', 'etsy_state']);

        try {
            $this->fetchAndStoreShopInfo($connection);
        } catch (Exception $e) {
            session()->flash('shop_info_error', $e->getMessage());
        }

        return $connection;
    }

    public function refreshTokenIfNeeded(MarketplaceConnection $connection): MarketplaceConnection
    {
        if (! $connection->isExpired()) {
            return $connection;
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'refresh_token' => $connection->refresh_token,
        ]);

        $this->assertOk($response, 'Token refresh failed');

        $tokens = $response->json();
        $connection->update([
            'access_token'  => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? $connection->refresh_token,
            'expires_at'    => now()->addSeconds($tokens['expires_in'] ?? 3600),
        ]);

        return $connection->fresh();
    }

    // ──────────────────────────────────────────────────
    // Shop info
    // ──────────────────────────────────────────────────

    public function fetchAndStoreShopInfo(MarketplaceConnection $connection): void
    {
        $connection = $this->refreshTokenIfNeeded($connection);
        $client = $this->client($connection);

        $meResponse = $client->get('/users/me');
        Log::info('Etsy /users/me status=' . $meResponse->status() . ' body=' . $meResponse->body());
        $this->assertOk($meResponse, 'Could not fetch Etsy user');

        $userId = $meResponse->json('user_id');

        $shopsResponse = $client->get("/users/{$userId}/shops");
        Log::info('Etsy /users/' . $userId . '/shops status=' . $shopsResponse->status() . ' body=' . $shopsResponse->body());

        if ($shopsResponse->successful() && $shopsResponse->json('shop_id')) {
            $connection->update([
                'etsy_user_id' => $userId,
                'shop_id'      => (string) $shopsResponse->json('shop_id'),
                'shop_name'    => $shopsResponse->json('shop_name'),
            ]);
        } else {
            throw new Exception('Etsy API returned no shop for this account. Response: ' . $shopsResponse->body());
        }
    }

    // ──────────────────────────────────────────────────
    // Product export
    // ──────────────────────────────────────────────────

    public function exportProduct(Product $product): MarketplaceListing
    {
        $connection = $this->requireConnection();
        $setting    = MarketplaceProductSetting::where('product_id', $product->id)
                        ->where('marketplace', 'etsy')
                        ->first();

        $title       = Str::limit($setting?->effectiveTitle($product) ?? $product->name, 140, '');
        $description = $setting ? $setting->effectiveDescription($product) : strip_tags($product->description ?? $product->name);
        $price       = number_format($setting ? $setting->effectivePrice($product) : (float) $product->cost, 2, '.', '');
        $quantity    = max(1, (int) $product->stock_qty);
        $taxonomyId  = config('services.etsy.default_taxonomy_id', 1622);
        $tags        = $setting?->tagsArray() ?? [];

        $payload = [
            'title'       => $title,
            'description' => $description,
            'price'       => $price,
            'quantity'    => $quantity,
            'who_made'    => 'i_did',
            'when_made'   => 'made_to_order',
            'taxonomy_id' => $taxonomyId,
            'state'       => 'draft',
        ];

        if (! empty($tags)) {
            $payload['tags'] = array_slice($tags, 0, 13);
        }

        $response = $this->client($connection)->post("/shops/{$connection->shop_id}/listings", $payload);
        $this->assertOk($response, 'Failed to create Etsy listing');

        $listingId = (string) $response->json('listing_id');

        $this->uploadProductImages($connection, $product, $listingId);

        return MarketplaceListing::updateOrCreate(
            ['product_id' => $product->id, 'marketplace' => 'etsy'],
            [
                'listing_id'     => $listingId,
                'status'         => 'draft',
                'sync_error'     => null,
                'last_synced_at' => now(),
            ]
        );
    }

    public function syncProduct(Product $product): MarketplaceListing
    {
        $connection = $this->requireConnection();
        $listing    = MarketplaceListing::where('product_id', $product->id)
                        ->where('marketplace', 'etsy')
                        ->firstOrFail();

        $setting = MarketplaceProductSetting::where('product_id', $product->id)
                    ->where('marketplace', 'etsy')
                    ->first();

        $tags    = $setting?->tagsArray() ?? [];
        $payload = [
            'title'       => Str::limit($setting?->effectiveTitle($product) ?? $product->name, 140, ''),
            'description' => $setting ? $setting->effectiveDescription($product) : strip_tags($product->description ?? $product->name),
            'price'       => number_format($setting ? $setting->effectivePrice($product) : (float) $product->cost, 2, '.', ''),
            'quantity'    => max(0, (int) $product->stock_qty),
        ];

        if (! empty($tags)) {
            $payload['tags'] = array_slice($tags, 0, 13);
        }

        $response = $this->client($connection)
            ->patch("/listings/{$listing->listing_id}", $payload);

        $this->assertOk($response, 'Failed to sync Etsy listing');

        $listing->update([
            'status'         => 'synced',
            'sync_error'     => null,
            'last_synced_at' => now(),
        ]);

        return $listing->fresh();
    }

    private function uploadProductImages(MarketplaceConnection $connection, Product $product, string $listingId): void
    {
        $images = $product->images()->where('status', 'enabled')->get();
        $rank   = 1;

        foreach ($images as $image) {
            try {
                $imagePath = ltrim(str_replace('/storage', '', $image->image), '/');
                $fullPath  = Storage::disk('public')->path($imagePath);

                if (! file_exists($fullPath)) {
                    continue;
                }

                $this->client($connection)
                    ->attach('image', fopen($fullPath, 'r'), basename($fullPath))
                    ->post("/shops/{$connection->shop_id}/listings/{$listingId}/images", [
                        'rank'       => $rank,
                        'overwrite'  => false,
                        'is_watermarked' => false,
                        'alt_text'   => $product->name,
                    ]);

                $rank++;
            } catch (Exception $e) {
                Log::warning("Etsy: failed to upload image for listing {$listingId}: " . $e->getMessage());
            }
        }
    }

    // ──────────────────────────────────────────────────
    // Order import
    // ──────────────────────────────────────────────────

    public function importNewOrders(): int
    {
        $connection = $this->requireConnection();
        $since      = $connection->last_order_import_at
                        ? $connection->last_order_import_at->timestamp
                        : strtotime('-30 days');

        $receipts = $this->fetchReceipts($connection, $since);
        $imported = 0;

        foreach ($receipts as $receipt) {
            try {
                if ($this->orderAlreadyImported($receipt['receipt_id'])) {
                    continue;
                }

                DB::transaction(function () use ($receipt, $connection) {
                    $this->createOrderFromReceipt($receipt, $connection);
                });

                $imported++;
            } catch (Exception $e) {
                Log::error('Etsy: failed to import receipt ' . $receipt['receipt_id'] . ': ' . $e->getMessage());
            }
        }

        $connection->update(['last_order_import_at' => now()]);

        return $imported;
    }

    private function fetchReceipts(MarketplaceConnection $connection, int $minCreated): array
    {
        $all     = [];
        $offset  = 0;
        $limit   = 100;

        do {
            $response = $this->client($connection)->get("/shops/{$connection->shop_id}/receipts", [
                'was_paid'    => true,
                'min_created' => $minCreated,
                'limit'       => $limit,
                'offset'      => $offset,
            ]);

            if (! $response->successful()) {
                break;
            }

            $results = $response->json('results') ?? [];
            $all     = array_merge($all, $results);
            $count   = $response->json('count') ?? 0;
            $offset += $limit;
        } while (count($all) < $count);

        return $all;
    }

    private function createOrderFromReceipt(array $receipt, MarketplaceConnection $connection): void
    {
        $nameParts = explode(' ', $receipt['name'] ?? 'Etsy Buyer', 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? '';

        $costTotal     = $this->parseAmount($receipt['subtotal'] ?? []);
        $shippingTotal = $this->parseAmount($receipt['total_shipping_cost'] ?? []);
        $taxTotal      = $this->parseAmount($receipt['total_tax_cost'] ?? []);
        $grandTotal    = $this->parseAmount($receipt['grandtotal'] ?? []);

        $order = Order::create([
            'payment_intent_id'    => 'etsy_' . $receipt['receipt_id'],
            'payment_type'         => 'etsy',
            'source'               => 'etsy',
            'marketplace_order_id' => (string) $receipt['receipt_id'],
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'email'                => $receipt['buyer_email'] ?? '',
            'telephone'            => null,
            'cost_total'           => $costTotal,
            'shipping_total'       => $shippingTotal,
            'tax_total'            => $taxTotal,
            'grand_total'          => $grandTotal,
            'billing_line_1'       => $receipt['first_line'] ?? '',
            'billing_line_2'       => $receipt['second_line'] ?? null,
            'billing_city'         => $receipt['city'] ?? '',
            'billing_county'       => $receipt['state'] ?? null,
            'billing_postcode'     => $receipt['zip'] ?? '',
            'billing_country'      => $receipt['country_iso'] ?? 'GB',
            'shipping_line_1'      => $receipt['first_line'] ?? '',
            'shipping_line_2'      => $receipt['second_line'] ?? null,
            'shipping_city'        => $receipt['city'] ?? '',
            'shipping_county'      => $receipt['state'] ?? null,
            'shipping_postcode'    => $receipt['zip'] ?? '',
            'shipping_country'     => $receipt['country_iso'] ?? 'GB',
            'status'               => 'processing',
        ]);

        foreach ($receipt['transactions'] ?? [] as $transaction) {
            $listingId = (string) ($transaction['listing_id'] ?? '');
            $product   = null;

            if ($listingId) {
                $ml = MarketplaceListing::where('marketplace', 'etsy')
                        ->where('listing_id', $listingId)
                        ->first();
                $product = $ml?->product;
            }

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $product?->id,
                'quantity'     => (int) ($transaction['quantity'] ?? 1),
                'product_cost' => $this->parseAmount($transaction['price'] ?? []),
                'product_total' => $this->parseAmount($transaction['price'] ?? []) * (int) ($transaction['quantity'] ?? 1),
            ]);
        }
    }

    private function orderAlreadyImported(int $receiptId): bool
    {
        return Order::where('marketplace_order_id', (string) $receiptId)
                    ->where('source', 'etsy')
                    ->exists();
    }

    private function parseAmount(array $amountObject): float
    {
        if (empty($amountObject)) {
            return 0.0;
        }

        $amount  = (float) ($amountObject['amount'] ?? 0);
        $divisor = (float) ($amountObject['divisor'] ?? 100);

        return $divisor > 0 ? round($amount / $divisor, 2) : 0.0;
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    private function client(MarketplaceConnection $connection): \Illuminate\Http\Client\PendingRequest
    {
        $connection = $this->refreshTokenIfNeeded($connection);

        return Http::baseUrl(self::BASE_URL)
            ->withToken($connection->access_token)
            ->withHeaders(['x-api-key' => $this->clientId . ':' . $this->clientSecret]);
    }

    private function requireConnection(): MarketplaceConnection
    {
        $connection = MarketplaceConnection::where('marketplace', 'etsy')->first();

        if (! $connection) {
            throw new Exception('No Etsy connection found. Please connect your Etsy shop first.');
        }

        if (! $connection->shop_id) {
            throw new Exception('Etsy shop ID is missing. Please disconnect and reconnect your Etsy shop.');
        }

        return $connection;
    }

    private function upsertConnection(array $tokens): MarketplaceConnection
    {
        return MarketplaceConnection::updateOrCreate(
            ['marketplace' => 'etsy'],
            [
                'access_token'  => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_at'    => isset($tokens['expires_in'])
                    ? now()->addSeconds($tokens['expires_in'])
                    : null,
                'scopes'        => $tokens['scope'] ?? null,
            ]
        );
    }

    private function assertOk(Response $response, string $context): void
    {
        if (! $response->successful()) {
            $body = $response->body();
            Log::error("Etsy API error [{$context}]: " . $body);
            throw new Exception("{$context}: " . ($response->json('error_description') ?? $response->json('error') ?? $body));
        }
    }

    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}
