<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Product\Review;
use App\Mail\ContactMessageReceived;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Display the application's landing page.
     */
    public function index(Request $request): \Inertia\Response
    {
        $clientIp = $request->ip();
        logger()->channel('load_website')->info("[{$clientIp}] Recorded access attempt.");

        /*if (!Auth::check()) {
            logger()->channel('load_website')->info("[{$clientIp}] Not logged in. Showing waitlist.");
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        $user = Auth::user();

        if (!$user->is_admin) {
            logger()->channel('load_website')->info("[{$clientIp}] Regular user. Showing waitlist.");
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        logger()->channel('load_website')->info("[{$clientIp}] Admin user. Loading landing page.");*/

        // Fetch the 4 most-viewed enabled products for the "Hottest Products" section.
        $featuredProducts = Product::query()
            ->select('product.id', 'product.name', 'product.mpn', 'product.cost')
            ->selectRaw('COALESCE(SUM(pv.views), 0) as views')
            ->leftJoin('product_view as pv', 'pv.product_id', '=', 'product.id')
            ->where('product.status', 'enabled')
            ->where('product.stock_qty', '>', 0)
            ->whereNull('product.parent_product_id')
            ->groupBy('product.id', 'product.name', 'product.mpn', 'product.cost')
            ->orderByDesc('views')
            ->orderByDesc('product.id')
            ->limit(4)
            ->get()
            ->map(function (Product $product) {
                return [
                    'id'    => $product->id,
                    'name'  => $product->name,
                    'mpn'   => $product->mpn,
                    'cost'  => (float) $product->cost,
                    'image' => $product->images()
                                ->where('status', 'enabled')
                                ->orderBy('id')
                                ->value('image'),
                    'slug'  => $product->seo?->slug,
                    'views' => (int) $product->views,
                ];
            });

        $testimonials = Review::query()
            ->approved()
            ->where('rating', '>=', 4)
            ->whereNotNull('message')
            ->where('message', '!=', '')
            ->with('user:id,name')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Review $r) => [
                'id'      => $r->id,
                'rating'  => $r->rating,
                'message' => Str::limit($r->message, 160),
                'user'    => ['name' => $r->user?->name ?? 'Customer'],
            ]);

        return Inertia::render('home/LandingPage', [
            'featuredProducts' => $featuredProducts,
            'testimonials'     => $testimonials,
            'season'           => $this->getSeason(),
        ]);
    }

    private function getSeason(): array
    {
        $month = (int) now()->month;
        $year  = now()->year;

        return match (true) {
            in_array($month, [3, 4, 5]) => [
                'id'           => 'spring',
                'banner'       => "Spring is here. Fresh floral scents for new beginnings",
                'eyebrow'      => "Spring Collection {$year}",
                'sub'          => 'Fresh, floral, and full of life. Welcome the new season into your home with a scent made just for you.',
                'motif'        => '✿',
                'sectionLabel' => 'Spring favourites',
            ],
            in_array($month, [6, 7, 8]) => [
                'id'           => 'summer',
                'banner'       => "Summer is here. Sun-kissed scents for long, warm days",
                'eyebrow'      => "Summer Collection {$year}",
                'sub'          => 'Light, fresh, and sun-kissed. Premium reed diffusers to fill your home with the warmth of summer.',
                'motif'        => '✦',
                'sectionLabel' => 'Summer favourites',
            ],
            in_array($month, [9, 10, 11]) => [
                'id'           => 'autumn',
                'banner'       => "Autumn warmth has arrived. Rich, spicy scents to cosy up your home",
                'eyebrow'      => "Autumn Collection {$year}",
                'sub'          => 'Warm, spicy, and comforting. Wrap your home in the rich, golden scents of the season.',
                'motif'        => '❧',
                'sectionLabel' => 'Autumn warmth',
            ],
            default => [
                'id'           => 'winter',
                'banner'       => "Give the gift of beautiful scent. Perfect for the festive season",
                'eyebrow'      => "Winter Collection {$year}",
                'sub'          => 'Rich, cosy, and luxurious. Reed diffusers that make the perfect winter gift for someone you love.',
                'motif'        => '❄',
                'sectionLabel' => 'Winter warmth',
            ],
        };
    }

    /**
     * Display the about page.
     */
    public function about(Request $request): \Inertia\Response
    {
        /*if (!Auth::check() || !Auth::user()->is_admin) {
            return Inertia::render('Welcome', ['siteName' => 'Chapter of You']);
        }*/

        return Inertia::render('About', ['siteName' => 'Chapter of You']);
    }

    /**
     * Display the contact page.
     */
    public function contact(Request $request): \Inertia\Response
    {
        /*if (!Auth::check() || !Auth::user()->is_admin) {
            return Inertia::render('Welcome', ['siteName' => 'Chapter of You']);
        }*/

        return Inertia::render('Contact', ['siteName' => 'Chapter of You']);
    }

    /**
     * Store a new contact message.
     */
    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        // Notify admin
        Mail::to(config('mail.admin_address', config('mail.from.address')))
            ->queue(new ContactMessageReceived($contactMessage));

        return redirect()->back()->with('success', 'Thank you for your message! I will get back to you soon.');
    }
}
