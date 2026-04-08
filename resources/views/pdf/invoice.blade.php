<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 13px;
        color: #2d1a1a;
        background: #fff;
    }

    /* ── Header ── */
    .header {
        border-bottom: 2px solid #c47078;
        padding-bottom: 20px;
        margin-bottom: 28px;
        overflow: hidden;
    }
    .brand { float: left; }
    .brand-name {
        font-size: 26px;
        font-weight: 700;
        color: #8c4a50;
        letter-spacing: -0.01em;
    }
    .brand-tagline {
        font-size: 11px;
        color: #9a7070;
        font-style: italic;
        margin-top: 2px;
    }
    .brand-contact {
        font-size: 11px;
        color: #6b4f4f;
        margin-top: 6px;
        line-height: 1.6;
    }
    .invoice-meta { float: right; text-align: right; }
    .invoice-label {
        font-size: 22px;
        font-weight: 700;
        color: #2d1a1a;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .invoice-number {
        font-size: 15px;
        color: #8c4a50;
        font-weight: 600;
        margin-top: 4px;
    }
    .invoice-date { font-size: 12px; color: #6b4f4f; margin-top: 3px; }
    .clearfix::after { content: ''; display: table; clear: both; }

    /* ── Addresses ── */
    .addresses {
        overflow: hidden;
        margin-bottom: 28px;
        background: #fdf4f3;
        border-radius: 6px;
        padding: 16px 20px;
    }
    .addr-block { float: left; width: 48%; }
    .addr-block + .addr-block { float: right; text-align: left; }
    .addr-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #c9a4a4;
        margin-bottom: 6px;
    }
    .addr-name { font-weight: 700; font-size: 13px; color: #2d1a1a; margin-bottom: 3px; }
    .addr-line { font-size: 12px; color: #6b4f4f; line-height: 1.65; }

    /* ── Items table ── */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .items-table th {
        background: #2d1a1a;
        color: #fffafa;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 9px 12px;
        text-align: left;
    }
    .items-table th:last-child,
    .items-table th:nth-child(2),
    .items-table th:nth-child(3) { text-align: right; }

    .items-table td {
        padding: 10px 12px;
        font-size: 12px;
        color: #2d1a1a;
        border-bottom: 1px solid #f0dcd8;
    }
    .items-table td:last-child,
    .items-table td:nth-child(2),
    .items-table td:nth-child(3) { text-align: right; }
    .items-table tr:last-child td { border-bottom: none; }
    .items-table tbody tr:nth-child(even) td { background: #fffafa; }

    /* ── Totals ── */
    .totals-wrap { float: right; width: 260px; margin-bottom: 28px; }
    .totals-table { width: 100%; border-collapse: collapse; }
    .totals-table td {
        padding: 6px 10px;
        font-size: 12px;
        color: #6b4f4f;
    }
    .totals-table td:last-child { text-align: right; font-weight: 600; color: #2d1a1a; }
    .totals-table .discount td { color: #2d7a3a; }
    .totals-table .grand td {
        font-size: 15px;
        font-weight: 700;
        color: #2d1a1a;
        border-top: 2px solid #c47078;
        padding-top: 10px;
        margin-top: 4px;
    }
    .totals-table .grand td:last-child { color: #8c4a50; }
    .totals-table .vat-note td {
        font-size: 11px;
        font-style: italic;
        color: #9a7070;
    }

    /* ── VAT notice ── */
    .vat-notice {
        clear: both;
        background: #fdf4f3;
        border-left: 3px solid #c9a84c;
        border-radius: 0 6px 6px 0;
        padding: 10px 14px;
        font-size: 11px;
        color: #6b4f4f;
        line-height: 1.6;
        margin-bottom: 24px;
    }
    .vat-notice strong { color: #2d1a1a; }

    /* ── Footer ── */
    .footer {
        border-top: 1px solid #f0dcd8;
        padding-top: 14px;
        text-align: center;
        font-size: 11px;
        color: #9a7070;
        line-height: 1.6;
    }
    .footer strong { color: #8c4a50; }
</style>
</head>
<body>

<!-- Header -->
<div class="header clearfix">
    <div class="brand">
        <div class="brand-name">Chapter of You</div>
        <div class="brand-tagline">Your chapter, your self-care</div>
        <div class="brand-contact">
            contact@chapterofyou.co.uk<br />
            www.chapterofyou.co.uk<br />
            @if($vatNumber)
                VAT No: {{ $vatNumber }}
            @endif
        </div>
    </div>
    <div class="invoice-meta">
        <div class="invoice-label">Invoice</div>
        <div class="invoice-number">#COY-{{ $order->id }}</div>
        <div class="invoice-date">
            Date: {{ $order->created_at->format('d F Y') }}<br />
            Order date: {{ $order->created_at->format('d/m/Y') }}
        </div>
    </div>
</div>

<!-- Addresses -->
<div class="addresses clearfix">
    <div class="addr-block">
        <div class="addr-label">Billed / Shipped To</div>
        <div class="addr-name">{{ $order->first_name }} {{ $order->last_name }}</div>
        <div class="addr-line">
            {{ $order->email }}<br />
            @if($order->telephone) {{ $order->telephone }}<br /> @endif
            {{ $order->shipping_line_1 }}<br />
            @if($order->shipping_line_2) {{ $order->shipping_line_2 }}<br /> @endif
            {{ $order->shipping_city }}@if($order->shipping_county), {{ $order->shipping_county }}@endif<br />
            {{ $order->shipping_postcode }}<br />
            {{ $order->shipping_country }}
        </div>
    </div>
    <div class="addr-block">
        <div class="addr-label">Payment</div>
        <div class="addr-line">
            <strong>Method:</strong> {{ ucfirst($order->payment_type) }}<br />
            <strong>Status:</strong> Paid<br />
            <strong>Reference:</strong> {{ $order->payment_intent_id }}
        </div>
    </div>
</div>

<!-- Items -->
<table class="items-table">
    <thead>
        <tr>
            <th>Description</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product->name ?? 'Product' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>£{{ number_format($item->product_cost, 2) }}</td>
            <td>£{{ number_format($item->product_total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Totals -->
<div class="totals-wrap">
    <table class="totals-table">
        <tr>
            <td>Subtotal (inc. VAT)</td>
            <td>£{{ number_format($order->cost_total, 2) }}</td>
        </tr>
        @if($order->voucher_discount > 0)
        <tr class="discount">
            <td>Discount</td>
            <td>−£{{ number_format($order->voucher_discount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td>Shipping</td>
            <td>{{ $order->shipping_total == 0 ? 'FREE' : '£' . number_format($order->shipping_total, 2) }}</td>
        </tr>
        <tr class="vat-note">
            <td>VAT (20% included)</td>
            <td>£{{ number_format($order->tax_total, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>Total Paid</td>
            <td>£{{ number_format($order->grand_total, 2) }}</td>
        </tr>
    </table>
</div>

<div style="clear:both;"></div>

<!-- VAT notice -->
<div class="vat-notice">
    @if($vatNumber)
        <strong>VAT Notice:</strong> All prices include VAT at the standard rate of 20%.
        VAT amount included: <strong>£{{ number_format($order->tax_total, 2) }}</strong>.
        Chapter of You VAT Registration No: {{ $vatNumber }}
    @else
        <strong>Note:</strong> All prices include VAT at the standard rate of 20%.
        VAT amount included in total: <strong>£{{ number_format($order->tax_total, 2) }}</strong>.
        Chapter of You is not currently VAT registered, VAT is paid under the Making Tax Digital flat rate scheme.
    @endif
</div>

<!-- Footer -->
<div class="footer">
    <strong>Chapter of You</strong> · chapterofyou.co.uk · contact@chapterofyou.co.uk<br />
    Thank you for your order. This document serves as your VAT receipt.
</div>

</body>
</html>
