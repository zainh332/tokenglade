<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['src/main.js', 'resources/css/app.css'])
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('build/assets/flower-9bebdb58.png') }}">
        <meta name="google-site-verification" content="efg2XUb3x3NC7DsUUkEtjtQ2k5ewYAMGt-rkqAjlQDM" />
        <script src="https://unpkg.com/@albedo-link/intent"></script>
    </head>
    <body class="antialiased overflow-x-hidden" style="background-color:#F6F8FC;">
       <div id="app" style="height:100vh;">
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
