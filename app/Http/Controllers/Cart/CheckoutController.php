<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

use App\Models\Cart;

class CheckoutController extends Controller
{
    private $cartManager;

    /**
     * Inject the CartManager service.
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * Display the checkout summary page.
     */
    public function index()
    {
        $cart = $this->cartManager->getCurrentCart();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty.');
        }

        $summary = $this->calculateFinalTotal($cart);
        return Inertia::render('checkout/Index', [
            'summary' => $summary,
            'cartItems' => $cart->items->load('product'),
        ]);
    }

    /**
     * Calculates the final, trusted total.
     * * @param \App\Models\Cart $cart The current cart object.
     * @return array Calculated totals.
     */
    public function calculateFinalTotal(Cart $cart)
    {
        $subtotal = 0;

        foreach ($cart->items as $item) {
            $productPrice = $item->product->cost;
            $subtotal += $productPrice * $item->quantity;
        }

        // free shipping?
        $shippingCost = ($subtotal > 100) ? 0.00 : 4.99;

        // tax
        $tax = round($subtotal * 0.20, 2);

        $finalTotal = round($subtotal + $shippingCost + $tax, 2);
        return [
            'subtotal' => round($subtotal, 2),
            'tax' => $tax,
            'shipping' => $shippingCost,
            'total' => $finalTotal,
        ];
    }

    /**
     * Handles the final payment processing request.
     * * The last security checkpoint before payment gateway interaction.
     */
    public function processPayment(Request $request)
    {
        $cart = $this->cartManager->getCurrentCart();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Cannot process empty cart.');
        }

        $summary = $this->calculateFinalTotal($cart);
        $finalTotal = $summary['total'];

        // TODO: PAYMENT INTEGRATION (e.g., Stripe, PayPal)
        // Use the $finalTotal derived from the server for the charge amount.
        // If the user's browser submitted a different "total" value, it is IGNORED here.

        // ... $paymentGateway->charge($finalTotal, $request->payment_token);

        // TODO: ORDER CREATION & CART CLEARING
        // If payment is successful:
        // return redirect()->route('order.confirmation')->with('success', "Payment of £{$finalTotal} processed securely.");

        // test for now:
        return response()->json(['message' => "Payment of £{$finalTotal} processed securely based on server-side validation."], 200);
    }
}
