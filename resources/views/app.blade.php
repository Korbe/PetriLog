<!DOCTYPE html>
<html lang="de-AT">

<head>
    @php($seo = $seo ?? \App\Support\Seo::defaults())
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @inertiaHead
    {{-- SEO: per page via withViewData(['seo' => Seo::page(...)]). With SSR the <Head> title is already part of
         @inertiaHead ($__inertiaSsrResponse is set by that directive), so only render it here without SSR. --}}
    @if (empty($__inertiaSsrResponse))
        <title inertia>{{ $seo['title'] }}</title>
    @endif
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    @foreach ($seo['og'] as $property => $content)
        <meta property="og:{{ $property }}" content="{{ $content }}">
    @endforeach
    @foreach ($seo['twitter'] as $name => $content)
        <meta name="twitter:{{ $name }}" content="{{ $content }}">
    @endforeach

    <script type="application/ld+json">{!! json_encode(\App\Support\Seo::siteJsonLd(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @foreach ($seo['jsonLd'] as $block)
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach

    {{-- App / PWA --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <meta name="theme-color" content="#118DF0">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/logo-192.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="PetriLog">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js', { scope: '/', updateViaCache: 'none' })
                    .catch(function (err) { console.error('Service Worker registration failed:', err); });
            });
        }
    </script>

    @routes
    @vite('resources/js/app.js')
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
