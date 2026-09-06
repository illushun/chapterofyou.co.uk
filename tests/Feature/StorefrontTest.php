<?php

namespace Tests\Feature;

use App\Models\JournalPost;
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
}
