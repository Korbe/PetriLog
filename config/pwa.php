<?php

return [
    'name' => env('APP_NAME', 'PetriLog'),
    'short_name' => 'PetriLog',
    'start_url' => '/app',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#118DF0',
    'orientation' => 'any',
    'scope' => '/',
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
    'screenshots' => [
        [
            'src' => '/images/icons/screenshots/Screenshot_1.png',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'platform' => 'wide',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_2.png',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'platform' => 'wide',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_3.png',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'platform' => 'wide',
        ],
        [
            'src' => '/images/icons/screenshots/Screenshot_4.png',
            'sizes' => '1008x2244',
            'type' => 'image/png',
            'platform' => 'wide',
        ],
    ],
    'description' => 'PetriLog ist eine moderne Anwendung für Angler mit der du deine Fänge dokumentieren kannst, inklusive Gewicht, Länge, Fangdatum, Ort und Fotos. Es gibt ein Gewässer Wiki indem alle Information zu Flüssen und Seen in Österreich aufgelistet sind inklusive Vereine.',
    'categories' => [
        'sports',
        'lifestyle',
    ],
];