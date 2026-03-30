<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartManager;
use App\Services\VoucherService;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(
        private CartManager $cartManager,
        private VoucherService $voucherService
    ) {
    }

    /**
     * POST /checkout/voucher/apply
     * Validates and stores the voucher in the session.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $cart = $this->cartManager->getCurrentCart();

        // Need subtotal to check minimum order value
        $subtotal = $cart->items->sum(fn ($item) => ($item->product->cost ?? 0) * $item->quantity);

        $result = $this->voucherService->validate($request->code, $cart, $subtotal);

        if (!$result['valid']) {
            return response()->json(['error' => $result['message']], 422);
        }

        $this->voucherService->applyToSession($result['voucher'], $result['discount']);

        return response()->json([
            'code'     => $result['voucher']->code,
            'discount' => $result['discount'],
            'type'     => $result['voucher']->type,
            'value'    => $result['voucher']->value,
            'message'  => 'Voucher applied! You save £' . number_format($result['discount'], 2) . '.',
        ]);
    }

    /**
     * POST /checkout/voucher/remove
     * Clears the voucher from the session.
     */
    public function remove()
    {
        $this->voucherService->clearFromSession();
        return response()->json(['message' => 'Voucher removed.']);
    }
}
