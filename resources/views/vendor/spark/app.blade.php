<!DOCTYPE html>
<html lang="de-AT">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="theme-color" content="#06212b" />

    <title>Abo & Rechnungen – {{ config('app.name', 'PetriLog') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts (wie in der App) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:500,600,700|schibsted-grotesk:400,500,600,700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.20/lodash.min.js" integrity="sha512-90vH1Z83AJY9DmlWa8WkjkV79yfS2n2Oxhsi2dZbIv0nC4E6m5AbH8Nh156kkM7JePmqD6tcZsfad1ueoaovww==" crossorigin="anonymous"></script>

    <!-- Styles: kompiliertes Spark-CSS -->
    <style>
        {!! file_get_contents($cssPath) !!}
    </style>

    @if (strpos((string) config('spark.brand.color'), '#') === 0)
    <style>
        .bg-custom-hex {
            background-color: {!! config('spark.brand.color') !!};
        }
    </style>
    @endif

    {{--
        PetriLog-Look für das Spark-Abrechnungsportal. Spark ist eine fertig kompilierte Vue-App – angepasst wird
        deshalb nur per CSS über die Tailwind-Klassen von Spark (Stand laravel/spark-stripe 5.x). Nach einem
        Spark-Update kurz /billing ansehen.
    --}}
    <style>
        :root {
            --pl-bg: #f4f1ea;
            --pl-surface: #ffffff;
            --pl-surface-2: #f9f7f2;
            --pl-border: rgba(15, 29, 34, 0.09);
            --pl-border-strong: rgba(15, 29, 34, 0.17);
            --pl-ink: #0f1d22;
            --pl-muted: rgba(15, 29, 34, 0.64);
            --pl-subtle: rgba(15, 29, 34, 0.44);
            --pl-accent: #118df0;
            --pl-accent-ink: #0c6fc0;
            --pl-accent-soft: rgba(17, 141, 240, 0.11);
            --pl-float: #ff5a1f;
            --pl-success: #0f8a5f;
            --pl-danger: #c62828;
            --pl-paper: #f3eee3;
            --pl-shadow: 0 1px 2px rgba(15, 29, 34, 0.04), 0 10px 30px -18px rgba(15, 29, 34, 0.22);
        }

        body.petri-billing,
        .petri-billing .font-sans {
            font-family: 'Schibsted Grotesk', ui-sans-serif, system-ui, sans-serif;
            color: var(--pl-ink);
            background: var(--pl-bg);
        }

        /* Seitenhintergrund */
        .petri-billing .min-h-screen.bg-white,
        .petri-billing .bg-gray-100 { background-color: var(--pl-bg) !important; }

        /* ---------- Seitenleiste wie in der App ---------- */
        .petri-billing #sideBar {
            position: relative;
            overflow: hidden;
            background: linear-gradient(160deg, #06212b 0%, #0a3140 60%, #104456 100%);
            color: var(--pl-paper);
            box-shadow: none;
        }
        .petri-billing #sideBar::after {
            content: '';
            position: absolute;
            right: -6rem;
            top: -6rem;
            width: 16rem;
            height: 16rem;
            border-radius: 999px;
            background: rgba(17, 141, 240, 0.25);
            filter: blur(60px);
            pointer-events: none;
        }
        .petri-billing #sideBar > * { position: relative; z-index: 1; }
        .petri-billing #sideBar h1 {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
            font-weight: 700;
            letter-spacing: -0.035em;
            color: #fff;
        }
        .petri-billing #sideBar h1::before {
            content: '';
            width: 2.5rem;
            height: 2.5rem;
            flex-shrink: 0;
            background: url('/logo-short.png') center / contain no-repeat;
        }
        .petri-billing #sideBar h2 {
            margin-top: 1rem;
            font: 500 11px/1.4 'Schibsted Grotesk', sans-serif;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: #ff8a5c;
        }
        .petri-billing #sideBar .text-gray-600,
        .petri-billing #sideBar .text-gray-700,
        .petri-billing #sideBar .text-gray-900 { color: rgba(243, 238, 227, 0.78); }
        .petri-billing #sideBar a { color: #fff; }
        .petri-billing #sideBar a:hover .underline,
        .petri-billing #sideBar a.underline:hover { text-decoration-color: var(--pl-float); }
        .petri-billing #sideBar .arrow-left { color: var(--pl-float); }
        .petri-billing #sideBarReturnLink a {
            display: inline-flex;
            padding: .7rem 1.1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            transition: background .15s;
        }
        .petri-billing #sideBarReturnLink a:hover { background: rgba(255, 255, 255, 0.18); }
        .petri-billing #sideBarReturnLink .underline { text-decoration: none; color: #fff; font-weight: 600; }

        /* Handy: Zurück-Leiste oben */
        .petri-billing #topNavReturnLink {
            background: #06212b;
            box-shadow: none;
        }
        .petri-billing #topNavReturnLink .underline { color: #fff; text-decoration: none; font-weight: 600; }
        .petri-billing #topNavReturnLink .arrow-left { color: var(--pl-float); }

        /* ---------- Inhalt ---------- */
        .petri-billing .text-gray-900,
        .petri-billing .text-gray-800 { color: var(--pl-ink); }
        .petri-billing .text-gray-700,
        .petri-billing .text-gray-600 { color: var(--pl-muted); }
        .petri-billing .text-gray-500,
        .petri-billing .text-gray-400 { color: var(--pl-subtle); }

        /* Abschnittsüberschriften */
        .petri-billing .text-2xl.font-semibold {
            font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
            font-weight: 600;
            letter-spacing: -0.03em;
            color: var(--pl-ink);
        }
        .petri-billing .font-bold.uppercase {
            font-size: 11px;
            letter-spacing: .18em;
            color: var(--pl-subtle);
        }

        /* Karten */
        .petri-billing .bg-white.shadow-sm,
        .petri-billing .bg-white.shadow {
            border: 1px solid var(--pl-border);
            box-shadow: var(--pl-shadow);
        }
        @media (min-width: 640px) {
            .petri-billing .sm\:rounded-lg { border-radius: 1.25rem; }
        }
        .petri-billing .border-gray-200,
        .petri-billing .border-gray-100 { border-color: var(--pl-border); }
        .petri-billing .border-gray-300 { border-color: var(--pl-border-strong); }

        /* Hinweise */
        .petri-billing .bg-blue-100 { background-color: var(--pl-accent-soft); }
        .petri-billing .border-blue-200 { border-color: rgba(17, 141, 240, 0.25); }
        .petri-billing .text-blue-800 { color: var(--pl-accent-ink); }

        /* Knöpfe: rund, normale Schreibweise statt GROSSBUCHSTABEN */
        .petri-billing button.uppercase.tracking-widest,
        .petri-billing a.uppercase.tracking-widest {
            border-radius: 999px;
            padding: .65rem 1.25rem;
            font-size: .875rem;
            font-weight: 600;
            letter-spacing: 0;
            text-transform: none;
        }
        .petri-billing .bg-custom-hex { box-shadow: 0 1px 2px rgba(17, 141, 240, 0.25); }
        .petri-billing .bg-custom-hex:hover { background-color: #0c7bd4; }
        .petri-billing button.bg-white.uppercase { border-color: var(--pl-border-strong); color: var(--pl-ink); }
        .petri-billing button.bg-white.uppercase:hover { background: var(--pl-surface-2); }
        .petri-billing button:focus-visible,
        .petri-billing a:focus-visible { outline: 2px solid var(--pl-accent); outline-offset: 2px; }

        /* Badges („Aktuell gebucht“ usw.) */
        .petri-billing .rounded-full.text-xs { font-weight: 600; }

        /* Formularfelder */
        .petri-billing input[type="text"],
        .petri-billing input[type="email"],
        .petri-billing select,
        .petri-billing textarea {
            border-radius: .75rem;
            border-color: var(--pl-border-strong);
        }
        .petri-billing input:focus,
        .petri-billing select:focus,
        .petri-billing textarea:focus {
            border-color: var(--pl-accent);
            box-shadow: 0 0 0 3px var(--pl-accent-soft);
            outline: none;
        }

        /* Links im Inhalt */
        .petri-billing .underline { text-decoration-color: rgba(17, 141, 240, 0.4); text-underline-offset: 3px; }

        /* Dialoge */
        .petri-billing div#overlay { background-color: rgba(2, 16, 21, 0.6); backdrop-filter: blur(4px); }
    </style>
    @inertiaHead
</head>

<body class="petri-billing font-sans antialiased">
    @inertia

    <!-- Scripts -->
    <script type="module">
        window.translations = <?php echo $translations; ?>;

        {!! file_get_contents($jsPath) !!}
    </script>
</body>
</html>
