<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ContactMessage;

class HomeController extends Controller
{
    /**
     * Display the application's landing page.
     */
    public function index(Request $request): \Inertia\Response
    {
        $clientIp = $request->ip();
        logger()->channel('load_website')->info("[{$clientIp}] Recorded access attempt.");

        if (!Auth::check()) {
            logger()->channel('load_website')->info("[{$clientIp}] Not logged in. Showing waitlist.");
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        $user = Auth::user();

        if ($user->is_admin) {
            logger()->channel('load_website')->info("[{$clientIp}] Admin user. Loading landing page.");
            return Inertia::render('home/LandingPage');
        }

        logger()->channel('load_website')->info("[{$clientIp}] Regular user. Showing waitlist.");
        return Inertia::render('Welcome', [
            'siteName' => 'Chapter of You',
        ]);
    }

    /**
     * Display the about page.
     */
    public function about(Request $request): \Inertia\Response
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        return Inertia::render('About', [
            'siteName' => 'Chapter of You',
        ]);
    }

    /**
     * Display the contact page.
     */
    public function contact(Request $request): \Inertia\Response
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        return Inertia::render('Contact', [
            'siteName' => 'Chapter of You',
        ]);
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

        ContactMessage::create($validated);

        return redirect()->back()->with('success', 'Thank you for your message! I will get back to you soon.');
    }
}
