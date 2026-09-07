<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JournalPost;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('publicRoutes')]
    public function test_public_pages_render(string $route): void
    {
        $this->get(route($route))->assertOk();
    }

    public static function publicRoutes(): array
    {
        return array_map(fn ($route) => [$route], [
            'home', 'about', 'contact', 'delivery', 'terms', 'privacy', 'returns',
            'products', 'gift-vouchers.index', 'journal.index', 'scent-finder.index', 'cart.view',
        ]);
    }

    public function test_recent_posts_include_the_correct_reading_time(): void
    {
        JournalPost::create([
            'title' => 'Test article', 'slug' => 'test-article', 'body' => str_repeat('word ', 650),
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $this->get(route('home'))->assertInertia(fn (Assert $page) => $page
            ->has('recentJournalPosts', 1)
            ->where('recentJournalPosts.0.reading_time', 4));
    }

    public function test_products_use_a_visible_and_valid_default_sort(): void
    {
        Product::create([
            'mpn' => 'ZED', 'name' => 'Zest', 'description' => 'Test',
            'cost' => 10, 'stock_qty' => 1, 'status' => 'enabled',
        ]);
        Product::create([
            'mpn' => 'ALP', 'name' => 'Alpine', 'description' => 'Test',
            'cost' => 10, 'stock_qty' => 1, 'status' => 'enabled',
        ]);

        $this->get(route('products', ['sort' => 'invalid,desc']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'name,asc')
                ->where('products.data.0.name', 'Alpine')
                ->where('products.data.1.name', 'Zest'));
    }

    public function test_products_can_be_filtered_with_a_shareable_category_slug(): void
    {
        $category = Category::create([
            'name' => 'Luxury Gifts', 'slug' => 'luxury-gifts', 'status' => 'enabled',
        ]);
        $matching = Product::create([
            'mpn' => 'GIFT', 'name' => 'Gift Diffuser', 'description' => 'Test',
            'cost' => 20, 'stock_qty' => 1, 'status' => 'enabled',
        ]);
        Product::create([
            'mpn' => 'OTHER', 'name' => 'Other Diffuser', 'description' => 'Test',
            'cost' => 20, 'stock_qty' => 1, 'status' => 'enabled',
        ]);
        $matching->categories()->attach($category);

        $this->get(route('products', ['category' => 'luxury-gifts']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.categories', ['luxury-gifts'])
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Gift Diffuser'));
    }
}
