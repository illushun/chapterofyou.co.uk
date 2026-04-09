<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Something Went Wrong — Chapter of You</title>
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

        /* ── Slow drifting petals — fewer, slower, more melancholy ── */
        .petal-field {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .petal {
            position: absolute;
            color: var(--border);
            opacity: 0;
            animation: drift linear infinite;
        }

        /* Falling downward this time — things went down */
        .petal:nth-child(1)  { left: 12%; font-size: 1rem;   animation-duration: 18s; animation-delay: 0s;   }
        .petal:nth-child(2)  { left: 28%; font-size: 0.75rem; animation-duration: 22s; animation-delay: 3s;   }
        .petal:nth-child(3)  { left: 48%; font-size: 1.2rem;  animation-duration: 16s; animation-delay: 6s;   }
        .petal:nth-child(4)  { left: 65%; font-size: 0.85rem; animation-duration: 20s; animation-delay: 1.5s; }
        .petal:nth-child(5)  { left: 80%; font-size: 1rem;   animation-duration: 24s; animation-delay: 9s;   }
        .petal:nth-child(6)  { left: 35%; font-size: 0.65rem; animation-duration: 19s; animation-delay: 4s;   }
        .petal:nth-child(7)  { left: 90%; font-size: 0.9rem;  animation-duration: 21s; animation-delay: 7s;   }

        @keyframes drift {
            0%   { transform: translateY(-5vh) rotate(0deg)   translateX(0);    opacity: 0; }
            8%   { opacity: 0.5; }
            50%  { transform: translateY(55vh) rotate(180deg) translateX(20px); opacity: 0.4; }
            92%  { opacity: 0.3; }
            100% { transform: translateY(112vh) rotate(360deg) translateX(-10px); opacity: 0; }
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

        .card::before {
            content: '';
            display: block;
            height: 4px;
            background: linear-gradient(90deg, var(--rose-lg), var(--rose));
        }

        /* ── 500 number ── */
        .five-hundred {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(6rem, 20vw, 9rem);
            font-weight: 300;
            font-style: italic;
            color: var(--border);
            line-height: 1;
            padding: 2.5rem 2rem 0;
            letter-spacing: -0.04em;
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
            margin: 0 auto 0.75rem;
            padding: 0 2rem;
        }

        /* ── Apology note ── */
        .apology {
            display: inline-flex;
            align-items: flex-start;
            gap: 0.5rem;
            background: #fffbf0;
            border: 1px solid #e8d898;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin: 0 2rem 1.75rem;
            font-size: 0.84rem;
            color: #7a6030;
            line-height: 1.55;
            text-align: left;
        }
        .apology svg { flex-shrink: 0; margin-top: 1px; color: #c9a830; }

        /* ── Actions ── */
        .actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
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
        }
        .btn-primary {
            border: 1px solid #a85058;
            background: linear-gradient(135deg, var(--rose-lg), var(--rose));
            color: #fff;
            box-shadow: 0 4px 14px rgba(140,74,80,0.22);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(140,74,80,0.32); }

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
            .actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
            .apology { margin: 0 1.25rem 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Falling petals -->
    <div class="petal-field" aria-hidden="true">
        <span class="petal">✿</span>
        <span class="petal">✦</span>
        <span class="petal">✿</span>
        <span class="petal">✦</span>
        <span class="petal">✿</span>
        <span class="petal">✿</span>
        <span class="petal">✦</span>
    </div>

    <!-- Card -->
    <div class="card" role="main">

        <div class="five-hundred" aria-hidden="true">500</div>

        <h1 class="script-heading">Something went wrong…</h1>

        <div class="divider" aria-hidden="true">
            <span></span>
            <span class="divider-petal">✿</span>
            <span></span>
        </div>

        <p class="body-text">
            Our server hit an unexpected snag. This is entirely on our end
            — not yours. We've been notified and are looking into it.
        </p>

        <div class="apology">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
            <span>
                If you were in the middle of placing an order, please don't retry payment
                until you've checked your email — your order may have gone through.
            </span>
        </div>

        <div class="actions">
            <a href="/" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Go Home
            </a>
            <button onclick="window.location.reload()" class="btn btn-ghost">
                Try Again
            </button>
        </div>

        <div class="contact-strip">
            <p class="contact-label">Need help with your order?</p>
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
