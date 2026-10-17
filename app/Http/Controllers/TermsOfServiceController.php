<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class TermsOfServiceController extends Controller
{
    public function show()
    {
        return Inertia::render('Public/Legal/TermsOfService')->withViewData([
            'seo' => Seo::page(
                'Nutzungsbedingungen',
                'Die Nutzungsbedingungen von PetriLog: Mitgliedschaft, Preise, Widerrufsrecht, Kündigung, '
                    . 'Nutzungsrechte an deinen Inhalten und Haftung.',
                ['breadcrumb' => true],
            ),
        ]);
    }
}
