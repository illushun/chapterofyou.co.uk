<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    private $cartManager;

    /**
     * Inject the CartManager service.
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
        Stripe::setApiKey(env('STRIPE_SECRET'));
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
        return Inertia::render('checkout/View', [
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
            // Ensure product exists before accessing cost
            $productPrice = $item->product->cost ?? 0.00;
            $subtotal += $productPrice * $item->quantity;
        }

        // free shipping?
        $shippingCost = ($subtotal >= 50) ? 0.00 : 4.99;

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
     * API endpoint: Fetches a Stripe Payment Intent client secret.
     */
    public function getPaymentIntent()
    {
        $cart = $this->cartManager->getCurrentCart();

        if ($cart->items->isEmpty()) {
            return response()->json(['error' => 'Cannot create payment intent for empty cart.'], 400);
        }

        $summary = $this->calculateFinalTotal($cart);
        // Stripe requires amount in the smallest currency unit (e.g., cents/pence)
        $finalTotalInPence = (int) ($summary['total'] * 100);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $finalTotalInPence,
                'currency' => 'gbp',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'cart_id' => $cart->id,
                    'user_id' => Auth::id() ?? 'guest',
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe PI Creation Error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment initialization failed. Check Stripe logs.'], 500);
        }
    }

    /**
     * Handles the final server-side payment processing and order creation.
     */
    public function processPayment(Request $request)
    {
        // 1. Validation for customer and address data
        $request->validate([
            'paymentIntentId' => 'required|string',
            'paymentType' => 'required|string',
            'email' => 'required|email',
            'fullName' => 'required|string',
            'addressLine1' => 'required|string',
            'city' => 'required|string',
            'postcode' => 'required|string',
            'telephone' => 'nullable|string',
            'county' => 'nullable|string',
        ]);

        $cart = $this->cartManager->getCurrentCart();
        $summary = $this->calculateFinalTotal($cart);
        $finalTotalInPence = (int) ($summary['total'] * 100);

        // 2. Perform Order Creation and Payment Confirmation within a Database Transaction
        return DB::transaction(function () use ($request, $cart, $summary, $finalTotalInPence) {
            try {
                // Retrieve Payment Intent from Stripe
                $paymentIntent = PaymentIntent::retrieve($request->paymentIntentId);

                // Ensure Payment Intent is successful
                if ($paymentIntent->status !== 'succeeded') {
                    throw new \Exception("Payment failed or is not ready. Status: {$paymentIntent->status}");
                }

                // Prevent amount tampering
                if ($paymentIntent->amount !== $finalTotalInPence) {
                    throw new \Exception('Payment amount mismatch. Potential tampering detected.');
                }

                // Create Order & Clear Cart
                $order = $this->createOrderAndClearCart($cart, $summary, $request->all(), $paymentIntent);

                // Redirect to confirmation page with the new Order ID
                return redirect()->route('order.confirmation', ['id' => $order->id])
                                 ->with('success', "Order #{$order->id} successfully placed.");

            } catch (\Exception $e) {
                Log::error('[CheckoutController] Order Processing Error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Payment confirmation failed. Please try again.')->withInput();
            }
        });
    }

    /**
     * Creates the Order and Order Items, and then clears the Cart.
     */
    private function createOrderAndClearCart(Cart $cart, array $summary, array $formData, PaymentIntent $paymentIntent): Order
    {
        $names = explode(' ', $formData['fullName'], 2);
        $firstName = $names[0];
        $lastName = $names[1] ?? $names[0];

        $orderData = [
            'user_id' => Auth::id(),
            'payment_intent_id' => $paymentIntent->id,
            'payment_type' => $paymentIntent->payment_method_types[0] ?? $formData['paymentType'],

            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $formData['email'],
            'telephone' => $formData['telephone'] ?? null,

            'cost_total' => $summary['subtotal'],
            'shipping_total' => $summary['shipping'],
            'tax_total' => $summary['tax'],
            'grand_total' => $summary['total'],

            'billing_line_1' => $formData['addressLine1'],
            'billing_line_2' => $formData['addressLine2'] ?? null,
            'billing_city' => $formData['city'],
            'billing_county' => $formData['county'] ?? null,
            'billing_postcode' => $formData['postcode'],
            'billing_country' => $formData['country'],
            'shipping_line_1' => $formData['addressLine1'],
            'shipping_line_2' => $formData['addressLine2'] ?? null,
            'shipping_city' => $formData['city'],
            'shipping_county' => $formData['county'] ?? null,
            'shipping_postcode' => $formData['postcode'],
            'shipping_country' => $formData['country'],

            'status' => 'successful',
        ];

        // Create the Order
        $order = Order::create($orderData);

        // Prepare and save Order Items
        $orderItems = $cart->items->map(function ($item) use ($order) {
            $productCost = $item->product->cost ?? 0.00;
            return new OrderItem([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product_cost' => $productCost,
                'product_total' => round($productCost * $item->quantity, 2),
            ]);
        });

        $order->items()->saveMany($orderItems);
        $this->cartManager->clearCart($cart);

        return $order;
    }
}
