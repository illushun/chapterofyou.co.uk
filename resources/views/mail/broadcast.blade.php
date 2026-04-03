<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $subject }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Georgia', serif;
            background: #fdf4f3;
            color: #2d1a1a;
            padding: 2rem 1rem;
        }

        .wrapper {
            max-width: 580px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            padding: 2rem 1.5rem 1.5rem;
            background: #fffafa;
            border: 1px solid #e5c9c7;
            border-bottom: none;
            border-radius: 20px 20px 0 0;
        }

        .brand-name {
            font-family: 'Georgia', serif;
            font-size: 1.6rem;
            font-style: italic;
            font-weight: normal;
            color: #8c4a50;
            letter-spacing: 0.02em;
        }

        .header-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 0.85rem;
        }

        .header-divider-line {
            display: inline-block;
            width: 50px;
            height: 1px;
            background: #e5c9c7;
        }

        .header-divider-dot {
            color: #c9a4a4;
            font-size: 0.7rem;
        }

        /* ── Body card ── */
        .card {
            background: #fffafa;
            border: 1px solid #e5c9c7;
            border-top: none;
            padding: 2rem 2.5rem;
        }

        .greeting {
            font-size: 1rem;
            color: #2d1a1a;
            margin-bottom: 1.5rem;
        }

        .greeting em {
            font-style: normal;
            font-weight: bold;
        }

        .body-content {
            font-size: 0.95rem;
            line-height: 1.75;
            color: #3d2222;
        }

        /* Allow admin-written HTML to render naturally */
        .body-content p  { margin-bottom: 1rem; }
        .body-content h2 { font-size: 1.1rem; font-weight: bold; margin: 1.25rem 0 0.5rem; color: #2d1a1a; }
        .body-content ul,
        .body-content ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .body-content li { margin-bottom: 0.3rem; }
        .body-content a  { color: #8c4a50; }
        .body-content strong { color: #2d1a1a; }

        /* ── CTA button area ── */
        .cta-wrap {
            text-align: center;
            margin: 2rem 0;
        }

        .cta-btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 999px;
            background: #a85058;
            color: #fff !important;
            text-decoration: none;
            font-family: 'Arial', sans-serif;
            font-size: 0.9rem;
            font-weight: bold;
            letter-spacing: 0.03em;
        }

        /* ── Footer ── */
        .footer {
            background: #fdf4f3;
            border: 1px solid #e5c9c7;
            border-top: none;
            border-radius: 0 0 20px 20px;
            padding: 1.25rem 2rem;
            text-align: center;
        }

        .footer p {
            font-size: 0.75rem;
            color: #9a7070;
            line-height: 1.6;
            font-family: 'Arial', sans-serif;
        }

        .footer a {
            color: #8c4a50;
            text-decoration: none;
        }

        .petal {
            font-size: 0.7rem;
            color: #e5c9c7;
            letter-spacing: 0.4rem;
            display: block;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <p class="brand-name">Chapter of You</p>
            <div class="header-divider">
                <span class="header-divider-line"></span>
                <span class="header-divider-dot">✦</span>
                <span class="header-divider-line"></span>
            </div>
        </div>

        <!-- Body -->
        <div class="card">
            <p class="greeting">Hi <em>{{ $recipientName }}</em>,</p>

            <div class="body-content">
                {!! $body !!}
            </div>

            <!-- Shop link -->
            <div class="cta-wrap">
                <a href="{{ config('app.url') }}/products" class="cta-btn">
                    Shop the Collection
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <span class="petal">✿ ✦ ✿</span>
            <p>
                You're receiving this because you have an account with Chapter of You.<br>
                &copy; {{ date('Y') }} Chapter of You. All rights reserved.
            </p>
            <p style="margin-top: 0.5rem;">
                <a href="{{ config('app.url') }}">chapterofyou.co.uk</a>
                &nbsp;·&nbsp;
                <a href="mailto:contact@chapterofyou.co.uk">contact@chapterofyou.co.uk</a>
            </p>
        </div>

    </div>
</body>
</html>
