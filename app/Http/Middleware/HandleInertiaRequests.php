<?php

namespace App\Http\Middleware;

use App\Models\JournalPost;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /** @var string */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'cartCount' => function () use ($request) {
                // Logged-in user - find their cart by user_id
                if ($request->user()) {
                    $cart = \App\Models\Cart::where('user_id', $request->user()->id)->first();

                    return $cart
                        ? (int) $cart->items()->sum('quantity')
                        : 0;
                }

                // Guest - find their cart by the session cart_session_id
                $sessionId = $request->session()->get('cart_session_id');

                if (! $sessionId) {
                    return 0;
                }

                $cart = \App\Models\Cart::where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->first();

                return $cart
                    ? (int) $cart->items()->sum('quantity')
                    : 0;
            },
            'cartProductIds' => function () use ($request) {
                if ($request->user()) {
                    $cart = \App\Models\Cart::where('user_id', $request->user()->id)->first();
                } else {
                    $sessionId = $request->session()->get('cart_session_id');
                    if (! $sessionId) {
                        return [];
                    }

                    $cart = \App\Models\Cart::where('session_id', $sessionId)
                        ->whereNull('user_id')
                        ->first();
                }

                return $cart
                    ? $cart->items()->pluck('product_id')->map(fn ($id) => (int) $id)->values()
                    : [];
            },
            'recentJournalPosts' => fn () => JournalPost::published()
                ->select('title', 'slug', 'excerpt', 'body', 'cover_image', 'published_at')
                ->latest('published_at')
                ->limit(3)
                ->get()
                ->map(fn ($p) => [
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'excerpt' => $p->excerpt,
                    'cover_image' => $p->cover_image ? asset('storage/'.$p->cover_image) : null,
                    'published_at' => $p->published_at->format('d M Y'),
                    'reading_time' => $p->reading_time,
                ]),
            'vatRegistered' => (bool) config('app.vat_number'),
        ];
    }
}
