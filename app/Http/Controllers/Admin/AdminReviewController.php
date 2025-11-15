<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        $reviews = Review::with('user:id,name,email')
            ->with('product:id,mpn')
            ->select('id', 'user_id', 'product_id', 'rating', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('admin/review/Index', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load(['user:id,name,email', 'product:id,mpn,name']);

        return Inertia::render('admin/review/Show', [
            'review' => $review,
        ]);
    }

    /**
     * Update the specified review status in storage.
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $review->status = $request->status;
        $review->save();

        return redirect()->back()->with('success', "Review #{$review->id} status updated to {$review->status} successfully.");
    }
}
