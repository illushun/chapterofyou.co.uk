<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>New Order #COY-{{ $orderId }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background-color: #f4f4f6;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: #1a1a2e;
        -webkit-font-smoothing: antialiased;
    }

    .wrapper {
        max-width: 600px;
        margin: 32px auto;
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* ── Header bar ── */
    .header {
        background: #1a1a2e;
        padding: 24px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .header-brand {
        font-size: 14px;
        font-weight: 700;
        color: #f2c4ce;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .header-badge {
        background: #f2c4ce;
        color: #1a1a2e;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    /* ── Alert banner ── */
    .alert-banner {
        background: #f0faf0;
        border-left: 4px solid #4caf74;
        padding: 16px 32px;
        font-size: 15px;
        color: #2d6a4a;
        font-weight: 600;
    }

    /* ── Body ── */
    .body {
        padding: 28px 32px;
    }

    .order-ref {
        font-size: 22px;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 4px;
    }
    .order-meta {
        font-size: 13px;
        color: #7a7a9a;
        margin-bottom: 24px;
    }

    /* ── Section ── */
    .section-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #7a7a9a;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #ece8e2;
    }

    /* ── Customer box ── */
    .info-box {
        background: #faf9f7;
        border: 1px solid #ece8e2;
        border-radius: 6px;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 14px;
        line-height: 1.7;
        color: #3a3a5c;
    }
    .info-box strong { color: #1a1a2e; }

    /* ── Items table ── */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .items-table th {
        text-align: left;
        padding: 8px 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7a7a9a;
        border-bottom: 1px solid #ece8e2;
    }
    .items-table th:last-child { text-align: right; }
    .items-table td {
        padding: 10px 10px;
        color: #1a1a2e;
        border-bottom: 1px solid #f4f4f6;
    }
    .items-table td:last-child { text-align: right; font-weight: 600; }

    /* ── Totals ── */
    .totals-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-bottom: 24px;
    }
    .totals-table td {
        padding: 5px 10px;
        color: #3a3a5c;
    }
    .totals-table td:last-child { text-align: right; }
    .totals-table .grand-total td {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a2e;
        border-top: 2px solid #ece8e2;
        padding-top: 10px;
    }
    .discount { color: #4caf74 !important; }

    /* ── CTA button ── */
    .cta-wrap { text-align: center; margin-bottom: 8px; }
    .cta-btn {
        display: inline-block;
        background: #1a1a2e;
        color: #ffffff !important;
        font-size: 14px;
        font-weight: 600;
        padding: 12px 28px;
        border-radius: 6px;
        text-decoration: none;
        letter-spacing: 0.02em;
    }

    /* ── Footer ── */
    .footer {
        background: #faf9f7;
        border-top: 1px solid #ece8e2;
        padding: 16px 32px;
        font-size: 12px;
        color: #a0a0b8;
        text-align: center;
        line-height: 1.6;
    }

    @media only screen and (max-width: 480px) {
        .wrapper { margin: 0; border-radius: 0; }
        .header, .body { padding: 20px; }
        .alert-banner { padding: 14px 20px; }
    }
</style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <span class="header-brand">Chapter of You</span>
        <span class="header-badge">New Order</span>
    </div>

    <!-- Alert -->
    <div class="alert-banner">
        A new order has been placed on the website.
    </div>

    <div class="body">

        <!-- Order ref -->
        <div class="order-ref">Order #COY-{{ $orderId }}</div>
        <div class="order-meta">Placed {{ $placedAt }}</div>

        <!-- Customer details -->
        <div class="section-title">Customer</div>
        <div class="info-box">
            <strong>{{ $firstName }} {{ $lastName }}</strong><br />
            {{ $email }}
            @if($telephone)
                <br />{{ $telephone }}
            @endif
        </div>

        <!-- Shipping address -->
        <div class="section-title">Shipping Address</div>
        <div class="info-box">
            {{ $shippingAddress['line_1'] }}
            @if($shippingAddress['line_2'])
                <br />{{ $shippingAddress['line_2'] }}
            @endif
            <br />{{ $shippingAddress['city'] }}
            @if($shippingAddress['county'])
                <br />{{ $shippingAddress['county'] }}
            @endif
            <br />{{ $shippingAddress['postcode'] }}
            <br />{{ $shippingAddress['country'] }}
        </div>

        <!-- Items -->
        <div class="section-title">Items Ordered</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>£{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="section-title">Order Total</div>
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td>£{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($voucherDiscount > 0)
            <tr>
                <td class="discount">Discount</td>
                <td class="discount">−£{{ number_format($voucherDiscount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>VAT (20%)</td>
                <td>£{{ number_format($tax, 2) }}</td>
            </tr>
            <tr>
                <td>Shipping</td>
                <td>{{ $shipping == 0 ? 'FREE' : '£' . number_format($shipping, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Grand Total</td>
                <td>£{{ number_format($total, 2) }}</td>
            </tr>
        </table>

        <!-- View in admin -->
        <div class="cta-wrap">
            <a href="{{ $adminUrl }}" class="cta-btn">View Order in Admina>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer">
        This is an automated notification sent to contact@chapterofyou.co.uk<br />
        Chapter of You · chapterofyou.co.uk
    </div>

</div>
</body>
</html>
