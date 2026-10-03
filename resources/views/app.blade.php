<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA & Native Mobile App Setup -->
    <meta name="theme-color" content="#050607">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Movie®">
    <meta name="format-detection" content="telephone=no">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/favicons/icon-192x192.png">
    
    <title inertia>{{ config('app.name', 'Movie®') }} - Your Ultimate Movie Experience</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="/images/favicons/favicon_1771172753.png">
    <link rel="shortcut icon" type="image/png" href="/images/favicons/favicon_1771172753.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=bebas-neue:400&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Dynamic Brand Styles & Dark Mode Scrollbar -->
    <style>
        :root {
            --brand-color: #e50914;
            --brand-color-hover: #e50914CC;
            --brand-color-light: #e509141A;
            --netflix: #e50914;
            --netflix-hover: #e50914CC;
            --netflix-light: #e509141A;
            --app-font: 'Roboto', sans-serif;
        }
        body {
            font-family: 'Roboto', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: #050607;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .bg-netflix { background-color: #e50914 !important; }
        .text-netflix { color: #e50914 !important; }
        .border-netflix { border-color: #e50914 !important; }
        .hover\:bg-netflix:hover { background-color: #e50914 !important; }
        .hover\:text-netflix:hover { color: #e50914 !important; }
        .netflix-glow { box-shadow: 0 0 25px rgba(229, 9, 20, 0.45) !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.9);
        }
        ::-webkit-scrollbar-thumb {
            background: #e50914;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #f43f5e;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="h-full bg-[#050607] text-white antialiased selection:bg-netflix selection:text-white">
    @inertia

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.log('SW registration error:', err);
                });
            });
        }
    </script>
</body>
</html>
