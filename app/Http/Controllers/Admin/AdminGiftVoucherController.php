<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftVoucherOrder;
use App\Services\GiftVoucherService;
use Inertia\Inertia;

class AdminGiftVoucherController extends Controller
{
    public function __construct(private GiftVoucherService $service) {}

    public function index()
    {
        $giftVouchers = GiftVoucherOrder::with(['order', 'voucher'])
            ->latest()
            ->paginate(20);

        return Inertia::render('admin/gift-voucher/Index', [
            'giftVouchers' => $giftVouchers,
        ]);
    }

    public function markDispatched(GiftVoucherOrder $giftVoucherOrder)
    {
        if ($giftVoucherOrder->isFulfilled()) {
            return back()->with('error', 'This voucher has already been fulfilled.');
        }

        $this->service->markPhysicalDispatched($giftVoucherOrder);

        return back()->with('success', "Gift voucher #{$giftVoucherOrder->id} marked as dispatched.");
    }

    public function resendEmail(GiftVoucherOrder $giftVoucherOrder)
    {
        if (! $giftVoucherOrder->isEmail()) {
            return back()->with('error', 'This is a physical voucher - use Mark Dispatched instead.');
        }

        if (! $giftVoucherOrder->recipient_email) {
            return back()->with('error', 'No recipient email address on record.');
        }

        $this->service->sendEVoucher($giftVoucherOrder);

        return back()->with('success', "Voucher code resent to {$giftVoucherOrder->recipient_email}.");
    }
}
