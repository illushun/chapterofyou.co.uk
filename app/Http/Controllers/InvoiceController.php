<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Download a VAT invoice PDF for a given order.
     * Accessible by the order owner or admin.
     */
    public function download(Order $order, Request $request)
    {
        // Security: only the order owner or an admin can download
        if (!Auth::check()) {
            abort(403);
        }

        $user = Auth::user();
        if (!$user->is_admin && $order->user_id !== $user->id) {
            // Also allow guest access via session (same as confirmation page)
            $guestOrderId = session('guest_order_id');
            if ($guestOrderId !== $order->id) {
                abort(403);
            }
        }

        $order->load('items.product');

        $pdf = Pdf::loadView('pdf.invoice', [
            'order'     => $order,
            'vatNumber' => config('app.vat_number'), // Set VAT_NUMBER in .env if registered
        ])->setPaper('a4', 'portrait');

        $filename = 'invoice-COY-' . $order->id . '.pdf';

        return $pdf->download($filename);
    }
}
