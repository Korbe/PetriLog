const CACHE_NAME = 'petrilog-cache-v3'; // Version erhöhen bei Updates
const urlsToCache = [
    '/',
    '/app',
    '/images/icons/logo-192.png',
    '/images/icons/logo-512.png',
];

// Installieren: Cache die wichtigen URLs
self.addEventListener('install', event => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
    self.skipWaiting(); // Sofort aktivieren
});

// Aktivieren: Alte Caches löschen
self.addEventListener('activate', event => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim(); // Kontrolle übernehmen
});

// Fetch-Event: immer HTTPS nutzen + Offline-Fallback
self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Optional: dynamisch cachen
                return response;
            })
            .catch(() => {
                // HTML Requests -> Offline-Seite
                if (event.request.headers.get('accept')?.includes('text/html')) {
                    return new Response(
                        `<!DOCTYPE html>
                        <html lang="de">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Offline</title>
                            <style>
                                body { display:flex; justify-content:center; align-items:center; height:100vh; font-family:sans-serif; text-align:center; background-color:#f0f4f8; color:#333; margin:0; padding:1rem; }
                                h1 { font-size:2rem; margin-bottom:1rem; }
                                p { font-size:1.2rem; }
                                button { padding:0.5rem 1rem; font-size:1rem; background-color:#118DF0; color:white; border:none; border-radius:6px; cursor:pointer; margin-top:1rem; }
                            </style>
                        </head>
                        <body>
                            <div>
                                <h1>Oh nein!</h1>
                                <p>Kein Netzwerk verfügbar.</p>
                                <button onclick="location.reload()">Seite neu laden</button>
                            </div>
                        </body>
                        </html>`,
                        { headers: { 'Content-Type': 'text/html' }, status: 503 }
                    );
                }

                // JSON Requests -> einfache Meldung
                return new Response(
                    JSON.stringify({ error: 'Kein Netzwerk verfügbar' }),
                    { status: 503, headers: { 'Content-Type': 'application/json' } }
                );
            })
    );
});