<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>Petrilog – Dein Fangbuch online</title>
    <meta name="description" content="Teile deine Fänge mit der Community und behalte deine Angelerlebnisse im Überblick.">

    <!-- Default Open Graph Meta Tags -->
    <meta property="og:title" content="Petrilog – Dein Fangbuch online" />
    <meta property="og:description"
        content="Teile deine Fänge mit der Community und behalte deine Angelerlebnisse im Überblick." />
    <meta property="og:image" content="'images/icons/logo-512.png'" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />

    <!-- Default Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Petrilog – Dein Fangbuch online">
    <meta name="twitter:description"
        content="Teile deine Fänge mit der Community und behalte deine Angelerlebnisse im Überblick.">
    <meta name="twitter:image" content="/images/icons/logo-512.png'">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDz9ywPxkkW1oOy70Rab2oqnhF02DLe5MA&libraries=marker&loading=async"
        async defer></script>

    <!-- Scripts -->
    @laravelPWA

    @routes
    @vite('resources/js/app.js')
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
