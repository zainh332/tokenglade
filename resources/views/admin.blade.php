<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0b0c10]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>TokenGlade — Admin Panel</title>
        @vite(['src/admin-main.js', 'resources/css/app.css'])
        <meta name="robots" content="noindex, nofollow">
        <link rel="icon" type="image/png" href="{{ Vite::asset('src/assets/token-glade-logo.png') }}">
        <script src="https://unpkg.com/@albedo-link/intent"></script>
    </head>
    <body class="h-full text-white antialiased overflow-x-hidden">
        <div id="admin-app" class="h-full"></div>
        <script>
            window.Laravel = {
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };
        </script>
    </body>
</html>
