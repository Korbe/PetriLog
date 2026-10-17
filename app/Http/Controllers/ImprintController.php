<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class ImprintController extends Controller
{
    public function show()
    {
        return Inertia::render('Public/Legal/Imprint')->withViewData([
            'seo' => Seo::page(
                'Impressum',
                'Impressum von PetriLog: Angaben gemäß § 5 ECG und Offenlegung nach § 25 Mediengesetz – '
                    . 'Ing. Lukas Korbitsch, Villach, Kärnten.',
                ['breadcrumb' => true],
            ),
        ]);
    }
}
