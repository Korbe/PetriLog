<?php

return [
    'id' => "https://petrilog.com/app",
    "lang" => "de",
    "dir" => "ltr",
    'name' => env('APP_NAME', 'PetriLog'),
    'short_name' => 'PetriLog',
    'start_url' => '/app',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#118DF0',
    'orientation' => 'any',
    'prefer_related_applications' => false,
    "related_applications" => [],
    'scope' => '/',
    "display_override" => [
        "window-controls-overlay",
        "standalone",
        "browser"
    ],
    "launch_handler" => [
        "client_mode" => ["focus-existing", "auto"]
    ],
    "scope_extensions" => [],
    'icons' => [
        [
            'src' => '/images/icons/logo-72.png',
            'sizes' => '72x72',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => '/images/icons/logo-96.png',
            'sizes' => '96x96',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => '/images/icons/logo-144.png',
            'sizes' => '144x144',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => '/images/icons/logo-152.png',
            'sizes' => '152x152',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => '/images/icons/logo-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
        [
            'src' => '/images/icons/logo-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
    ],
    // v=2: neues Design (Oktober 2026) – Version erhöhen, wenn die Bilder ersetzt werden (Browser-Cache)
    'screenshots' => [
        [
            'src' => '/images/icons/screenshots/Screenshot_1.png?v=2',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'form_factor' => 'narrow',
            'label' => 'Dashboard mit Fangstatistik',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_2.png?v=2',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'form_factor' => 'narrow',
            'label' => 'Alle Fänge im Fangbuch',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_5.png?v=2',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'form_factor' => 'narrow',
            'label' => 'Fang mit Foto und Fangdaten',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_3.png?v=2',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'form_factor' => 'narrow',
            'label' => 'Fang eintragen: Express oder ausführlich',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_4.png?v=2',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'form_factor' => 'narrow',
            'label' => 'Express-Eintrag direkt am Wasser',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_6.png?v=2',
            'sizes' => '1800x1125',
            'type' => 'image/png',
            'form_factor' => 'wide',
            'label' => 'Dashboard am Computer',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_8.png?v=2',
            'sizes' => '1800x1125',
            'type' => 'image/png',
            'form_factor' => 'wide',
            'label' => 'Fang mit Wetter zum Fangzeitpunkt',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_7.png?v=2',
            'sizes' => '1800x1125',
            'type' => 'image/png',
            'form_factor' => 'wide',
            'label' => 'Spot-Karte mit Heatmap',
        ],
    ],
    'description' => 'PetriLog ist eine moderne Anwendung für Angler mit der du deine Fänge dokumentieren kannst, inklusive Gewicht, Länge, Fangdatum, Ort und Fotos. Es gibt ein Gewässer Wiki indem alle Information zu Flüssen und Seen in Österreich aufgelistet sind inklusive Vereine.',
    'categories' => [
        'sports',
        'lifestyle',
    ],
];
