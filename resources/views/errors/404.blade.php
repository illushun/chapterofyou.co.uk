<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Page Not Found — Chapter of You</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --rose:    #8c4a50;
            --rose-lg: #c47078;
            --cream:   #fdf4f3;
            --card:    #fffafa;
            --border:  #e5c9c7;
            --muted:   #c9a4a4;
            --text:    #2d1a1a;
            --sub:     #6b4f4f;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--cream);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            overflow: hidden;
            position: relative;
        }

        /* ── Floating petals background ── */
        .petal-field {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .petal {
            position: absolute;
            font-size: 1.1rem;
            color: var(--border);
            opacity: 0;
            animation: drift linear infinite;
        }

        .petal:nth-child(1)  { left: 8%;  animation-duration: 12s; animation-delay: 0s;    font-size: 0.9rem; }
        .petal:nth-child(2)  { left: 18%; animation-duration: 16s; animation-delay: 2s;    font-size: 1.3rem; }
        .petal:nth-child(3)  { left: 30%; animation-duration: 10s; animation-delay: 4s;    font-size: 0.7rem; }
        .petal:nth-child(4)  { left: 45%; animation-duration: 14s; animation-delay: 1s;    font-size: 1rem; }
        .petal:nth-child(5)  { left: 58%; animation-duration: 11s; animation-delay: 3s;    font-size: 1.4rem; }
        .petal:nth-child(6)  { left: 70%; animation-duration: 17s; animation-delay: 0.5s;  font-size: 0.8rem; }
        .petal:nth-child(7)  { left: 82%; animation-duration: 13s; animation-delay: 5s;    font-size: 1.1rem; }
        .petal:nth-child(8)  { left: 92%; animation-duration: 15s; animation-delay: 2.5s;  font-size: 0.65rem; }
        .petal:nth-child(9)  { left: 25%; animation-duration: 18s; animation-delay: 7s;    font-size: 0.9rem; }
        .petal:nth-child(10) { left: 65%; animation-duration: 12s; animation-delay: 6s;    font-size: 1.2rem; }

        @keyframes drift {
            0%   { transform: translateY(110vh) rotate(0deg);   opacity: 0; }
            10%  { opacity: 0.55; }
            90%  { opacity: 0.35; }
            100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; }
        }

        /* ── Card ── */
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 8px 48px rgba(140, 74, 80, 0.12);
            overflow: hidden;
            text-align: center;
            animation: rise 0.7s cubic-bezier(.34,1.4,.64,1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(30px) scale(0.97); }
            to   { opacity: 1; transform: none; }
        }

        /* Rose top band */
        .card::before {
            content: '';
            display: block;
            height: 4px;
            background: linear-gradient(90deg, var(--rose-lg), var(--rose));
        }

        /* ── 404 number ── */
        .four-oh-four {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(6rem, 20vw, 9rem);
            font-weight: 300;
            font-style: italic;
            color: var(--border);
            line-height: 1;
            padding: 2.5rem 2rem 0;
            letter-spacing: -0.04em;
            /* Overlap the text with the script heading */
            margin-bottom: -1.5rem;
            position: relative;
            z-index: 0;
            user-select: none;
        }

        /* ── Script heading ── */
        .script-heading {
            font-family: 'Great Vibes', cursive;
            font-size: clamp(2.2rem, 7vw, 3.2rem);
            color: var(--rose);
            line-height: 1.2;
            position: relative;
            z-index: 1;
            margin-bottom: 0.75rem;
        }

        /* ── Petal divider ── */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin: 0.5rem auto 1.25rem;
            width: 80%;
        }
        .divider span:not(.divider-petal) {
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .divider-petal {
            font-size: 0.82rem;
            color: var(--muted);
        }

        /* ── Body text ── */
        .body-text {
            font-size: 0.95rem;
            color: var(--sub);
            line-height: 1.7;
            max-width: 360px;
            margin: 0 auto 2rem;
            padding: 0 2rem;
        }

        /* ── Actions ── */
        .actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            padding: 0 2rem 2.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.72rem 1.5rem;
            border-radius: 999px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .btn-primary {
            border: 1px solid #a85058;
            background: linear-gradient(135deg, var(--rose-lg), var(--rose));
            color: #fff;
            box-shadow: 0 4px 14px rgba(140,74,80,0.22);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(140,74,80,0.32);
        }

        .btn-ghost {
            border: 1px solid var(--border);
            background: var(--cream);
            color: var(--sub);
        }
        .btn-ghost:hover {
            border-color: var(--rose);
            color: var(--rose);
            transform: translateY(-1px);
        }

        /* ── Quick links ── */
        .quick-links {
            border-top: 1px solid #f0dcd8;
            padding: 1.25rem 2rem 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem 1.5rem;
            flex-wrap: wrap;
        }

        .quick-label {
            width: 100%;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            margin-bottom: 0.25rem;
        }

        .quick-link {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--rose);
            text-decoration: none;
            transition: color 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }
        .quick-link:hover { color: #6a3038; text-decoration: underline; text-underline-offset: 3px; }

        /* ── Branding footer ── */
        .brand-foot {
            margin-top: 1.75rem;
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 1rem;
            color: var(--muted);
            position: relative;
            z-index: 1;
            animation: rise 0.7s 0.15s cubic-bezier(.34,1.4,.64,1) both;
        }

        @media (max-width: 480px) {
            .actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <!-- Floating petals -->
    <div class="petal-field" aria-hidden="true">
        <span class="petal">✿</span>
        <span class="petal">✦</span>
        <span class="petal">✿</span>
        <span class="petal">✦</span>
        <span class="petal">✿</span>
        <span class="petal">✿</span>
        <span class="petal">✦</span>
        <span class="petal">✿</span>
        <span class="petal">✦</span>
        <span class="petal">✿</span>
    </div>

    <!-- Card -->
    <div class="card" role="main">

        <!-- Large faded 404 -->
        <div class="four-oh-four" aria-hidden="true">404</div>

        <!-- Script heading overlapping the number -->
        <h1 class="script-heading">Lost among the petals…</h1>

        <!-- Divider -->
        <div class="divider" aria-hidden="true">
            <span></span>
            <span class="divider-petal">✿</span>
            <span></span>
        </div>

        <!-- Body -->
        <p class="body-text">
            The page you're looking for seems to have drifted away.
            It may have moved, been removed, or the link might be a little off.
        </p>

        <!-- Actions -->
        <div class="actions">
            <a href="/" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Go Home
            </a>
            <a href="/products" class="btn btn-ghost">
                Browse Products
            </a>
        </div>

        <!-- Quick links -->
        <div class="quick-links">
            <p class="quick-label">You might be looking for</p>
            <a href="/journal" class="quick-link">Journal</a>
            <a href="/gift-vouchers" class="quick-link">Gift Vouchers</a>
            <a href="/account" class="quick-link">My Account</a>
            <a href="/delivery" class="quick-link">Delivery Info</a>
        </div>

    </div>

    <!-- Brand tagline -->
    <p class="brand-foot">Chapter of You &mdash; your chapter, your self-care</p>

</body>
</html>
