<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\Auth;

class VoucherService
{
    // ── Validation ─────────────────────────────────────────────────────────────

    /**
     * Validate a voucher code against a cart and user context.
     * Returns ['valid' => true, 'voucher' => Voucher, 'discount' => float]
     * or      ['valid' => false, 'message' => string]
     */
    public function validate(string $code, Cart $cart, float $subtotal): array
    {
        $voucher = Voucher::where('code', strtoupper(trim($code)))
            ->with('products')
            ->first();

        if (!$voucher) {
            return $this->fail('This voucher code is invalid.');
        }

        if (!$voucher->is_active) {
            return $this->fail('This voucher is no longer active.');
        }

        if (!$voucher->hasStarted()) {
            return $this->fail('This voucher is not yet valid.');
        }

        if ($voucher->isExpired()) {
            return $this->fail('This voucher has expired.');
        }

        if ($voucher->isExhausted()) {
            return $this->fail('This voucher has reached its maximum number of uses.');
        }

        if ($voucher->minimum_order_value !== null && $subtotal < $voucher->minimum_order_value) {
            return $this->fail(
                'This voucher requires a minimum order value of £' . number_format($voucher->minimum_order_value, 2) . '.'
            );
        }

        $user = Auth::user();

        if ($voucher->new_customers_only) {
            if (!$user) {
                return $this->fail('This voucher is only available to registered new customers.');
            }
            $hasOrders = Order::where('user_id', $user->id)
                ->where('status', 'successful')
                ->exists();
            if ($hasOrders) {
                return $this->fail('This voucher is only available to new customers.');
            }
        }

        if ($voucher->single_use_per_user && $user) {
            $alreadyUsed = VoucherUsage::where('voucher_id', $voucher->id)
                ->where('user_id', $user->id)
                ->exists();
            if ($alreadyUsed) {
                return $this->fail('You have already used this voucher.');
            }
        }

        // Product restriction check
        $discount = $this->calculateDiscount($voucher, $cart, $subtotal);

        if ($discount <= 0) {
            return $this->fail('This voucher does not apply to any items in your cart.');
        }

        return [
            'valid'    => true,
            'voucher'  => $voucher,
            'discount' => round($discount, 2),
        ];
    }

    // ── Discount calculation ───────────────────────────────────────────────────

    /**
     * Calculate the actual £ discount to apply.
     * If the voucher is product-restricted, only eligible line items count.
     */
    public function calculateDiscount(Voucher $voucher, Cart $cart, float $subtotal): float
    {
        $applicableSubtotal = $subtotal;

        if (!$voucher->applies_to_all_products && $voucher->products->isNotEmpty()) {
            $restrictedProductIds = $voucher->products->pluck('id')->toArray();

            $applicableSubtotal = $cart->items->reduce(function (float $carry, $item) use ($restrictedProductIds) {
                if (in_array($item->product_id, $restrictedProductIds)) {
                    $carry += ($item->product->cost ?? 0) * $item->quantity;
                }
                return $carry;
            }, 0.0);
        }

        if ($voucher->type === 'percentage') {
            return $applicableSubtotal * ($voucher->value / 100);
        }

        // Fixed: can't discount more than the applicable subtotal
        return min((float) $voucher->value, $applicableSubtotal);
    }

    // ── Recording usage ────────────────────────────────────────────────────────

    /**
     * Record that a voucher was used on an order.
     * Call this after the order is created.
     */
    public function recordUsage(
        Voucher $voucher,
        Order $order,
        float $discountApplied,
        float $totalBefore,
        float $totalAfter,
        string $ipAddress = null
    ): VoucherUsage {
        $usage = VoucherUsage::create([
            'voucher_id'         => $voucher->id,
            'user_id'            => $order->user_id,
            'order_id'           => $order->id,
            'guest_email'        => $order->user_id ? null : $order->email,
            'discount_applied'   => $discountApplied,
            'order_total_before' => $totalBefore,
            'order_total_after'  => $totalAfter,
            'ip_address'         => $ipAddress,
        ]);

        // Increment usage counter atomically
        $voucher->increment('uses_count');

        return $usage;
    }

    // ── Session helpers ────────────────────────────────────────────────────────

    /**
     * Store applied voucher data in the session.
     */
    public function applyToSession(Voucher $voucher, float $discount): void
    {
        session([
            'voucher' => [
                'id'       => $voucher->id,
                'code'     => $voucher->code,
                'discount' => $discount,
                'type'     => $voucher->type,
                'value'    => $voucher->value,
            ]
        ]);
    }

    /**
     * Clear any applied voucher from the session.
     */
    public function clearFromSession(): void
    {
        session()->forget('voucher');
    }

    /**
     * Get the currently applied voucher from session (or null).
     */
    public function getFromSession(): ?array
    {
        return session('voucher');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function fail(string $message): array
    {
        return ['valid' => false, 'message' => $message];
    }
}
