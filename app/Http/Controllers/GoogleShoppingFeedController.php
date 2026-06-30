<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class GoogleShoppingFeedController extends Controller
{
    private const WEBSITE_URL  = 'https://www.chapterofyou.co.uk';
    private const BRAND        = 'Chapter of You';
    private const CURRENCY     = 'GBP';
    // Google taxonomy ID for Home Fragrances / Reed Diffusers
    private const PRODUCT_CAT  = 'Home &amp; Garden &gt; Decor &gt; Candles &amp; Home Fragrances &gt; Home Fragrances';
    private const VAT_RATE     = 1.20;

    public function __invoke(): Response
    {
        $xml = cache()->remember('google-shopping-feed', now()->addHours(4), function () {
            $products = Product::with([
                    'images:product_id,image',
                    'seo:product_id,slug',
                ])
                ->where('status', 'enabled')
                ->whereNull('parent_product_id')
                ->whereHas('seo', fn ($q) => $q->whereNotNull('slug'))
                ->orderBy('id')
                ->get();

            return $this->buildFeed($products);
        });

        return response($xml, 200, [
            'Content-Type'  => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=14400',
        ]);
    }

    private function buildFeed($products): string
    {
        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
        $lines[] = '  <channel>';
        $lines[] = '    <title>' . self::BRAND . '</title>';
        $lines[] = '    <link>' . self::WEBSITE_URL . '</link>';
        $lines[] = '    <description>Luxury handmade reed diffusers crafted to order in the UK</description>';

        foreach ($products as $product) {
            $lines[] = $this->buildItem($product);
        }

        $lines[] = '  </channel>';
        $lines[] = '</rss>';

        return implode("\n", $lines);
    }

    private function buildItem(Product $product): string
    {
        $slug        = $product->seo->slug;
        $productUrl  = self::WEBSITE_URL . '/product/' . $slug;
        $priceGross  = number_format($product->cost * self::VAT_RATE, 2, '.', '');
        $availability = $product->stock_qty > 0 ? 'in stock' : 'out of stock';

        // Strip HTML and truncate description (Google max is 5000 chars, 500 recommended)
        $description = strip_tags($product->description);
        $description = preg_replace('/\s+/', ' ', trim($description));
        $description = mb_substr($description, 0, 500);

        // Images — first is main, rest go into additional_image_link (Google allows up to 10)
        $images      = $product->images->map(fn ($img) =>
            str_starts_with($img->image, 'http') ? $img->image : self::WEBSITE_URL . $img->image
        )->values();

        $mainImage   = $images->first() ?? '';
        $extraImages = $images->slice(1)->take(9);

        $lines = [];
        $lines[] = '    <item>';
        $lines[] = '      <g:id>'           . $this->e($product->id)         . '</g:id>';
        $lines[] = '      <g:title>'        . $this->e($product->name)       . '</g:title>';
        $lines[] = '      <g:description>'  . $this->e($description)         . '</g:description>';
        $lines[] = '      <g:link>'         . $this->e($productUrl)          . '</g:link>';

        if ($mainImage) {
            $lines[] = '      <g:image_link>' . $this->e($mainImage) . '</g:image_link>';
        }

        foreach ($extraImages as $img) {
            $lines[] = '      <g:additional_image_link>' . $this->e($img) . '</g:additional_image_link>';
        }

        $lines[] = '      <g:availability>'          . $availability                  . '</g:availability>';
        $lines[] = '      <g:price>'                  . $priceGross . ' ' . self::CURRENCY . '</g:price>';
        $lines[] = '      <g:brand>'                  . $this->e(self::BRAND)          . '</g:brand>';
        $lines[] = '      <g:condition>'              . 'new'                           . '</g:condition>';
        $lines[] = '      <g:mpn>'                    . $this->e($product->mpn)         . '</g:mpn>';
        $lines[] = '      <g:google_product_category>'. self::PRODUCT_CAT               . '</g:google_product_category>';

        // Handmade products typically don't have GTINs; tell Google not to require one
        $lines[] = '      <g:identifier_exists>no</g:identifier_exists>';

        $lines[] = '    </item>';

        return implode("\n", $lines);
    }

    private function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
