<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function download(Order $order, Request $request)
    {
        $user = $request->user();
        $ownsOrder = $user && $order->user_id === $user->id;
        $ownsGuestOrder = $order->user_id === null && session('guest_order_id') === $order->id;

        abort_unless($user?->is_admin || $ownsOrder || $ownsGuestOrder, 403);

        $order->load('items.product');

        $pdf = Pdf::loadView('pdf.invoice', [
            'order' => $order,
            'vatNumber' => config('app.vat_number'), // Set VAT_NUMBER in .env if registered
        ])->setPaper('a4', 'portrait');

        $filename = 'invoice-COY-'.$order->id.'.pdf';

        return $pdf->download($filename);
    }
}
