<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>You left something behind</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background-color: #f9ece8; font-family: 'Cormorant Garamond', Georgia, serif; color: #3d2b2b; -webkit-font-smoothing: antialiased; }
    .wrapper { max-width: 600px; margin: 0 auto; background-color: #fdf5f2; background-image: radial-gradient(ellipse at 10% 15%, rgba(212,160,140,0.35) 0%, transparent 55%), radial-gradient(ellipse at 90% 80%, rgba(212,160,140,0.28) 0%, transparent 55%); }
    .body { padding: 48px 40px 40px; text-align: center; }
    .greeting-script { font-family: 'Great Vibes', cursive; font-size: 42px; color: #b85050; line-height: 1.2; margin-bottom: 6px; }
    .gold-divider { border: none; border-top: 1.5px solid #c9a84c; opacity: 0.7; margin: 20px auto; width: 80%; }
    .body-para { font-size: 17px; line-height: 1.65; color: #3d2b2b; max-width: 420px; margin: 0 auto 16px; }
    .section-heading { font-family: 'Great Vibes', cursive; font-size: 34px; color: #b85050; margin-bottom: 14px; }

    /* Items */
    .items-table { margin: 0 auto 16px; width: 90%; border-collapse: collapse; }
    .item-row td { padding: 10px 8px; border-bottom: 1px solid rgba(201,168,76,0.2); text-align: left; font-size: 16px; color: #3d2b2b; }
    .item-img { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #e5c9c7; }
    .item-name { font-weight: 600; font-size: 16px; color: #2d1a1a; display: block; margin-bottom: 2px; }
    .item-meta { font-size: 14px; color: #8a6a5a; }
    .item-price { text-align: right !important; font-weight: 600; font-size: 17px; color: #8c4a50; white-space: nowrap; }

    /* Total */
    .total-row td { padding: 12px 8px; font-size: 18px; font-weight: 600; color: #2d1a1a; border-top: 1.5px solid rgba(201,168,76,0.5); }
    .total-row td:last-child { text-align: right; color: #8c4a50; }

    /* CTA */
    .cta-btn { display: inline-block; margin: 8px 0 4px; padding: 14px 36px; background: #b85050; color: #fff !important; font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 600; text-decoration: none; border-radius: 4px; letter-spacing: 0.03em; }

    .signoff-script { font-family: 'Great Vibes', cursive; font-size: 38px; color: #b85050; line-height: 1.3; margin-top: 4px; }
    .tagline { font-style: italic; font-size: 15px; color: #6b4f4f; margin-top: 6px; letter-spacing: 0.02em; }
    .small-print { font-size: 13px; color: #7a5a48; margin-top: 12px; line-height: 1.6; }
    .small-print a { color: #7a5a48; }

    @media only screen and (max-width: 480px) {
        .body { padding: 32px 20px 28px; }
        .greeting-script { font-size: 34px; }
        .items-table { width: 100%; }
    }
</style>
</head>
<body>
<div class="wrapper">
<div class="body">

    <div class="greeting-script">Hello {{ $firstName }},</div>
    <div class="greeting-script" style="font-size:34px; margin-top:-4px;">
        You left something behind
    </div>

    <hr class="gold-divider" />

    <p class="body-para">
        Your basket has been patiently waiting for you. Life gets busy, I understand.
        Your chosen scents are still here whenever you're ready.
    </p>

    <div class="section-heading">Your Basket</div>

    <table class="items-table">
        <tbody>
            @foreach($items as $item)
            <tr class="item-row">
                <td style="width:70px;">
                    @if($item['image'])
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="item-img" />
                    @endif
                </td>
                <td>
                    <span class="item-name">{{ $item['name'] }}</span>
                    <span class="item-meta">Quantity: {{ $item['quantity'] }}</span>
                </td>
                <td class="item-price">£{{ number_format($item['subtotal'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">Basket Total</td>
                <td>£{{ number_format($cartTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <a href="{{ $checkoutUrl }}" class="cta-btn">Complete Your Order →</a>

    <hr class="gold-divider" style="margin-top: 28px;" />

    <div style="margin-top: 8px;">
        <div class="signoff-script" style="font-size:30px; color:#8a6a5a;">Love,</div>
        <div class="signoff-script">Chapter of You 🤍</div>
        <div class="tagline">Your chapter, your self-care</div>
    </div>

    <hr class="gold-divider" style="margin-top: 28px;" />
    <p class="small-print">
        If you no longer wish to receive these emails, simply ignore this message, we'll only send one reminder.<br />
        Questions? <a href="mailto:contact@chapterofyou.co.uk">contact@chapterofyou.co.uk</a>
    </p>

</div>
</div>
</body>
</html>
