<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartManager;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoucherController extends Controller
{
    public function __construct(
        private CartManager $cartManager,
        private VoucherService $voucherService
    ) {
    }

    /**
     * POST /checkout/voucher/apply
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        try {
            $cart = $this->cartManager->getCurrentCart();

            // Explicitly reload to guarantee product is always available
            $cart->load('items.product');

            $subtotal = $cart->items->sum(
                fn ($item) => ($item->product->cost ?? 0) * $item->quantity
            );

            $result = $this->voucherService->validate($request->code, $cart, $subtotal);

            if (!$result['valid']) {
                return response()->json(['error' => $result['message']], 422);
            }

            $this->voucherService->applyToSession($result['voucher'], $result['discount']);

            return response()->json([
                'code'     => $result['voucher']->code,
                'discount' => $result['discount'],
                'type'     => $result['voucher']->type,
                'value'    => (float) $result['voucher']->value,
                'message'  => 'Voucher applied! You save £' . number_format($result['discount'], 2) . '.',
            ]);

        } catch (\Exception $e) {
            Log::error('[VoucherController] Apply error: ' . $e->getMessage(), [
                'code'  => $request->code,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * POST /checkout/voucher/remove
     */
    public function remove()
    {
        $this->voucherService->clearFromSession();
        return response()->json(['message' => 'Voucher removed.']);
    }
}
