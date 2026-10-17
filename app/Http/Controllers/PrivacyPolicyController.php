<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class PrivacyPolicyController extends Controller
{
    public function show()
    {
        return Inertia::render('Public/Legal/PrivacyPolicy')->withViewData([
            'seo' => Seo::page(
                'Datenschutzerklärung',
                'Welche Daten PetriLog verarbeitet, wofür sie genutzt werden, wie Google Analytics und Google Maps '
                    . 'eingesetzt werden und welche Rechte du nach der DSGVO hast.',
                ['breadcrumb' => true],
            ),
        ]);
    }
}
