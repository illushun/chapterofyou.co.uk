<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia; // Essential for the Inertia/Vue integration
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    private const ALLOWED_IP = '82.18.187.157';

    /**
     * Display the application's landing page.
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $clientIp = $request->ip();
        logger()->channel('load_website')->info("[{$clientIp}] Recorded access attempt:");

        if ($clientIp === self::ALLOWED_IP) {
            logger()->channel('load_website')->info("[{$clientIp}] Accepted. Redirecting to actual webpage.");

            return Inertia::render('home/LandingPage', [
                'promoText' => 'Free shipping on all orders over £50!',
                'featuredCategories' => [
                    ['name' => 'Candles', 'href' => '/category/candles', 'image' => '/images/cat-candles.jpg'],
                    ['name' => 'Services', 'href' => '/services', 'image' => '/images/cat-services.jpg'],
                ]
            ]);
        } else {
            logger()->channel('load_website')->info("[{$clientIp}] Denied. Redirecting to waiting list.");
        }

        return Inertia::render('Welcome', [
            'siteName' => 'Chapter of You',
        ]);
    }

    public function about(Request $request): \Inertia\Response
    {
        $clientIp = $request->ip();
        if ($clientIp !== self::ALLOWED_IP) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        return Inertia::render('About', [
            'siteName' => 'Chapter of You',
        ]);
    }

    public function contact(Request $request)
    {
        $clientIp = $request->ip();
        if ($clientIp !== self::ALLOWED_IP) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        return Inertia::render('Contact', [
            'siteName' => 'Chapter of You',
        ]);
    }
}
