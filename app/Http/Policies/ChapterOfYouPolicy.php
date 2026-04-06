<?php

namespace App\Http\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Basic;

/**
 * ChapterOfYouPolicy
 *
 * Extends Spatie's Basic CSP policy to add the external services
 * this site requires: Stripe, Google Analytics, Google Fonts, Bunny Fonts.
 *
 * Stripe's Payment Element injects inline scripts and loads resources
 * from several of its own domains — all of which must be explicitly
 * whitelisted here. See: https://stripe.com/docs/security/guide#csp
 */
class ChapterOfYouPolicy extends Basic
{
    public function configure(): void
    {
        // Apply all the sensible defaults from Basic first
        // (allows 'self' for scripts, styles, images, form-action etc.)
        parent::configure();

        // ── Scripts ───────────────────────────────────────────────────────
        // js.stripe.com      — the main Stripe.js library
        // m.stripe.network   — Stripe's fraud/telemetry scripts (Payment Element)
        // www.googletagmanager.com — Google Analytics (gtag.js async src)
        // www.google-analytics.com — GA script fallback
        $this->addDirective(Directive::SCRIPT, [
            'https://js.stripe.com',
            'https://m.stripe.network',
            'https://www.googletagmanager.com',
            'https://www.google-analytics.com',
        ]);

        // Allow the nonce so our inline GA script in app.blade.php can run.
        // The Basic policy already adds Keyword::NONCE to script-src, so
        // adding it here explicitly ensures it is always present.
        $this->addDirective(Directive::SCRIPT, Keyword::NONCE);

        // ── Frames ────────────────────────────────────────────────────────
        // Stripe mounts payment UI inside iframes served from these domains.
        $this->addDirective(Directive::FRAME, [
            'https://js.stripe.com',
            'https://hooks.stripe.com',
        ]);

        // ── Connections (XHR / fetch / WebSocket) ─────────────────────────
        // Stripe's JS makes API calls to these endpoints at runtime.
        $this->addDirective(Directive::CONNECT, [
            'https://api.stripe.com',
            'https://m.stripe.network',
            'https://m.stripe.com',
            'https://www.google-analytics.com',
            'https://www.googletagmanager.com',
            'https://region1.google-analytics.com',
        ]);

        // ── Workers ───────────────────────────────────────────────────────
        // Stripe's Payment Element spins up a Web Worker from a blob: URL
        // to isolate sensitive operations. Without this, the worker is
        // blocked and the payment form fails to initialise.
        $this->addDirective(Directive::WORKER, [
            Keyword::SELF,
            'blob:',
        ]);

        // ── Images ────────────────────────────────────────────────────────
        // Stripe loads card brand logos (Visa, Mastercard etc.) and Google
        // Analytics uses a 1x1 pixel beacon.
        $this->addDirective(Directive::IMG, [
            'https://*.stripe.com',
            'https://www.google-analytics.com',
            'https://www.googletagmanager.com',
            'https://www.gstatic.com',    // Google Pay logo
            'data:',                       // Stripe card brand SVGs inlined
        ]);

        // ── Styles ────────────────────────────────────────────────────────
        // Stripe injects inline styles into its iframe — these are governed
        // by Stripe's own CSP header on that frame, but we also need to
        // allow fonts.bunny.net and Google Fonts stylesheets loaded via
        // the <link> tags in app.blade.php.
        $this->addDirective(Directive::STYLE, [
            'https://fonts.bunny.net',
            'https://fonts.googleapis.com',
        ]);

        // ── Fonts ─────────────────────────────────────────────────────────
        $this->addDirective(Directive::FONT, [
            'https://fonts.bunny.net',
            'https://fonts.gstatic.com',
        ]);
    }
}
