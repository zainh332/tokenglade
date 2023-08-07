<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- @vite(['../src/main.js', 'resources/css/app.css']) --}}
        @vite(['src/main.js', 'resources/css/app.css'])
        {{-- @vite(['resources/js/app.js', 'resources/scss/style.scss']) --}}
        {{-- @vite(['src/main.js']) --}}
        {{-- <script>
            window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
            ]) !!};
        </script> --}}
    </head>
    <body class="antialiased">
       <div id="app"></div>
    </body>
</html>
