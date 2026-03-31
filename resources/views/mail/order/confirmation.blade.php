<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Order Confirmation #{{ $orderId }}</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&display=swap');

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background-color: #f9ece8;
        font-family: 'Cormorant Garamond', Georgia, serif;
        color: #3d2b2b;
        -webkit-font-smoothing: antialiased;
    }

    .email-wrapper {
        max-width: 600px;
        margin: 0 auto;
        background-color: #fdf5f2;
        /*
         * Blush/rose background gradient to evoke the image.
         * Email clients that don't support gradients fall back
         * to the solid background-color above.
         */
        background-image:
            radial-gradient(ellipse at 10% 15%, rgba(212,160,140,0.35) 0%, transparent 55%),
            radial-gradient(ellipse at 90% 80%, rgba(212,160,140,0.28) 0%, transparent 55%),
            radial-gradient(ellipse at 50% 50%, rgba(249,236,232,1) 0%, rgba(245,224,218,1) 100%);
    }

    .email-body {
        padding: 48px 40px 40px;
        text-align: center;
    }

    /* ── Greeting ── */
    .greeting-script {
        font-family: 'Great Vibes', cursive;
        font-size: 42px;
        color: #b85050;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    /* ── Gold divider ── */
    .gold-divider {
        border: none;
        border-top: 1.5px solid #c9a84c;
        opacity: 0.7;
        margin: 20px auto;
        width: 80%;
    }

    /* ── Section heading ── */
    .section-heading {
        font-family: 'Great Vibes', cursive;
        font-size: 38px;
        color: #b85050;
        margin-bottom: 14px;
    }

    /* ── Items list ── */
    .items-table {
        margin: 0 auto 12px;
        width: 80%;
        border-collapse: collapse;
    }
    .items-table td {
        padding: 4px 8px;
        font-size: 17px;
        font-weight: 400;
        color: #3d2b2b;
        text-align: center;
    }
    .items-table .divider-row td {
        border-top: 1px solid rgba(201,168,76,0.4);
        padding-top: 10px;
    }
    .total-line {
        font-size: 18px;
        font-weight: 600;
        color: #3d2b2b;
        margin-top: 4px;
    }

    /* ── Body paragraphs ── */
    .body-para {
        font-size: 17px;
        line-height: 1.65;
        color: #3d2b2b;
        max-width: 420px;
        margin: 0 auto;
    }
    .body-para em {
        font-style: italic;
    }

    /* ── Sign-off ── */
    .signoff-script {
        font-family: 'Great Vibes', cursive;
        font-size: 38px;
        color: #b85050;
        line-height: 1.3;
        margin-top: 4px;
    }
    .tagline {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-style: italic;
        font-size: 15px;
        color: #8a6a5a;
        margin-top: 6px;
        letter-spacing: 0.02em;
    }

    /* ── Order detail box ── */
    .order-detail {
        background: rgba(255,255,255,0.5);
        border: 1px solid rgba(201,168,76,0.3);
        border-radius: 6px;
        padding: 12px 20px;
        margin: 0 auto 16px;
        width: 80%;
        font-size: 15px;
        color: #6b4f4f;
        text-align: left;
    }
    .order-detail strong { color: #3d2b2b; }

    /* ── Responsive ── */
    @media only screen and (max-width: 480px) {
        .email-body { padding: 32px 20px 28px; }
        .greeting-script { font-size: 34px; }
        .section-heading { font-size: 28px; }
        .signoff-script { font-size: 32px; }
        .items-table, .order-detail { width: 100%; }
    }
</style>
</head>
<body>
<div class="email-wrapper">
<div class="email-body">

    {{-- ── Greeting ── --}}
    <div class="greeting-script">Hello {{ $firstName }},</div>
    <div class="greeting-script" style="font-size:36px; margin-top:-4px;">Thank you for your order!</div>

    <hr class="gold-divider" />

    {{-- ── Items ── --}}
    <div class="section-heading">Your Chosen Scents</div>

    <table class="items-table">
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['name'] }} &times; {{ $item['quantity'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Order cost breakdown ── --}}
    <table class="items-table" style="margin-top: 8px;">
        <tbody>
            <tr class="divider-row">
                <td style="text-align:left; color:#6b4f4f; font-size:16px;">Subtotal</td>
                <td style="text-align:right; color:#6b4f4f; font-size:16px;">£{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($voucherDiscount > 0)
            <tr>
                <td style="text-align:left; color:#7a9a6a; font-size:16px;">Discount</td>
                <td style="text-align:right; color:#7a9a6a; font-size:16px;">&minus;£{{ number_format($voucherDiscount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td style="text-align:left; color:#6b4f4f; font-size:16px;">VAT (20%)</td>
                <td style="text-align:right; color:#6b4f4f; font-size:16px;">£{{ number_format($tax, 2) }}</td>
            </tr>
            <tr>
                <td style="text-align:left; color:#6b4f4f; font-size:16px;">Shipping</td>
                <td style="text-align:right; color:#6b4f4f; font-size:16px;">
                    @if($shipping == 0) FREE @else £{{ number_format($shipping, 2) }} @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-top: 1px solid rgba(201,168,76,0.4); padding-top: 8px;"></td>
            </tr>
            <tr>
                <td class="total-line" style="text-align:left;">Order Total</td>
                <td class="total-line" style="text-align:right;">£{{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <hr class="gold-divider" />

    {{-- ── Dispatch message ── --}}
    <p class="body-para" style="margin-bottom: 0;">
        Your items are now being prepared, and you'll be notified once they are dispatched.
    </p>

    <hr class="gold-divider" />

    {{-- ── Handcrafted message ── --}}
    <p class="body-para">
        Please note all orders are made to order and are carefully handcrafted just for you, making every piece truly personal and unique.
        <em>Because of this, please allow 2–3 working days for me to create your order before dispatch.</em>
    </p>

    <hr class="gold-divider" />

    {{-- ── Appreciation ── --}}
    <p class="body-para">
        I truly appreciate your patience, and I can't wait for you to experience <em>your</em> <div style="font-family: 'Great Vibes', cursive; font-size: 15px;">signature scent</div>
    </p>

    <hr class="gold-divider" />

    {{-- ── Sign-off ── --}}
    <div style="margin-top: 8px;">
        <div class="signoff-script" style="font-size:30px; color:#8a6a5a;">Love,</div>
        <div class="signoff-script">Chapter of You 🤍</div>
        <div class="tagline">Your chapter, your self-care</div>
    </div>

    {{-- ── Small print ── --}}
    <hr class="gold-divider" style="margin-top: 28px;" />
    <p style="font-size:12px; color:#a08070; margin-top:12px; line-height:1.5;">
        Order #COY-0000{{ $orderId }} · Sent to {{ $shippingAddress['name'] }}<br />
        {{ $shippingAddress['line1'] }}@if($shippingAddress['line2']), {{ $shippingAddress['line2'] }}@endif,
        {{ $shippingAddress['city'] }}, {{ $shippingAddress['zip'] }}<br /><br />
        If you have any questions please email contact@chapterofyou.co.uk
    </p>

</div>
</div>
</body>
</html>
