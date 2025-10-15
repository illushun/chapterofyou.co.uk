<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Cart\Item as CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CartManager
{
    /**
     * The number of days a guest cart remains active before cleanup.
     */
    private const GUEST_CART_LIFESPAN_DAYS = 7;

    /**
     * Retrieves the current active cart, creating it if necessary.
     */
    public function getCurrentCart(): Cart
    {
        $user = Auth::user();
        $sessionId = $this->getSessionId();

        if ($user) {
            // Logged-in user: Get their existing cart or create a new one
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null, 'expires_at' => null]
            );

            // If the user has a temporary guest cart, merge it now
            $this->mergeGuestCart($cart, $sessionId);

        } else {
            // Guest user: Get cart by session ID or create a new one
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId, 'user_id' => null],
                ['expires_at' => Carbon::now()->addDays(self::GUEST_CART_LIFESPAN_DAYS)]
            );
            // Always refresh the expiry time for active guest carts
            $cart->update(['expires_at' => Carbon::now()->addDays(self::GUEST_CART_LIFESPAN_DAYS)]);
        }

        return $cart->load('items.product');
    }

    /**
     * Adds a product to the cart.
     */
    public function addItem(Cart $cart, int $productId, int $quantity = 1): CartItem
    {
        // Find existing item or create a new one
        $cartItem = $cart->items()->firstOrNew([
            'product_id' => $productId,
        ]);

        if ($cartItem->exists) {
            // Item exists, increase quantity
            $cartItem->quantity += $quantity;
        } else {
            // New item
            $cartItem->quantity = $quantity;
        }

        // Save the item
        $cartItem->save();

        // Update cart timestamp to prevent premature cleanup
        $cart->touch();

        return $cartItem;
    }

    /**
     * Updates the quantity of a cart item.
     */
    public function updateItem(Cart $cart, int $productId, int $quantity): ?CartItem
    {
        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            if ($quantity <= 0) {
                $cartItem->delete();
                return null;
            }
            $cartItem->quantity = $quantity;
            $cartItem->save();
            $cart->touch();
            return $cartItem;
        }
        return null;
    }

    /**
     * Removes an item from the cart.
     */
    public function removeItem(Cart $cart, int $productId): bool
    {
        return $cart->items()->where('product_id', $productId)->delete() > 0;
    }

    /**
     * Generates or retrieves the unique session ID from the session store.
     */
    private function getSessionId(): string
    {
        if (!session()->has('cart_session_id')) {
            session()->put('cart_session_id', Str::uuid());
        }
        return session('cart_session_id');
    }

    private function mergeGuestCart(Cart $userCart, string $guestSessionId): void
    {
        // Find the guest cart associated with the current session ID
        $guestCart = Cart::where('session_id', $guestSessionId)
                         ->whereNull('user_id')
                         ->first();

        if ($guestCart && $guestCart->id !== $userCart->id) {

            $guestCart->items->each(function (CartItem $guestItem) use ($userCart) {
                // Check if the item already exists in the user's cart
                $userItem = $userCart->items()->where('product_id', $guestItem->product_id)->first();

                if ($userItem) {
                    // Item exists: MERGE quantities
                    $userItem->quantity += $guestItem->quantity;
                    $userItem->save();
                } else {
                    // Item is new: Re-assign cart_id to the user's cart
                    $guestItem->cart_id = $userCart->id;
                    $guestItem->save();
                }
            });

            // Delete the old guest cart record after successful merge
            $guestCart->delete();
        }

        // Clear the session ID to finalise the merge process
        session()->forget('cart_session_id');
    }
}
