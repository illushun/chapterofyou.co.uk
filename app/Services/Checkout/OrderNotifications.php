<?php

namespace App\Services\Checkout;

use App\Mail\Order\Confirmation;
use App\Mail\Order\NewOrderAlert;
use App\Models\Order;
use App\Services\GiftVoucherService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderNotifications
{
    public function send(Order $order): void
    {
        $this->attempt($order, fn () => Mail::to($order->email)->queue(new Confirmation($order)));
        $this->attempt($order, fn () => Mail::to('contact@chapterofyou.co.uk')->queue(new NewOrderAlert($order)));

        $gift = $order->giftVoucherOrder;

        if ($gift?->delivery_type === 'email' && $gift->recipient_email) {
            $this->attempt($order, fn () => app(GiftVoucherService::class)->sendEVoucher($gift));
        }
    }

    private function attempt(Order $order, callable $send): void
    {
        try {
            $send();
        } catch (Throwable $e) {
            Log::error('Order notification failed after checkout', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
