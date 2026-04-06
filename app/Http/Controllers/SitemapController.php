<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Generate and return the XML sitemap.
     *
     * Includes:
     *   - Static pages (homepage, products listing, contact, about)
     *   - All enabled, in-stock or recently updated products (via SEO slug)
     *   - All enabled categories
     *
     * Google recommends keeping sitemaps under 50,000 URLs / 50MB.
     * If the catalogue grows very large, split into a sitemap index.
     */
    public function index(): Response
    {
        $urls = collect();
        $xml = cache()->remember('sitemap.xml', now()->addHour(), function () use ($urls) {
            // ── Static pages ──────────────────────────────────────────────────
            $staticPages = [
                [
                    'loc'        => url('/'),
                    'priority'   => '1.0',
                    'changefreq' => 'weekly',
                    'lastmod'    => Carbon::now()->toAtomString(),
                ],
                [
                    'loc'        => url('/products'),
                    'priority'   => '0.9',
                    'changefreq' => 'daily',
                    'lastmod'    => Carbon::now()->toAtomString(),
                ],
                [
                    'loc'        => url('/contact'),
                    'priority'   => '0.5',
                    'changefreq' => 'monthly',
                    'lastmod'    => Carbon::now()->toAtomString(),
                ],
                [
                    'loc'        => url('/about'),
                    'priority'   => '0.5',
                    'changefreq' => 'monthly',
                    'lastmod'    => Carbon::now()->toAtomString(),
                ],
                [
                    'loc'        => url('/delivery'),
                    'priority'   => '0.6',
                    'changefreq' => 'monthly',
                    'lastmod'    => Carbon::now()->toAtomString(),
                ],
            ];

            foreach ($staticPages as $page) {
                $urls->push($page);
            }

            // ── Products ──────────────────────────────────────────────────────
            // Only include top-level enabled products that have an SEO slug.
            // Variations (parent_product_id != null) are excluded — they share
            // the parent's URL and including them would create duplicate content.
            $products = Product::query()
                ->select('id', 'updated_at')
                ->with('seo:product_id,slug')
                ->where('status', 'enabled')
                ->whereNull('parent_product_id')
                ->whereHas('seo', fn ($q) => $q->whereNotNull('slug'))
                ->orderByDesc('updated_at')
                ->get();

            foreach ($products as $product) {
                $slug = $product->seo->slug;
                $urls->push([
                    'loc'        => url("/product/{$slug}"),
                    'priority'   => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod'    => $product->updated_at->toAtomString(),
                ]);
            }

            // ── Categories ────────────────────────────────────────────────────
            $categories = Category::query()
                ->select('id', 'name', 'updated_at')
                ->where('status', 'enabled')
                ->orderByDesc('updated_at')
                ->get();

            foreach ($categories as $category) {
                $urls->push([
                    'loc'        => url("/products?categories={$category->id}"),
                    'priority'   => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod'    => $category->updated_at->toAtomString(),
                ]);
            }

            return $this->buildXml($urls->all());
        });

        return response($xml, 200, [
            'Content-Type'  => 'application/xml',
            'Cache-Control' => 'public, max-age=3600', // Cache for 1 hour
        ]);
    }

    /**
     * Build the sitemap XML string.
     */
    private function buildXml(array $urls): string
    {
        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . e($url['loc']) . '</loc>';

            if (!empty($url['lastmod'])) {
                $lines[] = '    <lastmod>' . e($url['lastmod']) . '</lastmod>';
            }
            if (!empty($url['changefreq'])) {
                $lines[] = '    <changefreq>' . e($url['changefreq']) . '</changefreq>';
            }
            if (!empty($url['priority'])) {
                $lines[] = '    <priority>' . e($url['priority']) . '</priority>';
            }

            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }
}
