<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use Illuminate\Validation\ValidationException;

class CheckoutTotals
{
    public static function pence(float|int|string $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    public function calculate(Cart $cart, float $discount = 0, ?array $giftVoucher = null): array
    {
        $subtotal = $cart->items->sum(fn ($item) => self::pence($item->product->cost ?? 0) * $item->quantity);
        $shipping = $this->shipping($cart);
        $subtotal += self::pence($giftVoucher['amount'] ?? 0);

        if ($subtotal >= 5000) {
            $shipping = 0;
        }

        if (($giftVoucher['delivery_type'] ?? null) === 'physical') {
            $shipping += 299;
        }

        $discount = min($subtotal, max(0, self::pence($discount)));
        $discountedSubtotal = $subtotal - $discount;

        return [
            'subtotal' => $subtotal / 100,
            'voucher_discount' => $discount / 100,
            'vat_component' => config('app.vat_number') ? round($discountedSubtotal / 6) / 100 : 0.0,
            'shipping' => $shipping / 100,
            'total' => ($discountedSubtotal + $shipping) / 100,
        ];
    }

    private function shipping(Cart $cart): int
    {
        foreach ($cart->items as $item) {
            if (! $item->product?->courier?->courier) {
                throw ValidationException::withMessages(['shipping' => 'Shipping is unavailable for an item in your cart. Please contact us.']);
            }
        }

        $perItem = $cart->items->filter(fn ($item) => $item->product->courier?->per_item === 'yes');

        if ($perItem->isNotEmpty()) {
            return $perItem->sum(fn ($item) => self::pence($item->product->courier->courier->cost) * $item->quantity);
        }

        return $cart->items->map(fn ($item) => self::pence($item->product->courier?->courier?->cost ?? 0))->min() ?? 0;
    }
}
