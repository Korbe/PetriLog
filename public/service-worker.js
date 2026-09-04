// Dieser Service Worker cached nichts. Er existiert nur, damit die App als
// PWA installierbar ist. Jede Anfrage geht direkt ans Netzwerk; nur wenn
// kein Netzwerk verfügbar ist, wird ein einfacher Offline-Hinweis geliefert.

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    // Caches aus älteren Service-Worker-Versionen entfernen
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.map(key => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

const OFFLINE_HTML = `<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kein Netzwerk</title>
    <style>
        body { display:flex; justify-content:center; align-items:center; height:100vh; font-family:sans-serif; text-align:center; background-color:#f0f4f8; color:#333; margin:0; padding:1rem; }
        h1 { font-size:2rem; margin-bottom:1rem; }
        p { font-size:1.2rem; }
        button { padding:0.5rem 1rem; font-size:1rem; background-color:#118DF0; color:white; border:none; border-radius:6px; cursor:pointer; margin-top:1rem; }
    </style>
</head>
<body>
    <div>
        <h1>Kein Netzwerk</h1>
        <p>Es besteht aktuell keine Internetverbindung.</p>
        <button onclick="location.reload()">Seite neu laden</button>
    </div>
</body>
</html>`;

self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request).catch(() => {
            if (event.request.headers.get('accept')?.includes('text/html')) {
                return new Response(OFFLINE_HTML, { status: 503, headers: { 'Content-Type': 'text/html' } });
            }

            return new Response(
                JSON.stringify({ error: 'Kein Netzwerk verfügbar' }),
                { status: 503, headers: { 'Content-Type': 'application/json' } }
            );
        })
    );
});