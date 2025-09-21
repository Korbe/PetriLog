<!DOCTYPE html>
<html lang="de">

<head>
    <meta http-equiv="content-language" content="de">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow">




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


    @laravelPWA

    @routes
    @vite('resources/js/app.js')
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
