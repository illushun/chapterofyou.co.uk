<?php

namespace App\Services\ScentFinder;

use App\Models\Product;
use Illuminate\Support\Collection;

class ScentFinderService
{
    private const SCENT_FAMILY_POINTS = 10;

    private const MOOD_TAG_POINTS = 5;

    private const ROOM_POINTS = 8;

    private const RESULT_LIMIT = 4;

    public function match(array $answers): Collection
    {
        $wantedFamilies = $answers['scent_families'] ?? [];
        $wantedMoods = $answers['mood_tags'] ?? [];
        $wantedRooms = $answers['room_tags'] ?? [];

        return Product::with('categories', 'images', 'reviews', 'seo:product_id,slug')
            ->withCount('uniqueViews')
            ->whereNull('parent_product_id')
            ->where('status', 'enabled')
            ->where('stock_qty', '>', 0)
            ->whereHas('oils')
            ->get()
            ->map(function (Product $product) use ($wantedFamilies, $wantedMoods, $wantedRooms) {
                $product->match_score = $this->score($product, $wantedFamilies, $wantedMoods, $wantedRooms);

                return $product;
            })
            ->filter(fn (Product $product) => $product->match_score > 0)
            ->sortByDesc(fn (Product $product) => ($product->match_score * 1_000_000) + $product->total_unique_views)
            ->take(self::RESULT_LIMIT)
            ->values();
    }

    /** @param array<string> $wantedFamilies @param array<string> $wantedMoods @param array<string> $wantedRooms */
    private function score(Product $product, array $wantedFamilies, array $wantedMoods, array $wantedRooms): int
    {
        $score = 0;

        $score += count(array_intersect($wantedFamilies, $product->scent_families_array)) * self::SCENT_FAMILY_POINTS;
        $score += count(array_intersect($wantedMoods, $product->mood_tags_array)) * self::MOOD_TAG_POINTS;
        $score += count(array_intersect($wantedRooms, $product->room_tags_array)) * self::ROOM_POINTS;

        return $score;
    }
}
