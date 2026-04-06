<?php

return [

    /*
     * Point to our custom policy which extends Basic and adds
     * all the domains Stripe, Google Analytics and fonts require.
     */
    'presets' => [
        \App\Http\Policies\ChapterOfYouPolicy::class,
    ],

    /*
     * These presets are put in a report-only policy — useful for
     * testing changes without breaking anything.
     */
    'report_only_presets' => [
        //
    ],

    /*
     * CSP violation reports will be sent to this URL.
     * Leave empty to disable reporting.
     */
    'report_uri' => env('CSP_REPORT_URI', ''),

    /*
     * Set to false to disable all CSP headers (e.g. in local dev
     * if you want to turn it off temporarily).
     */
    'enabled' => env('CSP_ENABLED', true),

];
