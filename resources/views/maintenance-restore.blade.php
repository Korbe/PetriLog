<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta http-equiv="refresh" content="20">
    <title>Wartungsarbeiten – PetriLog</title>
    <style>
        :root { color-scheme: light dark; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
            background: #06212b; color: #eef1ec;
            font: 16px/1.6 "Schibsted Grotesk", "Segoe UI", system-ui, sans-serif;
        }
        main { max-width: 440px; text-align: center; }
        .spinner {
            width: 44px; height: 44px; margin: 0 auto 28px; border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, .15); border-top-color: #118df0; animation: spin 1s linear infinite;
        }
        h1 { margin: 0 0 12px; font-size: 28px; letter-spacing: -.02em; }
        p { margin: 0 0 10px; color: rgba(238, 241, 236, .72); }
        small { color: rgba(238, 241, 236, .45); }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .spinner { animation: none; } }
    </style>
</head>

<body>
    <main>
        <div class="spinner" aria-hidden="true"></div>
        <h1>Wir sind gleich wieder da</h1>
        <p>PetriLog wird gerade aus einer Datensicherung wiederhergestellt. Das dauert meist nur wenige Minuten.</p>
        <p>Diese Seite lädt sich automatisch neu. Danach bitte erneut anmelden.</p>
        <small>Petri Heil!</small>
    </main>
</body>

</html>
