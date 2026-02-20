<?php

return [
    'name' => 'PetriLog',
    'manifest' => [
        'id' => '/app',
        'name' => env('APP_NAME', 'PetriLog'),
        'short_name' => 'PetriLog',
        'start_url' => '/app',
        'background_color' => '#ffffff',
        'theme_color' => '#118DF0',
        'display' => 'standalone',
        'orientation' => 'any',
        'scope' => '/',
        'icons' => [
            '72x72' => [
                'path' => '/images/icons/logo-72.png',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/images/icons/logo-96.png',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/images/icons/logo-144.png',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/images/icons/logo-152.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/images/icons/logo-192.png',
                'purpose' => 'any maskable'
            ],
            '512x512' => [
                'path' => '/images/icons/logo-512.png',
                'purpose' => 'any maskable'
            ]
        ],
        'screenshots' => [
            [
                'src' => '/images/icons/screenshots/Screenshot_1.png',
                'sizes' => '1008x2244',
                'type' => 'image/png',
                'form_factor' => 'narrow'
            ],
            [
                'src' => '/images/icons/screenshots/Screenshot_2.png',
                'sizes' => '1008x2244',
                'type' => 'image/png',
                'form_factor' => 'narrow'
            ],
            [
                'src' => '/images/icons/screenshots/Screenshot_3.png',
                'sizes' => '1008x2244',
                'type' => 'image/png',
                'form_factor' => 'narrow'
            ],
            [
                'src' => '/images/icons/screenshots/Screenshot_4.png',
                'sizes' => '1008x2244',
                'type' => 'image/png',
                'form_factor' => 'narrow'
            ],
        ],
        'splash' => [
            '640x1136' => '',
            '750x1334' => '',
            '828x1792' => '',
            '1125x2436' => '',
            '1242x2208' => '',
            '1242x2688' => '',
            '1536x2048' => '',
            '1668x2224' => '',
            '1668x2388' => '',
            '2048x2732' => '',
        ],
        'custom' => [
            'description' => 'PetriLog ist eine moderne Anwendung für Angler mit der du deine Fänge dokumentieren kannst, inklusive Gewicht, Länge, Fangdatum, Ort und Fotos. Es gibt ein Gewässer Wiki indem alle Information zu Flüssen und Seen in Österreich aufgelistet sind inklusive Vereine.',
            "categories" => [
                "sports",
                "lifestyle"
            ]
        ],


    ]
];
