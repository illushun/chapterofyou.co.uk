<?php

namespace App\Services;

use App\Models\GiftVoucherOrder;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class GiftVoucherService
{
    /**
     * After a gift voucher is purchased, create the Voucher record
     * and a GiftVoucherOrder tracking row.
     *
     * For e-vouchers the code is sent immediately.
     * For physical vouchers the admin must trigger fulfilment manually.
     */
    public function createFromOrder(
        Order $order,
        float $amount,
        string $deliveryType,      // 'email' or 'physical'
        string $recipientName,
        ?string $recipientEmail,
        ?string $personalMessage,
    ): GiftVoucherOrder {

        // Generate a unique, readable voucher code
        $code = $this->generateCode();

        // Create the Voucher — fixed value, 1-year expiry, single use, all products
        $voucher = Voucher::create([
            'code'                    => $code,
            'description'             => "Gift Voucher — purchased by {$order->first_name} {$order->last_name}",
            'type'                    => 'fixed',
            'value'                   => $amount,
            'minimum_order_value'     => null,
            'applies_to_all_products' => true,
            'stackable'               => false,
            'new_customers_only'      => false,
            'single_use_per_user'     => false,   // enforced by max_uses=1 instead
            'max_uses'                => 1,
            'uses_count'              => 0,
            'valid_from'              => now(),
            'valid_until'             => now()->addYear(),
            'is_active'               => true,
        ]);

        // Create the tracking record
        $giftVoucherOrder = GiftVoucherOrder::create([
            'order_id'         => $order->id,
            'voucher_id'       => $voucher->id,
            'delivery_type'    => $deliveryType,
            'amount'           => $amount,
            'recipient_name'   => $recipientName,
            'recipient_email'  => $recipientEmail,
            'personal_message' => $personalMessage,
            'fulfilled_at'     => null,  // set when code is sent
        ]);

        // Send immediately for e-vouchers
        if ($deliveryType === 'email' && $recipientEmail) {
            $this->sendEVoucher($giftVoucherOrder);
        }

        return $giftVoucherOrder;
    }

    /**
     * Send the e-voucher email to the recipient.
     * Also called by the admin when manually fulfilling a physical voucher.
     */
    public function sendEVoucher(GiftVoucherOrder $giftVoucherOrder): void
    {
        Mail::to($giftVoucherOrder->recipient_email)
            ->send(new \App\Mail\GiftVoucher($giftVoucherOrder));

        $giftVoucherOrder->update(['fulfilled_at' => now()]);
    }

    /**
     * Mark a physical voucher as dispatched (admin action).
     */
    public function markPhysicalDispatched(GiftVoucherOrder $giftVoucherOrder): void
    {
        $giftVoucherOrder->update(['fulfilled_at' => now()]);
    }

    /**
     * Generate a clean, readable voucher code.
     * Format: GIFT-XXXX-XXXX-XXXX
     */
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
