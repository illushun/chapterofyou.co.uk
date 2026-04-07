<?php

namespace App\Http\Controllers;

use App\Models\GiftVoucherOrder;
use App\Services\GiftVoucherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GiftVoucherController extends Controller
{
    public function __construct(private GiftVoucherService $service)
    {
    }

    /**
     * Customer-facing gift voucher purchase page.
     */
    public function index()
    {
        return Inertia::render('gift-voucher/Purchase', [
            'amounts' => [10, 20, 25, 30, 50, 75, 100],
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'amount'           => ['required', 'numeric', 'min:5', 'max:500'],
            'delivery_type'    => ['required', 'in:email,physical'],
            'recipient_name'   => ['required', 'string', 'max:255'],
            'recipient_email'  => ['required_if:delivery_type,email', 'nullable', 'email'],
            'personal_message' => ['nullable', 'string', 'max:500'],
            'sender_name'      => ['required', 'string', 'max:255'],
            'sender_email'     => ['required', 'email'],
        ]);

        // Store gift voucher details in session — CheckoutController reads this after payment
        session(['pending_gift_voucher' => $validated]);

        // Redirect to checkout with a query param so the checkout page shows the gift voucher summary
        return response()->json(['redirect' => route('checkout.index', ['gift_voucher' => 1])]);
    }
}
