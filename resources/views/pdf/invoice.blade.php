<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Serif', Georgia, serif;
        font-size: 12px;
        color: #2d1a1a;
        background: #fff;
    }

    /* ─────────────────────────────────────────────
       TOP ACCENT BAR
    ───────────────────────────────────────────── */
    .accent-bar {
        height: 5px;
        background: #c47078;
        margin-bottom: 0;
    }

    /* ─────────────────────────────────────────────
       HEADER
    ───────────────────────────────────────────── */
    .header {
        background: #fdf4f3;
        padding: 28px 36px 24px;
        margin-bottom: 0;
        overflow: hidden;
        border-bottom: 1px solid #e5c9c7;
    }
    .header-left  { float: left; }
    .header-right { float: right; text-align: right; }
    .clearfix::after { content: ''; display: table; clear: both; }

    .brand-name {
        font-size: 28px;
        font-weight: 700;
        color: #8c4a50;
        letter-spacing: -0.01em;
        margin-bottom: 2px;
    }
    .brand-tagline {
        font-size: 10px;
        font-style: italic;
        color: #c9a4a4;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
    }
    .brand-contact {
        font-size: 10px;
        color: #6b4f4f;
        line-height: 1.7;
    }

    .doc-type {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        color: #c9a4a4;
        margin-bottom: 6px;
    }
    .invoice-number {
        font-size: 22px;
        font-weight: 700;
        color: #2d1a1a;
        margin-bottom: 8px;
    }
    .invoice-number span { color: #8c4a50; }
    .invoice-date {
        font-size: 10px;
        color: #6b4f4f;
        line-height: 1.7;
    }

    /* ─────────────────────────────────────────────
       BODY WRAPPER
    ───────────────────────────────────────────── */
    .body {
        padding: 28px 36px;
    }

    /* ─────────────────────────────────────────────
       ADDRESSES
    ───────────────────────────────────────────── */
    .addresses {
        overflow: hidden;
        margin-bottom: 28px;
    }
    .addr-col { float: left; width: 46%; }
    .addr-col + .addr-col { float: right; }

    .addr-heading {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: #c9a4a4;
        margin-bottom: 8px;
        padding-bottom: 5px;
        border-bottom: 1px solid #e5c9c7;
    }
    .addr-name {
        font-size: 13px;
        font-weight: 700;
        color: #2d1a1a;
        margin-bottom: 5px;
    }
    .addr-line {
        font-size: 11px;
        color: #6b4f4f;
        line-height: 1.75;
    }
    .addr-line strong { color: #2d1a1a; font-weight: 700; }

    /* ─────────────────────────────────────────────
       DIVIDER WITH PETAL
    ───────────────────────────────────────────── */
    .petal-divider {
        text-align: center;
        margin: 0 0 22px;
        color: #e5c9c7;
        font-size: 14px;
        line-height: 1;
        position: relative;
    }
    .petal-divider::before,
    .petal-divider::after {
        content: '';
        display: inline-block;
        vertical-align: middle;
        width: 38%;
        height: 1px;
        background: #e5c9c7;
        margin: 0 8px;
    }

    /* ─────────────────────────────────────────────
       ITEMS TABLE
    ───────────────────────────────────────────── */
    .section-label {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: #c9a4a4;
        margin-bottom: 8px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
    }
    .items-table thead tr {
        background: #2d1a1a;
    }
    .items-table th {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #fffafa;
        padding: 9px 14px;
        text-align: left;
    }
    .items-table th.r { text-align: right; }

    .items-table td {
        padding: 11px 14px;
        font-size: 12px;
        color: #2d1a1a;
        border-bottom: 1px solid #f0dcd8;
        vertical-align: middle;
    }
    .items-table td.r { text-align: right; }
    .items-table td.muted { color: #9a7070; font-size: 10px; }
    .items-table tr:last-child td { border-bottom: none; }
    .items-table tbody tr:nth-child(even) td { background: #fffafa; }

    .item-name { font-weight: 600; color: #2d1a1a; }

    /* ─────────────────────────────────────────────
       TOTALS
    ───────────────────────────────────────────── */
    .totals-wrap {
        float: right;
        width: 270px;
        margin-bottom: 28px;
    }
    .totals-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5c9c7;
        border-radius: 8px;
        overflow: hidden;
    }
    .totals-table td {
        padding: 8px 14px;
        font-size: 12px;
        color: #6b4f4f;
        border-bottom: 1px solid #f0dcd8;
    }
    .totals-table tr:last-child td { border-bottom: none; }
    .totals-table td:last-child {
        text-align: right;
        font-weight: 600;
        color: #2d1a1a;
    }
    .totals-table .row-bg td { background: #fffafa; }
    .totals-table .row-discount td { color: #2d7a3a; }
    .totals-table .row-discount td:last-child { color: #2d7a3a; }
    .totals-table .row-vat td {
        font-size: 10.5px;
        font-style: italic;
        color: #9a7070;
    }
    .totals-table .row-vat td:last-child { color: #9a7070; font-weight: 400; }
    .totals-table .row-grand td {
        background: #2d1a1a;
        color: #fffafa;
        font-size: 13px;
        font-weight: 700;
        padding: 11px 14px;
    }
    .totals-table .row-grand td:last-child {
        color: #f2c4ce;
        font-size: 15px;
    }

    /* ─────────────────────────────────────────────
       VAT NOTICE
    ───────────────────────────────────────────── */
    .vat-notice {
        clear: both;
        background: #fffafa;
        border: 1px solid #e5c9c7;
        border-left: 3px solid #c9a84c;
        border-radius: 0 6px 6px 0;
        padding: 10px 14px;
        font-size: 10.5px;
        color: #6b4f4f;
        line-height: 1.7;
        margin-bottom: 28px;
    }
    .vat-notice strong { color: #2d1a1a; }

    /* ─────────────────────────────────────────────
       FOOTER
    ───────────────────────────────────────────── */
    .footer {
        border-top: 1px solid #e5c9c7;
        padding-top: 14px;
        overflow: hidden;
    }
    .footer-left {
        float: left;
        font-size: 10px;
        color: #9a7070;
        line-height: 1.7;
    }
    .footer-left strong { color: #8c4a50; }
    .footer-right {
        float: right;
        font-size: 18px;
        color: #e5c9c7;
        letter-spacing: 0.15em;
    }

    /* ─────────────────────────────────────────────
       STATUS PILL
    ───────────────────────────────────────────── */
    .status-pill {
        display: inline-block;
        background: #f0faf0;
        border: 1px solid #a8d8b0;
        color: #2d7a3a;
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 3px 9px;
        border-radius: 999px;
    }
</style>
</head>
<body>

{{-- Top accent bar --}}
<div class="accent-bar"></div>

{{-- Header --}}
<div class="header clearfix">
    <div class="header-left">
        <div class="brand-name">Chapter of You</div>
        <div class="brand-tagline">Your chapter, your self-care</div>
        <div class="brand-contact">
            contact@chapterofyou.co.uk<br/>
            www.chapterofyou.co.uk<br/>
            @if($vatNumber)
                VAT Reg No: {{ $vatNumber }}
            @endif
        </div>
    </div>
    <div class="header-right">
        <div class="doc-type">Invoice</div>
        <div class="invoice-number">#COY-<span>{{ $order->id }}</span></div>
        <div class="invoice-date">
            <strong style="color:#2d1a1a;">Date:</strong> {{ $order->created_at->format('d F Y') }}<br/>
            <strong style="color:#2d1a1a;">Payment:</strong> <span class="status-pill">Paid</span>
        </div>
    </div>
</div>

{{-- Body --}}
<div class="body">

    {{-- Addresses --}}
    <div class="addresses clearfix">
        <div class="addr-col">
            <div class="addr-heading">Billed &amp; Shipped To</div>
            <div class="addr-name">{{ $order->first_name }} {{ $order->last_name }}</div>
            <div class="addr-line">
                {{ $order->email }}<br/>
                @if($order->telephone){{ $order->telephone }}<br/>@endif
                {{ $order->shipping_line_1 }}<br/>
                @if($order->shipping_line_2){{ $order->shipping_line_2 }}<br/>@endif
                {{ $order->shipping_city }}@if($order->shipping_county), {{ $order->shipping_county }}@endif<br/>
                {{ $order->shipping_postcode }}<br/>
                {{ $order->shipping_country }}
            </div>
        </div>
        <div class="addr-col">
            <div class="addr-heading">Payment Details</div>
            <div class="addr-line">
                <strong>Method:</strong> {{ ucfirst($order->payment_type) }}<br/>
                <strong>Order ref:</strong> #COY-{{ $order->id }}<br/>
                <strong>Transaction:</strong><br/>
                <span style="font-size:10px; word-break:break-all;">{{ $order->payment_intent_id }}</span>
            </div>
        </div>
    </div>

    {{-- Petal divider --}}
    <div class="petal-divider">&#10047;</div>

    {{-- Items --}}
    <div class="section-label">Items Ordered</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%;">Description</th>
                <th class="r" style="width:15%;">Qty</th>
                <th class="r" style="width:18%;">Unit Price</th>
                <th class="r" style="width:17%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td><span class="item-name">{{ $item->product->name ?? 'Product' }}</span></td>
                <td class="r">{{ $item->quantity }}</td>
                <td class="r">£{{ number_format($item->product_cost, 2) }}</td>
                <td class="r">£{{ number_format($item->product_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tr class="row-bg">
                <td>Subtotal (inc. VAT)</td>
                <td>£{{ number_format($order->cost_total, 2) }}</td>
            </tr>
            @if($order->voucher_discount > 0)
            <tr class="row-discount">
                <td>Discount applied</td>
                <td>&minus;£{{ number_format($order->voucher_discount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Shipping</td>
                <td>{{ $order->shipping_total == 0 ? 'FREE' : '£' . number_format($order->shipping_total, 2) }}</td>
            </tr>
            <tr class="row-vat">
                <td>VAT included (20%)</td>
                <td>£{{ number_format($order->tax_total, 2) }}</td>
            </tr>
            <tr class="row-grand">
                <td>Total Paid</td>
                <td>£{{ number_format($order->grand_total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="clear:both;"></div>

    {{-- VAT notice --}}
    <div class="vat-notice">
        @if($vatNumber)
            <strong>VAT Receipt:</strong> All prices include VAT at the standard UK rate of 20%.
            VAT included in this invoice: <strong>£{{ number_format($order->tax_total, 2) }}</strong>.
            Chapter of You — VAT Registration No: <strong>{{ $vatNumber }}</strong>
        @else
            <strong>VAT Note:</strong> All prices include VAT at the standard UK rate of 20%.
            VAT included in this invoice: <strong>£{{ number_format($order->tax_total, 2) }}</strong>.
            Chapter of You is not currently VAT registered, VAT is paid under the Making Tax Digital flat rate scheme.
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer clearfix">
        <div class="footer-left">
            <strong>Chapter of You</strong> &nbsp;&middot;&nbsp; chapterofyou.co.uk &nbsp;&middot;&nbsp; contact@chapterofyou.co.uk<br/>
            Thank you for your order. This document serves as your VAT receipt.
        </div>
        <div class="footer-right">&#10047; &#10047; &#10047;</div>
    </div>

</div>
</body>
</html>
