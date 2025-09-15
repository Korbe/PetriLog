<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Dynamische Meta aus Session --}}
    @if (session('meta'))
        @foreach (session('meta') as $name => $content)
            @if (Str::startsWith($name, 'og:'))
                <meta property="{{ $name }}" content="{{ $content }}">
            @else
                <meta name="{{ $name }}" content="{{ $content }}">
            @endif
        @endforeach
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Google Maps API vorladen --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=marker&loading=async"
        async defer loading="async"></script>

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
