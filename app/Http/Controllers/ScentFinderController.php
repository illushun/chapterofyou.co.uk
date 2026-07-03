<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use App\Services\ScentFinder\ScentFinderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ScentFinderController extends Controller
{
    /**
     * Quiz landing page — /scent-finder
     */
    public function index()
    {
        return Inertia::render('scent-finder/Quiz');
    }

    /**
     * Handle quiz submission and show matched products — /scent-finder/results
     */
    public function results(Request $request, ScentFinderService $scentFinder)
    {
        $validated = $request->validate([
            'scent_families' => ['required', 'array', 'min:1'],
            'scent_families.*' => [Rule::in(Product::SCENT_FAMILIES)],
            'mood_tags' => ['required', 'array', 'min:1'],
            'mood_tags.*' => [Rule::in(Product::MOOD_TAGS)],
            'intensity' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $products = $scentFinder->match($validated);

        return Inertia::render('scent-finder/Results', [
            'products' => $products,
            'answers' => $validated,
            'wishlistedIds' => Auth::check()
                ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray()
                : [],
        ]);
    }
}
