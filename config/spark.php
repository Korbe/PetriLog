<?php

use App\Models\User;

return [

    'terms_url' => '/terms-of-service',

    /*
    |--------------------------------------------------------------------------
    | Spark Path
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the URI at which the Spark billing
    | portal is available. You are free to change this URI to a value that
    | you prefer. You shall link to this location from your application.
    |
    */

    'path' => 'billing',

    /*
    |--------------------------------------------------------------------------
    | Spark Middleware
    |--------------------------------------------------------------------------
    |
    | These are the middleware that requests to the Spark billing portal must
    | pass through before being accepted. Typically, the default list that
    | is defined below should be suitable for most Laravel applications.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | These configuration values allow you to customize the branding of the
    | billing portal, including the primary color and the logo that will
    | be displayed within the billing portal. This logo value must be
    | the absolute path to an SVG logo within the local filesystem.
    |
    */

    'brand' =>  [
        //'logo' => realpath(__DIR__.'/../public/logo.svg'),
        'color' => '#118DF0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Proration Behavior
    |--------------------------------------------------------------------------
    |
    | This value determines if charges are prorated when making adjustments
    | to a plan such as incrementing or decrementing the quantity of the
    | plan. This also determines proration behavior if changing plans.
    |
    */

    'prorates' => true,

    /*
    |--------------------------------------------------------------------------
    | Spark Date Format
    |--------------------------------------------------------------------------
    |
    | This date format will be utilized by Spark to format dates in various
    | locations within the billing portal, such as while showing invoice
    | dates. You should customize the format based on your own locale.
    |
    */

    'date_format' => 'd.m.Y',

    /*
    |--------------------------------------------------------------------------
    | Spark Billables
    |--------------------------------------------------------------------------
    |
    | Below you may define billable entities supported by your Spark driven
    | application. The Paddle edition of Spark currently only supports a
    | single billable model entity (team, user, etc.) per application.
    |
    | In addition to defining your billable entity, you may also define its
    | plans and the plan's features, including a short description of it
    | as well as a "bullet point" listing of its distinctive features.
    |
    */

    'billables' => [

        'user' => [
            'model' => User::class,

            'trial_days' => 0,

            'default_interval' => 'annually',

            'plans' => [
                [
                    'name' => 'Jahresabo',
                    'short_description' => 'Alle Funktionen freigeschaltet, um deine Fänge das ganze Jahr über zu dokumentieren und zu teilen.',
                    // 'monthly_id' => env('SPARK_STANDARD_MONTHLY_PLAN', 'pri_1000'),
                    'yearly_id' => env('SPARK_STANDARD_YEARLY_PLAN'),
                    'features' => [
                        'Unbegrenzt Einträge',
                        'Google Maps Integration',
                        'Fotos hochladen',
                        'Fang mit Freunden teilen',
                        '-- Keine extra Kosten'
                    ],
                    'archived' => false,
                ],
            ],

        ],

    ],
];
