<?php

return [
    'name' => 'PetriLog',
    'manifest' => [
        "id" => "com.korbitsch.petrilog",
        'name' => env('APP_NAME', 'PetriLog'),
        'short_name' => 'PetriLog',
        'start_url' => '/login',
        'background_color' => '#ffffff',
        'theme_color' => '#118DF0',
        'display' => 'standalone',
        'orientation' => 'any',
        'status_bar' => 'black',
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
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/images/icons/logo-512.png',
                'purpose' => 'any'
            ],
        ],
        'splash' => [
            '640x1136' => '/images/icons/splash-640x1136.png',
            '750x1334' => '/images/icons/splash-750x1334.png',
            '828x1792' => '/images/icons/splash-828x1792.png',
            '1125x2436' => '/images/icons/splash-1125x2436.png',
            '1242x2208' => '/images/icons/splash-1242x2208.png',
            '1242x2688' => '/images/icons/splash-1242x2688.png',
            '1536x2048' => '/images/icons/splash-1536x2048.png',
            '1668x2224' => '/images/icons/splash-1668x2224.png',
            '1668x2388' => '/images/icons/splash-1668x2388.png',
            '2048x2732' => '/images/icons/splash-2048x2732.png',
        ],
        'custom' => [
            'description' => 'PetriLog ist eine moderne Anwendung für Angler mit der du deine Fänge dokumentieren kannst, inklusive Gewicht, Länge, Fangdatum, Ort und Fotos.',
            "categories" => [
                "entertainment",
                "lifestyle",
                "personalization",
                "photo",
                "social",
                "sports",
                "utilities"
            ]
        ],


    ]
];
