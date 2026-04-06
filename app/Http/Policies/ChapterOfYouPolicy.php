<?php

namespace App\Support\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

/**
 * ChapterOfYouPolicy
 *
 * A CSP preset for spatie/laravel-csp v3.
 * Implements Preset (not extends Basic) — this is the v3 API.
 *
 * Works alongside Spatie\Csp\Presets\Basic in config/csp.php.
 * Basic handles the 'self' defaults; this class adds the extra
 * domains required by Stripe, Google Analytics and fonts.
 */
class ChapterOfYouPolicy implements Preset
{
    public function configure(Policy $policy): void
    {
        // ── Scripts ───────────────────────────────────────────────────────
        // js.stripe.com    — Stripe.js library loaded in checkout
        // m.stripe.network — Stripe's fraud/telemetry scripts (Payment Element)
        // googletagmanager + google-analytics — GA4 gtag script and beacons
        $policy->add(Directive::SCRIPT, [
            'https://js.stripe.com',
            'https://m.stripe.network',
            'https://www.googletagmanager.com',
            'https://www.google-analytics.com',
        ]);

        // Allow inline scripts that carry the CSP nonce (e.g. the GA
        // dataLayer script in app.blade.php with nonce="{{ csp_nonce() }}")
        $policy->addNonce(Directive::SCRIPT);

        // ── Frames ────────────────────────────────────────────────────────
        // Stripe mounts its payment UI inside iframes from these origins.
        $policy->add(Directive::FRAME, [
            'https://js.stripe.com',
            'https://hooks.stripe.com',
        ]);

        // ── Connections (XHR / fetch) ─────────────────────────────────────
        // Stripe's JS calls its API at runtime; GA sends event beacons here.
        $policy->add(Directive::CONNECT, [
            'https://api.stripe.com',
            'https://m.stripe.network',
            'https://m.stripe.com',
            'https://www.google-analytics.com',
            'https://www.googletagmanager.com',
            'https://region1.google-analytics.com',
        ]);

        // ── Workers ───────────────────────────────────────────────────────
        // Stripe's Payment Element spawns a Web Worker from a blob: URL to
        // isolate sensitive card data. This is the critical directive —
        // without blob: the worker is blocked and the form never initialises.
        $policy->add(Directive::WORKER, [
            Keyword::SELF,
            'blob:',
        ]);

        // ── Images ────────────────────────────────────────────────────────
        // Stripe loads card brand logos; GA uses a 1×1 tracking pixel;
        // Google Pay needs gstatic.com for its button logo.
        $policy->add(Directive::IMG, [
            'https://*.stripe.com',
            'https://www.google-analytics.com',
            'https://www.googletagmanager.com',
            'https://www.gstatic.com',
            'data:',
        ]);

        // ── Styles ────────────────────────────────────────────────────────
        // Font stylesheet links in app.blade.php and Vue pages.
        $policy->add(Directive::STYLE, [
            'https://fonts.bunny.net',
            'https://fonts.googleapis.com',
            Keyword::UNSAFE_INLINE, // Required for Stripe's injected styles
        ]);

        // ── Fonts ─────────────────────────────────────────────────────────
        $policy->add(Directive::FONT, [
            'https://fonts.bunny.net',
            'https://fonts.gstatic.com',
        ]);
    }
}
