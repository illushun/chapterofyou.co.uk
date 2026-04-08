<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\JournalPost;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
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
                // Logged-in user — find their cart by user_id
                if ($request->user()) {
                    $cart = \App\Models\Cart::where('user_id', $request->user()->id)->first();

                    return $cart
                        ? (int) $cart->items()->sum('quantity')
                        : 0;
                }

                // Guest — find their cart by the session cart_session_id
                $sessionId = $request->session()->get('cart_session_id');

                if (!$sessionId) {
                    return 0;
                }

                $cart = \App\Models\Cart::where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->first();

                return $cart
                    ? (int) $cart->items()->sum('quantity')
                    : 0;
            },
            'recentJournalPosts' => fn () => JournalPost::published()
                ->select('title', 'slug', 'excerpt', 'cover_image', 'published_at')
                ->latest('published_at')
                ->limit(3)
                ->get()
                ->map(fn ($p) => [
                    'title'        => $p->title,
                    'slug'         => $p->slug,
                    'excerpt'      => $p->excerpt,
                    'cover_image'  => $p->cover_image ? asset('storage/' . $p->cover_image) : null,
                    'published_at' => $p->published_at->format('d M Y'),
                    'reading_time' => $p->reading_time,
                ]),
        ];
    }
}
