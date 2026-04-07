<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Your Order Has Been Dispatched!</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background-color: #f9ece8; font-family: 'Cormorant Garamond', Georgia, serif; color: #3d2b2b; -webkit-font-smoothing: antialiased; }
    .email-wrapper {
        max-width: 600px; margin: 0 auto; background-color: #fdf5f2;
        background-image:
            radial-gradient(ellipse at 10% 15%, rgba(212,160,140,0.35) 0%, transparent 55%),
            radial-gradient(ellipse at 90% 80%, rgba(212,160,140,0.28) 0%, transparent 55%),
            radial-gradient(ellipse at 50% 50%, rgba(249,236,232,1) 0%, rgba(245,224,218,1) 100%);
    }
    .email-body { padding: 48px 40px 40px; text-align: center; }
    .greeting-script { font-family: 'Great Vibes', cursive; font-size: 42px; color: #b85050; line-height: 1.2; margin-bottom: 6px; }
    .dispatch-intro { font-size: 18px; line-height: 1.5; color: #3d2b2b; margin-top: 4px; }
    .gold-divider { border: none; border-top: 1.5px solid #c9a84c; opacity: 0.7; margin: 22px auto; width: 80%; }
    .section-heading { font-family: 'Great Vibes', cursive; font-size: 34px; color: #b85050; margin-bottom: 14px; }
    .items-table { margin: 0 auto 12px; width: 80%; border-collapse: collapse; }
    .items-table td { padding: 4px 8px; font-size: 17px; font-weight: 400; color: #3d2b2b; text-align: center; }
    .items-table .divider-row td { border-top: 1px solid rgba(201,168,76,0.4); padding-top: 10px; }
    .total-line { font-size: 18px; font-weight: 600; color: #3d2b2b; }
    .body-para { font-size: 17px; line-height: 1.65; color: #3d2b2b; max-width: 420px; margin: 0 auto; }
    .tracking-link {
        display: inline-block; margin-top: 10px;
        font-size: 17px; color: #b85050; text-decoration: underline;
        font-family: 'Cormorant Garamond', Georgia, serif;
    }
    .contact-email { font-size: 17px; font-weight: 700; color: #3d2b2b; }
    .signoff-script { font-family: 'Great Vibes', cursive; font-size: 38px; color: #b85050; line-height: 1.3; margin-top: 4px; }
    .tagline { font-style: italic; font-size: 15px; color: #8a6a5a; margin-top: 6px; letter-spacing: 0.02em; }
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

    {{-- Greeting --}}
    <div class="greeting-script">Hello {{ $firstName }},</div>
    <p class="dispatch-intro">Exciting news... your order has now been dispatched! 🎉</p>

    <hr class="gold-divider" />

    {{-- Items --}}
    <div class="section-heading">Your Chosen Scents</div>
    <table class="items-table">
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
            </tr>
            @endforeach
            <tr class="divider-row">
                <td class="total-line">Total: <strong>£{{ number_format($total, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <hr class="gold-divider" />

    {{-- Dispatch info + tracking --}}
    <p class="body-para">
        Your parcel is now on its way to you,<br />and you should receive it soon.
    </p>

    @if(!empty($trackingUrl))
        <p class="body-para" style="margin-top: 16px;">You can track your order here:</p>
        <a href="{{ $trackingUrl }}" class="tracking-link">Tracking Link</a>
    @endif

    <hr class="gold-divider" />

    {{-- Closing message --}}
    <p class="body-para">
        I truly hope you love your order as much as I loved creating it for you! 🤍
    </p>

    <p class="body-para" style="margin-top: 18px;">
        If you have any questions regarding your order,<br />
        please don't hesitate to email me at<br />
        <span class="contact-email">contact@chapterofyou.co.uk</span>
    </p>

    <hr class="gold-divider" />

    {{-- Sign-off --}}
    <div style="margin-top: 8px;">
        <div class="signoff-script" style="font-size:30px; color:#8a6a5a;">Love,</div>
        <div class="signoff-script">Chapter of You 🤍</div>
        <div class="tagline">Your chapter, your self-care</div>
    </div>

    <hr class="gold-divider" style="margin-top: 28px;" />
    <p style="font-size:12px; color:#a08070; margin-top:12px; line-height:1.5;">
        Order #COY-{{ $orderId }}
    </p>

</div>
</div>
</body>
</html>
