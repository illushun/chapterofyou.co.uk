<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ContactMessage;

class HomeController extends Controller
{
    private const ALLOWED_IPS = [
        '82.18.187.157', // kace
        '176.27.250.172' // stu
    ];

    private function validIp(Request $request): bool
    {
        return in_array($request->ip(), self::ALLOWED_IPS);
    }

    /**
     * Display the application's landing page.
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $clientIp = $request->ip();
        logger()->channel('load_website')->info("[{$clientIp}] Recorded access attempt:");

        if (!Auth::check()) {
            //return redirect()->route('login');
            logger()->channel('load_website')->info("[{$clientIp}] Not logged in. Redirecting to waiting list.");
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        $user = Auth::user();
        if ($user->is_admin) {
            logger()->channel('load_website')->info("[{$clientIp}] Admin user detected. Redirecting to actual webpage.");

            return redirect('/products');
            /*return Inertia::render('home/LandingPage', [
                'promoText' => 'Free shipping on all orders over £50!',
                'featuredCategories' => [
                    ['name' => 'Candles', 'href' => '/category/candles', 'image' => '/images/cat-candles.jpg'],
                    ['name' => 'Services', 'href' => '/services', 'image' => '/images/cat-services.jpg'],
                ]
            ]);*/
        } else {
            logger()->channel('load_website')->info("[{$clientIp}] Regular user detected.");
        }

        return Inertia::render('Welcome', [
            'siteName' => 'Chapter of You',
        ]);
    }

    public function about(Request $request): \Inertia\Response
    {
        if (!Auth::check()) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        $user = Auth::user();
        if ($user->is_admin) {
            return Inertia::render('About', [
                'siteName' => 'Chapter of You',
            ]);
        }

        return Inertia::render('Welcome', [
            'siteName' => 'Chapter of You',
        ]);
    }

    public function contact(Request $request)
    {
        if (!Auth::check()) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        $user = Auth::user();
        if ($user->is_admin) {
            return Inertia::render('Contact', [
                'siteName' => 'Chapter of You',
            ]);
        }

        return Inertia::render('Welcome', [
            'siteName' => 'Chapter of You',
        ]);
    }

    /**
     * Store a new contact message in the database.
     */
    public function storeContact(Request $request)
    {
        if (!$this->validIp($request)) {
            Log::warning('Contact form submission attempt blocked due to IP restriction.', ['ip' => $request->ip()]);
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create($validated);
        return redirect()->back()->with('success', 'Thank you for your message! I will get back to you soon.');
    }
}
