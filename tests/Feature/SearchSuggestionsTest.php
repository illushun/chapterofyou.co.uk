<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JournalPost;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_case_insensitive_results_from_each_content_type(): void
    {
        $product = Product::create([
            'mpn' => 'ROSE-01', 'name' => 'Velvet Rose', 'description' => 'Test',
            'cost' => 24, 'stock_qty' => 3, 'status' => 'enabled',
        ]);
        Category::create(['name' => 'Rose Collection', 'slug' => 'rose-collection', 'status' => 'enabled']);
        JournalPost::create([
            'title' => 'How to style rose fragrance', 'slug' => 'style-rose-fragrance',
            'body' => 'Test', 'status' => 'published', 'published_at' => now()->subMinute(),
        ]);

        $response = $this->getJson(route('search.suggestions', ['q' => 'rOsE']));

        $response->assertOk()
            ->assertJsonPath('results.0.title', $product->name)
            ->assertJsonFragment(['title' => 'Rose Collection', 'type' => 'category'])
            ->assertJsonFragment(['title' => 'How to style rose fragrance', 'type' => 'journal']);
    }

    public function test_search_hides_unavailable_and_unpublished_content(): void
    {
        Product::create([
            'mpn' => 'SECRET', 'name' => 'Secret Scent', 'description' => 'Test',
            'cost' => 24, 'stock_qty' => 0, 'status' => 'enabled',
        ]);
        Category::create(['name' => 'Secret Collection', 'slug' => 'secret-collection', 'status' => 'disabled']);
        JournalPost::create([
            'title' => 'Secret Story', 'slug' => 'secret-story', 'body' => 'Test',
            'status' => 'draft', 'published_at' => null,
        ]);

        $this->getJson(route('search.suggestions', ['q' => 'secret']))
            ->assertOk()
            ->assertJsonCount(0, 'results');
    }

    public function test_search_requires_at_least_two_characters(): void
    {
        $this->getJson(route('search.suggestions', ['q' => 'a']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }
}
