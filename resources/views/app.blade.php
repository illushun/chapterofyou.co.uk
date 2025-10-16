<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-HKD85XSYN0"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-HKD85XSYN0');
        </script>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="description" content="MyFPL offers game predictions, promising player data, and in-depth stats to help you dominate your Fantasy Premier League.">
        <meta name="keywords" content="Fantasy Premier League, FPL, game predictions, player stats, Fantasy Football, player data, FPL analytics, MyFPL">

        <meta name="robots" content="index, follow">
        <meta name="author" content="Stuart Davey">

        <link rel="canonical" href="https://www.chapterofyou.co.uk">

        <!-- Open Graph Meta Tags -->
        <meta property="og:title" content="Chapter of You | Your chapter, your self-care">
        <meta property="og:description" content="Discover luxurious hand-made reed diffusers">
        <meta property="og:url" content="https://www.chapterofyou.co.uk">
        <meta property="og:type" content="website">

        <!-- Add an image tag here -->
        <!-- <meta property="og:image" content="https://www.chapterofyou.co.uk/images/share_image.png"> -->

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Chapter of You | Your chapter, your self-care">
        <meta name="twitter:description" content="Discover luxurious hand-made reed diffusers">

        <!-- Link to the image used for sharing -->
        <!-- <meta name="twitter:image" content="https://www.chapterofyou.co.uk/images/share_image.png"> -->

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        <!-- Content Language -->
        <meta http-equiv="Content-Language" content="en">

        <!-- X-UA-Compatible for IE -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])

        @inertiaHead
    </head>

    <body class="font-sans antialiased" style="background-color: var(--background);">
        @inertia
        @routes
    </body>
</html>
