<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        @if(isset($meta))
            <title>{{ $meta['title'] }}</title>
            <meta name="description" content="{{ $meta['description'] }}">

            <!-- Open Graph / Facebook / Discord / Telegram -->
            <meta property="og:type" content="website">
            <meta property="og:site_name" content="TokenGlade">
            <meta property="og:url" content="{{ $meta['url'] }}">
            <meta property="og:title" content="{{ $meta['title'] }}">
            <meta property="og:description" content="{{ $meta['description'] }}">
            <meta property="og:image" content="{{ $meta['image'] }}">
            <meta property="og:image:secure_url" content="{{ $meta['image'] }}">
            <meta property="og:image:type" content="image/png">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">

            <!-- Twitter / X Cards -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:site" content="@TokenGlade">
            <meta name="twitter:creator" content="@TokenGlade">
            <meta name="twitter:url" content="{{ $meta['url'] }}">
            <meta name="twitter:title" content="{{ $meta['title'] }}">
            <meta name="twitter:description" content="{{ $meta['description'] }}">
            <meta name="twitter:image" content="{{ $meta['image'] }}">
            <meta name="twitter:image:src" content="{{ $meta['image'] }}">
        @else
            <title>TokenGlade | Mint, Discover & Trade Stellar Tokens</title>
        @endif
        @if(config('app.env') === 'staging')
            <meta name="robots" content="noindex, nofollow">
        @endif
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['src/main.js', 'resources/css/app.css'])
        <link rel="icon" type="image/png" href="{{ Vite::asset('src/assets/token-glade-logo.png') }}">
        <meta name="google-site-verification" content="efg2XUb3x3NC7DsUUkEtjtQ2k5ewYAMGt-rkqAjlQDM" />
        <script src="https://unpkg.com/@albedo-link/intent"></script>
        <script>
            // Inline theme loader to prevent Flash of Unstyled Content (FOUC)
            (function() {
                const theme = localStorage.getItem('theme') || 'light';
                if (theme === 'light') {
                    document.documentElement.classList.add('light');
                } else {
                    document.documentElement.classList.remove('light');
                }
            })();
        </script>
    </head>
    <body class="antialiased overflow-x-hidden bg-theme-bg text-theme-ink min-h-screen transition-colors duration-200">
       <div id="app" class="min-h-screen">
        <beta-ribbon></beta-ribbon>
       </div>
       <script>
           window.Laravel = {
               csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };
            
            // Google tag (gtag.js)
            (function() {
                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=G-FNY5NE54YN';
                document.getElementsByTagName('head')[0].appendChild(script);
                
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', 'G-FNY5NE54YN');
            })();
        </script>
    </body>
</html>
