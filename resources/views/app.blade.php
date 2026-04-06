<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-HKD85XSYN0"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-HKD85XSYN0');
        </script>

        <!-- Charset & viewport — truly global, never overridden -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Author — static, fine to keep here -->
        <meta name="author" content="Chapter of You">

        <!-- Keywords — low SEO value (ignored by Google) but harmless to keep -->
        <meta name="keywords" content="Reed Diffusers, Hand-made, Luxurious, Home Fragrance, Aromatherapy, Scented Oils, Relaxation, Wellness, Gift Ideas, Chapter of You">

        <!-- IE compatibility -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Language" content="en">

        <!-- Favicon -->
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <link rel="icon" href="/favicon-96x96.png" type="image/png" sizes="96x96">

        <link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">

        <link rel="manifest" href="/site.webmanifest">

        <meta name="theme-color" content="#8c4a50">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <title inertia>{{ config('app.name', 'Chapter of You') }}</title>

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased" style="background-color: var(--background);">
        @inertia
        @routes
    </body>
</html>
