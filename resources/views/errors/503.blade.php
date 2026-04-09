<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Back Soon — Chapter of You</title>
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

        html, body { height: 100%; }

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

        /* ── Slow gentle pulse background ── */
        .glow {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(80px);
            opacity: 0;
            animation: pulse-glow 6s ease-in-out infinite;
        }
        .glow--1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(196,112,120,0.18) 0%, transparent 70%);
            top: -100px; left: -100px;
            animation-delay: 0s;
        }
        .glow--2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(196,112,120,0.12) 0%, transparent 70%);
            bottom: -80px; right: -80px;
            animation-delay: 3s;
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0; transform: scale(0.9); }
            50%       { opacity: 1; transform: scale(1.05); }
        }

        /* ── Orbiting petals ── */
        .orbit-field {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .orbit-petal {
            position: absolute;
            top: 50%;
            left: 50%;
            color: var(--border);
            font-size: 0.9rem;
            opacity: 0;
            animation: orbit linear infinite;
            transform-origin: 0 0;
        }
        .orbit-petal:nth-child(1) { animation-duration: 28s; animation-delay: 0s;   --r: 340px; --start: 0deg; }
        .orbit-petal:nth-child(2) { animation-duration: 34s; animation-delay: 5s;   --r: 280px; --start: 72deg; }
        .orbit-petal:nth-child(3) { animation-duration: 22s; animation-delay: 10s;  --r: 380px; --start: 144deg; }
        .orbit-petal:nth-child(4) { animation-duration: 40s; animation-delay: 2s;   --r: 310px; --start: 216deg; }
        .orbit-petal:nth-child(5) { animation-duration: 30s; animation-delay: 7s;   --r: 360px; --start: 288deg; }
        @keyframes orbit {
            0%   { opacity: 0; transform: rotate(var(--start)) translateX(var(--r)) rotate(calc(-1 * var(--start))); }
            10%  { opacity: 0.45; }
            90%  { opacity: 0.3; }
            100% { opacity: 0; transform: rotate(calc(var(--start) + 360deg)) translateX(var(--r)) rotate(calc(-1 * (var(--start) + 360deg))); }
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
            animation: rise 0.8s cubic-bezier(.34,1.4,.64,1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: none; }
        }

        .card::before {
            content: '';
            display: block;
            height: 4px;
            background: linear-gradient(90deg, var(--rose-lg), var(--rose));
        }

        /* ── Illustration area ── */
        .illustration {
            padding: 2.5rem 2rem 0;
            position: relative;
        }

        /* Animated candle/diffuser icon made from CSS */
        .diffuser-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 0.5rem;
            position: relative;
        }
        .diffuser-body {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #faeaea, #fdf4f3);
            border: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            animation: breathe 3s ease-in-out infinite;
            box-shadow: 0 0 0 0 rgba(196,112,120,0.3);
        }
        @keyframes breathe {
            0%, 100% { transform: scale(1);    box-shadow: 0 0 0 0 rgba(196,112,120,0.25); }
            50%       { transform: scale(1.06); box-shadow: 0 0 0 12px rgba(196,112,120,0); }
        }

        /* ── 503 faded ── */
        .five-oh-three {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(5rem, 18vw, 7.5rem);
            font-weight: 300;
            font-style: italic;
            color: var(--border);
            line-height: 1;
            letter-spacing: -0.04em;
            margin-bottom: -1.25rem;
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

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin: 0.5rem auto 1.25rem;
            width: 80%;
        }
        .divider span:not(.divider-petal) { flex: 1; height: 1px; background: var(--border); }
        .divider-petal { font-size: 0.82rem; color: var(--muted); }

        /* ── Body text ── */
        .body-text {
            font-size: 0.95rem;
            color: var(--sub);
            line-height: 1.7;
            max-width: 360px;
            margin: 0 auto 1rem;
            padding: 0 2rem;
        }

        /* ── Maintenance note ── */
        .maintenance-note {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            background: rgba(140,74,80,0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.85rem 1.1rem;
            margin: 0 2rem 1.75rem;
            font-size: 0.85rem;
            color: var(--sub);
            line-height: 1.6;
            text-align: left;
        }
        .maintenance-note svg { flex-shrink: 0; margin-top: 1px; color: var(--rose); }

        /* ── Dot pulse loading indicator ── */
        .loading-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-bottom: 1.75rem;
        }
        .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--muted);
            animation: dot-pulse 1.4s ease-in-out infinite;
        }
        .dot:nth-child(1) { animation-delay: 0s; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dot-pulse {
            0%, 80%, 100% { transform: scale(0.7); background: var(--muted); }
            40%            { transform: scale(1.2); background: var(--rose); }
        }

        /* ── Action ── */
        .actions {
            padding: 0 2rem 2rem;
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
            border: none;
            width: 100%;
            justify-content: center;
        }
        .btn-ghost {
            border: 1px solid var(--border);
            background: var(--cream);
            color: var(--sub);
        }
        .btn-ghost:hover { border-color: var(--rose); color: var(--rose); transform: translateY(-1px); }

        /* ── Contact strip ── */
        .contact-strip {
            border-top: 1px solid #f0dcd8;
            padding: 1.25rem 2rem 1.75rem;
        }
        .contact-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            margin-bottom: 0.6rem;
        }
        .contact-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--rose);
            text-decoration: none;
            transition: color 0.15s;
        }
        .contact-link:hover { color: #6a3038; text-decoration: underline; text-underline-offset: 3px; }

        /* ── Brand footer ── */
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
            .maintenance-note { margin: 0 1.25rem 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Soft glows -->
    <div class="glow glow--1" aria-hidden="true"></div>
    <div class="glow glow--2" aria-hidden="true"></div>

    <!-- Orbiting petals -->
    <div class="orbit-field" aria-hidden="true">
        <span class="orbit-petal">✿</span>
        <span class="orbit-petal">✦</span>
        <span class="orbit-petal">✿</span>
        <span class="orbit-petal">✦</span>
        <span class="orbit-petal">✿</span>
    </div>

    <!-- Card -->
    <div class="card" role="main">

        <!-- Breathng diffuser icon -->
        <div class="illustration">
            <div class="diffuser-icon">
                <div class="diffuser-body">✿</div>
            </div>
        </div>

        <div class="five-oh-three" aria-hidden="true">503</div>

        <h1 class="script-heading">Back very soon…</h1>

        <div class="divider" aria-hidden="true">
            <span></span>
            <span class="divider-petal">✿</span>
            <span></span>
        </div>

        <p class="body-text">
            I'm giving the site a little care and attention right now.
            I won't be long, please check back in a few minutes.
        </p>

        <!-- Animated loading dots -->
        <div class="loading-dots" aria-label="Loading">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>

        <div class="maintenance-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
            <span>
                If you were placing an order, please don't retry payment until the
                site is back, your order may have gone through. Check your email
                for a confirmation.
            </span>
        </div>

        <div class="actions">
            <button onclick="window.location.reload()" class="btn btn-ghost">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                Refresh the page
            </button>
        </div>

        <div class="contact-strip">
            <p class="contact-label">Urgent enquiry?</p>
            <a href="mailto:contact@chapterofyou.co.uk" class="contact-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                contact@chapterofyou.co.uk
            </a>
        </div>

    </div>

    <p class="brand-foot">Chapter of You &mdash; your chapter, your self-care</p>

</body>
</html>
