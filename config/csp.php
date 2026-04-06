<?php

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;

return [

    /*
     * Presets are applied in order. Basic handles the 'self' defaults
     * for scripts, styles, images and form actions. Our custom preset
     * then layers on Stripe, Google Analytics and font domains.
     *
     * In v3 there is no single 'policy' key — use this 'presets' array.
     */
    'presets' => [
        Spatie\Csp\Presets\Basic::class,
        App\Support\Csp\ChapterOfYouPolicy::class,
    ],

    /*
     * Report-only presets — useful for testing a new policy without
     * breaking anything. Leave empty in production.
     */
    'report_only_presets' => [
        //
    ],

    /*
     * CSP violation reports will be sent here. Leave empty to disable.
     */
    'report_uri' => env('CSP_REPORT_URI', ''),

    /*
     * Set to false to disable CSP headers entirely (e.g. while debugging).
     * Can also be toggled via .env: CSP_ENABLED=false
     */
    'enabled' => env('CSP_ENABLED', true),

    /*
     * Whether to add CSP headers during Vite hot reloading (local dev).
     * Usually best left false so HMR isn't blocked by the policy.
     */
    'enabled_while_hot_reloading' => env('CSP_ENABLED_WHILE_HOT_RELOADING', false),

    /*
     * The class responsible for generating nonces used in inline tags.
     * The default RandomString generator is fine for production.
     */
    'nonce_generator' => Spatie\Csp\Nonce\RandomString::class,

];
