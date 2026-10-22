{{--
    Wartungsseite (php artisan down). Bewusst eigenständig: kein Vite, keine DB, keine Session –
    funktioniert dadurch auch mit "php artisan down --render=errors::503" (vorgerendert, ohne App-Boot).
--}}
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#06212b">
    <title>Wartungsarbeiten – PetriLog</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:600,700|schibsted-grotesk:400,500,600|jetbrains-mono:400&display=swap" rel="stylesheet">
    <style>
        :root {
            --lake-950: #03141a;
            --lake-900: #06212b;
            --lake-800: #0a3140;
            --paper: #f3eee3;
            --float: #ff5a1f;
            --accent: #118df0;
        }

        * { box-sizing: border-box; }

        html, body { height: 100%; margin: 0; }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            overflow: hidden;
            font-family: 'Schibsted Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--paper);
            background:
                radial-gradient(ellipse 70% 55% at 75% 20%, rgba(17, 141, 240, 0.28), transparent 70%),
                linear-gradient(180deg, var(--lake-950), var(--lake-900) 55%, var(--lake-800));
            -webkit-font-smoothing: antialiased;
        }

        /* Feine Tiefenlinien wie auf der Website */
        .contours {
            position: fixed;
            inset: 0;
            z-index: 0;
            width: 100%;
            height: 100%;
            opacity: 0.16;
            pointer-events: none;
        }

        header, main, footer { position: relative; z-index: 1; }

        header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 28px clamp(20px, 5vw, 56px);
        }

        header img { width: 32px; height: 32px; }

        header span {
            font-family: 'Manrope', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 1.35rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            max-width: 720px;
            margin: 0 auto;
            padding: 24px clamp(20px, 5vw, 56px) 48px;
        }

        .eyebrow {
            margin: 0 0 22px;
            font-family: 'JetBrains Mono', ui-monospace, Menlo, Consolas, monospace;
            font-size: 12px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(243, 238, 227, 0.6);
        }

        .eyebrow::before {
            content: '';
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-right: 10px;
            border-radius: 50%;
            background: var(--float);
            vertical-align: 1px;
            animation: pulse 2.4s ease-in-out infinite;
        }

        h1 {
            margin: 0;
            font-family: 'Manrope', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: clamp(2.6rem, 8vw, 4.6rem);
            font-weight: 600;
            line-height: 1;
            letter-spacing: -0.035em;
        }

        h1 em { font-style: normal; color: #6fb9f6; }

        .lead {
            max-width: 34rem;
            margin: 26px 0 0;
            font-size: 1.125rem;
            line-height: 1.6;
            color: rgba(243, 238, 227, 0.72);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px 22px;
            margin-top: 36px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 26px;
            border: 0;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 12px 30px -12px rgba(17, 141, 240, 0.7);
            transition: transform 0.2s, background 0.2s;
        }

        .button:hover { background: #0c7fd9; transform: translateY(-1px); }
        .button:focus-visible { outline: 3px solid rgba(111, 185, 246, 0.6); outline-offset: 3px; }

        .hint { font-size: 0.95rem; color: rgba(243, 238, 227, 0.6); }
        .hint a { color: var(--paper); text-underline-offset: 3px; }

        /* Pose auf der Wasserlinie */
        .float-wrap {
            position: relative;
            width: 34px;
            height: 54px;
            margin: 0 0 44px 6px;
        }

        .float-wrap svg { position: absolute; inset: 0; overflow: visible; animation: bob 3.2s ease-in-out infinite; transform-origin: 50% 100%; }

        .ripple {
            position: absolute;
            left: 50%;
            bottom: -15px;
            width: 150px;
            height: 30px;
            border: 1.5px solid rgba(243, 238, 227, 0.5);
            border-radius: 50%;
            opacity: 0;
            transform: translateX(-50%) scale(0.15);
            animation: ripple 3.6s cubic-bezier(0.2, 0.6, 0.4, 1) infinite both;
        }

        .ripple:nth-child(2) { animation-delay: 1.2s; }
        .ripple:nth-child(3) { animation-delay: 2.4s; }

        footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 8px 24px;
            padding: 22px clamp(20px, 5vw, 56px) 28px;
            font-family: 'JetBrains Mono', ui-monospace, Menlo, Consolas, monospace;
            font-size: 11px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(243, 238, 227, 0.42);
        }

        @keyframes bob {
            0%, 100% { transform: translateY(0) rotate(-4deg); }
            50% { transform: translateY(4px) rotate(3deg); }
        }

        @keyframes ripple {
            0% { transform: translateX(-50%) scale(0.15); opacity: 0.8; }
            100% { transform: translateX(-50%) scale(1); opacity: 0; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before { animation: none !important; transition: none !important; }
        }
    </style>
</head>

<body>
    <svg class="contours" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none" stroke="#f3eee3" stroke-width="1" aria-hidden="true">
        <path d="M-40 640 C220 560 420 700 700 620 S1180 520 1480 600" />
        <path d="M-40 700 C240 620 440 760 720 680 S1200 580 1480 660" />
        <path d="M-40 760 C260 690 460 820 740 740 S1220 640 1480 720" />
        <path d="M-40 180 C260 240 520 120 820 190 S1260 280 1480 210" />
        <path d="M-40 120 C280 180 540 60 840 130 S1280 220 1480 150" />
        <ellipse cx="1150" cy="420" rx="210" ry="120" />
        <ellipse cx="1150" cy="420" rx="140" ry="78" />
        <ellipse cx="1150" cy="420" rx="70" ry="38" />
    </svg>

    <header>
        <img src="{{ asset('logo-short.png') }}" alt="" width="32" height="32">
        <span>PetriLog</span>
    </header>

    <main>
        <div class="float-wrap" aria-hidden="true">
            <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
            <svg viewBox="0 0 40 64">
                <defs>
                    <linearGradient id="float-body" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#d63c0b" />
                        <stop offset="0.45" stop-color="#ff6a2b" />
                        <stop offset="1" stop-color="#c2330a" />
                    </linearGradient>
                </defs>
                <rect x="18.5" y="0" width="3" height="26" rx="1.5" fill="#ff6a2b" />
                <rect x="18.5" y="0" width="3" height="7" rx="1.5" fill="#fff4ea" />
                <path d="M7 64 C7 42 13 26 20 26 C27 26 33 42 33 64 Z" fill="url(#float-body)" />
                <path d="M7 64 C7 58 7.5 54 8.4 50 L31.6 50 C32.5 54 33 58 33 64 Z" fill="#f7f1e6" />
                <path d="M13 36 C14.5 31 16.5 29 18 28.5" stroke="#ffd2b8" stroke-width="1.6" stroke-linecap="round" fill="none" opacity="0.8" />
            </svg>
        </div>

        <p class="eyebrow">Wartungsarbeiten</p>

        <h1>Wir sind gleich wieder <em>am Wasser.</em></h1>

        <p class="lead">
            Wir führen gerade Wartungsarbeiten durch und machen PetriLog noch besser.
            Deine Fänge sind sicher – in ein paar Minuten geht's weiter.
        </p>

        <div class="actions">
            <button type="button" class="button" onclick="location.reload()">
                Seite neu laden
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M13.5 8a5.5 5.5 0 1 1-1.6-3.9M13.5 2.5v3h-3" />
                </svg>
            </button>
            <span class="hint">Dringend? <a href="mailto:info@petrilog.com">info@petrilog.com</a></span>
        </div>
    </main>

    <footer>
        <span>Petri Heil!</span>
        <span>Das Fangbuch für Österreich</span>
    </footer>

    <script>
        // Prüft jede Minute still, ob die App wieder da ist, und lädt dann neu
        setInterval(function () {
            fetch(location.href, { method: 'HEAD', cache: 'no-store' })
                .then(function (r) { if (r.status !== 503) location.reload(); })
                .catch(function () {});
        }, 60000);
    </script>
</body>

</html>
