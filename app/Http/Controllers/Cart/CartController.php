<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
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
     * Display the shopping cart contents.
     */
    public function view()
    {
        // Retrieve the current cart (guest or user, handling merging if a guest just logged in)
        $cart = $this->cartManager->getCurrentCart();

        // Transform the cart data for the frontend
        $cartData = $cart->items->map(function ($item) {
            // Note: We assume 'product' relationship is loaded on the item
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name ?? 'Deleted Product', // Handle missing product gracefully
                'cost' => $item->product->cost ?? 0.00,
                'quantity' => $item->quantity,
                'image_url' => $item->product->images->first()->image ?? 'https://via.placeholder.com/100?text=No+Image',
                'subtotal' => round(($item->product->cost ?? 0.00) * $item->quantity, 2),
                'stock_qty' => $item->product->stock_qty ?? 0,
            ];
        });

        // Calculate the total cart value
        $cartTotal = $cartData->sum('subtotal');

        return Inertia::render('cart/View', [
            'cartItems' => $cartData,
            'cartTotal' => $cartTotal,
        ]);
    }

    /**
     * Adds an item to the cart (API endpoint).
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $cart = $this->cartManager->getCurrentCart();
        $this->cartManager->addItem($cart, $request->product_id, $request->quantity ?? 1);

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    /**
     * Updates an item's quantity in the cart (API endpoint).
     */
    public function update(Request $request, int $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $cart = $this->cartManager->getCurrentCart();
        $item = $this->cartManager->updateItem($cart, $productId, $request->quantity);

        if ($item === null && $request->quantity > 0) {
             return response()->json(['error' => 'Cart item not found.'], 404);
        }

        return redirect()->back(303); // Use 303 to trigger a GET request/Inertia refresh
    }

    /**
     * Removes an item from the cart (API endpoint).
     */
    public function remove(int $productId)
    {
        $cart = $this->cartManager->getCurrentCart();
        $this->cartManager->removeItem($cart, $productId);

        return redirect()->back(303)->with('success', 'Product removed from cart!');
    }
}
