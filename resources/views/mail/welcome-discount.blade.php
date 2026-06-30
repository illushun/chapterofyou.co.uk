<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your 10% off code — Chapter of You</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #fdf4f3; color: #2d1a1a; padding: 2rem 1rem; }
        .wrapper { max-width: 580px; margin: 0 auto; }
        .header {
            text-align: center; padding: 2rem 1.5rem 1.5rem;
            background: #fffafa; border: 1px solid #e5c9c7;
            border-bottom: none; border-radius: 20px 20px 0 0;
        }
        .brand-name {
            font-family: 'Georgia', serif; font-size: 1.6rem;
            font-style: italic; font-weight: normal; color: #8c4a50; letter-spacing: 0.02em;
        }
        .header-divider { display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-top: 0.85rem; }
        .header-line { display: inline-block; width: 50px; height: 1px; background: #e5c9c7; }
        .header-dot { color: #c9a4a4; font-size: 0.7rem; }
        .card {
            background: #fffafa; border: 1px solid #e5c9c7;
            border-top: none; padding: 2rem 2.5rem;
        }
        .greeting { font-size: 1rem; color: #2d1a1a; margin-bottom: 1.25rem; line-height: 1.6; }
        .body-text { font-size: 0.95rem; line-height: 1.75; color: #3d2222; margin-bottom: 1rem; }
        .code-block {
            text-align: center; background: #fdf4f3;
            border: 2px dashed #e5c9c7; border-radius: 14px; padding: 1.5rem; margin: 1.75rem 0;
        }
        .code-label {
            font-size: 0.7rem; font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.12em; color: #8c4a50; margin-bottom: 0.6rem; font-family: Arial, sans-serif;
        }
        .code-value { font-family: 'Courier New', monospace; font-size: 2rem; font-weight: bold; color: #2d1a1a; letter-spacing: 0.15em; }
        .code-note { font-size: 0.78rem; color: #9a7070; margin-top: 0.6rem; font-family: Arial, sans-serif; }
        .cta-wrap { text-align: center; margin: 1.75rem 0 0.5rem; }
        .cta-btn {
            display: inline-block; padding: 0.85rem 2.25rem; border-radius: 999px;
            background: #a85058; color: #ffffff !important; text-decoration: none;
            font-family: Arial, sans-serif; font-size: 0.95rem; font-weight: bold; letter-spacing: 0.03em;
        }
        .footer {
            background: #fdf4f3; border: 1px solid #e5c9c7; border-top: none;
            border-radius: 0 0 20px 20px; padding: 1.25rem 2rem; text-align: center;
        }
        .footer p { font-size: 0.75rem; color: #9a7070; line-height: 1.6; font-family: Arial, sans-serif; }
        .footer a { color: #8c4a50; text-decoration: none; }
        .petal { font-size: 0.7rem; color: #e5c9c7; letter-spacing: 0.4rem; display: block; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <p class="brand-name">Chapter of You</p>
            <div class="header-divider">
                <span class="header-line"></span>
                <span class="header-dot">&#10022;</span>
                <span class="header-line"></span>
            </div>
        </div>
        <div class="card">
            <p class="greeting">Thank you for joining — here's your little gift.</p>
            <p class="body-text">
                Welcome to the Chapter of You community. I'm so glad you're here.
            </p>
            <p class="body-text">
                As a thank you for signing up, I'd love to give you 10% off your first order.
                Use the code below at checkout — it's yours, valid for 30 days.
            </p>
            <div class="code-block">
                <p class="code-label">Your exclusive discount code</p>
                <p class="code-value">{{ $voucherCode }}</p>
                <p class="code-note">10% off &nbsp;·&nbsp; One use &nbsp;·&nbsp; Valid for 30 days</p>
            </div>
            <p class="body-text">
                Head over to the shop and find your scent — every diffuser is hand-poured with care, made just for you.
            </p>
            <div class="cta-wrap">
                <a href="{{ config('app.url') }}/products" class="cta-btn">Shop the Collection</a>
            </div>
        </div>
        <div class="footer">
            <span class="petal">&#10047; &#10022; &#10047;</span>
            <p>
                You received this because you signed up at chapterofyou.co.uk.<br>
                &copy; {{ date('Y') }} Chapter of You. All rights reserved.
            </p>
            <p style="margin-top:0.5rem"><a href="{{ config('app.url') }}">chapterofyou.co.uk</a></p>
        </div>
    </div>
</body>
</html>
