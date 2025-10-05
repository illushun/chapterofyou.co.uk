<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia; // Essential for the Inertia/Vue integration

class HomeController extends Controller
{
    /**
     * Display the application's landing page.
     * @return \Inertia\Response
     */
    public function index(): \Inertia\Response
    {
        // Props are defined here and passed directly to the Vue component
        return Inertia::render('home/LandingPage', [
            // Ensure data is minimal and highly optimized for page speed
            'promoText' => 'Free shipping on all orders over £50!',
            'featuredCategories' => [
                ['name' => 'Candles', 'href' => '/category/candles', 'image' => '/images/cat-candles.jpg'],
                ['name' => 'Services', 'href' => '/services', 'image' => '/images/cat-services.jpg'],
            ]
        ]);
    }
}