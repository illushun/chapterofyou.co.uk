<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartManager;
use App\Services\Checkout\CheckoutTotals;
use App\Services\Checkout\CreateOrder;
use App\Services\Checkout\OrderNotifications;
use App\Services\Checkout\StripePayments;
use App\Services\VoucherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        private CartManager $cartManager,
        private VoucherService $vouchers,
        private CheckoutTotals $totals,
        private StripePayments $payments,
        private CreateOrder $orders,
        private OrderNotifications $notifications,
    ) {}

    public function index()
    {
        $cart = $this->cartManager->getCurrentCart();

        if ($cart->items->isEmpty() && ! session('pending_gift_voucher')) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty.');
        }

        try {
            [$summary] = $this->quote($cart);
        } catch (ValidationException $e) {
            if (! isset($e->errors()['voucher'])) {
                return redirect()->route('cart.view')->with('error', $e->getMessage());
            }

            $this->vouchers->clearFromSession();
            session()->flash('error', $e->getMessage());
            [$summary] = $this->quote($cart);
        }

        $cart->load('items.product.images');
        $cartItems = $cart->items->map(function ($item) {
            $image = $item->product->images->firstWhere('status', 'enabled')
                ?? $item->product->images->first();

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product' => [
                    'name' => $item->product->name,
                    'cost' => $item->product->cost,
                    'image_url' => $image?->image,
                ],
            ];
        });

        return Inertia::render('checkout/View', [
            'summary' => $summary,
            'cartItems' => $cartItems,
            'addresses' => Auth::user()?->addresses()->where('type', 'shipping')->orderByDesc('is_default')->get() ?? collect(),
            'appliedVoucher' => $this->vouchers->getFromSession(),
            'isGuest' => ! Auth::check(),
            'giftVoucher' => session('pending_gift_voucher'),
        ]);
    }

    public function getPaymentIntent()
    {
        $cart = $this->cartManager->getCurrentCart();

        if ($cart->items->isEmpty() && ! session('pending_gift_voucher')) {
            return response()->json(['error' => 'Cannot create payment intent for empty cart.'], 400);
        }

        try {
            [$summary, $voucher] = $this->quote($cart);
            $intent = $this->payments->create([
                'amount' => CheckoutTotals::pence($summary['total']),
                'currency' => 'gbp',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'cart_id' => $cart->id,
                    'user_id' => Auth::id() ?? 'guest',
                    'voucher_code' => $voucher?->code,
                ],
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret,
                'paymentIntentId' => $intent->id,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment initialization failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Payment initialization failed.'], 500);
        }
    }

    public function processPayment(CheckoutRequest $request)
    {
        $cart = $this->cartManager->getCurrentCart();

        try {
            $intent = $this->payments->retrieve($request->validated('paymentIntentId'));
            $this->validatePayment($intent, $cart);

            $order = DB::transaction(function () use ($cart, $intent, $request) {
                $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
                $existing = Order::where('payment_intent_id', $intent->id)->first();

                if ($existing) {
                    abort_unless(
                        Auth::check() ? $existing->user_id === Auth::id() : session('guest_order_id') === $existing->id,
                        403,
                    );

                    return $existing;
                }

                $cart->load('items.product.courier.courier');
                [$summary, $voucher] = $this->quote($cart, lockVoucher: true);

                if ($intent->amount !== CheckoutTotals::pence($summary['total'])) {
                    throw ValidationException::withMessages(['payment' => 'Your cart total has changed. Please reload checkout.']);
                }

                $order = $this->orders->create($cart, $summary, $request->validated(), $intent, session('pending_gift_voucher'));

                if ($voucher && $summary['voucher_discount'] > 0) {
                    $this->vouchers->recordUsage(
                        voucher: $voucher,
                        order: $order,
                        discountApplied: $summary['voucher_discount'],
                        totalBefore: $summary['subtotal'] + $summary['shipping'],
                        totalAfter: $summary['total'],
                        ipAddress: $request->ip(),
                    );
                }

                return $order;
            });
        } catch (Throwable $e) {
            Log::error('Order processing failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Payment confirmation failed. Please try again.')->withInput();
        }

        if ($order->wasRecentlyCreated) {
            session()->forget('pending_gift_voucher');
            $this->vouchers->clearFromSession();

            if (! Auth::check()) {
                session(['guest_order_id' => $order->id]);
            }

            $this->notifications->send($order);
        }

        return redirect()->route('order.confirmation', ['id' => $order->id])
            ->with('success', "Order #{$order->id} successfully placed.");
    }

    private function quote(Cart $cart, bool $lockVoucher = false): array
    {
        $cart->loadMissing('items.product.courier.courier');
        $applied = $this->vouchers->getFromSession();
        $voucher = null;
        $discount = 0.0;

        if ($applied) {
            $subtotal = $cart->items->sum(fn ($item) => ($item->product->cost ?? 0) * $item->quantity);
            $result = $this->vouchers->validate($applied['code'], $cart, $subtotal, $lockVoucher);

            if (! $result['valid']) {
                throw ValidationException::withMessages(['voucher' => $result['message']]);
            }

            $voucher = $result['voucher'];
            $discount = $result['discount'];
            $this->vouchers->applyToSession($voucher, $discount);
        }

        return [$this->totals->calculate($cart, $discount, session('pending_gift_voucher')), $voucher];
    }

    private function validatePayment(PaymentIntent $intent, Cart $cart): void
    {
        if ($intent->status !== 'succeeded'
            || $intent->currency !== 'gbp'
            || (string) ($intent->metadata->cart_id ?? '') !== (string) $cart->id) {
            throw ValidationException::withMessages(['payment' => 'This payment does not match your checkout.']);
        }
    }
}
