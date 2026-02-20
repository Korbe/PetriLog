<!DOCTYPE html>
<html lang="de">

<head>
    <meta http-equiv="content-language" content="de">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ url('/manifest.json') }}">



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
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-54ZZQRJ2RT"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-54ZZQRJ2RT');
    </script>

    {{-- Google Maps API vorladen --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=marker&loading=async"
        async defer loading="async"></script>


    @php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                    'url' => url('/'),
                    'logo' => asset('logo.png'),
                ],
                [
                    '@type' => 'WebSite',
                    'url' => url()->current(),
                    'name' => config('app.name'),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => url('/search?q={search_term_string}'),
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    @endphp

    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('Service Worker registered!', reg))
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }
    </script>

    @routes
    @vite('resources/js/app.js')
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
