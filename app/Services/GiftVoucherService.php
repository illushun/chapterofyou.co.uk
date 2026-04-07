<?php

namespace App\Services;

use App\Models\GiftVoucherOrder;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class GiftVoucherService
{
    public function createFromOrder(
        Order $order,
        float $amount,
        string $deliveryType,
        string $recipientName,
        ?string $recipientEmail,
        ?string $personalMessage,
    ): GiftVoucherOrder {

        $code = $this->generateCode();

        $voucher = Voucher::create([
            'code'                    => $code,
            'description'             => "Gift Voucher — purchased by {$order->first_name} {$order->last_name}",
            'type'                    => 'fixed',
            'value'                   => $amount,
            'minimum_order_value'     => null,
            'applies_to_all_products' => true,
            'stackable'               => false,
            'new_customers_only'      => false,
            'single_use_per_user'     => false,
            'max_uses'                => 1,
            'uses_count'              => 0,
            'valid_from'              => now(),
            'valid_until'             => now()->addYear(),
            'is_active'               => true,
        ]);

        $giftVoucherOrder = GiftVoucherOrder::create([
            'order_id'         => $order->id,
            'voucher_id'       => $voucher->id,
            'delivery_type'    => $deliveryType,
            'amount'           => $amount,
            'recipient_name'   => $recipientName,
            'recipient_email'  => $recipientEmail,
            'personal_message' => $personalMessage,
            'fulfilled_at'     => null,
        ]);

        Log::info('[GiftVoucher] Record created', [
            'order_id'        => $order->id,
            'voucher_code'    => $code,
            'delivery_type'   => $deliveryType,
            'recipient_email' => $recipientEmail,
        ]);

        if ($deliveryType === 'email' && $recipientEmail) {
            $this->sendEVoucher($giftVoucherOrder);
        }

        return $giftVoucherOrder;
    }

    public function sendEVoucher(GiftVoucherOrder $giftVoucherOrder): void
    {
        $giftVoucherOrder->load(['voucher', 'order']);

        if (!$giftVoucherOrder->recipient_email) {
            Log::error('[GiftVoucher] No recipient email on record', [
                'id' => $giftVoucherOrder->id,
            ]);
            return;
        }

        if (!$giftVoucherOrder->voucher) {
            Log::error('[GiftVoucher] Voucher relationship not found', [
                'id' => $giftVoucherOrder->id,
            ]);
            return;
        }

        try {
            Log::info('[GiftVoucher] Attempting to send e-voucher email', [
                'to'           => $giftVoucherOrder->recipient_email,
                'voucher_code' => $giftVoucherOrder->voucher->code,
            ]);

            Mail::to($giftVoucherOrder->recipient_email)
                ->send(new \App\Mail\GiftVoucher($giftVoucherOrder));

            $giftVoucherOrder->update(['fulfilled_at' => now()]);

            Log::info('[GiftVoucher] E-voucher email sent successfully', [
                'to' => $giftVoucherOrder->recipient_email,
            ]);

        } catch (\Exception $e) {
            Log::error('[GiftVoucher] Failed to send e-voucher email', [
                'to'    => $giftVoucherOrder->recipient_email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function markPhysicalDispatched(GiftVoucherOrder $giftVoucherOrder): void
    {
        $giftVoucherOrder->update(['fulfilled_at' => now()]);
    }

    private function generateCode(): string
    {
        do {
            $code = 'GIFT-'
                . strtoupper(Str::random(4)) . '-'
                . strtoupper(Str::random(4)) . '-'
                . strtoupper(Str::random(4));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }
}
