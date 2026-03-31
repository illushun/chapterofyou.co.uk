<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Your Order Has Been Dispatched!</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background-color: #f9ece8; font-family: 'Cormorant Garamond', Georgia, serif; color: #3d2b2b; }
    .email-wrapper {
        max-width: 600px; margin: 0 auto; background-color: #fdf5f2;
        background-image:
            radial-gradient(ellipse at 10% 15%, rgba(212,160,140,0.35) 0%, transparent 55%),
            radial-gradient(ellipse at 90% 80%, rgba(212,160,140,0.28) 0%, transparent 55%),
            radial-gradient(ellipse at 50% 50%, rgba(249,236,232,1) 0%, rgba(245,224,218,1) 100%);
    }
    .email-body { padding: 48px 40px 40px; text-align: center; }
    .greeting-script { font-family: 'Great Vibes', cursive; font-size: 42px; color: #b85050; line-height: 1.2; margin-bottom: 6px; }
    .gold-divider { border: none; border-top: 1.5px solid #c9a84c; opacity: 0.7; margin: 20px auto; width: 80%; }
    .section-heading { font-family: 'Great Vibes', cursive; font-size: 34px; color: #b85050; margin-bottom: 14px; }
    .dispatch-badge {
        display: inline-block; background: rgba(122,154,106,0.15); border: 1.5px solid #7a9a6a;
        border-radius: 8px; padding: 12px 24px; margin: 8px auto 16px; color: #4a7a3a; font-size: 18px; font-weight: 600;
    }
    .items-table { margin: 0 auto 12px; width: 80%; border-collapse: collapse; }
    .items-table td { padding: 4px 8px; font-size: 17px; font-weight: 400; color: #3d2b2b; text-align: center; }
    .items-table .divider-row td { border-top: 1px solid rgba(201,168,76,0.4); padding-top: 10px; }
    .total-line { font-size: 18px; font-weight: 600; color: #3d2b2b; }
    .body-para { font-size: 17px; line-height: 1.65; color: #3d2b2b; max-width: 420px; margin: 0 auto; }
    .signoff-script { font-family: 'Great Vibes', cursive; font-size: 38px; color: #b85050; line-height: 1.3; margin-top: 4px; }
    .tagline { font-family: 'Cormorant Garamond', Georgia, serif; font-style: italic; font-size: 15px; color: #8a6a5a; margin-top: 6px; }
    @media only screen and (max-width: 480px) {
        .email-body { padding: 32px 20px 28px; }
        .greeting-script { font-size: 34px; }
        .section-heading { font-size: 28px; }
        .signoff-script { font-size: 32px; }
        .items-table { width: 100%; }
    }
</style>
</head>
<body>
<div class="email-wrapper">
<div class="email-body">

    <div class="greeting-script">Hello {{ $firstName }},</div>
    <div class="greeting-script" style="font-size:34px; margin-top:-4px;">Your order is on its way! 🎉</div>

    <hr class="gold-divider" />

    <div class="dispatch-badge">✦ Your order has been dispatched ✦</div>

    <p class="body-para" style="margin-bottom: 0;">
        Wonderful news — your order has left us and is making its way to you.
        Please allow a few days for delivery depending on your location.
    </p>

    <hr class="gold-divider" />

    <div class="section-heading">Your Order</div>

    <table class="items-table">
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['name'] }} &times; {{ $item['quantity'] }}</td>
            </tr>
            @endforeach
            <tr class="divider-row">
                <td class="total-line">
                    Total: <strong>£{{ number_format($total, 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <hr class="gold-divider" />

    <p class="body-para">
        Delivering to: <strong>{{ $shippingAddress['name'] }}</strong><br />
        {{ $shippingAddress['line1'] }}@if($shippingAddress['line2']), {{ $shippingAddress['line2'] }}@endif,
        {{ $shippingAddress['city'] }}, {{ $shippingAddress['zip'] }}
    </p>

    <hr class="gold-divider" />

    <p class="body-para">
        I truly hope you love your <em>signature scent</em> — it was crafted especially for you.
        If you have any questions about your delivery please reply to this email.
    </p>

    <hr class="gold-divider" />

    <div style="margin-top: 8px;">
        <div class="signoff-script" style="font-size:30px; color:#8a6a5a;">Love,</div>
        <div class="signoff-script">Chapter of You 🤍</div>
        <div class="tagline">Your chapter, your self-care</div>
    </div>

    <hr class="gold-divider" style="margin-top: 28px;" />
    <p style="font-size:12px; color:#a08070; margin-top:12px; line-height:1.5;">
        Order #COY-0000{{ $orderId }}
    </p>

</div>
</div>
</body>
</html>
