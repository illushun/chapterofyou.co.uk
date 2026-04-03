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
     * Display a listing of reviews with optional status filter.
     */
    public function index(Request $request)
    {
        $status = $request->get('status'); // 'pending' | 'approved' | 'rejected' | null (all)

        $query = Review::with('user:id,name,email')
            ->with('product:id,mpn,name')
            ->select('id', 'user_id', 'product_id', 'rating', 'status', 'created_at');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $reviews = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Counts per status for the tab badges
        $counts = [
            'all'      => Review::count(),
            'pending'  => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
        ];

        return Inertia::render('admin/review/Index', [
            'reviews'       => $reviews,
            'counts'        => $counts,
            'activeStatus'  => $status ?? 'all',
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
     * Update the specified review's status.
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $review->status = $request->status;
        $review->save();

        return redirect()->back()->with('success', "Review #{$review->id} status updated to {$review->status}.");
    }

    /**
     * Save or update the admin's reply to a review.
     * The reply is shown publicly on the product page beneath the review.
     */
    public function reply(Request $request, Review $review)
    {
        $request->validate([
            'admin_reply' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->admin_reply = $request->admin_reply ?: null;
        $review->save();

        $message = $request->admin_reply
            ? "Reply saved for review #{$review->id}."
            : "Reply removed from review #{$review->id}.";

        return redirect()->back()->with('success', $message);
    }
}
