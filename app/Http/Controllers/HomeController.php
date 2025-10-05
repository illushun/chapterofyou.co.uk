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
    public function index(): \Inertia\Response
    {
        // 1. Get the current user's IP
        // We use $request->ip() which is Laravel's reliable way to get the client IP
        $clientIp = $request->ip();

        // Log the access attempt for monitoring (optional but good for debugging)
        Log::info('Access attempt on /', ['ip' => $clientIp, 'target' => self::ALLOWED_IP]);

        // 2. IP Check Logic
        if ($clientIp === self::ALLOWED_IP) {
            // RENDER MAIN LANDING PAGE (Home/LandingPage.vue)
            return Inertia::render('Home/LandingPage', [
                'promoText' => 'Free shipping on all orders over £50!',
                'featuredCategories' => [
                    ['name' => 'Candles', 'href' => '/category/candles', 'image' => '/images/cat-candles.jpg'],
                    ['name' => 'Services', 'href' => '/services', 'image' => '/images/cat-services.jpg'],
                ]
            ]);
        }
        
        // RENDER WAITLIST PAGE (Welcome.vue)
        return Inertia::render('Welcome', [
            'siteName' => 'Chapter of You',
        ]);
    }
}