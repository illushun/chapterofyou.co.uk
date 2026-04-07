<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>A little something, just for you 🤍</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        background-color: #f9ece8;
        font-family: 'Cormorant Garamond', Georgia, serif;
        color: #3d2b2b;
        -webkit-font-smoothing: antialiased;
    }
    .wrapper {
        max-width: 600px;
        margin: 0 auto;
        background-color: #fdf5f2;
        background-image:
            radial-gradient(ellipse at 10% 15%, rgba(212,160,140,0.35) 0%, transparent 55%),
            radial-gradient(ellipse at 90% 80%, rgba(212,160,140,0.28) 0%, transparent 55%);
    }
    .body { padding: 48px 40px 40px; text-align: center; }

    .greeting-script {
        font-family: 'Great Vibes', cursive;
        font-size: 42px;
        color: #b85050;
        line-height: 1.2;
        margin-bottom: 6px;
    }
    .gold-divider {
        border: none;
        border-top: 1.5px solid #c9a84c;
        opacity: 0.7;
        margin: 20px auto;
        width: 80%;
    }
    .body-para {
        font-size: 17px;
        line-height: 1.65;
        color: #3d2b2b;
        max-width: 420px;
        margin: 0 auto 16px;
    }

    /* ── Voucher card ── */
    .voucher-card {
        margin: 24px auto;
        width: 85%;
        background: #fff;
        border: 2px solid #c9a84c;
        border-radius: 12px;
        /* Enough bottom padding so content clears the flower */
        padding: 28px 24px 36px;
        position: relative;
        /* overflow hidden removed — was clipping content */
        overflow: visible;
    }

    /*
     * Flowers repositioned to corners so they never overlap text.
     * Reduced size and raised opacity slightly so they're decorative
     * without being too faint or too intrusive.
     * pointer-events: none means they don't interfere with clicks.
     */
    .voucher-flower {
        position: absolute;
        font-size: 52px;
        color: #f0d8d0;
        pointer-events: none;
        user-select: none;
        line-height: 1;
        z-index: 0;
    }
    .voucher-flower--tl { top: -4px;  left: 8px;  }
    .voucher-flower--br { bottom: -4px; right: 8px; }

    /* All direct children of voucher-card sit above the flowers */
    .voucher-card > *:not(.voucher-flower) { position: relative; z-index: 1; }

    .voucher-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #a0742a;  /* darker gold — was #c9a84c, too light */
        margin-bottom: 8px;
    }
    .voucher-brand {
        font-family: 'Great Vibes', cursive;
        font-size: 32px;
        color: #b85050;
        margin-bottom: 12px;
    }
    .voucher-amount {
        font-family: 'Cormorant Garamond', serif;
        font-size: 52px;
        font-weight: 600;
        color: #2d1a1a;
        line-height: 1;
        margin-bottom: 16px;
    }
    .voucher-code-label {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6b4f3a;  /* darker — was #8a6a5a */
        margin-bottom: 6px;
    }
    .voucher-code {
        display: inline-block;
        background: #fdf5f2;
        border: 1.5px dashed #c9a84c;
        border-radius: 6px;
        padding: 10px 20px;
        font-family: 'Courier New', monospace;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: #2d1a1a;
        margin-bottom: 14px;
    }
    .voucher-expiry {
        font-size: 14px;
        font-style: italic;
        color: #5a3d2b;  /* darker — was #8a6a5a */
    }
    .voucher-terms {
        font-size: 13px;
        color: #7a5a48;  /* darker — was #a08070 */
        margin-top: 10px;
        line-height: 1.5;
    }

    /* ── Personal message box ── */
    .message-box {
        margin: 0 auto 20px;
        width: 80%;
        background: rgba(255,255,255,0.6);
        border-left: 3px solid #c9a84c;
        padding: 14px 18px;
        text-align: left;
        border-radius: 0 6px 6px 0;
    }
    .message-box-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #a0742a;  /* darker gold */
        margin-bottom: 6px;
    }
    .message-box-text {
        font-size: 16px;
        font-style: italic;
        line-height: 1.65;
        color: #3d2b2b;
    }

    /* ── CTA button ── */
    .cta-btn {
        display: inline-block;
        margin: 8px 0 4px;
        padding: 13px 32px;
        background: #b85050;
        color: #fff !important;
        font-family: 'Cormorant Garamond', serif;
        font-size: 17px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 4px;
        letter-spacing: 0.03em;
    }

    .signoff-script {
        font-family: 'Great Vibes', cursive;
        font-size: 38px;
        color: #b85050;
        line-height: 1.3;
        margin-top: 4px;
    }
    .tagline {
        font-style: italic;
        font-size: 15px;
        color: #6b4f4f;  /* darker — was #8a6a5a */
        margin-top: 6px;
        letter-spacing: 0.02em;
    }

    /* Small print — darkened for legibility */
    .small-print {
        font-size: 13px;
        color: #7a5a48;  /* was #a08070 */
        margin-top: 12px;
        line-height: 1.6;
    }
    .small-print a { color: #7a5a48; }

    @media only screen and (max-width: 480px) {
        .body { padding: 32px 20px 28px; }
        .greeting-script { font-size: 34px; }
        .voucher-card, .message-box { width: 100%; }
        .voucher-amount { font-size: 42px; }
        .voucher-code { font-size: 16px; }
    }
</style>
</head>
<body>
<div class="wrapper">
<div class="body">

    <div class="greeting-script">Hello {{ $recipientName }},</div>
    <div class="greeting-script" style="font-size:34px; margin-top:-4px;">
        You've received a gift!
    </div>

    <hr class="gold-divider" />

    <p class="body-para">
        <strong>{{ $purchaserName }}</strong> has sent you a Chapter of You gift voucher.
        A little something to treat yourself, because you deserve it.
    </p>

    @if($personalMessage)
    <div class="message-box">
        <div class="message-box-label">A note from {{ $purchaserName }}</div>
        <div class="message-box-text">"{{ $personalMessage }}"</div>
    </div>
    @endif

    {{-- ── Voucher card ── --}}
    <div class="voucher-card">
        {{-- Flowers as real elements so they don't overlap content --}}
        <span class="voucher-flower voucher-flower--tl" aria-hidden="true">✿</span>
        <span class="voucher-flower voucher-flower--br" aria-hidden="true">✿</span>

        <div class="voucher-label">Gift Voucher</div>
        <div class="voucher-brand">Chapter of You</div>
        <div class="voucher-amount">£{{ number_format($amount, 2) }}</div>
        <div class="voucher-code-label">Your unique code</div>
        <div class="voucher-code">{{ $voucherCode }}</div>
        <div class="voucher-expiry">Valid until {{ $validUntil }}</div>
        <div class="voucher-terms">
            Single use &middot; All products &middot; Cannot be combined with other offers
        </div>
    </div>

    <p class="body-para">
        Simply enter your code at checkout to redeem your voucher.
    </p>

    <a href="{{ $shopUrl }}" class="cta-btn">Shop the Collection &rarr;</a>

    <hr class="gold-divider" style="margin-top: 28px;" />

    <div style="margin-top: 8px;">
        <div class="signoff-script" style="font-size:30px; color:#8a6a5a;">Love,</div>
        <div class="signoff-script">Chapter of You 🤍</div>
        <div class="tagline">Your chapter, your self-care</div>
    </div>

    <hr class="gold-divider" style="margin-top: 28px;" />
    <p class="small-print">
        If you have any questions please contact
        <a href="mailto:contact@chapterofyou.co.uk">contact@chapterofyou.co.uk</a>
    </p>

</div>
</div>
</body>
</html>
