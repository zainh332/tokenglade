<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['src/main.js', 'resources/css/app.css'])
        
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('build/assets/flower-9bebdb58.png') }}">
    </head>
    <body class="antialiased">
       <div id="app"></div>
    </body>
</html>
